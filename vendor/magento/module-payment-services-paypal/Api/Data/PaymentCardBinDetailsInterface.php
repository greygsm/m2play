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

namespace Magento\PaymentServicesPaypal\Api\Data;

interface PaymentCardBinDetailsInterface
{
    public const BIN = 'bin';

    /**
     * Get bin
     *
     * @return string
     */
    public function getBin();

    /**
     * Set bin
     *
     * @param string $bin
     * @return $this
     */
    public function setBin(string $bin);
}
