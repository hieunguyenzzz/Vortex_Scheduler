<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Block\Adminhtml\Listview\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Main extends Generic implements TabInterface
{
    protected $_jobs = null;


    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Cron\Model\Config $jobs
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Vortex\Scheduler\Model\Config $jobs,
        array $data = []
    ) {
        $this->_jobs = $jobs;
        parent::__construct($context, $registry,$formFactory,$data);
    }



    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Schedule');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Schedule');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_vortex_scheduler_listview');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('listview_');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Schedule Information')]);
        $df=$this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $fieldset->addField('schedule_id', 'hidden', ['name' => 'schedule_id']);

        $fieldset->addField(
            'job_code',
            'select',
            ['name' => 'job_code', 'label' => __('Job Code'), 'title' => __('Job Code'), 'required' => true, 'values'=>$this->_getJobCodeList()]
        );

        $fieldset->addField(
            'status',
            'select',
            ['name' => 'status', 'label' => __('Status'), 'title' => __('Status'), 'required' => false,
                'values' =>array(
                    array(
                        'value'=>'pending',
                        'label'=>'pending'
                    ),
                    array(
                        'value'=>'success',
                        'label'=>'success'
                    ),
                    array(
                        'value'=>'error',
                        'label'=>'error'
                    ),
                    array(
                        'value'=>'missed',
                        'label'=>'missed'
                    ),
                    array(
                        'value'=>'running',
                        'label'=>'running'
                    )
                ),
            ]
        );

        $fieldset->addField(
            'messages',
            'text',
            ['name' => 'messages', 'label' => __('Messages'), 'title' => __('Messages'), 'required' => false]
        );

        if ($model->getId()){
            $fieldset->addField(
                'created_at',
                'text',
                ['name' => 'created_at', 'label' => __('Created At'), 'title' => __('Created At'), 'required' => false]
            );
            $fieldset->addField(
                'scheduled_at',
                'text',
                ['name' => 'scheduled_at', 'label' => __('Scheduled At'), 'title' => __('Scheduled At'), 'required' => false, 'date_format'=>$df]
            );
            $fieldset->addField(
                'executed_at',
                'text',
                ['name' => 'executed_at', 'label' => __('Executed At'), 'title' => __('Executed At'), 'required' => false,'date_format'=>$df]
            );
            $fieldset->addField(
                'finished_at',
                'text',
                ['name' => 'finished_at', 'label' => __('Finished At'), 'title' => __('Finished At'), 'required' => false,'date_format'=>$df]
            );
        }
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * @return array
     */
    private function _getJobCodeList(){
        $groupedJobList = $this->_jobs->getAllJobs();
        foreach ($groupedJobList as $jobGroup => $jobConfig) {
            $jobList = $groupedJobList[$jobGroup];
            foreach ($jobList as $code => $values) {
                $jobCodeList[] = array('value'=>$code,'label'=>$code);
            }
        }
        asort($jobCodeList);
        return $jobCodeList;
    }
}
