/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals',
        'TIG_Buckaroo/js/model/buckaroo-fee'
    ],
    function (Component, quote, totals, BuckarooFee) {
        'use strict';

        return Component.extend({
            defaults            : {
                template : 'TIG_Buckaroo/summary/buckaroo_fee'
            },
            totals              : quote.getTotals(),
            model               : {},
            excludingTaxMessage : '(Excluding Tax)',
            includingTaxMessage : '(Including Tax)',

            /**
             * @override
             */
            initialize : function (options) {
                this.model = new BuckarooFee(this.level);

                return this._super(options);
            },

            /**
             * Get gift wrapping price based on options.
             * @returns {int}
             */
            getValue : function () {
                var price = 0,
                    buckarooFeeSegment;

                if (
                    this.totals() &&
                    totals.getSegment('buckaroo_fee') &&
                    totals.getSegment('buckaroo_fee').hasOwnProperty('extension_attributes')
                ) {
                    buckarooFeeSegment = totals.getSegment('buckaroo_fee')['extension_attributes'];

                    switch (this.level) {
                        case 'order':
                            price = buckarooFeeSegment.hasOwnProperty('buckaroo_fee') ?
                                buckarooFeeSegment['buckaroo_fee'] :
                                0;
                            break;
                    }
                }

                return this.getFormattedPrice(price);
            },

            /**
             * Get gift wrapping price (including tax) based on options.
             * @returns {int}
             */
            getIncludingTaxValue : function () {
                var price = 0,
                    buckarooFeeSegment;

                if (
                    this.totals() &&
                    totals.getSegment('buckaroo_fee') &&
                    totals.getSegment('buckaroo_fee').hasOwnProperty('extension_attributes')
                ) {
                    buckarooFeeSegment = totals.getSegment('buckaroo_fee')['extension_attributes'];

                    switch (this.level) {
                        case 'order':
                            price = buckarooFeeSegment.hasOwnProperty('buckaroo_fee_incl_tax') ?
                                buckarooFeeSegment['buckaroo_fee_incl_tax'] :
                                0;
                            break;
                    }
                }

                return this.getFormattedPrice(price);
            },

            /**
             * Check gift wrapping option availability.
             * @returns {Boolean}
             */
            isAvailable : function () {
                var isAvailable = false,
                    buckarooFeeSegment;

                if (!this.isFullMode()) {
                    return false;
                }

                if (
                    this.totals() &&
                    totals.getSegment('buckaroo_fee') &&
                    totals.getSegment('buckaroo_fee').hasOwnProperty('extension_attributes')
                ) {
                    buckarooFeeSegment = totals.getSegment('buckaroo_fee')['extension_attributes'];

                    isAvailable = buckarooFeeSegment.length > 0;
                }

                return isAvailable;
            },

            /**
             * Check if both gift wrapping prices should be displayed.
             * @returns {Boolean}
             */
            displayBothPrices : function () {
                var displayBothPrices = false;

                switch (this.level) {
                    case 'order':
                        displayBothPrices = this.model.displayBothprices();
                        break;
                }

                return displayBothPrices;
            },

            /**
             * Check if gift wrapping prices should be displayed including tax.
             * @returns {Boolean}
             */
            displayPriceInclTax : function () {
                var displayPriceInclTax = false;

                switch (this.level) {
                    case 'order':
                        displayPriceInclTax = this.model.displayInclTaxPrice();
                        break;
                }

                return displayPriceInclTax && !this.displayBothPrices();
            },

            /**
             * Check if gift wrapping prices should be displayed excluding tax.
             * @returns {Boolean}
             */
            displayPriceExclTax : function () {
                return !this.displayPriceInclTax() && !this.displayBothPrices();
            }
        });
    }
);
