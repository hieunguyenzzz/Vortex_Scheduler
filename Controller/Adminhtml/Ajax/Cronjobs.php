<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Controller\Adminhtml\Ajax;

use Magento\Customer\Model\Customer;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Area;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Vortex\Scheduler\Helper\CronData;


class Cronjobs extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
    protected $storeManager;

    /** @var FormKey */
    protected $formKey;

    /** @var CronData */
    protected $cronData;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param FormKey $formKey
     * @param CronData $cronData
     */
    public function __construct(
        Context $context,
        FormKey $formKey,
        CronData $cronData
    )
    {
        $this->formKey = $formKey;
        $this->cronData = $cronData;

        parent::__construct($context);
    }

    /**
     * TODO
     *
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $formKey = $this->formKey->getFormKey();
        try {
            $cronjobs = $this->cronData->getCronjobData();

            $result = [
                'cronjobs' => $cronjobs,
                'form_key' => $formKey
            ];

            echo json_encode($result, true);

        } catch (\Exception $e) {

            $result = [
                'cronjobs' => [],
                'form_key' => $formKey
            ];

            echo json_encode($result, true);
        }
    }
}

