<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Controller\Adminhtml\Listview;

class MassDelete extends \Vortex\Scheduler\Controller\Adminhtml\Listview
{

    public function execute()
    {
        $ids = $this->getRequest()->getParam('schedule_ids');
        $model = $this->_objectManager->create('Magento\Cron\Model\Schedule');
        foreach ($ids as $id){
            $model->load($id);
            if ($model->getId()) {
                $model->delete();
            }
        }
        $this->messageManager->addSuccess(__('Schedules Deleted.'));
        $this->_redirect('scheduler/listview/index');
    }
}