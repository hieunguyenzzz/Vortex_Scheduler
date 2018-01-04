<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Controller\Adminhtml\Job;

use Vortex\Scheduler\Api\Data\JobInterface;

class Schedule extends \Vortex\Scheduler\Controller\Adminhtml\Job
{
    /**
     *
     * @var \Vortex\Scheduler\Model\ScheduleFactory
     */
    protected $_scheduleFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Vortex\Scheduler\Model\ScheduleFactory $scheduleFactory,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context, $resultPageFactory, $registry);
        $this->_scheduleFactory = $scheduleFactory;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam(JobInterface::JOB_ID);
        $model = $this->_objectManager->create('Vortex\Scheduler\Model\Job');
        $errMsg = [];
        try {
            if ($id) {
                $model->load($id);
                if (!$model->getId()) {
                    $errMsg[] = __('This job ID(%1) no longer exists.', $id);
                } else {
                    $this->scheduleIt($model);
                }
            }
        } catch (\Exception $e) {
            $errMsg[] = __($e->getMessage());
        }

        if (!empty($errMsg)) {
            $this->messageManager
                ->addError(implode('<br>', $errMsg));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('scheduler/job/index');
    }

    /**
     *
     * @param \Vortex\Scheduler\Model\Job $job
     */
    protected function scheduleIt(\Vortex\Scheduler\Model\Job $job)
    {
        $schedule = $this->_scheduleFactory->create()
            ->initializeFromJob($job)
            ->setJob($job)
            ->setScheduledReason("Admin Scheduled")
            ->schedule()
            ->save();

        $this->messageManager
            ->addSuccess(__('Cron Job "%1" scheduled.', $job->getName()));
    }
}
