<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Controller\Adminhtml\Listview;

class Save extends \Vortex\Scheduler\Controller\Adminhtml\Listview
{
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                $model = $this->_objectManager->create('Magento\Cron\Model\Schedule');
                $data = $this->getRequest()->getPostValue();
                $inputFilter = new \Zend_Filter_Input(
                    [],
                    [],
                    $data
                );

                $data = $inputFilter->getUnescaped();
                $id = $data['schedule_id'];

                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('The wrong item is specified.'));
                    }
                }else{
                    unset ($data['schedule_id']);
                    $tz = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
                    $data['created_at'] = strftime('%Y-%m-%d %H:%M:%S', $tz->scopeTimeStamp());
                    $data['scheduled_at'] = strftime('%Y-%m-%d %H:%M:00', $tz->scopeTimeStamp()+60);
                }
                $model->setData($data);
                $session = $this->_objectManager->get('Magento\Backend\Model\Session');
                $session->setPageData($model->getData());
                $model->save();
                $this->messageManager->addSuccess(__('You saved the schedule.'));
                $session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('scheduler/listview/edit', ['id' => $model->getId()]);
                    return;
                }
                $this->_redirect('scheduler/listview/index');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('id');
                if (!empty($id)) {
                    $this->_redirect('scheduler/listview/edit', ['id' => $id]);
                } else {
                    $this->_redirect('scheduler/listview/new');
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Something wrong. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                $this->_redirect('scheduler/listview/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->_redirect('scheduler/listview/index');
    }
}
