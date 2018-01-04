<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Model\ResourceModel;

use Vortex\Scheduler\Model\Job as ModelJob;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Vortex\Scheduler\Api\Data\JobInterface;


class Job extends AbstractDb
{

    /**
     * @var ReinitableConfigInterface
     */
    protected $_configReinitableConfigInterface;

    protected $_helper;

    protected $_configInterface;

    public function __construct(
        //\Vortex\Scheduler\Helper\Config $helper,
        ReinitableConfigInterface $configReinitableConfigInterface,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configInterface,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    )
    {
        $this->_init(
            JobInterface::TABLE_NAME,
            JobInterface::JOB_ID
        );
        $this->_configReinitableConfigInterface = $configReinitableConfigInterface;
        $this->_configInterface = $configInterface;

        //$this->_helper = $helper;
        parent::__construct($context, $connectionName);
    }

    protected function _construct()
    {
        $this->_init(
            JobInterface::TABLE_NAME,
            JobInterface::JOB_ID
        );
    }
    /**
     * Save object object data
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws \Exception
     * @api
     */
    public function save(\Magento\Framework\Model\AbstractModel $object)
    {

        if ($object->isDeleted()) {
            return $this->delete($object);
        }

        if (!$object instanceof \Vortex\Scheduler\Model\Job) {
            throw new InvalidArgumentException(sprintf("Expected object of type 'Vortex\Scheduler\Model\Job' got '%s'", get_class($object)));
        }

        if (!$object->getId() && !$object->getName()) {
            Mage::throwException('Invalid data. Must have job name(code).');
        }
        try {
            if ($object->getId() == '') {
                $object->setId($object->getName());
            }
            $this->_configInterface
                ->saveConfig($this->getJobPathPrefix($object->getId(), $object->getGroup()).'/instance', $object->getInstance(), 'default', 0);
            $this->_configInterface
                ->saveConfig($this->getJobPathPrefix($object->getId(), $object->getGroup()).'/run/model', $object->getInstance(), 'default', 0);

            $this->_configInterface
                ->saveConfig($this->getJobPathPrefix($object->getId(), $object->getGroup()).'/method', $object->getMethod(), 'default', 0);
            $this->_configInterface
                ->saveConfig($this->getJobPathPrefix($object->getId(), $object->getGroup()).'/schedule/cron_expr', $object->getSchedule(), 'default', 0);
            if ($object->getConfigPath()) {
                $this->_configInterface
                    ->saveConfig($this->getJobPathPrefix($object->getId(), $object->getGroup()).'/config_path', $object->getConfigPath(), 'default', 0);
            }
            $this->_configInterface
                ->saveConfig($this->getJobPathPrefix($object->getId(), $object->getGroup()).'/enabled', $object->getEnabled(), 'default', 0);
            $object->setHasDataChanges(false);
        } catch (Exception $e) {
            $object->setHasDataChanges(true);
            throw $e;
        }
        return $this;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function delete(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object instanceof \Vortex\Scheduler\Model\Job) {
            throw new InvalidArgumentException(sprintf("Expected object of type 'Vortex\Scheduler\Model\Job' got '%s'", get_class($object)));
        }
        try {
            // DO DELETE
            $connection = $this->getConnection();
            $connection->delete(
                $this->getMainTable(),
                array(
                    'path LIKE ?' => '%crontab/%/'.$object->getName().'/%'
                )
            );
            // END DO DELETE

            $this->_configReinitableConfigInterface->reinit();
        } catch (\Exception $e) {
            throw $e;
        }
        return $this;
    }


    protected function getJobPathPrefix($jobCode, $group = 'default')
    {
        if (!$group) {
            $group = 'default';
        }
        return 'crontab/'.$group.'/jobs/' . $jobCode;
    }


    public function setModelFromJobData(\Vortex\Scheduler\Model\Job $job, array $data)
    {
        $job->setId($data[JobInterface::JOB_NAME])
            ->setName($data[JobInterface::JOB_NAME])
            ->setMethod($data[JobInterface::JOB_METHOD])
            ->setInstance($data[JobInterface::JOB_CLASS])
            ->setEnabled($data['enable'])
            ->setGroup($data['group']);
        if (isset($job[JobInterface::JOB_SCHEDULE])) {
            $job->setSchedule($data[JobInterface::JOB_SCHEDULE]);
        }
        if (isset($job[JobInterface::JOB_CONFIG])) {
            $job->setConfigPath($data[JobInterface::JOB_CONFIG]);
        }
        return $job;
    }

}
