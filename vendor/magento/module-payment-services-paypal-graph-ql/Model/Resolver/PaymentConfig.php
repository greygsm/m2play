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

namespace Magento\PaymentServicesPaypalGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\PaymentServicesPaypal\Api\PaymentConfigManagementInterface;

class PaymentConfig implements ResolverInterface
{
    /**
     * @var PaymentConfigManagementInterface
     */
    private PaymentConfigManagementInterface $paymentConfigManagement;

    /**
     * @param PaymentConfigManagementInterface $paymentConfigManagement
     */
    public function __construct(
        PaymentConfigManagementInterface $paymentConfigManagement
    ) {
        $this->paymentConfigManagement = $paymentConfigManagement;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $location = $args['location'];
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

        return $this->paymentConfigManagement->getConfig($location, $storeId);
    }
}
