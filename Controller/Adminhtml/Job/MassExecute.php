<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Controller\Adminhtml\Job;

class MassExecute extends MassAbstract
{
    /**
     *
     * @var \Vortex\Scheduler\Model\ScheduleFactory
     */
    protected $scheduleFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Vortex\Scheduler\Model\JobFactory $modelFactory,
        \Vortex\Scheduler\Model\ScheduleFactory $scheduleFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct(
            $context,
            $resultPageFactory,
            $registry,
            $modelFactory,
            $logger
        );
        $this->scheduleFactory = $scheduleFactory;
    }

    protected function itemOperation(\Vortex\Scheduler\Model\Job $item)
    {
        $key = $item->getName();

        $schedule = $this->scheduleFactory->create()
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
            $msgs = __('Ran %1 (Duration: %2 sec)', $key, (int)$schedule->getDuration());
            if ($messages) {
                $msgs .= '<br>'.__('%1 messages:<pre>%2</pre>', $key, $messages);
            }

            $this->messageManager->addSuccess($msgs);
        } else {
            $msgs = __('Error while running %1', $key);
            if ($messages) {
                $msgs .= '<br>'.__('%1 messages:<pre>%2</pre>', $key, $messages);
            }

            $this->messageManager->addError($msgs);
        }

        return $item;
    }
}
