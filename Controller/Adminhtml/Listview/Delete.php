<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Controller\Adminhtml\Listview;

class Delete extends \Vortex\Scheduler\Controller\Adminhtml\Listview
{

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->_objectManager->create('Vortex\Scheduler\Model\Listview');
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('Item deleted.'));
                $this->_redirect('scheduler/listview/index');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('We can\'t delete this item right now. Please check log file. '.$e->getMessage())
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_redirect('scheduler/listview/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->messageManager->addError(__('This item is removed by magento default or We can\'t find a item to delete.'));
        $this->_redirect('scheduler/listview/');
    }
}
