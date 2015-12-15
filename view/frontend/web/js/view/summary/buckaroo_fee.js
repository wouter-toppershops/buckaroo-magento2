/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote'
    ],
    function (Component, quote) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'TIG_Buckaroo/summary/buckaroo_fee'
            },
            totals: quote.getTotals(),
            isDisplayed: function() {
                return this.isFullMode() && this.getValue() != 0;
            },
            getValue: function() {
                var price = this.totals().buckaroo_fee;
                return this.getFormattedPrice(price);
            }
        });
    }
);
