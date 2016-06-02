<?php
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
 * to servicedesk@totalinternetgroup.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@totalinternetgroup.nl for more information.
 *
 * @copyright   Copyright (c) 2016 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */

namespace TIG\Buckaroo\Model\ConfigProvider\Method;

/**
 * @method getDueDate()
 * @method getSendEmail()
 */
class Afterpay extends AbstractConfigProvider
{
    const XPATH_AFTERPAY_ACTIVE                 = 'payment/tig_buckaroo_afterpay/active';
    const XPATH_AFTERPAY_PAYMENT_FEE            = 'payment/tig_buckaroo_afterpay/payment_fee';
    const XPATH_AFTERPAY_PAYMENT_FEE_LABEL      = 'payment/tig_buckaroo_afterpay/payment_fee_label';
    const XPATH_AFTERPAY_SEND_EMAIL             = 'payment/tig_buckaroo_afterpay/send_email';
    const XPATH_AFTERPAY_ACTIVE_STATUS          = 'payment/tig_buckaroo_afterpay/active_status';
    const XPATH_AFTERPAY_ORDER_STATUS_SUCCESS   = 'payment/tig_buckaroo_afterpay/order_status_success';
    const XPATH_AFTERPAY_ORDER_STATUS_FAILED    = 'payment/tig_buckaroo_afterpay/order_status_failed';
    const XPATH_AFTERPAY_AVAILABLE_IN_BACKEND   = 'payment/tig_buckaroo_afterpay/available_in_backend';
    const XPATH_AFTERPAY_DUE_DATE               = 'payment/tig_buckaroo_afterpay/due_date';
    const XPATH_AFTERPAY_ALLOWED_CURRENCIES     = 'payment/tig_buckaroo_afterpay/allowed_currencies';
    const XPATH_AFTERPAY_BUSINESS               = 'payment/tig_buckaroo_afterpay/business';
    const XPATH_AFTERPAY_PAYMENT_METHODS        = 'payment/tig_buckaroo_afterpay/payment_method';

    /**
     * @return array
     */
    public function getConfig()
    {
        if (!$this->scopeConfig->getValue(self::XPATH_AFTERPAY_ACTIVE)) {
            return [];
        }

        $paymentFeeLabel = $this->getBuckarooPaymentFeeLabel(\TIG\Buckaroo\Model\Method\Afterpay::PAYMENT_METHOD_CODE);

        return [
            'payment' => [
                'buckaroo' => [
                    'afterpay' => [
                        'sendEmail'         => (bool) $this->getSendEmail(),
                        'paymentFeeLabel'   => $paymentFeeLabel,
                        'allowedCurrencies' => $this->getAllowedCurrencies(),
                        'businessMethod'    => $this->getBusiness(),
                        'paymentMethod'     => $this->getPaymentMethod(),
                    ],
                    'response' => [],
                ],
            ],
        ];
    }

    /**
     * businessMethod 1 = B2C
     * businessMethod 2 = B2B
     * businessMethod 3 = Both
     *
     * @return bool|int
     */
    public function getBusiness()
    {
        $business = (int) $this->scopeConfig->getValue(
            self::XPATH_AFTERPAY_BUSINESS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $business ? $business : false;
    }

    /**
     * paymentMethod 1 = afterpayacceptgiro
     * paymentMethod 2 = afterpaydigiaccept
     * @return bool|int
     */
    public function getPaymentMethod()
    {
        $paymentMethod = (int) $this->scopeConfig->getValue(
            self::XPATH_AFTERPAY_PAYMENT_METHODS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $paymentMethod ? $paymentMethod : false;
    }

    /**
     * Get the methods name
     * @return bool|string
     */
    public function getPaymentMethodName()
    {
        $paymentMethodName = false;

        if ($this->getPaymentMethod()) {
            switch ($this->getPaymentMethod()) {
                case '1':
                    $paymentMethodName = 'afterpayacceptgiro';
                    break;
                case '2':
                    $paymentMethodName = 'afterpaydigiaccept';
            }
        }

        return $paymentMethodName;
    }

    /**
     * @return float
     */
    public function getPaymentFee()
    {
        $paymentFee = $this->scopeConfig->getValue(
            self::XPATH_AFTERPAY_PAYMENT_FEE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $paymentFee ? $paymentFee : false;
    }
}
