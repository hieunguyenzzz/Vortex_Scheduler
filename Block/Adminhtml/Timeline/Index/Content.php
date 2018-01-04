<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Block\Adminhtml\Timeline\Index;

/**
 * Adminhtml customer view personal information sales block.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Content extends \Magento\Backend\Block\Template
{
    /** @var \Vortex\Scheduler\Helper\CronData */
    protected $cronData;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Vortex\Scheduler\Helper\CronData $cronData
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Vortex\Scheduler\Helper\CronData $cronData,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->cronData = $cronData;
    }

    public function getCronjobData()
    {
        return $this->cronData->getCronjobData();
    }
    
    public function getCronjobGroups()
    {
        return $this->cronData->getCronjobGroups();
    }
}
