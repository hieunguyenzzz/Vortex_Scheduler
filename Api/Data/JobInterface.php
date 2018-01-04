<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Api\Data;

interface JobInterface
{
    const TABLE_NAME = 'core_config_data';

    const JOB_ID = 'id';
    const JOB_NAME = 'name';
    const JOB_CLASS = 'instance';
    const JOB_METHOD = 'method';
    const JOB_SCHEDULE = 'schedule';
    const JOB_CONFIG = 'config_path';
    const JOB_GROUP = 'group';
    const JOB_ENABLED = 'enabled';

    public function isEnabled();
    public function enable();
    public function disable();
}
