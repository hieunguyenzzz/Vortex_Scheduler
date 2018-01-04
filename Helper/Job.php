<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Helper;

use Magento\Cron\Model\ConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;

class Job extends AbstractHelper implements ConfigInterface
{

    /**
     *
     * @var array
     */
    protected $jobs;
    /**
     * Cron config data
     *
     * @var \Magento\Cron\Model\Config\Data
     */
    protected $_configData;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Vortex\Scheduler\Model\Config $configData
    ) {
        parent::__construct($context);
        $this->_configData = $configData;

    }

    public function getJobCodes($group = null)
    {
        $codes = [];
        $jobs = $this->getJobs($group);
        if (null === $group) {
            foreach ($jobs as $groupJobs) {
                $codes = array_merge($codes, array_keys($groupJobs));
            }
        } else {
            $codes = array_keys($jobs);
        }

        // Sort
        sort($codes);

        return $codes;
    }

    /**
     *
     * @param string $group Cron job group name. Returns all by default
     */
    public function getJobs($group = null)
    {
        if (null === $this->jobs) {
            $this->jobs = $this->_configData->getAllJobs();
        }

        if (null === $group) {
            return $this->jobs;
        } else {
            if (isset($this->jobs[$group])) {
                return $this->jobs[$group];
            } else {
                throw new \Exception(__("Job group '%1' does not exist", $group));
            }
        }
    }

    public function getJobNameOptions($group = null)
    {
        $names = $this->getJobCodes($group);
        $ret = [];
        foreach ($names as $name) {
            $ret[] = ['value' => $name, 'label' => $name];
        }

        return $ret;
    }

    /**
     * Validates cron schedule expression
     * @param string $schedule
     */
    public function validateSchedule($schedule)
    {
        $result = preg_match(
            '/^(\*|((\*\/)?[1-5]‌​?[0-9])) '.
            '(\*|((\*\/)?[1-5]?[0‌​-9])) '.
            '(\*|((\*\/)?(1?[0-9]‌​|2[0-3]))) '.
            '(\*|((\*\/)?([1-9]|[‌​12][0-9]|3[0-1]))) '.
            '(\*|((\*\/)?([1-9]|1‌​[0-2]))) '.
            '(\*|((\*\/)?[0-6]))$‌​/i',
            $schedule
        );
        return $result === 1;
    }

    public function isEnabled($name)
    {
        #TODO
        return true;
    }
}
