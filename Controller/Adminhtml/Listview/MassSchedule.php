<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Controller\Adminhtml\Listview;

class MassSchedule extends \Vortex\Scheduler\Controller\Adminhtml\Listview
{

    public function execute()
    {
        $ids = $this->getRequest()->getParam('schedule_ids');
        $model = $this->_objectManager->create('Magento\Cron\Model\Schedule');
        $tz = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        foreach ($ids as $id){
            $model->load($id);
            $code = $model->getJobCode();
            $newModel = $this->_objectManager->create('Magento\Cron\Model\Schedule');
            $newModel->setJobCode($code);
            $newModel->setCreatedAt(strftime('%Y-%m-%d %H:%M:%S', $tz->scopeTimeStamp()));
            $newModel->setScheduledAt(strftime('%Y-%m-%d %H:%M:00', $tz->scopeTimeStamp()+60));
            $newModel->save();
        }
        $this->messageManager->addSuccess(__('Schedules Added.'));
        $this->_redirect('scheduler/listview/index');
    }
}