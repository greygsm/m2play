<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Braintree\Block\Adminhtml\System\Config;

use Braintree\Result\Error;
use Braintree\Result\Successful;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PayPal\Braintree\Gateway\Config\PayPal\Config;
use PayPal\Braintree\Model\Ui\ConfigProvider;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Backend\Block\Template\Context;
use PayPal\Braintree\Gateway\Config\Config as BraintreeConfig;
use PayPal\Braintree\Gateway\Config\PayPalCredit\Config as PayPalCreditConfig;
use PayPal\Braintree\Gateway\Config\PayPalPayLater\Config as PayPalPayLaterConfig;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * PayPal buttons preview block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Preview extends Field
{
    /**
     * @var string
     */
    protected $_template = 'PayPal_Braintree::system/config/preview.phtml';

    /**
     * @var Config $config
     */
    protected Config $config;

    /**
     * @var BraintreeConfig $braintreeConfig
     */
    private BraintreeConfig $braintreeConfig;

    /**
     * @var ConfigProvider $configProvider
     */
    private ConfigProvider $configProvider;

    /**
     * @var PayPalCreditConfig $payPalCreditConfig
     */
    private PayPalCreditConfig $payPalCreditConfig;

    /**
     * @var PayPalPayLaterConfig $payPalPayLaterConfig
     */
    private PayPalPayLaterConfig $payPalPayLaterConfig;

    /**
     * Preview constructor.
     *
     * @param Context $context
     * @param Config $config
     * @param PayPalCreditConfig $payPalCreditConfig
     * @param PayPalPayLaterConfig $payPalPayLaterConfig
     * @param BraintreeConfig $braintreeConfig
     * @param ConfigProvider $configProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $config,
        PayPalCreditConfig $payPalCreditConfig,
        PayPalPayLaterConfig $payPalPayLaterConfig,
        BraintreeConfig $braintreeConfig,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->braintreeConfig = $braintreeConfig;
        $this->configProvider = $configProvider;
        $this->payPalCreditConfig = $payPalCreditConfig;
        $this->payPalPayLaterConfig = $payPalPayLaterConfig;
    }

    /**
     * Return element html
     *
     * @param AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        if ($this->isPayPalActive()) {
            return $this->_toHtml();
        }
        return '';
    }

    /**
     * Button alias
     *
     * @return string
     */
    public function getAlias(): string
    {
        return 'braintree.admin-config';
    }

    /**
     * Button container
     *
     * @return string
     */
    public function getContainerId(): string
    {
        return 'braintree-admin-config';
    }

    /**
     * Get currency code
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getCurrency(): ?string
    {
        return $this->_storeManager->getStore()->getBaseCurrencyCode();
    }

    /**
     * Get order amount
     *
     * @return float|null
     */
    public function getAmount(): ?float
    {
        return 1000.00;
    }

    /**
     * Check PayPal active
     *
     * @return bool
     */
    public function isPayPalActive(): bool
    {
        return $this->config->isActive();
    }

    /**
     * Check PayPal Credit active
     *
     * @return bool
     */
    public function isCreditActive(): bool
    {
        return $this->payPalCreditConfig->isActive();
    }

    /**
     * Check PayPal Pay Later active
     *
     * @return bool
     */
    public function isPayLaterActive(): bool
    {
        return $this->payPalPayLaterConfig->isActive();
    }

    /**
     * Show PayPal buttons
     *
     * @param string $type
     * @param string $location
     * @return bool
     */
    public function showPayPalButton(string $type, string $location): bool
    {
        return $this->config->showPayPalButton($type, $location);
    }

    /**
     * Get merchant name
     *
     * @return string|null
     */
    public function getMerchantName(): ?string
    {
        return $this->config->getMerchantName();
    }

    /**
     * Get button location based on scope type
     *
     * @return string
     */
    public function getButtonLocation(): string
    {
        $scopeData = $this->getScopeType();
        if (is_array($scopeData)) {
            $scopeType = $scopeData[0];
            $scopeCode = $scopeData[1];
        } else {
            $scopeType = $scopeData;
            $scopeCode = null;
        }
        return $this->_scopeConfig->getValue('payment/braintree_paypal/payment_location', $scopeType, $scopeCode);
    }

    /**
     * Get button shape
     *
     * @param string $type
     * @param string $location
     * @return string
     */
    public function getButtonShape(string $type, string $location = Config::BUTTON_AREA_CART): string
    {
        return $this->getConfigValue($location, $type, 'shape', $this->getScopeType());
    }

    /**
     * Get button color
     *
     * @param string $type
     * @param string $location
     * @return string
     */
    public function getButtonColor(string $type, string $location = Config::BUTTON_AREA_CART): string
    {
        return $this->getConfigValue($location, $type, 'color', $this->getScopeType());
    }

    /**
     * Get button size
     *
     * @param string $type
     * @param string $location
     * @return string
     * @deprecated as Size field is redundant
     */
    public function getButtonSize(string $type, string $location = Config::BUTTON_AREA_CART): string
    {
        return $this->getConfigValue($location, $type, 'size', $this->getScopeType());
    }

    /**
     * Get button label
     *
     * @param string $type
     * @param string $location
     * @return string
     */
    public function getButtonLabel(string $type, string $location = Config::BUTTON_AREA_CART): string
    {
        return $this->getConfigValue($location, $type, 'label', $this->getScopeType());
    }

    /**
     * Get braintree environment
     *
     * @return string
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getEnvironment(): string
    {
        return $this->braintreeConfig->getEnvironment();
    }

    /**
     * Get client token
     *
     * @return Error|Successful|string|null
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getClientToken(): Error|Successful|string|null
    {
        return $this->configProvider->getClientToken();
    }

    /**
     * Get merchant country
     *
     * @return string|null
     */
    public function getMerchantCountry(): ?string
    {
        return $this->payPalPayLaterConfig->getMerchantCountry();
    }

    /**
     * Get messaging layout
     *
     * @param string $type
     * @param string $location
     * @return string
     */
    public function getMessagingLayout(string $type, string $location = Config::BUTTON_AREA_CART): string
    {
        return $this->getConfigValue($location, $type, 'layout', $this->getScopeType());
    }

    /**
     * Get messaging logo
     *
     * @param string $type
     * @param string $location
     * @return string
     */
    public function getMessagingLogo(string $type, string $location = Config::BUTTON_AREA_CART): string
    {
        return $this->getConfigValue($location, $type, 'logo', $this->getScopeType());
    }

    /**
     * Get messaging logo position
     *
     * @param string $type
     * @param string $location
     * @return string
     */
    public function getMessagingLogoPosition(string $type, string $location = Config::BUTTON_AREA_CART): string
    {
        return $this->getConfigValue($location, $type, 'logo_position', $this->getScopeType());
    }

    /**
     * Get messaging text color
     *
     * @param string $type
     * @param string $location
     * @return string
     */
    public function getMessagingTextColor(string $type, string $location = Config::BUTTON_AREA_CART): string
    {
        return $this->getConfigValue($location, $type, 'text_color', $this->getScopeType());
    }

    /**
     * Get scope type
     *
     * @return array|string
     */
    public function getScopeType(): array|string
    {
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;

        if ($this->getRequest()->getParam('website')) {
            $scopeType = ScopeInterface::SCOPE_WEBSITE;
            $websiteId = $this->getRequest()->getParam('website');

            return [$scopeType, $websiteId];
        }
        return $scopeType;
    }

    /**
     * Get configuration field value based on scope type and code
     *
     * @param string $location
     * @param string $type
     * @param string $style
     * @param mixed $scopeData
     * @return mixed
     */
    public function getConfigValue(string $location, string $type, string $style, mixed $scopeData): mixed
    {
        if (is_array($scopeData)) {
            $scopeType = $scopeData[0];
            $scopeCode = $scopeData[1];
        } else {
            $scopeType = $scopeData;
            $scopeCode = null;
        }

        return $this->_scopeConfig->getValue(
            'payment/braintree_paypal/button_location_' . $location . '_type_' . $type . '_' . $style,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Get button config
     *
     * @return array
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getButtonConfig(): array
    {
        return [
            'clientToken' => $this->getClientToken(),
            'currency' => $this->getCurrency(),
            'environment' => $this->getEnvironment(),
            'merchantCountry' => $this->getMerchantCountry(),
            'isCreditActive' => $this->isCreditActive()
        ];
    }
}
