/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            scriptLoader: 'Magento_PaymentServicesPaypal/js/lib/script-loader-wrapper'
        }
    },
    shim: {
        'Magento_PaymentServicesPaypal/js/lib/script-loader': {
            exports: 'paypalLoadScript'
        }
    }
};
