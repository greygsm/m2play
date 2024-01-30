<?php

/**
 * ADOBE CONFIDENTIAL
 *
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 */

declare(strict_types=1);

namespace Magento\PaymentServicesPaypal\Plugin\Vault;

use Magento\PaymentServicesBase\Model\HttpException;
use Magento\PaymentServicesPaypal\Model\VaultService;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\PaymentTokenRepository as OriginalPaymentTokenRepository;

class PaymentTokenRepository
{
    /**
     * @var VaultService
     */
    private $vaultService;

    /**
     * @param VaultService $vaultService
     */
    public function __construct(
        VaultService $vaultService,
    ) {
        $this->vaultService = $vaultService;
    }

    /**
     * Before deleting the vault token in Magento, call the payments vault service to delete the token
     *
     * @param OriginalPaymentTokenRepository $subject
     * @param PaymentTokenInterface $paymentToken
     * @return null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDelete(
        OriginalPaymentTokenRepository $subject,
        PaymentTokenInterface $paymentToken
    ) {
        $response = $this->vaultService->deleteVaultedCard($paymentToken, (int)$paymentToken->getCustomerId());
        if (!$response['is_successful']) {
            throw new HttpException($response['message'], $response['status']);
        }
        return null;
    }
}
