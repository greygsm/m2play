<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 * @author Serhiy Gavrylov
 */
namespace SG\Comments\Block\Product\Comment;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Form extends Template
{

    /**
     * @param Context $context
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Get comment product post action URL
     *
     * @return string
     */
    public function getAjaxPostUrl(): string
    {
        return $this->getUrl('sg_comments/product/ajaxPostComment');
    }

    /**
     * Get Ajax URL for getting customer email
     *
     * @return string
     */
    public function getAjaxCustomerEmailUrl(): string
    {
        return $this->getUrl('sg_comments/product/ajaxGetEmail');
    }

    /**
     * Get Ajax URL for comments list html
     *
     * @return string
     */
    public function getAjaxListUrl(): string
    {
        return $this->getUrl('sg_comments/product/ajaxList');
    }

    /**
     * Get comment product id
     *
     * @return int
     */
    public function getProductId(): int
    {
        return $this->getRequest()->getParam('id', false);
    }
}
