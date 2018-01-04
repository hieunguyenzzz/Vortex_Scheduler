<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Controller\Adminhtml\Job;

class Delete extends \Vortex\Scheduler\Controller\Adminhtml\Job
{

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->_objectManager->create('Vortex\Scheduler\Model\Job');
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('Job deleted.'));
                $this->_redirect('scheduler/job/index');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('We can\'t delete this job right now. Please check log file. '.$e->getMessage())
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_redirect('scheduler/job/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->_redirect('scheduler/job/index');
    }
}
