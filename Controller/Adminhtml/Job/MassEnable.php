<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Controller\Adminhtml\Job;

class MassEnable extends MassAbstract
{
    protected function itemOperation(\Vortex\Scheduler\Model\Job $item)
    {
        return $item->enable();
    }
}
