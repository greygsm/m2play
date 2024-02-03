<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 * @author Serhiy Gavrylov
 */

namespace SG\Comments\Ui\DataProvider\Comments\Listing;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider as CoreDataProvider;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;

class DataProvider extends CoreDataProvider
{
    /**
     * @param string $name
     * @param Reporting $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param RedirectInterface $redirect
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        Reporting $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        private readonly RedirectInterface $redirect,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            '',
            '',
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
    }

    /**
     * @inheritdoc
     */
    public function getData(): array
    {
        $productId = $this->getProductId();
        if ($productId) {
            $filter = $this->filterBuilder->setField('product_id')
                ->setConditionType('=')
                ->setValue($productId)
                ->create();
            $this->addFilter($filter);
        }

        return parent::getData();
    }

    /**
     * Getting current product id
     * @return string|null
     */
    private function getProductId(): ?string
    {
        $refUrl = $this->redirect->getRefererUrl();
        if (!str_contains($refUrl, 'catalog/product/edit')) {
            return null;
        }
        $pathSegments = explode('/', parse_url($refUrl, PHP_URL_PATH));
        $idIndex = array_search('id', $pathSegments);

        return $idIndex !== false ? $pathSegments[$idIndex + 1] : null;
    }
}
