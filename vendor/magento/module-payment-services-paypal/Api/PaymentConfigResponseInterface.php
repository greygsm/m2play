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

namespace Magento\PaymentServicesPaypal\Api;

interface PaymentConfigResponseInterface
{
    public const DATA_APPLE_PAY = 'apple_pay';
    public const DATA_HOSTED_FIELDS = 'hosted_fields';
    public const DATA_SMART_BUTTONS = 'smart_buttons';

    /**
     * Get payment sdk params.
     *
     * @return Magento\PaymentServicesPaypal\Api\Data\PaymentConfigApplePayInterface
     * @since 100.1.0
     */
    public function getApplePay();

    /**
     * Set configuration
     *
     * @param array $applePay
     * @return $this
     */
    public function setApplePay($applePay);

    /**
     * Get Hosted Fields
     *
     * @return Magento\PaymentServicesPaypal\Api\Data\PaymentConfigHostedFieldsInterface
     * @since 100.1.0
     */
    public function getHostedFields();

    /**
     * Set Hosted Fields
     *
     * @param array $hostedFields
     * @return $this
     */
    public function setHostedFields($hostedFields);

    /**
     * Get Hosted Fields
     *
     * @return Magento\PaymentServicesPaypal\Api\Data\PaymentConfigSmartButtonsInterface
     * @since 100.1.0
     */
    public function getSmartButtons();

    /**
     * Set Hosted Fields
     *
     * @param array $smartButtons
     * @return $this
     */
    public function setSmartButtons($smartButtons);
}
