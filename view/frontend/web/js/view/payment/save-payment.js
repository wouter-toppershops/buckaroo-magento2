/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'uiComponent',
        'jquery',
        'Magento_Checkout/js/action/get-totals'
    ],
    function (Component, $, getTotals) {
        'use strict';

        return Component.extend({

            initialize: function () {
                this._super();
                $('body').on(
                    'click',
                    '.payment-methods input[type="radio"][name="payment[method]"]',
                    getTotals.bind(this, {'test': function() {}})
                );
                return this;
            }
        });
    }
);
