<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Controller\Adminhtml\Job;

use Vortex\Scheduler\Api\Data\JobInterface;

class Edit extends \Vortex\Scheduler\Controller\Adminhtml\Job
{
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();

        $this->setupModel();

        $this->_initAction();
        $resultPage->addBreadcrumb(
            __('Edit Job'),
            __('Edit Job')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Cron Job'));
        return $resultPage;
    }

    protected function setupModel()
    {
        $id = $this->getRequest()->getParam(JobInterface::JOB_ID);
        $model = $this->_objectManager->create('Vortex\Scheduler\Model\Job');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager
                    ->addError(__('This job (%1) not exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('scheduler/job/index');
            }
        }

        // 3. Set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')
            ->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        $this->_coreRegistry->register('edit_job', $model);
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
