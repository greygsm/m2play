<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaymentServicesPaypal\Plugin\Config;

use Magento\PaymentServicesPaypal\Model\ApplePayConfigProvider;
use Magento\PaymentServicesPaypal\Model\HostedFieldsConfigProvider;
use Magento\PaymentServicesPaypal\Model\SmartButtonsConfigProvider;
use Magento\Payment\Model\Method\Adapter as PaymentAdapter;
use Magento\PaymentServicesPaypal\Model\Config;

class Adapter
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Return isActive for payment methods based on whether payments is enabled
     *
     * @param PaymentAdapter $subject
     * @param bool $result
     * @param string|null $storeId
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsActive(PaymentAdapter $subject, $result, $storeId = null): bool
    {
        if (in_array($subject->getCode(), [
            HostedFieldsConfigProvider::CODE,
            SmartButtonsConfigProvider::CODE,
            ApplePayConfigProvider::CODE
        ])) {
            return $this->config->isEnabled($storeId);
        } elseif ($subject->getCode() === HostedFieldsConfigProvider::CC_VAULT_CODE) {
            return $this->config->isEnabled($storeId) && $this->config->isVaultEnabled($storeId);
        } else {
            return $result;
        }
    }
}
