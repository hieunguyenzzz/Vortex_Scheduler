<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Block\Adminhtml\Job;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry = null;

    protected $_context;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_context = $context;
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize edit block
     *
     * @return void
     */
    protected function _construct()
    {
        //$this->_objectId = 'id';
        $this->_blockGroup = 'Vortex_Scheduler';
        $this->_controller = 'adminhtml_job';

        parent::_construct();

        if ($this->_isAllowedAction('Vortex_Scheduler::edit')) {
            $model = $this->_registry->registry('edit_job');
            $modelConfig = $this->_context->getScopeConfig()->getValue('crontab/'.$model->getGroup().'/jobs/'.$model->getName().'/instance');
            if ($modelConfig != null || $modelConfig != '') {
                $this->buttonList->add(
                    'delete',
                    [
                        'label' => __('Delete Job added/Job config'),
                        'class' => 'delete',
                        'onclick' => 'deleteConfirm(\'' . __(
                                'Are you sure you want to do this?'
                            ) . '\', \'' . $this->getDeleteUrl() . '\')'
                    ]
                );
            }
            $this->buttonList->update('save', 'label', __('Save Job'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => [
                                'event' => 'saveAndContinueEdit',
                                'target' => '#edit_form'],
                        ],
                    ]
                ],
                -100
            );
        } else {
            $this->buttonList->remove('save');
        }
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_registry->_registry('edit_job')->getId()) {
            return __("Edit Job: '%1'", $this->escapeHtml($this->_registry
                ->_registry('edit_job')->getName()));
        } else {
            return __('New Job');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', ['_current' => true, 'back' => 'edit']);
    }
}
