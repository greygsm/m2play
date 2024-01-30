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
use Magento\Framework\UrlInterface;
use Magento\PaymentServicesPaypal\Api\Data\PaymentConfigApplePayInterface;
use Magento\PaymentServicesPaypal\Api\Data\PaymentConfigHostedFieldsInterface;
use Magento\PaymentServicesPaypal\Api\Data\PaymentConfigSmartButtonsInterface;
use Magento\PaymentServicesPaypal\Api\Data\PaymentConfigApplePayInterfaceFactory;
use Magento\PaymentServicesPaypal\Api\Data\PaymentConfigHostedFieldsInterfaceFactory;
use Magento\PaymentServicesPaypal\Api\Data\PaymentConfigSmartButtonsInterfaceFactory;
use Magento\PaymentServicesPaypal\Api\Data\PaymentConfigSmartButtonsMessageStylesInterface;
use Magento\PaymentServicesPaypal\Api\Data\PaymentConfigSmartButtonsMessageStylesInterfaceFactory;
use Magento\PaymentServicesPaypal\Api\Data\PaymentConfigSmartButtonsLogoInterface;
use Magento\PaymentServicesPaypal\Api\Data\PaymentConfigSmartButtonsLogoInterfaceFactory;
use Magento\PaymentServicesPaypal\Api\Data\PaymentConfigButtonStylesInterface;
use Magento\PaymentServicesPaypal\Api\Data\PaymentConfigButtonStylesInterfaceFactory;
use Magento\PaymentServicesPaypal\Api\Data\PaymentConfigSdkParamsInterface;
use Magento\PaymentServicesPaypal\Api\Data\PaymentConfigSdkParamsInterfaceFactory;
use Magento\PaymentServicesPaypal\Api\PaymentConfigManagementInterface;
use Magento\PaymentServicesBase\Model\Config as BaseConfig;
use Magento\PaymentServicesPaypal\Api\PaymentConfigResponseInterface;
use Magento\PaymentServicesPaypal\Api\PaymentSdkManagementInterface;
use Magento\PaymentServicesPaypal\Model\ApplePayConfigProvider;
use Magento\PaymentServicesPaypal\Model\Config;
use Magento\PaymentServicesPaypal\Model\HostedFieldsConfigProvider;
use Magento\PaymentServicesPaypal\Model\SmartButtonsConfigProvider;
use Magento\Store\Model\StoreManagerInterface;
use phpDocumentor\Reflection\Location;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class PaymentConfigManagement implements PaymentConfigManagementInterface
{
    /**
     * @var array
     */
    private array $locations;

    /**
     * @var array
     */
    private array $buttonsLocations;

    /**
     * @var array
     */
    private array $ccLocations;

    /**
     * @var array
     */
    private array $messageStyles;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var BaseConfig
     */
    private $baseConfig;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var PaymentSdkManagementInterface
     */
    private $paymentSdkManagement;

    /**
     * @var PaymentConfigHostedFieldsInterfaceFactory
     */
    private $paymentConfigHostedFieldsFactory;

    /**
     * @var PaymentConfigSmartButtonsInterfaceFactory
     */
    private $paymentConfigSmartButtonsFactory;

    /**
     * @var PaymentConfigApplePayInterfaceFactory
     */
    private $paymentConfigApplePayFactory;

    /**
     * @var PaymentConfigSmartButtonsMessageStylesInterfaceFactory
     */
    private $paymentConfigSmartButtonsMessageStylesFactory;

    /**
     * @var PaymentConfigSmartButtonsLogoInterfaceFactory
     */
    private $paymentConfigSmartButtonsLogoFactory;

    /**
     * @var PaymentConfigButtonStylesInterfaceFactory
     */
    private $paymentConfigButtonStylesFactory;

    /**
     * @var PaymentConfigSdkParamsInterfaceFactory
     */
    private $paymentConfigSdkParamsFactory;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @param Config $config
     * @param BaseConfig $baseConfig
     * @param UrlInterface $url
     * @param StoreManagerInterface $storeManager
     * @param PaymentConfigApplePayInterfaceFactory $paymentConfigApplePayFactory
     * @param PaymentConfigHostedFieldsInterfaceFactory $paymentConfigHostedFieldsFactory
     * @param PaymentConfigSmartButtonsInterfaceFactory $paymentConfigSmartButtonsFactory
     * @param PaymentConfigSmartButtonsMessageStylesInterfaceFactory $paymentConfigSmartButtonsMessageStylesFactory
     * @param PaymentConfigSmartButtonsLogoInterfaceFactory $paymentConfigSmartButtonsLogoFactory
     * @param PaymentConfigButtonStylesInterfaceFactory $paymentConfigButtonStylesFactory
     * @param PaymentConfigSdkParamsInterfaceFactory $paymentConfigSdkParamsFactory
     * @param PaymentSdkManagementInterface $paymentSdkManagement
     * @param array $locations
     * @param array $buttonsLocations
     * @param array $ccLocations
     * @param array $messageStyles
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Config $config,
        BaseConfig $baseConfig,
        UrlInterface $url,
        StoreManagerInterface $storeManager,
        PaymentConfigApplePayInterfaceFactory $paymentConfigApplePayFactory,
        PaymentConfigHostedFieldsInterfaceFactory $paymentConfigHostedFieldsFactory,
        PaymentConfigSmartButtonsInterfaceFactory $paymentConfigSmartButtonsFactory,
        PaymentConfigSmartButtonsMessageStylesInterfaceFactory $paymentConfigSmartButtonsMessageStylesFactory,
        PaymentConfigSmartButtonsLogoInterfaceFactory $paymentConfigSmartButtonsLogoFactory,
        PaymentConfigButtonStylesInterfaceFactory $paymentConfigButtonStylesFactory,
        PaymentConfigSdkParamsInterfaceFactory $paymentConfigSdkParamsFactory,
        PaymentSdkManagementInterface $paymentSdkManagement,
        $locations = [],
        $buttonsLocations = [],
        $ccLocations = [],
        $messageStyles = []
    ) {
        $this->config = $config;
        $this->baseConfig = $baseConfig;
        $this->url = $url;
        $this->storeManager = $storeManager;
        $this->paymentConfigApplePayFactory = $paymentConfigApplePayFactory;
        $this->paymentConfigHostedFieldsFactory = $paymentConfigHostedFieldsFactory;
        $this->paymentConfigSmartButtonsFactory = $paymentConfigSmartButtonsFactory;
        $this->paymentConfigSmartButtonsMessageStylesFactory = $paymentConfigSmartButtonsMessageStylesFactory;
        $this->paymentConfigSmartButtonsLogoFactory = $paymentConfigSmartButtonsLogoFactory;
        $this->paymentConfigButtonStylesFactory = $paymentConfigButtonStylesFactory;
        $this->paymentConfigSdkParamsFactory = $paymentConfigSdkParamsFactory;
        $this->paymentSdkManagement = $paymentSdkManagement;
        $this->locations = $locations;
        $this->buttonsLocations = $buttonsLocations;
        $this->ccLocations = $ccLocations;
        $this->messageStyles = $messageStyles;
    }

    /**
     * @inheritdoc
     */
    public function getConfig(string $location, ?int $store = null): array
    {
        $config = [];
        $location = strtoupper($location);
        if (!in_array($location, $this->locations)) {
            return $config;
        }

        $config[PaymentConfigResponseInterface::DATA_SMART_BUTTONS] =
            $this->getConfigItem($location, SmartButtonsConfigProvider::CODE, $store);
        $config[PaymentConfigResponseInterface::DATA_APPLE_PAY] =
            $this->getConfigItem($location, ApplePayConfigProvider::CODE, $store);
        $config[PaymentConfigResponseInterface::DATA_HOSTED_FIELDS] =
            $this->getConfigItem($location, HostedFieldsConfigProvider::CODE, $store);

        return $config;
    }

    /**
     * Get config per location.
     *
     * @param string $location
     * @param string $methodCode
     * @param int $store
     * @return PaymentConfigHostedFieldsInterface| PaymentConfigApplePayInterface | PaymentConfigSmartButtonsInterface
     * @throws NoSuchEntityException
     */
    public function getConfigItem(string $location, string $methodCode, ?int $store = null):
    PaymentConfigSmartButtonsInterface |
    PaymentConfigHostedFieldsInterface |
    PaymentConfigApplePayInterface
    {
        $storeId = $store ?? (int)$this->storeManager->getStore()->getId();
        $location = strtoupper($location);
        return $this->getConfigByMethod(strtolower($location), $methodCode, $storeId);
    }

    /**
     * Get config per location.
     *
     * @param string $location
     * @param string $code
     * @param int $store
     * @return PaymentConfigHostedFieldsInterface| PaymentConfigApplePayInterface | PaymentConfigSmartButtonsInterface
     */
    private function getConfigByMethod(string $location, string $code, int $store):
    PaymentConfigSmartButtonsInterface |
    PaymentConfigHostedFieldsInterface |
    PaymentConfigApplePayInterface
    {
        $config = [];

        if ($code === SmartButtonsConfigProvider::CODE) {
            $config = $this->getSmartButtonsConfig($location, $store);
        } elseif ($code === ApplePayConfigProvider::CODE) {
            $config = $this->getApplePayConfig($location, $store);
        } elseif ($code === HostedFieldsConfigProvider::CODE) {
            $config = $this->getHostedFieldsConfig($store);
        } else {
            return $config;
        }
        $config->setSortOrder($this->config->getSortOrder($code, $store));
        $config->setPaymentIntent($this->config->getPaymentIntent($code, $store));
        $config->setTitle($this->config->getPaymentTitle($code, $store));
        $config->setCode($code);

        //  If the merchant has not turned on the payment extension then we don't need to display the sdk params
        if ($this->baseConfig->isConfigured('', $store) && $config->hasIsVisible()) {
            $config->setHasIsVisible(true);
            $config->setSdkParams($this->getSdkParams($location, $store, $code));
        } else {
            $config->setSdkParams([]);
            $config->setHasIsVisible(false);
        }

        if ($code === HostedFieldsConfigProvider::CODE && !in_array(strtoupper($location), $this->ccLocations)) {
            $config->setHasIsVisible(false);
        }

        return $config;
    }

    /**
     * Get config for paypal smart buttons.
     *
     * @param string $location
     * @param int $store
     * @return PaymentConfigSmartButtonsInterface
     */
    private function getSmartButtonsConfig(string $location, int $store): PaymentConfigSmartButtonsInterface
    {
        /** @var PaymentConfigSmartButtonsInterface $smartButtonConfig */
        $smartButtonConfig = $this->paymentConfigSmartButtonsFactory->create();
        /** @var PaymentConfigSmartButtonsMessageStylesInterface $messageStyles  */
        $messageStyles = $this->paymentConfigSmartButtonsMessageStylesFactory->create();
        $messageStyles->setData($this->messageStyles);
        $smartButtonConfig->setHasIsVisible($this->config->isLocationEnabled(strtolower($location), $store));
        $smartButtonConfig->setMessageStyles($messageStyles);
        $smartButtonConfig->setHasDisplayMessage($this->config->canDisplayPayLaterMessage($store));
        $buttonStyles = $this->getButtonStyles($store);
        $smartButtonConfig->setButtonStyles($buttonStyles);
        $smartButtonConfig->setHasDisplayVenmo($this->config->isFundingSourceEnabledByName('venmo'));

        return $smartButtonConfig;
    }

    /**
     * Get config for Apple Pay.
     *
     * @param string $location
     * @param int $store
     * @return PaymentConfigApplePayInterface
     */
    private function getApplePayConfig(string $location, int $store): PaymentConfigApplePayInterface
    {
        $applePayConfig = $this->paymentConfigApplePayFactory->create();
        $applePayConfig->setHasIsVisible($this->config->isApplePayLocationEnabled(strtolower($location), $store));
        $buttonStyles = $this->getButtonStyles($store);
        $color = $buttonStyles->getColor();
        if ($color !== 'white') {
            $buttonStyles->setColor('black');
        }
        $applePayConfig->setButtonStyles($buttonStyles);
        $applePayConfig->setPaymentSource(ApplePayConfigProvider::PAYMENT_SOURCE);

        return $applePayConfig;
    }

    /**
     * Get config for Hosted Fields.
     *
     * @param int $store
     * @return PaymentConfigHostedFieldsInterface
     */
    private function getHostedFieldsConfig(int $store): PaymentConfigHostedFieldsInterface
    {
        /** @var PaymentConfigHostedFieldsInterface $hostedFieldsConfig */
        $hostedFieldsConfig = $this->paymentConfigHostedFieldsFactory->create();
        $hostedFieldsConfig->setHasIsVisible($this->config->isHostedFieldsEnabled($store));
        $hostedFieldsConfig->setPaymentSource(HostedFieldsConfigProvider::CC_SOURCE);
        $threeDS = $this->config->getThreeDS($store);
        $hostedFieldsConfig->setThreeDS($threeDS);
        $hostedFieldsConfig->setHasIsVaultEnabled($this->config->isVaultEnabled($store));
        $hostedFieldsConfig->setCcVaultCode(HostedFieldsConfigProvider::CC_VAULT_CODE);
        $hostedFieldsConfig->setRequiresCardDetails($this->config->isSignifydEnabled());

        return $hostedFieldsConfig;
    }

    /**
     * Get SDK params.
     *
     * @param string $location
     * @param int $store
     * @param string $code
     * @return array|PaymentConfigSdkParamsInterface
     */
    private function getSdkParams(string $location, int $store, string $code): array | PaymentConfigSdkParamsInterface
    {
        $sdkParams = $this->paymentSdkManagement->getParams($location, $store, $code);
        if (count($sdkParams) > 0 && $sdkParams[0]['params']) {
            $sdkParams = $sdkParams[0]['params'];
        }
        $params = [];
        foreach ($sdkParams as $sdkParamItem) {
            if (isset($sdkParamItem['name']) && isset($sdkParamItem['value'])) {
                /** @var  PaymentConfigSdkParamsInterface $configSdk */
                $params[] = $this->paymentConfigSdkParamsFactory
                    ->create()
                    ->setName($sdkParamItem['name'])
                    ->setValue($sdkParamItem['value']);
            }
        }

        return $params;
    }

    /**
     * Get Button Styles.
     *
     * @param int $store
     * @return PaymentConfigButtonStylesInterface
     */
    private function getButtonStyles(int $store): PaymentConfigButtonStylesInterface
    {
        /** @var PaymentConfigButtonStylesInterface $buttonStyles */
        $buttonStyles = $this->paymentConfigButtonStylesFactory->create();
        $buttonConfig = $this->config->getButtonConfiguration($store);
        $buttonStyles->setLayout($buttonConfig['layout']);
        $buttonStyles->setColor($buttonConfig['color']);
        $buttonStyles->setShape($buttonConfig['shape']);
        $buttonStyles->setLabel($buttonConfig['label']);
        $buttonStyles->setHasTagline($buttonConfig['tagline']);
        if (isset($buttonConfig['height'])) {
            $buttonStyles->setHeight($buttonConfig['height']);
            $buttonStyles->setUseDefaultHeight(false);
        } else {
            $buttonStyles->setUseDefaultHeight(true);
        }

        return $buttonStyles;
    }
}
