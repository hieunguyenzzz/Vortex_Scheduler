<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Controller\Adminhtml\Listview;

class Index extends \Vortex\Scheduler\Controller\Adminhtml\Listview
{
    /**
     * List view.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $this->_checkCronConfig();

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Vortex_Scheduler::vortex_scheduler_cronjobs_listview');
        $resultPage->getConfig()->getTitle()->prepend(__('List Scheduled'));
        return $resultPage;
    }

    /**
     * check cron config
     *
     * @return void
     */
    protected function _checkCronConfig()
    {
        $schedules = $this->_objectManager->create('Magento\Cron\Model\Schedule')->getCollection();
        $schedules->addFieldToFilter('status','success');
        $schedules->setOrder(
            'finished_at',
            'desc'
        );

        $schedule = $schedules->getFirstItem();

        $foundRecently = 0;
        $tz = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $date = $tz->date()->format('Y-m-d H:i:s');
        if (is_object($schedule)) {
            $finished = $schedule->getCreatedAt();
            if ($finished) {
                $date1 = \DateTime::createFromFormat('Y-m-d H:i:s', $finished);
                $date2 = \DateTime::createFromFormat('Y-m-d H:i:s', $date);
                $minutes = (abs($date2->getTimestamp())-abs($date1->getTimestamp()))/60;
                $foundRecently = 1;
            }
        }

        if (!$foundRecently || $minutes > 30) {
            $this->messageManager->addError(
                $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml('Cron did not config.')
            );
        }

    }
}
