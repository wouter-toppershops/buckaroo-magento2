/**
 *                  ___________       __            __
 *                  \__    ___/____ _/  |_ _____   |  |
 *                    |    |  /  _ \\   __\\__  \  |  |
 *                    |    | |  |_| ||  |   / __ \_|  |__
 *                    |____|  \____/ |__|  (____  /|____/
 *                                              \/
 *          ___          __                                   __
 *         |   |  ____ _/  |_   ____ _______   ____    ____ _/  |_
 *         |   | /    \\   __\_/ __ \\_  __ \ /    \ _/ __ \\   __\
 *         |   ||   |  \|  |  \  ___/ |  | \/|   |  \\  ___/ |  |
 *         |___||___|  /|__|   \_____>|__|   |___|  / \_____>|__|
 *                  \/                           \/
 *                  ________
 *                 /  _____/_______   ____   __ __ ______
 *                /   \  ___\_  __ \ /  _ \ |  |  \\____ \
 *                \    \_\  \|  | \/|  |_| ||  |  /|  |_| |
 *                 \______  /|__|    \____/ |____/ |   __/
 *                        \/                       |__|
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Creative Commons License.
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to servicedesk@tig.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@tig.nl for more information.
 *
 * @copyright   Copyright (c) 2015 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
/*browser:true*/
/*global define*/
define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/payment/additional-validators',
        'jquery/validate',
        'mage/translate'
    ],
    function (ko, $, Component, quote, additionalValidators) {
        'use strict';

        /**
         *
         * Validate IBAN and BIC number
         *
         */

        function isValidIBAN($v){ //This function check if the checksum if correct
            $v = $v.replace(/^(.{4})(.*)$/,"$2$1"); //Move the first 4 chars from left to the right
            $v = $v.replace(/[A-Z]/g,function($e){return $e.charCodeAt(0) - 'A'.charCodeAt(0) + 10}); //Convert A-Z to 10-25
            var $sum = 0;
            var $ei = 1; //First exponent
            for(var $i = $v.length - 1; $i >= 0; $i--){
                $sum += $ei * parseInt($v.charAt($i),10); //multiply the digit by it's exponent
                $ei = ($ei * 10) % 97; //compute next base 10 exponent  in modulus 97
            };
            return $sum % 97 == 1;
        }

        /**
         * Add validation methods
         * */

        $.validator.addMethod(
            'IBAN', function (value) {
                var patternIBAN = new RegExp('^[a-zA-Z]{2}[0-9]{2}[a-zA-Z0-9]{4}[0-9]{7}([a-zA-Z0-9]?){0,16}$');
                return (patternIBAN.test(value) && isValidIBAN(value));
            },$.mage.__('Enter Valid IBAN'));

        $.validator.addMethod(
            'BIC', function (value) {
                var patternBIC = new RegExp('^([a-zA-Z]){4}([a-zA-Z]){2}([0-9a-zA-Z]){2}([0-9a-zA-Z]{3})?$');
                return patternBIC.test(value);
            }, $.mage.__('Enter Valid BIC number'));

        /**
         * check country requires IBAN or BIC field
         * */

        return Component.extend({
            /**
             *
             * Include template
             *
             */

            defaults: {
                template: 'TIG_Buckaroo/payment/tig_buckaroo_sepadirectdebit',
                bankaccountholder: '',
                bankaccountnumber: '',
                bicnumber: '',
                minimumWords: 2
            },

            initObservable: function () {
                this._super().observe(['bankaccountholder', 'bankaccountnumber', 'bicnumber', 'minimumWords']);

                /**
                 * check if country is NL, if so load: bank account number | ifnot load: bicnumber
                 */
                this.isnl = ko.computed( function () {
                    var address = quote.billingAddress();

                    if (address === null)
                    {
                        return false;
                    }

                    return address.countryId == 'NL';
                }, this);

                /**
                 * Run validation on the three inputfields
                 */

                var runValidation = function () {
                    $('.' + this.getCode() + ' [data-validate]').valid();
                };
                this.bankaccountholder.subscribe(runValidation,this);
                this.bankaccountnumber.subscribe(runValidation,this);
                this.bicnumber.subscribe(runValidation,this);

                /**
                 * Check if the required fields are filled. If so: enable place order button | if not: disable place order button
                 */
                this.accountNumberIsValid = ko.computed( function () {
                    if (this.isnl())
                    {
                        return (this.bankaccountholder().length >= this.minimumWords() && this.bankaccountnumber().length > 0);
                    } else {
                        return (this.bankaccountholder().length >= this.minimumWords() && this.bicnumber().length > 0);

                    }
                }, this);

                return this;
            },

            /**
             * Run function
             */

            validate: function () {
                return $('.' + this.getCode() + ' [data-validate]').valid();
            },

            getData: function() {
                return {
                    "method": this.item.method,
                    "po_number": null,
                    "additional_data": {
                        "customer_bic": this.bicnumber(),
                        "customer_iban": this.bankaccountnumber(),
                        "customer_account_name": this.bankaccountholder()
                    }
                };
            }
        });
    }
);


