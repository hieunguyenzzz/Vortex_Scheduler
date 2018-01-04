<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Controller\Adminhtml\Job;

use Vortex\Scheduler\Api\Data\JobInterface;

class Execute extends \Vortex\Scheduler\Controller\Adminhtml\Job
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
                    $this->runIt($model);
                }
            }
        } catch (\Exception $e) {
            $errMsg[] = __($e->getMessage());
        }

        if (!empty($errMsg)) {
            $this->messageManager
                ->addError(implode('<br>', $errMsg));
        } else {
            $this->messageManager
                ->addSuccess(__('Job %1 updated', $model->getName()));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('scheduler/job/index');
    }

    protected function runIt(\Vortex\Scheduler\Model\Job $item)
    {
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

    /**
     * Check if admin has permissions to visit related pages
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization
            ->isAllowed('Vortex_Scheduler::vortex_scheduler_cronjobs_job');
    }
}
