<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 * @author Serhiy Gavrylov
 */
declare(strict_types=1);

namespace SG\Comments\Controller\Product;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class AjaxList implements HttpPostActionInterface
{

    /**
     * Constructor
     *
     * @param PageFactory $resultPageFactory
     * @param RequestInterface $request
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        private readonly PageFactory $resultPageFactory,
        private readonly RequestInterface $request,
        private readonly JsonFactory $resultJsonFactory,
    ) {
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $result = $this->resultJsonFactory->create();
        $resultPage = $this->resultPageFactory->create();
        $block = $resultPage->getLayout()
            ->createBlock('SG\Comments\Block\Product\Comment')
            ->setTemplate('SG_Comments::comment-list.phtml')
            ->setData('productId',$this->request->getParam('id'));
        $html = $block->toHtml();
        $result->setData(['html' => $html]);
        return $result;
    }
}
