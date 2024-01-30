<?php
/*************************************************************************
 * ADOBE CONFIDENTIAL
 * ___________________
 *
 * Copyright [first year code created] Adobe
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
 **************************************************************************/
declare(strict_types=1);

namespace Magento\PaymentServicesPaypal\Model\Api;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\PaymentServicesPaypal\Api\PaymentSdkManagementInterface;
use Magento\PaymentServicesPaypal\Model\ApplePayConfigProvider;
use Magento\PaymentServicesPaypal\Model\Config;
use Magento\PaymentServicesPaypal\Model\HostedFieldsConfigProvider;
use Magento\PaymentServicesPaypal\Model\SdkService;
use Magento\PaymentServicesPaypal\Model\SmartButtonsConfigProvider;
use Magento\Store\Model\StoreManagerInterface;
use Magento\PaymentServicesPaypal\Model\SdkService\PaymentOptionsBuilderFactory;

class PaymentSdkManagement implements PaymentSdkManagementInterface
{
    // TODO: Convert to di
    private const LOCATIONS = [
        'PRODUCT_DETAIL',
        'MINICART',
        'CART',
        'CHECKOUT'
    ];

    private const BUTTONS_LOCATIONS = [
        'PRODUCT_DETAIL',
        'MINICART',
        'CART',
        'CHECKOUT'
    ];

    private const CC_LOCATIONS = [
        'CHECKOUT'
    ];

    /**
     * @var Config
     */
    private $config;

    /**
     * @var PaymentOptionsBuilderFactory
     */
    private $paymentOptionsBuilderFactory;

    /**
     * @var SdkService
     */
    private $sdkService;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param PaymentOptionsBuilderFactory $paymentOptionsBuilderFactory
     * @param SdkService $sdkService
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     */
    public function __construct(
        PaymentOptionsBuilderFactory $paymentOptionsBuilderFactory,
        SdkService $sdkService,
        StoreManagerInterface $storeManager,
        Config $config
    ) {
        $this->paymentOptionsBuilderFactory = $paymentOptionsBuilderFactory;
        $this->sdkService = $sdkService;
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getParams(string $location, int $store = null, string $methodCode = null): array
    {
        $params = [];
        $store = $store ?? (int)$this->storeManager->getStore()->getId();
        $location = strtoupper($location);
        if (!in_array($location, self::LOCATIONS)) {
            return $params;
        }

        if ($methodCode) {
            return [
                [
                    'code' => $methodCode,
                    'params' => $this->getSdkParams(
                        $methodCode . '_' . $location,
                        strtolower($location),
                        $methodCode,
                        $store
                    )
                ]
            ];
        }

        if (in_array($location, self::BUTTONS_LOCATIONS)) {
            $params[] = [
                'code' => SmartButtonsConfigProvider::CODE,
                'params' => $this->getSdkParams(
                    SmartButtonsConfigProvider::CODE . '_' . $location,
                    strtolower($location),
                    SmartButtonsConfigProvider::CODE,
                    $store
                )
            ];
            $params[] = [
                'code' => ApplePayConfigProvider::CODE,
                'params' => $this->getSdkParams(
                    ApplePayConfigProvider::CODE . '_' . $location,
                    strtolower($location),
                    ApplePayConfigProvider::CODE,
                    $store
                )
            ];

        }
        if (in_array($location, self::CC_LOCATIONS)) {
            $params[] = [
                'code' => HostedFieldsConfigProvider::CODE,
                'params' => $this->getSdkParams(
                    HostedFieldsConfigProvider::CODE . '_' . $location,
                    strtolower($location),
                    HostedFieldsConfigProvider::CODE,
                    $store
                )
            ];
        }

        return $params;
    }

    /**
     * Get script params for paypal smart buttons sdk.
     *
     * @param string $cacheKey
     * @param string $location
     * @param string $code
     * @param int $store
     * @return array
     * @throws NoSuchEntityException
     */
    private function getSdkParams(string $cacheKey, string $location, string $code, int $store): array
    {
        if (!$this->config->isEnabled($store)) {
            return [];
        }
        $storeViewId = $this->storeManager->getStore($store)->getId();
        $cachedParams = $this->sdkService->loadFromSdkParamsCache($cacheKey, (string)$storeViewId);
        if (count($cachedParams) > 0) {
            return $cachedParams;
        }

        $paymentOptions = [];
        if ($code === SmartButtonsConfigProvider::CODE) {
            $paymentOptions = $this->getSmartButtonsOptions($location, $store);
        } elseif ($code === ApplePayConfigProvider::CODE) {
            $paymentOptions = $this->getApplePayOptions($location, $store);
        } elseif ($code === HostedFieldsConfigProvider::CODE) {
            $paymentOptions = $this->getCCOptions($store);
        }

        try {
            $params = $this->sdkService->getSdkParams(
                $paymentOptions,
                true,
                $this->config->getPaymentIntent($code, $store)
            );
        } catch (\InvalidArgumentException | NoSuchEntityException $e) {
            return [];
        }
        $result = [];
        foreach ($params as $param) {
            $result[] = [
                'name' => $param['name'],
                'value' => $param['value']
            ];
        }
        if (count($result) > 0) {
            $this->sdkService->updateSdkParamsCache($result, $cacheKey, (string)$storeViewId);
        }
        return $result;
    }

    /**
     * Get script option for paypal smart buttons sdk.
     *
     * @param string $location
     * @param int $store
     * @return array
     * @throws NoSuchEntityException
     */
    private function getSmartButtonsOptions(string $location, int $store): array
    {
        if (!$this->config->isLocationEnabled($location, $store)) {
            return [];
        }
        $paymentOptionsBuilder = $this->paymentOptionsBuilderFactory->create();
        $paymentOptionsBuilder->setAreButtonsEnabled(true);
        $paymentOptionsBuilder->setIsPayPalCreditEnabled(
            $this->config->isFundingSourceEnabledByName('paypal_credit', $store)
        );
        $paymentOptionsBuilder->setIsVenmoEnabled($this->config->isFundingSourceEnabledByName('venmo'), $store);
        $paymentOptionsBuilder->setIsApplePayEnabled(false);
        $paymentOptionsBuilder->setIsCreditCardEnabled(
            $location === 'checkout' && $this->config->isFundingSourceEnabledByName('card', $store)
        );
        $paymentOptionsBuilder->setIsPaylaterMessageEnabled($this->config->canDisplayPayLaterMessage());
        return $paymentOptionsBuilder->build();
    }

    /**
     * Get script option for Apple Pay sdk.
     *
     * @param string $location
     * @param int $store
     * @return array
     */
    private function getApplePayOptions(string $location, int $store): array
    {
        if (!$this->config->isApplePayLocationEnabled($location, $store)) {
            return [];
        }
        $paymentOptionsBuilder = $this->paymentOptionsBuilderFactory->create();
        $paymentOptionsBuilder->setAreButtonsEnabled(true);
        $paymentOptionsBuilder->setIsPayPalCreditEnabled(false);
        $paymentOptionsBuilder->setIsVenmoEnabled(false);
        $paymentOptionsBuilder->setIsApplePayEnabled(true);
        $paymentOptionsBuilder->setIsCreditCardEnabled(false);
        $paymentOptionsBuilder->setIsPaylaterMessageEnabled(false);
        return $paymentOptionsBuilder->build();
    }

    /**
     * Get script option for Apple Pay sdk.
     *
     * @param int $store
     * @return array
     */
    private function getCCOptions(int $store): array
    {
        if (!$this->config->isHostedFieldsEnabled($store)) {
            return [];
        }
        $paymentOptionsBuilder = $this->paymentOptionsBuilderFactory->create();
        $paymentOptionsBuilder->setIsCreditCardEnabled(true);
        return $paymentOptionsBuilder->build();
    }
}
