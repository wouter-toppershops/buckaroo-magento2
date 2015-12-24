/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'uiComponent',
        'jquery',
        'Magento_Checkout/js/action/get-totals',
        'mage/storage',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/quote'
    ],
    function (Component, $, getTotals, storage, totals, resourceUrlManager, quote) {
        'use strict';

        return Component.extend({

            initialize: function () {
                this._super();

                /**
                 * Observe the onclick event on all payment methods.
                 */
                $('body').on(
                    'click',
                    '.payment-methods input[type="radio"][name="payment[method]"]',
                    this.savePaymentMethod
                );
            },

            /**
             * Save the selected payment method.
             */
            savePaymentMethod: function() {
                /**
                 * Build the URL for saving the selected payment method.
                 */
                var params = (resourceUrlManager.getCheckoutMethod() == 'guest') ? {quoteId: quote.getQuoteId()} : {};
                var urls = {
                    'guest': '/guest-carts/:quoteId/totals',
                    'customer': '/carts/mine/set-payment-information'
                };
                var url = resourceUrlManager.getUrl(urls, params);

                /**
                 * Send the selected payment method, along with a cart identifier, the billing address and a 'skip
                 * validation' flag to the save payment method API.
                 */
                storage.post(
                    url,
                    /**
                     * The APi expects a JSON object with the selected payment method and the selected billing address.
                     */
                    JSON.stringify(
                        {
                            paymentMethod: {
                                method: $('.payment-methods input[type="radio"][name="payment[method]"]:checked').val(),
                                additional_data: {
                                    buckaroo_skip_validation: true
                                }
                            },
                            billingAddress: quote.billingAddress()
                        }
                    )
                ).done(
                    function () {
                        /**
                         * Update the totals in the summary block.
                         *
                         * While the method is called 'getTotals', it will actually fetch the latest totals from
                         * Magento's API and then update the entire summary block.
                         *
                         * Please note that the empty array is required for this function. it may contain callbacks,
                         * however these MUST return true for the function to work as expected. otherwise it will
                         * silently crash.
                         */
                        getTotals([]);
                    }
                ).error(
                    function () {
                        totals.isLoading(false);
                    }
                );
            }
        });
    }
);
