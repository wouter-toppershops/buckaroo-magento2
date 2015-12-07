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
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote'
    ],
    function (ko, Component, quote) {
        'use strict';

        return Component.extend({
            initObservable: function () {

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
                 * Bind this values to the input field.
                 */
                this.bankaccountholder = ko.observable('');
                this.bankaccountnumber = ko.observable('');
                this.bicnumber = ko.observable('');

                /**
                 * Check if the required fields are filled. If so: enable place order button | ifnot: disable place order button
                 */
                this.accountNumberIsValid = ko.computed( function () {
                    if (this.isnl())
                    {
                        return !(this.bankaccountholder() == '' || this.bankaccountnumber() == '');
                    } else {
                        return !(this.bankaccountholder() == '' || this.bicnumber() == '');
                    }
                }, this);

                return this;
            },
            defaults: {
                template: 'TIG_Buckaroo/payment/tig_buckaroo_sepadirectdebit'
            }
        });
    }
);
