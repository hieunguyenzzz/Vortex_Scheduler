<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Model;

use Vortex\Scheduler\Helper\Run;

class Schedule extends \Magento\Cron\Model\Schedule
{
    const STATUS_SKIP_LOCKED = 'locked';
    const STATUS_SKIP_OTHERJOBRUNNING = 'other_job_running';
    const STATUS_SKIP_WRONGUSER = 'wrong_user';
    const STATUS_SKIP_PILINGUP = 'skipped';
    const STATUS_REPEAT = 'repeat';
    const STATUS_DISAPPEARED = 'gone';
    const STATUS_DIDNTDOANYTHING = 'nothing';
    const STATUS_KILLED = 'killed';
    const STATUS_DIED = 'died'; // note that died != killed

    /**
     * @var bool
     */
    protected $jobWasLocked = false;

    /**
     * Placeholder to keep track of active redirect buffer.
     *
     * @var bool
     */
    protected $_redirect = false;
    protected $objectManager;
    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $log;

    /**
     * The buffer will be flushed after any output call which causes
     * the buffer's length to equal or exceed this value.
     *
     * Prior to PHP 5.4.0, the value 1 set the chunk size to 4096 bytes.
     */
    protected $_redirectOutputHandlerChunkSize = 100; // bytes

    /**
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->objectManager = $objectManager;
    }

    /**
     * Initialize from job
     *
     * @param \Vortex\Scheduler\Model\Job
     * @return $this
     */
    public function initializeFromJob(\Vortex\Scheduler\Model\Job $job)
    {
        $this->setJobCode($job->getName());
        $expr = $job->getSchedule();
        $this->setCronExpr($expr ? $expr : '* * * * *');
        $this->setStatus(self::STATUS_PENDING);
        return $this;
    }

    /**
     * Run this task now
     *
     * @param bool $tryLockJob
     * @param bool $forceRun
     * @return $this
     */
    public function runNow($tryLockJob = true)
    {
        $tries = 5;
        while ($this->_registry->registry('currently_running_schedule')) {
            if ($tries <= 0) {
                break;
            }

            $tries--;
            sleep(5);
        }

        if ($this->_registry->registry('currently_running_schedule')) {
            return $this;
        }

        // if this schedule doesn't exist yet, create it
        if (!$this->getCreatedAt()) {
            $this->schedule();
        }

        // lock job requires the record to be saved and having status self::STATUS_PENDING
        // workaround could be to do this: $this->setStatus(self::STATUS_PENDING)->save();
        $this->jobWasLocked = false;
        if ($tryLockJob && !$this->tryLockJob()) {
            $this->setStatus(self::STATUS_SKIP_LOCKED);
            // another cron started this job intermittently, so skip it
            $this->jobWasLocked = true;
            $this->_logger->log(
                'Info',
                __(
                    'Job "%1" (id: %1) is locked. Skipping.',
                    $this->getJobCode(),
                    $this->getId()
                )
            );
            return $this;
        }

        try {
            // Track the last user to run a job
            // $this->setLastRunUser();

            $job = $this->getJob();

            if (!$job) {
                throw new \Exception(__(
                    "Could not create job with jobCode '%1'",
                    $this->getJobCode()
                ));
            }

            $startTime = time();
            $this
                ->setExecutedAt(strftime('%Y-%m-%d %H:%M:%S', $startTime))
                ->setLastSeen(strftime('%Y-%m-%d %H:%M:%S', $startTime))
                ->setStatus(self::STATUS_RUNNING)
                ->setHost(gethostname())
                ->setPid(getmypid())
                ->save();

            Run::configure($this->_registry);

            $this->_registry->register('currently_running_schedule', $this);

            $this->_eventManager->dispatch(
                'cron_' . $this->getJobCode() . '_before',
                ['schedule' => $this]
            );
            $this->_eventManager->dispatch('cron_before', ['schedule' => $this]);

            $this->_registry->unregister('current_cron_task');
            $this->_registry->register('current_cron_task', $this);

            $this->_logger->log('Info', 'Start: ' . $this->getJobCode());

            $this->_startBufferToMessages();
            $this->jobErrorContext();

            $callback = [$this->objectManager->create($job->getInstance()),
                $job->getMethod()];
            try {
                // this is where the magic happens
                $messages = call_user_func_array($callback, [$this]);
                $this->restoreErrorContext();
                $this->_stopBufferToMessages();
            } catch (\Exception $e) {
                $this->restoreErrorContext();
                $this->_stopBufferToMessages();
                throw $e;
            }

            $this->_logger->log('Info', 'Stop: ' . $this->getJobCode());

            if (!empty($messages)) {
                if (is_object($messages)) {
                    $messages = get_class($messages);
                } elseif (!is_scalar($messages)) {
                    $messages = var_export($messages, 1);
                }

                $this->addMessages(PHP_EOL . 'RETURN_VALUE: ' . PHP_EOL . $messages);
            }

            // schedules can report an error state by returning a string that starts with "ERROR:", "NOTHING", or "REPEAT"
            // or they can set set the status directly to the schedule object that's passed as a parameter
            if ((is_string($messages) && strtoupper(substr($messages, 0, 6)) == 'ERROR:')
                || $this->getStatus() === self::STATUS_ERROR
            ) {
                $this->setStatus(self::STATUS_ERROR);
                #TODO: send mail
                $this->_eventManager->dispatch(
                    'cron_' . $this->getJobCode() . '_after_error',
                    ['schedule' => $this]
                );
                $this->_eventManager->dispatch('cron_after_error', ['schedule' => $this]);
            } elseif (
                (is_string($messages) && strtoupper(substr($messages, 0, 7)) == 'NOTHING')
                || $this->getStatus() === self::STATUS_DIDNTDOANYTHING
            ) {
                $this->setStatus(self::STATUS_DIDNTDOANYTHING);
                $this->_eventManager->dispatch(
                    'cron_' . $this->getJobCode() . '_after_nothing',
                    ['schedule' => $this]
                );
                $this->_eventManager->dispatch('cron_after_nothing', ['schedule' => $this]);
            } elseif (
                (is_string($messages) && strtoupper(substr($messages, 0, 6)) == 'REPEAT')
                || $this->getStatus() === self::STATUS_REPEAT
            ) {
                $this->setStatus(self::STATUS_REPEAT);
                $this->_eventManager->dispatch(
                    'cron_' . $this->getJobCode() . '_after_repeat',
                    ['schedule' => $this]
                );
                $this->_eventManager->dispatch('cron_after_repeat', ['schedule' => $this]);
            } else {
                $this->setStatus(self::STATUS_SUCCESS);
                $this->_eventManager->dispatch(
                    'cron_' . $this->getJobCode() . '_after_success',
                    ['schedule' => $this]
                );
                $this->_eventManager->dispatch('cron_after_success', ['schedule' => $this]);
            }

        } catch (\Exception $e) {
            $this->setStatus(self::STATUS_ERROR);
            $this->addMessages(PHP_EOL . 'EXCEPTION' . PHP_EOL . $e->__toString());
            $this->_eventManager->dispatch(
                'cron_' . $this->getJobCode() . '_exception',
                ['schedule' => $this, 'exception' => $e]
            );
            $this->_eventManager->dispatch(
                'cron_exception',
                ['schedule' => $this, 'exception' => $e]
            );
            #TODO: send error mail
        }

        $this->setFinishedAt(strftime('%Y-%m-%d %H:%M:%S', time()));
        // $this->setMemoryUsage(memory_get_peak_usage(true) / pow(1024, 2));  // convert bytes to megabytes
        $this->_eventManager->dispatch('cron_' . $this->getJobCode() . '_after', ['schedule' => $this]);
        $this->_eventManager->dispatch('cron_after', ['schedule' => $this]);

        $this->save();
        $this->_registry->unregister('currently_running_schedule');

        return $this;
    }

    /**
     * Switch the job error context
     */
    protected function jobErrorContext()
    {

    }

    protected function restoreErrorContext()
    {

    }

    /**
     * Get job duration.
     *
     * @return bool|int time in seconds, or false
     */
    public function getDuration()
    {
        $duration = false;
        if ($this->getExecutedAt() && ($this->getExecutedAt() != '0000-00-00 00:00:00')) {
            if ($this->getFinishedAt() && ($this->getFinishedAt() != '0000-00-00 00:00:00')) {
                $time = strtotime($this->getFinishedAt());
            } elseif ($this->getStatus() == self::STATUS_RUNNING) {
                $time = time();
            } else {
                return false;
            }
            $duration = $time - strtotime($this->getExecutedAt());
        }
        return $duration;
    }

    /**
     * Schedule this task to be executed at a given time
     *
     * @param int $time
     * @return \Vortex\Scheduler\Model\Schedule
     */
    public function schedule($time = null)
    {
        if (is_null($time)) {
            $time = time();
        }
        $this->setStatus(self::STATUS_PENDING)
            ->setCreatedAt(strftime('%Y-%m-%d %H:%M:%S', time()))
            ->setScheduledAt(strftime('%Y-%m-%d %H:%M:00', $time))
            ->save();
        return $this;
    }

    /**
     * Redirect all output to the messages field of this Schedule.
     * We use ob_start with `_addBufferToMessages` to redirect the output.
     *
     * @return $this
     */
    protected function _startBufferToMessages()
    {
        if ($this->_redirect) {
            return $this;
        }

        $this->addMessages('| START CRON |' . PHP_EOL);

        ob_start(
            [$this, '_addBufferToMessages'],
            $this->_redirectOutputHandlerChunkSize
        );

        $this->_redirect = true;

        return $this;
    }

    /**
     * Stop redirecting all output to the messages field of this Schedule.
     * We use ob_end_flush to stop redirecting the output.
     *
     * @return $this
     */
    protected function _stopBufferToMessages()
    {
        if (!$this->_redirect) {
            return $this;
        }

        ob_end_flush();
        $this->addMessages('| STOP CRON |' . PHP_EOL);

        $this->_redirect = false;

        return $this;
    }

    /**
     * Used as callback function to redirect the output buffer
     * directly into the messages field of this schedule.
     *
     * @param $buffer
     *
     * @return string
     */
    public function _addBufferToMessages($buffer)
    {
        $this->addMessages($buffer)
            ->saveMessages(); // Save the directly to the schedule record.

        return $buffer;
    }

    /**
     * Append data to the current messages field.
     *
     * @param $messages
     *
     * @return $this
     */
    public function addMessages($messages)
    {
        $this->setMessages($this->getMessages() . $messages);

        return $this;
    }

    /**
     * Save message
     *
     * @return $this
     */
    public function saveMessages()
    {
        return $this->save();
    }
}
