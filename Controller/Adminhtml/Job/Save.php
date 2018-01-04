<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Controller\Adminhtml\Job;

use Vortex\Scheduler\Api\Data\JobInterface;

class Save extends \Vortex\Scheduler\Controller\Adminhtml\Job
{
    public function execute()
    {
        $redirectBack = $this->getRequest()->getParam('back', false);
        $data = $this->getRequest()->getParams();
        $id = $this->getRequest()->getParam(JobInterface::JOB_ID, false);

        try {
            if ($id) {
                /* @var $model \Vortex\Scheduler\Model\Job */
                $model = $this->_objectManager
                    ->create('Vortex\Scheduler\Model\Job');
                $model->load($id);
                if ($model->getId()) {
                    if (!isset($data['group'])) {
                        $data['group'] = $model->getGroup();
                    } elseif ($data['group'] == '') {
                        $data['group'] = $model->getGroup();
                    }
                    if ($data['group'] != $model->getGroup()) {
                        $model->delete();
                        $model = $this->_objectManager
                            ->create('Vortex\Scheduler\Model\Job');
                    }
                    $model->setData($data)->save();
                    $name = $model->getJobCode();
                    $this->messageManager->addSuccess(
                        __('Job %1 saved', $name)
                    );
                }
            } else {
                $model = $this->_objectManager
                    ->create('Vortex\Scheduler\Model\Job');
                $model->setData($data)->save();
                $name = $model->getJobCode();
                $this->messageManager->addSuccess(
                    __('Job %1 saved', $name)
                );
            }
        } catch (\Exception $e) {
            $this->log->critical($e->getMessage());
            $this->messageManager->addError(__('Operation error'));
            $this->_session->setFormData($data);
            return $this->_redirect('scheduler/job/edit', ['id' => $id, 'back' => $redirectBack, '_current' => true]);
        }

        return $this->_redirect('scheduler/job/index');
    }
}
