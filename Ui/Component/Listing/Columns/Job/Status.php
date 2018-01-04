<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Ui\Component\Listing\Columns\Job;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Config\Model\Config\Source\Yesno as Options;

class Status extends Column
{
    /**
     * Column name
     */
    const NAME = 'status';

    /**
     *
     * @var Options
     */
    protected $options;

    /**
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Options $options
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Options $options,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->options = $options->toArray();
    }
    /**
     * Prepare Data Source
     *
     * @param  array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$this->getData('name')])) {
                    $status = 1;
                    if (isset($item['enabled'])) {
                        $status = $item['enabled'];
                    }
                    if ($status == '') {
                        $status = 1;
                    }
                    $item[$this->getData('name')] =
                        '<span class="button '
                        .$this->getStatusClass($status)
                        .'">'
                        .$this->getStatusItem($status)
                        .'</span>';
                }
            }
        }

        return $dataSource;
    }

    protected function getStatusClass($statusCode)
    {
        return $statusCode ? 'enabled' : 'disabled';
    }
    protected function getStatusItem($statusCode)
    {
        return $this->options[$statusCode];
    }

    protected function decorate($statusCode, $value)
    {
        return $value;
    }
}
