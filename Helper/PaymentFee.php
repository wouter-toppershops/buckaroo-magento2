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

namespace TIG\Buckaroo\Helper;

class PaymentFee extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Tax\Helper\Data
     */
    public $taxHelper;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    public $catalogHelper;

    /**
     * @param \Magento\Tax\Helper\Data     $taxHelper
     * @param \Magento\Catalog\Helper\Data $catalogHelper
     */
    public function __construct(
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Catalog\Helper\Data $catalogHelper
    ) {
        $this->taxHelper = $taxHelper;
        $this->catalogHelper = $catalogHelper;
    }

    /**
     * Get shipping price
     *
     * @param  float                                           $price
     * @param  bool|null                                       $includingTax
     * @param  \Magento\Customer\Model\Address|null            $shippingAddress
     * @param  int|null                                        $ctc
     * @param  null|string|bool|int|\Magento\Store\Model\Store $store
     * @return float
     */
    public function getPaymentFeePrice(
        $price,
        $includingTax = null,
        $shippingAddress = null,
        $ctc = null,
        $store = null
    ) {
        $pseudoProduct = new \Magento\Framework\DataObject();
        /** @noinspection PhpUndefinedMethodInspection */
        $pseudoProduct->setTaxClassId(4); /** @todo make dynamic (get from configprovider) */

        $billingAddress = false;
        /** @noinspection PhpUndefinedMethodInspection */
        if ($shippingAddress && $shippingAddress->getQuote() && $shippingAddress->getQuote()->getBillingAddress()) {
            /** @noinspection PhpUndefinedMethodInspection */
            $billingAddress = $shippingAddress->getQuote()->getBillingAddress();
        }

        $price = $this->catalogHelper->getTaxPrice(
            $pseudoProduct,
            $price,
            $includingTax,
            $shippingAddress,
            $billingAddress,
            $ctc,
            $store,
            $this->paymentFeeIncludesTax($store)
        );

        return $price;
    }

    /**
     * Check if payment fee prices include tax
     *
     * @param  null|string|bool|int|\Magento\Store\Model\Store $store
     * @return bool
     */
    public function paymentFeeIncludesTax($store = null)
    {
        return true; /** @todo make dynamic (get from configprovider) */
        return $this->_config->shippingPriceIncludesTax($store);
    }
}
