<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Controller\Adminhtml\Job;

use Magento\Backend\App\Action\Context;

abstract class MassAbstract extends \Vortex\Scheduler\Controller\Adminhtml\Job
{
    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $_log;
    /**
     *
     * @var \Vortex\Scheduler\Model\JobFactory
     */
    protected $_factory;

    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Vortex\Scheduler\Model\JobFactory $modelFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context, $resultPageFactory, $registry);
        $this->_log = $logger;
        $this->_factory = $modelFactory;
    }

    public function execute()
    {
        $selected = $this->getSelected();
        try {
            if (!empty($selected)) {
                $this->setArray($selected);
            } else {
                $this->messageManager->addError(__('Please select item(s).'));
            }
        } catch (\Exception $e) {
            $this->_log->critical($e->getMessage());
            $this->messageManager->addError(__('Operation error'));
        }
        return $this->_redirect('scheduler/job/index');
    }

    protected function setArray($items)
    {
        $errMsg = [];
        foreach ($items as $id) {
            $job = $this->_factory->create();
            try {
                $job->load($id);
                if ($job->getId()) {
                    $this->itemOperation($job)->save($job);
                }
            } catch (\Exception $e) {
                $err = true;
                $this->_log->critical($e->getMessage());
                $errMsg[] = __($e->getMessage());
            }
        }
        if (!empty($err)) {
            $this->messageManager
                ->addError(implode('<br>', $errMsg));
        }
    }

    protected function getSelected()
    {
        $param = $this->getRequest()->getParam('selected');
        if (!is_array($param)) {
            $param = explode(',', $param);
        }
        return $param;
    }
    /**
     * Action on each item of mass action
     * @param \Vortex\Scheduler\Model\Job $item
     * @return \Vortex\Scheduler\Model\job
     */
    abstract protected function itemOperation(\Vortex\Scheduler\Model\Job $item);

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
