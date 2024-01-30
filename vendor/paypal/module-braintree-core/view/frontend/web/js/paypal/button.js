/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'rjsResolver',
        'uiRegistry',
        'uiComponent',
        'underscore',
        'jquery',
        'Magento_Customer/js/customer-data',
        'mage/translate',
        'braintree',
        'braintreeDataCollector',
        'braintreePayPalCheckout',
        'PayPal_Braintree/js/form-builder',
        'PayPal_Braintree/js/helper/remove-non-digit-characters',
        'PayPal_Braintree/js/helper/replace-single-quote-character',
        'domReady!'
    ],
    function (
        resolver,
        registry,
        Component,
        _,
        $,
        customerData,
        $t,
        braintree,
        dataCollector,
        paypalCheckout,
        formBuilder,
        removeNonDigitCharacters,
        replaceSingleQuoteCharacter
    ) {
        'use strict';
        let buttonIds = [];

        return {
            events: {
                onClick: null,
                onCancel: null,
                onError: null
            },

            /**
             * Initialize button
             *
             * @param buttonConfig
             * @param lineItems
             */
            init: function (buttonConfig, lineItems) {
                if ($('.action-braintree-paypal-message').length) {
                    $('.product-add-form form').on('keyup change paste', 'input, select, textarea', function () {
                        let currentPrice, currencySymbol;
                        currentPrice = $(".product-info-main span").find("[data-price-type='finalPrice']").text();
                        currencySymbol = $('.action-braintree-paypal-message[data-pp-type="product"]').data('currency-symbol');
                        $('.action-braintree-paypal-message[data-pp-type="product"]').attr('data-pp-amount', currentPrice.replace(currencySymbol,''));
                    });
                }

                this.loadSDK(buttonConfig, lineItems);
            },

            /**
             * Load Braintree PayPal SDK
             *
             * @param buttonConfig
             * @param lineItems
             */
            loadSDK: function (buttonConfig, lineItems) {
                braintree.create({
                    authorization: buttonConfig.clientToken
                }, function (clientErr, clientInstance) {
                    if (clientErr) {
                        console.error('paypalCheckout error', clientErr);
                        return this.showError("PayPal Checkout could not be initialized. Please contact the store owner.");
                    }
                    dataCollector.create({
                        client: clientInstance,
                        paypal: true
                    }, function (err, dataCollectorInstance) {
                        if (err) {
                            return console.log(err);
                        }
                    });
                    paypalCheckout.create({
                        client: clientInstance
                    }, function (err, paypalCheckoutInstance) {
                        if (typeof paypal !== 'undefined' ) {
                            this.renderPayPalButtons(paypalCheckoutInstance, lineItems);
                            this.renderPayPalMessages();
                        } else {
                            let configSDK = {
                                components: 'buttons,messages,funding-eligibility',
                                "enable-funding": (this.isCreditActive(buttonConfig)) ? 'credit' : 'paylater',
                                currency: buttonConfig.currency
                            };

                            let buyerCountry = this.getMerchantCountry(buttonConfig);
                            if (buttonConfig.environment === 'sandbox'
                                && (buyerCountry !== '' || buyerCountry !== 'undefined'))
                            {
                                configSDK["buyer-country"] = buyerCountry;
                            }
                            paypalCheckoutInstance.loadPayPalSDK(configSDK, function () {
                                this.renderPayPalButtons(paypalCheckoutInstance, lineItems);
                                this.renderPayPalMessages();
                            }.bind(this));
                        }
                    }.bind(this));
                }.bind(this));
            },

            /**
             * Is Credit enabled
             *
             * @param buttonConfig
             * @returns {boolean}
             */
            isCreditActive: function (buttonConfig) {
                return buttonConfig.isCreditActive;
            },

            /**
             * Get merchant country
             *
             * @param buttonConfig
             * @returns {string}
             */
            getMerchantCountry: function (buttonConfig) {
                return buttonConfig.merchantCountry;
            },

            /**
             * Render PayPal buttons
             *
             * @param paypalCheckoutInstance
             * @param lineItems
             */
            renderPayPalButtons: function (paypalCheckoutInstance, lineItems) {
                this.payPalButton(paypalCheckoutInstance, lineItems);
            },

            /**
             * Render PayPal messages
             */
            renderPayPalMessages: function () {
                $('.action-braintree-paypal-message').each(function () {
                    paypal.Messages({
                        amount: $(this).data('pp-amount'),
                        pageType: $(this).data('pp-type'),
                        style: {
                            layout: $(this).data('messaging-layout'),
                            text: {
                              color:   $(this).data('messaging-text-color')
                            },
                            logo: {
                                type: $(this).data('messaging-logo'),
                                position: $(this).data('messaging-logo-position')
                            }
                        }
                    }).render('#' + $(this).attr('id'));


                });
            },

            /**
             * @param paypalCheckoutInstance
             * @param lineItems
             */
            payPalButton: function (paypalCheckoutInstance, lineItems) {
                $('.action-braintree-paypal-logo').each(function () {
                    if ($.inArray($(this).attr('id'), buttonIds) === -1) {
                        buttonIds.push($(this).attr('id'));

                        let currentElement = $(this);
                        let style = {
                            label: currentElement.data('label'),
                            color: currentElement.data('color'),
                            shape: currentElement.data('shape')
                        };

                        if (currentElement.data('fundingicons')) {
                            style.fundingicons = currentElement.data('fundingicons');
                        }

                        // Render
                        let paypalActions;
                        let button = paypal.Buttons({
                            fundingSource: currentElement.data('funding'),
                            style: style,
                            createOrder: function () {
                                return paypalCheckoutInstance.createPayment({
                                    amount: currentElement.data('amount'),
                                    locale: currentElement.data('locale'),
                                    currency: currentElement.data('currency'),
                                    flow: 'checkout',
                                    enableShippingAddress: true,
                                    displayName: currentElement.data('displayname'),
                                    lineItems: $.parseJSON(lineItems)
                                });
                            },
                            validate: function (actions) {
                                let cart = customerData.get('cart'),
                                    customer = customerData.get('customer'),
                                    declinePayment = false,
                                    isGuestCheckoutAllowed;

                                isGuestCheckoutAllowed = cart().isGuestCheckoutAllowed;
                                declinePayment = !customer().firstname && !isGuestCheckoutAllowed && (typeof isGuestCheckoutAllowed !== 'undefined');

                                if (declinePayment) {
                                    actions.disable();
                                }
                                paypalActions = actions;
                            },

                            onCancel: function (cancelData) {
                                jQuery("#maincontent").trigger('processStop');
                            },

                            onError: function (errorData) {
                                console.error('paypalCheckout button render error', errorData);
                                jQuery("#maincontent").trigger('processStop');
                            },

                            onClick: function (clickData) {
                                if (currentElement.data('location') === 'productpage') {
                                    let form = $("#product_addtocart_form");
                                    if (!(form.validation() && form.validation('isValid'))) {
                                        return false;
                                    }
                                }

                                let cart = customerData.get('cart'),
                                    customer = customerData.get('customer'),
                                    declinePayment = false,
                                    isGuestCheckoutAllowed;

                                isGuestCheckoutAllowed = cart().isGuestCheckoutAllowed;
                                declinePayment = !customer().firstname && !isGuestCheckoutAllowed && (typeof isGuestCheckoutAllowed !== 'undefined');
                                if (declinePayment) {
                                    alert($t('To check out, please sign in with your email address.'));
                                }
                            },

                            onApprove: function (approveData) {
                                return paypalCheckoutInstance.tokenizePayment(approveData, function (err, payload) {
                                    jQuery("#maincontent").trigger('processStart');

                                    // Map the shipping address correctly
                                    let address = payload.details.shippingAddress;
                                    let recipientFirstName, recipientLastName;
                                    if (typeof address.recipientName !== 'undefined' && _.isString(address.recipientName)) {
                                        let recipientName = address.recipientName.split(" ");
                                        recipientFirstName = replaceSingleQuoteCharacter(recipientName[0]);
                                        recipientLastName = replaceSingleQuoteCharacter(recipientName[1]);
                                    } else {
                                        recipientFirstName = replaceSingleQuoteCharacter(payload.details.firstName);
                                        recipientLastName = replaceSingleQuoteCharacter(payload.details.lastName);
                                    }
                                    payload.details.shippingAddress = {
                                        streetAddress: typeof address.line2 !== 'undefined' && _.isString(address.line2)
                                            ? replaceSingleQuoteCharacter(address.line1) + " " + replaceSingleQuoteCharacter(address.line2)
                                            : replaceSingleQuoteCharacter(address.line1),
                                        locality: replaceSingleQuoteCharacter(address.city),
                                        postalCode: address.postalCode,
                                        countryCodeAlpha2: address.countryCode,
                                        email: replaceSingleQuoteCharacter(payload.details.email),
                                        recipientFirstName: recipientFirstName,
                                        recipientLastName: recipientLastName,
                                        telephone: removeNonDigitCharacters(_.get(payload, ['details', 'phone'], '')),
                                        region: typeof address.state !== 'undefined'
                                            ? replaceSingleQuoteCharacter(address.state)
                                            : ''
                                    };

                                    payload.details.email = replaceSingleQuoteCharacter(payload.details.email);
                                    payload.details.firstName = replaceSingleQuoteCharacter(payload.details.firstName);
                                    payload.details.lastName = replaceSingleQuoteCharacter(payload.details.lastName);
                                    if (typeof payload.details.businessName !== 'undefined' && _.isString(payload.details.businessName)) {
                                        payload.details.businessName = replaceSingleQuoteCharacter(payload.details.businessName);
                                    }

                                    // Map the billing address correctly
                                    let isRequiredBillingAddress = currentElement.data('requiredbillingaddress');
                                    if ((isRequiredBillingAddress === 1) && (typeof payload.details.billingAddress !== 'undefined')) {
                                        let billingAddress = payload.details.billingAddress;
                                        payload.details.billingAddress = {
                                            streetAddress: typeof billingAddress.line2 !== 'undefined' && _.isString(billingAddress.line2)
                                                ? replaceSingleQuoteCharacter(billingAddress.line1) + " " + replaceSingleQuoteCharacter(billingAddress.line2)
                                                : replaceSingleQuoteCharacter(billingAddress.line1),
                                            locality: replaceSingleQuoteCharacter(billingAddress.city),
                                            postalCode: billingAddress.postalCode,
                                            countryCodeAlpha2: billingAddress.countryCode,
                                            telephone: removeNonDigitCharacters(_.get(payload, ['details', 'phone'], '')),
                                            region: typeof billingAddress.state !== 'undefined'
                                                ? replaceSingleQuoteCharacter(billingAddress.state)
                                                : ''
                                        };
                                    }

                                    if (currentElement.data('location') === 'productpage') {
                                        let form = $("#product_addtocart_form");
                                        payload.additionalData = form.serialize();
                                    }

                                    let actionSuccess = currentElement.data('actionsuccess');
                                    formBuilder.build(
                                        {
                                            action: actionSuccess,
                                            fields: {
                                                result: JSON.stringify(payload)
                                            }
                                        }
                                    ).submit();
                                });
                            }
                        });
                        if (!button.isEligible()) {
                            console.log('PayPal button is not elligible')
                            currentElement.parent().remove();
                            return;
                        }
                        if (button.isEligible() && $('#' + currentElement.attr('id')).length) {
                            button.render('#' + currentElement.attr('id'));
                        }
                    }
                });
            },
        }
    }
);
