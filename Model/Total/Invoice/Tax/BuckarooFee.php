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
 * @copyright Copyright (c) 2015 Total Internet Group B.V. (http://www.tig.nl)
 * @license   http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
namespace TIG\Buckaroo\Model\Total\Invoice\Tax;

class BuckarooFee extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    /**
     * Collect buckaroo fee tax totals
     *
     * @param  \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $order = $invoice->getOrder();

        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        $buckarooFeeTaxAmountLeft = $order->getBuckarooFeeTaxAmount() - $order->getBuckarooFeeTaxAmountInvoiced();
        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        $baseBuckarooFeeTaxAmountLeft = $order->getBuckarooFeeBaseTaxAmount()
            - $order->getBuckarooFeeBaseTaxAmountInvoiced();

        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        if ($order->getBuckarooFeeBaseTaxAmount() && $baseBuckarooFeeTaxAmountLeft > 0) {
            if ($baseBuckarooFeeTaxAmountLeft < $invoice->getBaseGrandTotal()) {
                $invoice->setGrandTotal($invoice->getGrandTotal() + $buckarooFeeTaxAmountLeft);
                $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseBuckarooFeeTaxAmountLeft);
            } else {
                $buckarooFeeTaxAmountLeft = $invoice->getTaxAmount();
                $baseBuckarooFeeTaxAmountLeft = $invoice->getBaseTaxAmount();

                $invoice->setGrandTotal(0);
                $invoice->setBaseGrandTotal(0);
            }

            $invoice->setTaxAmount($invoice->getTaxAmount() + $buckarooFeeTaxAmountLeft);
            $invoice->setBaseTaxAmount($invoice->getBaseTaxAmount() + $baseBuckarooFeeTaxAmountLeft);

            /**
             * @noinspection PhpUndefinedMethodInspection
             */
            $invoice->setBuckarooFeeTaxAmount($buckarooFeeTaxAmountLeft);
            /**
             * @noinspection PhpUndefinedMethodInspection
             */
            $invoice->setBuckarooFeeBaseTaxAmount($baseBuckarooFeeTaxAmountLeft);
        }

        return $this;
    }
}
