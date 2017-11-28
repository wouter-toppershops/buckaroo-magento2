<?php
/**
 *
 *          ..::..
 *     ..::::::::::::..
 *   ::'''''':''::'''''::
 *   ::..  ..:  :  ....::
 *   ::::  :::  :  :   ::
 *   ::::  :::  :  ''' ::
 *   ::::..:::..::.....::
 *     ''::::::::::::''
 *          ''::''
 *
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
 * @copyright   Copyright (c) Total Internet Group B.V. https://tig.nl/copyright
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
namespace TIG\Buckaroo\Model\Config\Source\PayPerEmail;

/**
 * Class AfterpayPaymentMethods
 *
 * @package TIG\Buckaroo\Model\Config\Source
 */
class PaymentMethodsAfterExpiry implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => 'amex',               'label' => __('American Express')],
            ['value' => 'eps',                'label' => __('EPS')],
            ['value' => 'giftcard',           'label' => __('Giftcards')],
            ['value' => 'giropay',            'label' => __('Giropay')],
            ['value' => 'ideal',              'label' => __('iDeal')],
            ['value' => 'mastercard',         'label' => __('Mastercard')],
            ['value' => 'paypal',             'label' => __('PayPal')],
            ['value' => 'sofortueberweisung', 'label' => __('Sofort Banking')],
            ['value' => 'transfer',           'label' => __('Bank Transfer')],
            ['value' => 'visa',               'label' => __('Visa')],
            ['value' => 'maestro',            'label' => __('eMaestro')],
            ['value' => 'visaelectron',       'label' => __('Visa Electron')],
            ['value' => 'vpay',               'label' => __('V PAY')]
        ];

        return $options;
    }
}