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
 * @copyright Copyright (c) 2015 Total Internet Group B.V. (http://www.tig.nl)
 * @license   http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/payment/additional-validators',
        'TIG_Buckaroo/js/action/place-order',
        'Magento_Checkout/js/model/quote',
        'ko',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Ui/js/lib/knockout/bindings/datepicker'
        /*,
         'jquery/validate'*/
    ],
    function (
        $,
        Component,
        additionalValidators,
        placeOrderAction,
        quote,
        ko,
        checkoutData,
        selectPaymentMethodAction
    ) {
        'use strict';

        return Component.extend(
            {
                defaults                : {
                    template : 'TIG_Buckaroo/payment/tig_buckaroo_payperemail',
                    businessMethod: null,
                    paymentMethod: null,
                    selectedGender: null,
                    firstName: null,
                    lastName: null,
                    email: null,
                    CustomerFirsName: null,
                    CustomerLastName: null,
                    CustomerEmail: null,
                    BillingFirstName: null,
                    BillingLastName: null,
                    BillingEmail: null,
                    genderValidate: null
                },
                redirectAfterPlaceOrder : true,
                paymentFeeLabel : window.checkoutConfig.payment.buckaroo.payperemail.paymentFeeLabel,
                currencyCode : window.checkoutConfig.quoteData.quote_currency_code,
                baseCurrencyCode : window.checkoutConfig.quoteData.base_currency_code,

                /**
                 * @override
                 */
                initialize : function (options) {
                    if (checkoutData.getSelectedPaymentMethod() == options.index) {
                        window.checkoutConfig.buckarooFee.title(this.paymentFeeLabel);
                    }

                    return this._super(options);
                },

                initObservable: function () {
                    this._super().observe(
                        [
                            'businessMethod',
                            'paymentMethod',
                            'selectedGender',
                            'firstname',
                            'lastname',
                            'email',
                            'CustomerFirstName',
                            'CustomerLastName',
                            'CustomerEmail',
                            'BillingFirstName',
                            'BillingLastName',
                            'BillingEmail',
                            'genderValidate',
                            'dummy'
                        ]
                    );

                    this.paymentMethod  = window.checkoutConfig.payment.buckaroo.payperemail.paymentMethod;
                    this.firstName      = quote.billingAddress().firstname;
                    this.lastName       = quote.billingAddress().lastname;
                    this.email          = customerData.email || quote.guestEmail;

                    /**
                     * Observe customer first & lastname
                     */
                    this.CustomerFirstName = ko.computed(
                        function () {
                            return this.firstName;
                        },
                        this
                    );
                    this.BillingFirstName(this.CustomerFirstName());


                    this.CustomerLastName = ko.computed(
                        function () {
                            return this.lastName;
                        },
                        this
                    );
                    this.BillingLastName(this.CustomerLastName());

                    this.CustomerEmail = ko.computed(
                        function () {
                            return this.email;
                        },
                        this
                    );
                    this.BillingEmail(this.CustomerEmail());

                    /**
                     * observe radio buttons
                     * check if selected
                     */
                    var self = this;
                    this.setSelectedGender = function (value) {
                        self.selectedGender(value);
                        return true;
                    };

                    this.setSelectedGender(customerData.gender);

                    /**
                     * Validation on the input fields
                     */

                    var runValidation = function () {
                        $('.' + this.getCode() + ' [data-validate]').filter(':not([name*="agreement"])').valid();
                        additionalValidators.validate();
                    };

                    this.BillingFirstName.subscribe(runValidation,this);
                    this.BillingLastName.subscribe(runValidation,this);
                    this.genderValidate.subscribe(runValidation,this);


                    /**
                     * Check if the required fields are filled. If so: enable place order button (true) | if not: disable place order button (false)
                     */
                    this.buttoncheck = ko.computed(
                        function () {
                            return this.selectedGender() !== null &&
                            this.BillingFirstName() !== null &&
                            this.BillingLastName() !== null &&
                            this.genderValidate() !== null &&
                            this.validate()
                        },
                        this
                    );

                    /**
                     * The agreement checkbox won't force an update of our bindings. So check for changes manually and notify
                     * the bindings if something happend. Use $.proxy() to access the local this object. The dummy property is
                     * used to notify the bindings.
                     */
                    $('.payment-methods').on(
                        'click',
                        '.' + this.getCode() + ' [name*="agreement"]',
                        $.proxy(
                            function () {
                                this.dummy.notifySubscribers();
                            },
                            this
                        )
                    );

                    return this;
                },

                /**
                 * Place order.
                 *
                 * @todo To override the script used for placeOrderAction, we need to override the placeOrder method
                 *          on our parent class (Magento_Checkout/js/view/payment/default) so we can
                 *
                 *          placeOrderAction has been changed from Magento_Checkout/js/action/place-order to our own
                 *          version (TIG_Buckaroo/js/action/place-order) to prevent redirect and handle the response.
                 */
                placeOrder: function (data, event) {
                    var self = this,
                        placeOrder;

                    if (event) {
                        event.preventDefault();
                    }

                    if (this.validate() && additionalValidators.validate()) {
                        this.isPlaceOrderActionAllowed(false);
                        placeOrder = placeOrderAction(this.getData(), this.redirectAfterPlaceOrder, this.messageContainer);

                        $.when(placeOrder).fail(
                            function () {
                                self.isPlaceOrderActionAllowed(true);
                            }
                        ).done(this.afterPlaceOrder.bind(this));
                        return true;
                    }
                    return false;
                },

                afterPlaceOrder: function () {
                    var response = window.checkoutConfig.payment.buckaroo.response;
                    response = $.parseJSON(response);
                    if (response.RequiredAction !== undefined && response.RequiredAction.RedirectURL !== undefined) {
                        window.location.replace(response.RequiredAction.RedirectURL);
                    }
                },

                selectPaymentMethod: function () {
                    window.checkoutConfig.buckarooFee.title(this.paymentFeeLabel);

                    selectPaymentMethodAction(this.getData());
                    checkoutData.setSelectedPaymentMethod(this.item.method);
                    return true;
                },

                /**
                 * Run validation function
                 */

                validate: function () {
                    return (
                    $('.' + this.getCode() + ' [data-validate]:not([name*="agreement"])').valid() &&
                    additionalValidators.validate()
                    );
                },

                getData: function () {
                    return {
                        "method": this.item.method,
                        "po_number": null,
                        "additional_data": {
                            "customer_gender" : this.genderValidate(),
                            "customer_billingFirstName" : this.BillingFirstName(),
                            "customer_billingLastName" : this.BillingLastName(),
                            "customer_email" : this.BillingLastName()
                        }
                    };
                }
            }
        );
    }
);

