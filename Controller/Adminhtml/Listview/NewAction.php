<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Controller\Adminhtml\Listview;

class NewAction extends \Vortex\Scheduler\Controller\Adminhtml\Listview
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
