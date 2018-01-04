<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Block\Adminhtml\Job\Edit;

use Vortex\Scheduler\Api\Data\JobInterface;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     *
     * @var \Vortex\Scheduler\Helper\Job
     */
    protected $_jobHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Vortex\Scheduler\Helper\Job $jobHelper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_jobHelper = $jobHelper;
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('job_form');
        $this->setTitle(__('Job Information'));
        $this->setAction('*/job/save');
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('edit_job');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => [
                'id' => 'edit_form',
                'action' => $this->getData('action'),
                'method' => 'post']]
        );

        $form->setHtmlIdPrefix('block_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General Information'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            JobInterface::JOB_ID,
            'hidden',
            ['name' => JobInterface::JOB_ID]
        );

        if (!$model->getId()) {
            $fieldset->addField(
                JobInterface::JOB_NAME,
                'text',
                [
                    'name' => JobInterface::JOB_NAME,
                    'label' => __('Name'),
                    'title' => __('Name'),
                    'required' => true
                ]
            );
        } else {
            $fieldset->addField(
                JobInterface::JOB_NAME,
                'label',
                [
                    'name' => JobInterface::JOB_NAME,
                    'label' => __('Name'),
                    'title' => __('Name')]
            );
        }

        $fieldset->addField(
            JobInterface::JOB_CLASS,
            'text',
            [
                'name' => JobInterface::JOB_CLASS,
                'label' => __('Class'),
                'title' => __('Class'),
                'required' => true
            ]
        );
        $fieldset->addField(
            JobInterface::JOB_METHOD,
            'text',
            [
                'name' => JobInterface::JOB_METHOD,
                'label' => __('Method'),
                'title' => __('Method'),
                'required' => true
            ]
        );
        $fieldset->addField(
            JobInterface::JOB_SCHEDULE,
            'text',
            [
                'name' => JobInterface::JOB_SCHEDULE,
                'label' => __('Schedule'),
                'title' => __('Schedule'),
                'required' => false
            ]
        );
        $fieldset->addField(
            JobInterface::JOB_CONFIG,
            'text',
            [
                'name' => JobInterface::JOB_CONFIG,
                'label' => __('Config path'),
                'title' => __('Config path'),
                'required' => false
            ]
        );

        $fieldset->addField(
            JobInterface::JOB_GROUP,
            'text',
            [
                'name' => JobInterface::JOB_GROUP,
                'label' => __('Group'),
                'title' => __('Group'),
                'required' => true
            ]
        );

        $fieldset->addField(
            JobInterface::JOB_ENABLED,
            'select',
            [
                'name' => JobInterface::JOB_ENABLED,
                'label' => __('Status'),
                'title' => __('Status'),
                'required' => true,
                'options' => ['1' => __('Enabled'), '0' => __('Disabled')]
            ]
        );
        if (!$model->getId()) {
            $model->setData(JobInterface::JOB_ENABLED, '1');
            $model->setData(JobInterface::JOB_GROUP, 'default');

        }

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
