<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Controller\Adminhtml\Listview;

class Edit extends \Vortex\Scheduler\Controller\Adminhtml\Listview
{

    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Vortex_Scheduler::vortex_scheduler_cronjobs_listview');

        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Magento\Cron\Model\Schedule');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                $this->_redirect('scheduler/listview/index');
                return;
            }
            $header = __("Edit Schedule '%1'", $model->getJobCode());
            $resultPage->getConfig()->getTitle()->prepend($header);
        } else {
            $header = __('Add New Schedule');
            $resultPage->getConfig()->getTitle()->prepend($header);
        }
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $this->_coreRegistry->register('current_vortex_scheduler_listview', $model);
        $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend($header);
        $this->_view->renderLayout();
    }
}
