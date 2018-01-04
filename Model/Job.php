<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Vortex\Scheduler\Api\Data\JobInterface;
use Vortex\Scheduler\Model\ResourceModel\Schedule\CollectionFactory;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

class Job extends AbstractModel implements IdentityInterface, JobInterface
{
    const CACHE_TAG = 'vortex_cronjob';
    protected $_eventPrefix = 'vortex_cronjob';
    /**
     *
     * @var \Vortex\Scheduler\Model\ResourceModel\Schedule\CollectionFactory
     */
    protected $_scheduleColFactory;

    /**
     * @var \Vortex\Scheduler\Model\Config
     */
    protected $_config;

    protected $_schedules;


    public function __construct(
        \Vortex\Scheduler\Model\Config $config
    ) {
        $this->_config = $config;
        $this->_init('Vortex\Scheduler\Model\ResourceModel\Job');
    }

    /**
     * Define resource model
     */
    protected function _construct()
    {
          $this->_init('Vortex\Scheduler\Model\ResourceModel\Job');
    }

    public function disable()
    {
        $this->setData(JobInterface::JOB_ENABLED, 0);
        return $this;
    }

    public function enable()
    {
        $this->setData(JobInterface::JOB_ENABLED, 1);
        return $this;
    }

    public function isEnabled()
    {
        return $this->getData(JobInterface::JOB_ENABLED);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [self::CACHE_TAG . '_' . $this->getId()];
        return $identities;
    }

    public function getSchedules()
    {
        if (!$this->_schedules) {
            /* @var $schedule \Vortex\Scheduler\Model\ResourceModel\Schedule\Collection */
            $schedule = $this->getResourceCollection();
            $this->_schedules = $schedule;
        }
        return $this->_schedules;
    }

    /**
     * Get job code
     *
     * @return mixed
     */
    public function getJobCode() {
        return $this->getData(JobInterface::JOB_NAME);
    }

    /**
     * Fill Job data from config
     *
     * @param string $id
     * @return void
     */
    public function load($id, $field = NULL)
    {
        if ($id) {
            //$field = JobInterface::JOB_NAME;
            $jobs = $this->_config->getAllJobs();
            foreach ($jobs as $group => $groupJobs) {
                foreach ($groupJobs as $jobName => $job) {
                    if ($id == $jobName) {
                        $status = 1;
                        if (isset($job[JobInterface::JOB_ENABLED])) {
                            $status = $job[JobInterface::JOB_ENABLED];
                        }
                        $this->setId($jobName);
                        $this->setId($jobName)
                            ->setName($jobName)
                            ->setMethod($job[JobInterface::JOB_METHOD])
                            ->setInstance($job[JobInterface::JOB_CLASS])
                            ->setEnabled($status)
                            ->setGroup($group);
                        if (isset($job[JobInterface::JOB_SCHEDULE])) {
                            $this->setSchedule($job[JobInterface::JOB_SCHEDULE]);
                        }
                        if (isset($job[JobInterface::JOB_CONFIG])) {
                            $this->setConfigPath($job[JobInterface::JOB_CONFIG]);
                        }
                        break;
                    }
                }
            }
        }
        return $this;
    }
}
