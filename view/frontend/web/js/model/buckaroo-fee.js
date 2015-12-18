/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'uiElement',
        'underscore'
    ],
    function(uiElement, _) {
        "use strict";

        var buckarooFeeConfig = window.buckarooConfig ?
            window.buckarooConfig.buckarooFee :
            window.checkoutConfig.buckarooFee;

        var provider = uiElement();

        return function(itemId) {
            return {
                itemId: itemId,
                observables: {},
                getConfigValue: function(key) {
                    return buckarooFeeConfig[key];
                },
                getPriceFormat: function() {
                    return window.buckarooConfig.priceFormat;
                },

                /**
                 * Get gift wrapping price display mode.
                 * @returns {Boolean}
                 */
                displayWrappingBothPrices: function () {
                    return !!buckarooFeeConfig.displayWrappingBothPrices;
                },

                /**
                 * Get printed card price display mode.
                 * @returns {Boolean}
                 */
                displayCardBothPrices: function () {
                    return !!buckarooFeeConfig.displayCardBothPrices;
                },

                /**
                 * Get gift wrapping price display mode.
                 * @returns {Boolean}
                 */
                displayGiftWrappingInclTaxPrice: function () {
                    return !!buckarooFeeConfig.displayGiftWrappingInclTaxPrice;
                },

                /**
                 * Get printed card price display mode.
                 * @returns {Boolean}
                 */
                displayCardInclTaxPrice: function () {
                    return !!buckarooFeeConfig.displayCardInclTaxPrice;
                }
            };
        };
    }
);
