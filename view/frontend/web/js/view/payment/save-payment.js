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
                $('body').on(
                    'click',
                    '.payment-methods input[type="radio"][name="payment[method]"]',
                    this.savePaymentMethod
                );
            },

            savePaymentMethod: function() {
                var params = (resourceUrlManager.getCheckoutMethod() == 'guest') ? {quoteId: quote.getQuoteId()} : {};
                var urls = {
                    'guest': '/guest-carts/:quoteId/totals',
                    'customer': '/carts/mine/set-payment-information'
                };
                var url = resourceUrlManager.getUrl(urls, params);

                storage.post(
                    url,
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
