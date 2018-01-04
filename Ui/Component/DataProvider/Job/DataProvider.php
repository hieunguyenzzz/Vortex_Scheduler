<?php

/**
 * Copyright 2017 VortexCommerce
 *
 * @author Vortex dev team
 * See LICENSE.txt for license details.
 */
namespace Vortex\Scheduler\Ui\Component\DataProvider\Job;

use Magento\Framework\View\Element\UiComponent;
use Magento\Framework\Api\Search\SearchResultInterface;

class DataProvider extends UiComponent\DataProvider\DataProvider
{
    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $arrItems = ['items' => []];

        foreach ($searchResult->getItems() as $item) {
            $itemData = [];
            $custAttrs = $item->getCustomAttributes();
            if ($custAttrs) {
                foreach ($custAttrs as $attribute) {
                    $itemData[$attribute->getAttributeCode()]
                        = $attribute->getValue();
                }
            }
            $arrItems['items'][] = array_merge($itemData, $item->getData());
        }

        $arrItems['totalRecords'] = $searchResult->getTotalCount();

        return $arrItems;
    }
}
