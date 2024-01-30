<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace PayPal\Braintree\Model\Ui\ThreeDeeSecure;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use PayPal\Braintree\Gateway\Config\Config;
use PayPal\Braintree\Model\GooglePay\Config as GooglePayConfig;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var GooglePayConfig
     */
    private GooglePayConfig $googlePayConfig;

    /**
     * ConfigProvider constructor.
     *
     * @param Config $config
     * @param GooglePayConfig $googlePayConfig
     */
    public function __construct(Config $config, GooglePayConfig $googlePayConfig)
    {
        $this->config = $config;
        $this->googlePayConfig = $googlePayConfig;
    }

    /**
     * @inheritDoc
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getConfig(): array
    {
        // 3DS is currently used either by googlepay or card payments, so do not include if none of those is enabled.
        if (!$this->isAvailable()) {
            return [];
        }

        return [
            'payment' => [
                Config::CODE_3DSECURE => [
                    'enabled' => $this->isEnabled(),
                    'challengeRequested' => $this->isChallengeAlwaysRequested(),
                    'thresholdAmount' => $this->getThresholdAmount(),
                    'specificCountries' => $this->get3DSecureSpecificCountries()
                ]
            ]
        ];
    }

    /**
     * 3DS is currently used either by googlepay or card payments, so do not include if none of those is enabled.
     *
     * @return bool
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function isAvailable(): bool
    {
        return $this->config->isActive() || $this->googlePayConfig->isActive();
    }

    /**
     * Is enabled
     *
     * @return bool
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function isEnabled(): bool
    {
        return $this->config->isVerify3DSecure();
    }

    /**
     * Is challenge always requested
     *
     * @return bool
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function isChallengeAlwaysRequested(): bool
    {
        return $this->config->is3DSAlwaysRequested();
    }

    /**
     * Get threshold amount
     *
     * @return float
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getThresholdAmount(): float
    {
        return $this->config->getThresholdAmount();
    }

    /**
     * Get 3D secure specific countries
     *
     * @return array
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function get3DSecureSpecificCountries(): array
    {
        return $this->config->get3DSecureSpecificCountries();
    }
}
