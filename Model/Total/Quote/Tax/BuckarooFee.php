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

namespace TIG\Buckaroo\Model\Total\Quote\Tax;

use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;

class BuckarooFee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    const QUOTE_TYPE = 'buckaroo_fee';
    const CODE_QUOTE_GW = 'buckaroo_fee';

    /**
     * @var \TIG\Buckaroo\Model\ConfigProvider\Method\Factory
     */
    protected $configProviderFactory;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    public $priceCurrency;

    /**
     * @param \TIG\Buckaroo\Model\ConfigProvider\Method\Factory $configProviderFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \TIG\Buckaroo\Model\ConfigProvider\Method\Factory $configProviderFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        $this->setCode('pretax_ buckaroo_fee');

        $this->configProviderFactory = $configProviderFactory;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Collect gift wrapping related items and add them to tax calculation
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        if (!$shippingAssignment->getItems()) {
            return $this;
        }
//        $paymentMethod = $quote->getPayment()->getMethod();
//        if (!$paymentMethod || strpos($paymentMethod, 'tig_buckaroo_') !== 0) {
//            return $this;
//        }
//
//        $methodInstance = $quote->getPayment()->getMethodInstance();
//        if (!$methodInstance instanceof \TIG\Buckaroo\Model\Method\AbstractMethod) {
//            throw new \LogicException('Buckaroo payment fee is only available for Buckaroo payment methods.');
//        }
//
//        $buckarooPaymentMethodCode = $methodInstance->buckarooPaymentMethodCode;
//        if (!$this->configProviderFactory->has($buckarooPaymentMethodCode)) {
//            return $this;
//        }
//
//        $configProvider = $this->configProviderFactory->get($buckarooPaymentMethodCode);
//
//        $basePaymentFee = $configProvider->getPaymentFee();
        $basePaymentFee = 10;

        $paymentFee = $this->priceCurrency->convert($basePaymentFee, $quote->getStore());

        $productTaxClassId = 4; /** @todo make dynamic (get from configprovider) */

        $address = $shippingAssignment->getShipping()->getAddress();
        $associatedTaxables = $address->getAssociatedTaxables();
        if (!$associatedTaxables) {
            $associatedTaxables = [];
        }

        $associatedTaxables[] = [
            CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TYPE => self::QUOTE_TYPE,
            CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_CODE => self::CODE_QUOTE_GW,
            CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_UNIT_PRICE => $paymentFee,
            CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_BASE_UNIT_PRICE => $basePaymentFee,
            CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_QUANTITY => 1,
            CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TAX_CLASS_ID => $productTaxClassId,
            CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_PRICE_INCLUDES_TAX => false,
            CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_ASSOCIATION_ITEM_CODE
            => CommonTaxCollector::ASSOCIATION_ITEM_CODE_FOR_QUOTE,
        ];

        $address->setAssociatedTaxables($associatedTaxables);

        return $this;
    }
}
