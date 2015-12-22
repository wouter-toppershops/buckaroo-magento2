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
namespace TIG\Buckaroo\Model\Total\Quote;

class BuckarooFee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
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
        $this->setCode('buckaroo_fee');

        $this->configProviderFactory = $configProviderFactory;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Collect grand total address amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     *
     * @throws \LogicException
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        if (!$shippingAssignment->getItems()) {
            return $this;
        }

        $paymentMethod = $quote->getPayment()->getMethod();
        if (!$paymentMethod || strpos($paymentMethod, 'tig_buckaroo_') !== 0) {
            return $this;
        }

        $methodInstance = $quote->getPayment()->getMethodInstance();
        if (!$methodInstance instanceof \TIG\Buckaroo\Model\Method\AbstractMethod) {
            throw new \LogicException('Buckaroo payment fee is only available for Buckaroo payment methods.');
        }

        $buckarooPaymentMethodCode = $methodInstance->buckarooPaymentMethodCode;
        if (!$this->configProviderFactory->has($buckarooPaymentMethodCode)) {
            return $this;
        }

        $configProvider = $this->configProviderFactory->get($buckarooPaymentMethodCode);
        $basePaymentFee = $configProvider->getPaymentFee();

        if ($basePaymentFee < 0.01) {
            return $this;
        }

        $paymentFee = $this->priceCurrency->convert($basePaymentFee, $quote->getStore());

        /** @noinspection PhpUndefinedMethodInspection */
        $quote->setBuckarooFee($paymentFee);
        /** @noinspection PhpUndefinedMethodInspection */
        $quote->setBaseBuckarooFee($basePaymentFee);

        /** @noinspection PhpUndefinedMethodInspection */
        $total->setBuckarooFee($paymentFee);
        /** @noinspection PhpUndefinedMethodInspection */
        $total->setBaseBuckarooFee($basePaymentFee);

        /** @noinspection PhpUndefinedMethodInspection */
        $total->setBaseGrandTotal($total->getBaseGrandTotal() + $basePaymentFee);
        /** @noinspection PhpUndefinedMethodInspection */
        $total->setGrandTotal($total->getGrandTotal() + $paymentFee);

        return $this;
    }

    /**
     * Add buckaroo fee information to address
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $totals = [
            'code' => $this->getCode(),
            'title' => $this->getLabel(),
            'buckaroo_fee' => $total->getBuckarooFee(),
            'base_buckaroo_fee' => $total->getBaseBuckarooFee(),
            'buckaroo_fee_incl_tax' => $total->getBuckarooFeeInclTax(),
            'base_buckaroo_fee_incl_tax' => $total->getBaseBuckarooFeeInclTax(),
            'buckaroo_fee_tax_amount' => $total->getBuckarooFeeTaxAmount(),
            'buckaroo_fee_base_tax_amount' => $total->getBuckarooFeeBaseTaxAmount(),
        ];

        return $totals;
    }


    /**
     * Get Buckaroo label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Payment Fee');
    }
}
