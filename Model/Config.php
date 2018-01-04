<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Model;

class Config extends \Magento\Cron\Model\Config
{

    /**
     * Cron config data
     *
     * @var \Magento\Cron\Model\Config\Data
     */
    protected $_configData;

    public function __construct(
        \Magento\Cron\Model\Config\Data $configData
    ) {
        $this->_configData = $configData;
        parent::__construct($configData);
    }

    /**
     * Get All Enable Jobs
     * @return array
     */
    public function getJobs()
    {
        $jobs = parent::getJobs();
        $listJobs = array();
        $newJobs = array();
        foreach ($jobs as $group => $jobGroups) {
            foreach ($jobGroups as $jobCode => $job) {
                $enable = 1;
                if (isset($job['enabled'])) {
                    $enable = $job['enabled'];
                }
                if (isset($listJobs[$jobCode])) {
                    $findJobs = $this->_configData->get($group);
                    if (is_array($findJobs)) {
                        if (isset($findJobs[$jobCode])) {
                            if ($enable) {
                                $newJobs[$group][$jobCode] = $job;
                            }
                            unset($newJobs[$listJobs[$jobCode]][$jobCode]);
                        }
                    }
                } elseif ($enable) {
                    $newJobs[$group][$jobCode] = $job;
                }
                $listJobs[$jobCode] = $group;
            }
        }
        return $newJobs;
    }

    /**
     * Get All Jobs
     *
     * @return array
     */
    public function getAllJobs()
    {
        $jobs = parent::getJobs();
        $listJobs = array();
        $newJobs = array();
        foreach ($jobs as $group => $jobGroups) {
            foreach ($jobGroups as $jobCode => $job) {
                if (isset($listJobs[$jobCode])) {
                    $findJobs = $this->_configData->get($group);
                    if (is_array($findJobs)) {
                        if (isset($findJobs[$jobCode])) {
                            $newJobs[$group][$jobCode] = $job;
                            unset($newJobs[$listJobs[$jobCode]][$jobCode]);
                        }
                    }
                } else {
                    $newJobs[$group][$jobCode] = $job;
                }
                $listJobs[$jobCode] = $group;
            }
        }
        return $newJobs;
    }
}
