<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Controller\Adminhtml\Job;

use Vortex\Scheduler\Controller\Adminhtml;

class Index extends \Vortex\Scheduler\Controller\Adminhtml\Job
{
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $this->_initAction();
        $resultPage->addBreadcrumb(
            __('Manage Cron Jobs'),
            __('Manage Cron Jobs')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Jobs Avaiable'));
        return $resultPage;
    }
}
