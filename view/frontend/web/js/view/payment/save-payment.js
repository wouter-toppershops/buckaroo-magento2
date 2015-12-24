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
                    function() {
                        var params = (resourceUrlManager.getCheckoutMethod() == 'guest') ? {quoteId: quote.getQuoteId()} : {};
                        var urls = {
                            'guest': '/guest-carts/:quoteId/totals',
                            'customer': '/carts/mine/set-payment-information'
                        };
                        var url = resourceUrlManager.getUrl(urls, params);

                        totals.isLoading(true);
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
                                getTotals({'test': function() {}});
                            }
                        ).error(
                            function () {
                                totals.isLoading(false);
                            }
                        ).always(
                            function () {
                                totals.isLoading(false);
                            }
                        );
                    }
                );
                return this;
            }
        });
    }
);
