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

use \TIG\Buckaroo\Model\Config\Source\Display\Type as DisplayType;

class PaymentFee extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \TIG\Buckaroo\Model\ConfigProvider\BuckarooFee
     */
    protected $configProvider;

    /**
     * @param \Magento\Framework\App\Helper\Context          $context
     * @param \TIG\Buckaroo\Model\ConfigProvider\BuckarooFee $configProvider
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \TIG\Buckaroo\Model\ConfigProvider\BuckarooFee $configProvider
    ) {
        parent::__construct($context);

        $this->configProvider = $configProvider;
    }

    /**
     * Return totals of data object
     *
     * @param  \Magento\Framework\DataObject $dataObject
     * @return array
     */
    public function getTotals($dataObject)
    {
        $totals = [];

        $displayBothPrices = false;
        $displayIncludeTaxPrice = false;

        if ($dataObject instanceof \Magento\Sales\Model\Order ||
            $dataObject instanceof \Magento\Sales\Model\Order\Invoice ||
            $dataObject instanceof \Magento\Sales\Model\Order\Creditmemo
        ) {
            $displayBothPrices = $this->displaySalesBothPrices();
            $displayIncludeTaxPrice = $this->displaySalesIncludeTaxPrice();
        } elseif ($dataObject instanceof \Magento\Quote\Model\Quote\Address\Total) {
            $displayBothPrices = $this->displayCartBothPrices();
            $displayIncludeTaxPrice = $this->displayCartIncludeTaxPrice();
        }

        /**
         * Buckaroo fee for order totals
         */
        if ($displayBothPrices || $displayIncludeTaxPrice) {
            if ($displayBothPrices) {
                /** @noinspection PhpUndefinedMethodInspection */
                $this->addTotalToTotals(
                    $totals,
                    'buckaroo_fee_excl',
                    $dataObject->getBuckarooFee(),
                    $dataObject->getBasebuckarooFee(),
                    'Buckaroo Fee (Excl. Tax)'
                );
            }
            /** @noinspection PhpUndefinedMethodInspection */
            $this->addTotalToTotals(
                $totals,
                'buckaroo_fee_incl',
                $dataObject->getBuckarooFee() + $dataObject->getBuckarooFeeTaxAmount(),
                $dataObject->getBasebuckarooFee() + $dataObject->getBuckarooFeeBaseTaxAmount(),
                'Buckaroo Fee (Incl. Tax)'
            );
        } else {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->addTotalToTotals(
                $totals,
                'buckaroo_fee',
                $dataObject->getBuckarooFee(),
                $dataObject->getBasebuckarooFee(),
                'Buckaroo Fee'
            );
        }

        return $totals;
    }

    /**
     * Check ability to display prices including tax for buckaroo fee in shopping cart
     *
     * @param \Magento\Store\Model\Store|int|null $store
     * @return bool
     */
    public function displayCartIncludeTaxPrice($store = null)
    {
        $configValue = $this->configProvider->getPriceDisplayCart($store);

        return $configValue == DisplayType::DISPLAY_TYPE_BOTH ||
        $configValue == DisplayType::DISPLAY_TYPE_INCLUDING_TAX;
    }

    /**
     * Check ability to display prices excluding tax for buckaroo fee in shopping cart
     *
     * @param \Magento\Store\Model\Store|int|null $store
     * @return bool
     */
    public function displayCartExcludeTaxPrice($store = null)
    {
        $configValue = $this->configProvider->getPriceDisplayCart($store);

        return $configValue == DisplayType::DISPLAY_TYPE_EXCLUDING_TAX;
    }

    /**
     * Check ability to display both prices for buckaroo fee in shopping cart
     *
     * @param \Magento\Store\Model\Store|int|null $store
     * @return bool
     */
    public function displayCartBothPrices($store = null)
    {
        $configValue = $this->configProvider->getPriceDisplayCart($store);

        return $configValue == DisplayType::DISPLAY_TYPE_BOTH;
    }

    /**
     * Check ability to display prices including tax for buckaroo fee in backend sales
     *
     * @param \Magento\Store\Model\Store|int|null $store
     * @return bool
     */
    public function displaySalesIncludeTaxPrice($store = null)
    {
        $configValue = $this->configProvider->getPriceDisplaySales($store);

        return $configValue == DisplayType::DISPLAY_TYPE_BOTH ||
        $configValue == DisplayType::DISPLAY_TYPE_INCLUDING_TAX;
    }

    /**
     * Check ability to display prices excluding tax for buckaroo fee in backend sales
     *
     * @param \Magento\Store\Model\Store|int|null $store
     * @return bool
     */
    public function displaySalesExcludeTaxPrice($store = null)
    {
        $configValue = $this->configProvider->getPriceDisplaySales($store);

        return $configValue == DisplayType::DISPLAY_TYPE_EXCLUDING_TAX;
    }

    /**
     * Check ability to display both prices for buckaroo fee in backend sales
     *
     * @param \Magento\Store\Model\Store|int|null $store
     * @return bool
     */
    public function displaySalesBothPrices($store = null)
    {
        $configValue = $this->configProvider->getPriceDisplaySales($store);

        return $configValue == DisplayType::DISPLAY_TYPE_BOTH;
    }

    /**
     * @param \Magento\Store\Model\Store|int|null $store
     *
     * @return mixed
     */
    public function getBuckarooFeeTaxClass($store = null)
    {
        $configValue = $this->configProvider->getTaxClass($store);

        return $configValue;
    }

    /**
     * Add total into array totals
     *
     * @param  array &$totals
     * @param  string $code
     * @param  float $value
     * @param  float $baseValue
     * @param  string $label
     * @return void
     */
    protected function addTotalToTotals(&$totals, $code, $value, $baseValue, $label)
    {
        if ($value == 0 && $baseValue == 0) {
            return;
        }
        $total = ['code' => $code, 'value' => $value, 'base_value' => $baseValue, 'label' => $label];
        $totals[] = $total;
    }
}
