<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;

/**
 * An Abstract class for support tool related commands.
 */
class Scheduler extends Command
{

    /**
     * Object manager factory
     *
     * @var ObjectManagerFactory
     */
    protected $_objectManagerFactory;

    /**
     *
     * @var \Vortex\Scheduler\Model\ScheduleFactory
     */
    protected $_scheduleFactory;

    /**
     *
     * @var \Vortex\Scheduler\Model\Config
     */
    protected $_config;

    /**
     * Name of input group option
     */
    const INPUT_KEY_GROUP = 'group';

    /**
     * Name of input job option
     */
    const INPUT_KEY_EXCLUDE_GROUP = 'exclude-group';


    /**
     * @param \Vortex\Scheduler\Model\ScheduleFactory $scheduleFactory
     * @param \Vortex\Scheduler\Model\Config $config
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(
        \Vortex\Scheduler\Model\ScheduleFactory $scheduleFactory,
        \Vortex\Scheduler\Model\Config $config,
        ObjectManagerFactory $objectManagerFactory
    ) {
        $this->_objectManagerFactory = $objectManagerFactory;
        $this->_scheduleFactory = $scheduleFactory;
        $this->_config = $config;
        parent::__construct();
    }

    /**
     * Define the shell command
     * @return void
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                self::INPUT_KEY_GROUP,
                null,
                InputOption::VALUE_OPTIONAL,
                'Run jobs only from specified group'
            ),
            new InputOption(
                self::INPUT_KEY_EXCLUDE_GROUP,
                null,
                InputOption::VALUE_OPTIONAL,
                'Run jobs exclude group'
            ),
        ];
        $this->setName('scheduler:run')
            ->setDescription('Run scheduler via groups or jobs')
            ->setDefinition($options);
        parent::configure();
    }

    /**
     * Execute function
     * @param InputInterface  $input  Input
     * @param OutputInterface $output Output
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return null|int null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $groups = $input->getOption(self::INPUT_KEY_GROUP);
        $excludeGroups = $input->getOption(self::INPUT_KEY_EXCLUDE_GROUP);

        if (!$groups && !$excludeGroups) {
            $output->writeln('<info>' . 'Please update groups or exclude-groups parameter.' . '</info>');
        } elseif($groups && $excludeGroups) {
            $output->writeln('<info>' . 'Please use only one option groups or exclude-groups.' . '</info>');
        } else {
            if ($groups) {
                $this->getJobsAndRun($groups);
            }
            if ($excludeGroups) {
                $this->getJobsAndRun($excludeGroups, true);
            }
        }
    }

    /**
     * Get jobs and run
     *
     * @param $groups
     * @param bool|false $exclude
     */
    public function getJobsAndRun($groups, $exclude = false)
    {
        $groups = explode(',', $groups);
        $jobs = $this->_config->getJobs();
        foreach ($jobs as $group => $jobGroups) {
            foreach ($jobGroups as $jobCode => $job) {
                if ($exclude && !in_array($group, $groups)) {
                    $this->runIt($jobCode);
                } elseif ($exclude == false && in_array($group, $groups)) {
                    $this->runIt($jobCode);
                }
            }
        }
    }

    /**
     * Run job via job code
     *
     * @param $jobCode
     * @return bool
     */
    protected function runIt($jobCode)
    {
        $omParams = $_SERVER;
        $omParams[StoreManager::PARAM_RUN_CODE] = 'admin';
        $omParams[Store::CUSTOM_ENTRY_POINT_PARAM] = true;
        $objectManager = $this->_objectManagerFactory->create($omParams);
        $item = $objectManager->create('Vortex\Scheduler\Model\Job');
        $item->load($jobCode);

        if ($item->getId()) {
            $key = $item->getName();

            $schedule = $this->_scheduleFactory->create()
                ->initializeFromJob($item)
                ->setJob($item)
                ->setScheduledReason("Run From Admin");

            $schedule->runNow(false)->save();

            $messages = $schedule->getMessages();

            if (
            in_array(
                $schedule->getStatus(),
                [
                    \Vortex\Scheduler\Model\Schedule::STATUS_SUCCESS,
                    \Vortex\Scheduler\Model\Schedule::STATUS_DIDNTDOANYTHING
                ]
            )
            ) {
                $msgs = __('Ran %1 (Duration: %2 sec)', $key, intval($schedule->getDuration()));
            } else {
                $msgs = __('Error while running %1', $key);
            }
            echo $msgs.PHP_EOL;
            
            return $item;
        } else {
            return false;
        }
    }
}