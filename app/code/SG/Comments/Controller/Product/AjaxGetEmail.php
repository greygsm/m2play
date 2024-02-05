<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 * @author Serhiy Gavrylov
 */
declare(strict_types=1);

namespace SG\Comments\Controller\Product;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class AjaxGetEmail implements HttpPostActionInterface
{

    /**
     * @param JsonFactory $resultJsonFactory
     * @param Session $customerSession
     */
    public function __construct(
        private readonly JsonFactory $resultJsonFactory,
        private readonly Session     $customerSession,
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
        $email = $this->customerSession->isLoggedIn() ? $this->customerSession->getCustomer()->getEmail() : null;
        $result->setData(['email' => $email]);
        return $result;
    }
}
