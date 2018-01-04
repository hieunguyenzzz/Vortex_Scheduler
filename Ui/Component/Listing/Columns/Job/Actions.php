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

class Actions extends Column
{
    /* Url path */
    const URL_PATH_EDIT = 'scheduler/job/edit';
    const URL_PATH_DELETE = 'scheduler/job/delete';
    const URL_PATH_EXECUTE = 'scheduler/job/execute';
    const URL_PATH_SCHEDULE = 'scheduler/job/schedule';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var string
     */
    private $editUrl;
    /**
     *
     * @var \Vortex\Scheduler\Helper\Config
     */
    private $config;

    /**
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $urlBuilder
     * @param array              $components
     * @param array              $data
     * @param string             $editUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        //\Vortex\Scheduler\Helper\Config $config,
        $components = [],
        $data = [],
        $editUrl = self::URL_PATH_EDIT
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->editUrl = $editUrl;
        //$this->config = $config;
        parent::__construct($context, $uiComponentFactory, $components, $data);
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
                $name = $this->getData('name');
                if (isset($item['id'])) {
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(
                            $this->editUrl,
                            ['id' => $item['id']]
                        ),
                        'label' => __('Edit')
                    ];
                    $item[$name]['execute'] = [
                        'href' => $this->urlBuilder
                            ->getUrl(
                                self::URL_PATH_EXECUTE,
                                ['id' => $item['id']]
                            ),
                        'label' => __('Run Job'),
                        'confirm' => [
                            'title' => __('Run "${ $.$data.name }"'),
                            'message' => __('Are you sure you want to run '
                                . '"${ $.$data.name }" job?')
                        ]
                    ];
                    $item[$name]['schedule'] = [
                        'href' => $this->urlBuilder
                            ->getUrl(
                                self::URL_PATH_SCHEDULE,
                                ['id' => $item['id']]
                            ),
                        'label' => __('Schedule Job'),
                        'confirm' => [
                            'title' => __('Schedule "${ $.$data.name }"'),
                            'message' => __('Are you sure you want to schedule '
                                . '"${ $.$data.name }" job?')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
