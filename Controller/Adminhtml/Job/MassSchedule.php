<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Controller\Adminhtml\Job;

class MassSchedule extends MassAbstract
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
        $schedule = $this->scheduleFactory->create()
            ->initializeFromJob($item)
            ->setJob($item)
            ->setScheduledReason("Scheduled From Admin")
            ->schedule()
            ->save();

        $this->messageManager
            ->addSuccess(__('Job "%1" has been scheduled.', $item->getName()));
        return $item;
    }
}
