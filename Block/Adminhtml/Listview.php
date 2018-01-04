<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Block\Adminhtml;

class Listview extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'listview';
        $this->_headerText = __('List View');
        $this->_addButtonLabel = __('Add Schedule');
        parent::_construct();
    }
}
