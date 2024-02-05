<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 * @author Serhiy Gavrylov
 */
declare(strict_types=1);

namespace SG\Comments\Controller\Product;

use DateTime;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Validator\EmailAddress;
use SG\Comments\Api\CommentRepositoryInterface;
use SG\Comments\Api\Data\CommentInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;

class AjaxPostComment implements HttpPostActionInterface
{

    /**
     * @param RequestInterface $request
     * @param Validator $formKeyValidator
     * @param Escaper $escaper
     * @param JsonFactory $resultJsonFactory
     * @param CommentInterfaceFactory $commentFactory
     * @param CommentRepositoryInterface $commentRepository
     * @param Session $customerSession
     * @param EmailAddress $emailValidator
     */
    public function __construct(
        private readonly RequestInterface           $request,
        private readonly Validator                  $formKeyValidator,
        private readonly Escaper                    $escaper,
        private readonly JsonFactory                $resultJsonFactory,
        private readonly CommentInterfaceFactory    $commentFactory,
        private readonly CommentRepositoryInterface $commentRepository,
        private readonly Session                    $customerSession,
        private readonly EmailAddress               $emailValidator,
    ) {
    }

    /**
     * Execute post comment action
     *
     * @return ResultInterface
     * @throws LocalizedException
     */
    public function execute(): ResultInterface
    {
        $validFormKey = $this->formKeyValidator->validate($this->request);

        if ($validFormKey && $this->validateParams() && $this->request->isAjax()) {

            $commentModel = $this->commentFactory->create();
            $commentModel->setData($this->retrieveCommentData());
            $this->commentRepository->save($commentModel);
        }

        return $this->resultJsonFactory->create()->setData(['result' => 'Success']);
    }

    /**
     * Validate params
     *
     * @return true
     * @throws LocalizedException
     */
    private function validateParams(): true
    {
        if (!$this->customerSession->isLoggedIn()) {
            if (trim((string)$this->request->getParam('name')) === '') {
                throw new LocalizedException(__('Name is missing'));
            }
            if (!$this->emailValidator->isValid($this->request->getParam('email'))) {
                throw new LocalizedException(__('Email is invalid'));
            }
        }

        if (trim((string)$this->request->getParam('message')) === '') {
            throw new LocalizedException(__('Message is missing'));
        }

        return true;
    }

    /**
     * Prepare data for save
     *
     * @return array
     */
    private function retrieveCommentData(): array
    {
        $name = $this->request->getParam('name') ?? $this->customerSession->getCustomer()->getFirstname();
        $message = $this->request->getParam('message') ?? '';
        return [
            'created_at' => (new DateTime('now'))->format('Y/m/d H:i:s'),
            'name' => $this->escaper->escapeHtml($name),
            'email' => $this->request->getParam('email') ?? $this->customerSession->getCustomer()->getEmail(),
            'message' => $this->escaper->escapeHtml($message),
            'product_id' => $this->request->getParam('product_id'),
            'customer_id' => $this->customerSession->getCustomer()->getId()
        ];
    }
}
