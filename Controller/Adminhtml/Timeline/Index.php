<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Controller\Adminhtml\Timeline;

class Index extends \Magento\Backend\App\Action
{
    /**
     * Preview Newsletter template
     *
     * @return void|$this
     * @throws \RuntimeException
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Cronjob timeline'));
        $this->_view->renderLayout();
    }
}
