/*browser:true*/
/*global define*/
define([
    'underscore',
    'jquery',
    'mage/translate',
    'braintreeThreeDSecure',
    'PayPal_Braintree/js/googlepay/implementations/shortcut/adapter',
    'PayPal_Braintree/js/helper/escape-non-ascii-characters',
    'PayPal_Braintree/js/helper/format-amount',
    'PayPal_Braintree/js/model/full-screen-loader',
], function (
    _,
    $,
    $t,
    threeDSecure,
    braintree,
    escapeNonAsciiCharacters,
    formatAmount,
    defaultFullScreenLoader
) {
    'use strict';

    /**
     * 3D Secure implementation generic enough to be used for GooglePay button payments.
     *
     * This can be used in other pages along with the basket page, but not in the checkout page when placing an order.
     */
    return {
        code: 'three_d_secure',
        fullScreenLoader: defaultFullScreenLoader,
        config: null,
        billingAddress: null,
        totalAmount: null,

        /**
         * Get code.
         *
         * @returns {string}
         */
        getCode: function () {
            return this.code;
        },

        /**
         *
         * @return {boolean}
         */
        isEnabled: function () {
            return this.config !== null ? _.get(this.config, ['enabled'], false) : false;
        },

        /**
         * Set the full screen loader implementation.
         *
         * @param fullScreenLoader
         */
        setFullScreenLoader: function (fullScreenLoader) {
            this.fullScreenLoader = fullScreenLoader;
        },

        /**
         * Get the full screen loader implementation.
         */
        getFullScreenLoader: function () {
            return this.fullScreenLoader;
        },

        /**
         * Set 3d secure config.
         *
         * @param {object} config
         */
        setConfig: function (config) {
            this.config = config;
            this.config.thresholdAmount = parseFloat(_.get(config, 'thresholdAmount', '0.0'));
        },

        /**
         * Get the billing address data.
         */
        getBillingAddress: function () {
            return this.billingAddress;
        },

        /**
         * Set the billing address data.
         *
         * @param {object} value
         */
        setBillingAddress: function (value) {
            this.billingAddress = value;
        },

        /**
         * Get the total amount to be charged.
         */
        getTotalAmount: function () {
            return this.totalAmount;
        },

        /**
         * Set the full screen loader implementation.
         *
         * @param {string} value
         */
        setTotalAmount: function (value) {
            this.totalAmount = formatAmount(value);
        },

        /**
         * Get the Braintree environment.
         *
         * @return {string|null}
         */
        getEnvironment: function () {
            return _.get(this.config, 'environment', 'TEST');
        },

        /**
         * Get the Braintree Client Token.
         *
         * @return {string|null}
         */
        getClientToken: function () {
            return _.get(this.config, 'clientToken', null);
        },

        /**
         * Check minimal amount for 3d secure activation.
         *
         * @param {Number} amount
         * @returns {Boolean}
         */
        isAmountAvailable: function (amount) {
            amount = parseFloat(amount.toString());

            return amount >= this.config.thresholdAmount;
        },

        /**
         * Check if current country is available for 3d secure.
         *
         * @param {String} countryId
         * @returns {Boolean}
         */
        isCountryAvailable: function (countryId) {
            let key,
                specificCountries = _.get(this.config, 'specificCountries', []);

            // all countries are available
            if (!specificCountries.length) {
                return true;
            }

            for (key in specificCountries) {
                if (countryId === specificCountries[key]) {
                    return true;
                }
            }

            return false;
        },

        /**
         * Get the challenge requested config (default false).
         *
         * @returns {Boolean}
         */
        getChallengeRequested: function () {
            return _.get(this.config, 'challengeRequested', false);
        },

        /**
         * Trigger 3DS verification & validate Braintree payment nonce.
         *
         * @param {Object} context
         * @returns {Object}
         */
        validate: function (context) {
            let self = this,
                clientInstance = braintree.getApiClient(),
                state = $.Deferred(),
                billingAddress = self.getBillingAddress();

            if (billingAddress.regionCode == null) {
                billingAddress.regionCode = undefined;
            }

            if (billingAddress.regionCode !== undefined && billingAddress.regionCode.length > 2) {
                billingAddress.regionCode = undefined;
            }

            if (!self.isAmountAvailable(self.getTotalAmount()) || !self.isCountryAvailable(billingAddress.countryId)) {
                state.resolve();
                return state.promise();
            }

            self.getFullScreenLoader().startLoader();

            let setup3d = function(clientInstance) {
                threeDSecure.create({
                    version: 2,
                    client: clientInstance
                }, function (threeDSecureErr, threeDSecureInstance) {
                    if (threeDSecureErr) {
                        self.getFullScreenLoader().stopLoader();
                        return state.reject($t('Please try again with another form of payment.'));
                    }

                    let threeDSContainer = document.createElement('div'),
                        tdMask = document.createElement('div'),
                        tdFrame = document.createElement('div'),
                        tdBody = document.createElement('div');

                    threeDSContainer.id = 'braintree-three-d-modal';
                    tdMask.className ="bt-mask";
                    tdFrame.className ="bt-modal-frame";
                    tdBody.className ="bt-modal-body";

                    tdFrame.appendChild(tdBody);
                    threeDSContainer.appendChild(tdMask);
                    threeDSContainer.appendChild(tdFrame);

                    let threeDSecureParameters = {
                        amount: self.getTotalAmount(),
                        nonce: context.paymentMethodNonce,
                        bin: context.creditCardBin,
                        challengeRequested: self.getChallengeRequested(),
                        billingAddress: {
                            givenName: escapeNonAsciiCharacters(billingAddress.firstname),
                            surname: escapeNonAsciiCharacters(billingAddress.lastname),
                            phoneNumber: billingAddress.telephone,
                            streetAddress: billingAddress.street[0],
                            extendedAddress: billingAddress.street[1],
                            locality: billingAddress.city,
                            region: billingAddress.regionCode,
                            postalCode: billingAddress.postcode,
                            countryCodeAlpha2: billingAddress.countryId
                        },
                        onLookupComplete: function (data, next) {
                            next();
                        },
                        addFrame: function (err, iframe) {
                            self.getFullScreenLoader().stopLoader();

                            if (err) {
                                console.log('Unable to verify card over 3D Secure', err);
                                return state.reject($t('Please try again with another form of payment.'));
                            }

                            tdBody.appendChild(iframe);
                            document.body.appendChild(threeDSContainer);
                        },
                        removeFrame: function () {
                            self.getFullScreenLoader().startLoader();
                            document.body.removeChild(threeDSContainer);
                        }
                    };

                    if (_.has(context, 'email') && context.email !== null) {
                        threeDSecureParameters.email = context.email;
                    }

                    threeDSecureInstance.verifyCard(threeDSecureParameters, function (err, response) {
                        self.getFullScreenLoader().stopLoader();

                        if (err) {
                            console.error('3DSecure validation failed', err);
                            if (err.code === 'THREEDS_LOOKUP_VALIDATION_ERROR') {
                                let errorMessage = err.details.originalError.details.originalError.error.message;
                                if (errorMessage === 'Billing line1 format is invalid.' && billingAddress.street[0].length > 50) {
                                    return state.reject(
                                      $t('Billing line1 must be string and less than 50 characters. Please update the address and try again.')
                                    );
                                } else if (errorMessage === 'Billing line2 format is invalid.' && billingAddress.street[1].length > 50) {
                                    return state.reject(
                                      $t('Billing line2 must be string and less than 50 characters. Please update the address and try again.')
                                    );
                                }
                                return state.reject($t(errorMessage));
                            } else {
                                return state.reject($t('Please try again with another form of payment.'));
                            }
                        }

                        let liability = {
                            shifted: response.liabilityShifted,
                            shiftPossible: response.liabilityShiftPossible
                        };

                        if (liability.shifted || (!liability.shifted && !liability.shiftPossible)) {
                            context.paymentMethodNonce = response.nonce;
                            state.resolve();
                        } else {
                            state.reject($t('Please try again with another form of payment.'));
                        }
                    });
                });
            };

            if (!clientInstance) {
                self.createClientInstance(setup3d)
            } else {
                setup3d(clientInstance);
            }

            return state.promise();
        },

        /**
         * Create a Braintree client instance with simplified form.
         *
         * @param {Function} setupThreeDSecureCallback
         * @return {*}
         */
        createClientInstance: function (setupThreeDSecureCallback) {
            let self = this;

            return require(['PayPal_Braintree/js/googlepay/implementations/shortcut/form'], function(c) {
                c.defaults.clientConfig.clientToken = self.getClientToken()
                c.defaults.clientConfig.environment = self.getEnvironment()
                braintree.setConfig(c.defaults.clientConfig);
                braintree.setup(setupThreeDSecureCallback);
            });
        }
    };
});
