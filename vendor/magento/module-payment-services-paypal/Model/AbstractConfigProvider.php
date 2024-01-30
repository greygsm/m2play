<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaymentServicesPaypal\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\PaymentServicesPaypal\Model\SdkService\PaymentOptionsBuilder;
use Magento\PaymentServicesPaypal\Model\SdkService\PaymentOptionsBuilderFactory;
use Magento\Store\Model\StoreManagerInterface;

abstract class AbstractConfigProvider implements ConfigProviderInterface
{
    public const CODE = '';

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
     * @param Config $config
     * @param PaymentOptionsBuilderFactory $paymentOptionsBuilderFactory
     * @param SdkService $sdkService
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Config $config,
        PaymentOptionsBuilderFactory $paymentOptionsBuilderFactory,
        SdkService $sdkService,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->paymentOptionsBuilderFactory = $paymentOptionsBuilderFactory;
        $this->sdkService = $sdkService;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        return [
            'payment' => [
                $this->getCode() => []
            ]
        ];
    }

    /**
     * Get payment method code.
     *
     * @return string
     */
    protected function getCode() : string
    {
        return self::CODE;
    }

    /**
     * Get script params for PayPal js sdk loading.
     *
     * @param string $paymentCode
     * @param string $location
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getScriptParams(string $paymentCode, string $location) : array
    {
        $storeViewId = $this->storeManager->getStore()->getId();
        $cachedParams = $this->sdkService->loadFromSdkParamsCache($location, (string)$storeViewId);
        if (count($cachedParams) > 0) {
            return $cachedParams;
        }
        $paymentOptions = $this->getPaymentOptions()->build();
        try {
            $paymentIntent = $this->config->getPaymentIntent($paymentCode);
            $params = $this->sdkService->getSdkParams(
                $paymentOptions,
                false,
                $paymentIntent
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
            $this->sdkService->updateSdkParamsCache($result, $location, (string)$storeViewId);
        }
        return $result;
    }

    /**
     * Get payment options.
     *
     * @return PaymentOptionsBuilder
     */
    protected function getPaymentOptions(): PaymentOptionsBuilder
    {
        return $this->paymentOptionsBuilderFactory->create();
    }
}
