<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Model\Config\Converter;

/**
 * Convert data incoming from data base storage
 */
class Db extends \Magento\Cron\Model\Config\Converter\Db
{
    /**
     * Extract and prepare cron job data
     *
     * @param array $cronTab
     * @return array
     */
    protected function _extractParams(array $cronTab)
    {
        $result = [];
        foreach ($cronTab as $groupName => $groupConfig) {
            $jobs = $groupConfig['jobs'];
            foreach ($jobs as $jobName => $value) {
                $result[$groupName][$jobName] = $value;

                if (isset($value['schedule']) && is_array($value['schedule'])) {
                    $this->_processConfigParam($value, $jobName, $result[$groupName]);
                    $this->_processScheduleParam($value, $jobName, $result[$groupName]);
                    $this->_processStatusParam($value, $jobName, $result[$groupName]);

                }

                $this->_processRunModel($value, $jobName, $result[$groupName]);
            }
        }
        return $result;
    }

    /**
     * Fetch parameter 'cron_expr' from 'schedule' container, reassign it
     *
     * @param array  $jobConfig
     * @param string $jobName
     * @param array  $result
     * @return void
     */
    protected function _processStatusParam(array $jobConfig, $jobName, array &$result)
    {
        if (isset($jobConfig['enabled'])) {
            $result[$jobName]['enabled'] = $jobConfig['enabled'];
        }
    }
}
