<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Model\ResourceModel\Job;

use Vortex\Scheduler\Api\Data\JobInterface;

class Collection extends \Magento\Framework\Data\Collection
{
    const MODEL = 'Vortex\Scheduler\Model\Job';
    const MODEL_RESOURCE = 'Vortex\Scheduler\Model\ResourceModel\Job';

    protected $_idFieldName = JobInterface::JOB_NAME;

    protected $_helper;

    public function __construct(
        \Vortex\Scheduler\Helper\Job $helper
    ) {
        $this->_helper = $helper;
    }

    public function toOptionArray()
    {
        return $this->_toOptionArray(
            $this->_idFieldName,
            JobInterface::JOB_NAME
        );
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(static::MODEL, static::MODEL_RESOURCE);
    }

    /**
     * Load data
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return \Magento\Indexer\Model\Indexer\Collection
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        //@TODO
        if (!$this->isLoaded()) {
            $objMan = \Magento\Framework\App\ObjectManager::getInstance();
            $jf = $objMan->create('\Vortex\Scheduler\Model\JobFactory');
            $helper = $objMan->create('\Vortex\Scheduler\Helper\Job');
            $jobs = $helper->getJobs();
            if (is_array($jobs)) {
                foreach ($jobs as $group => $groupJobs) {
                    foreach ($groupJobs as $jobName => $job) {
                        $item = $jf->create();
                        $status = 1;
                        if (isset($job[JobInterface::JOB_ENABLED])) {
                            $status = $job[JobInterface::JOB_ENABLED];
                        }
                        $item->setId($jobName)
                            ->setName($jobName)
                            ->setMethod($job[JobInterface::JOB_METHOD])
                            ->setInstance($job[JobInterface::JOB_CLASS])
                            ->setEnabled($status)
                            ->setGroup($group);
                        if (isset($job[JobInterface::JOB_SCHEDULE])) {
                            $item->setSchedule($job[JobInterface::JOB_SCHEDULE]);
                        }
                        if (isset($job[JobInterface::JOB_CONFIG])) {
                            $item->setConfigPath($job[JobInterface::JOB_CONFIG]);
                        }
                        $this->_addItem($item);
                    }
                }
            }
            $this->_setIsLoaded(true);
        }

        return $this;
    }

    /**
     * @param null $group
     * @return mixed
     * @throws \Exception
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
}
