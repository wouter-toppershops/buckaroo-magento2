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
 * @copyright   Copyright (c) 2015 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */

namespace TIG\Buckaroo\Block\Order;

use \Magento\Sales\Model\Order;

class Totals extends \Magento\Sales\Block\Order\Totals
{
    /**
     * @var \TIG\Buckaroo\Helper\PaymentFee
     */
    protected $helper = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry                      $registry
     * @param \TIG\Buckaroo\Helper\PaymentFee                  $helper
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \TIG\Buckaroo\Helper\PaymentFee $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $registry);
        $this->initTotals();
    }

    /**
     * Initialize buckaroo fee totals for order/invoice/creditmemo
     *
     * @return $this
     */
    public function initTotals()
    {
        $this->_initTotals();
        /** @noinspection PhpUndefinedMethodInspection */
        $source = $this->getSource();
        $totals = $this->helper->getBuckarooPaymentFeeTotal($source);
        if (!empty($totals)) {
            $this->addTotalBefore(new \Magento\Framework\DataObject($totals[0]), 'grand_total');
        }
        return $this->_totals;
    }

    /**
     * get totals array for visualization
     *
     * @param array|null $area
     * @return array
     */
    public function getTotals($area = null)
    {
        $totals = [];
        if ($area === null) {
            $totals = $this->initTotals();
        } else {
            $area = (string) $area;
            foreach ($this->_totals as $total) {
                /** @noinspection PhpUndefinedMethodInspection */
                $totalArea = (string) $total->getArea();
                if ($totalArea == $area) {
                    $totals[] = $total;
                }
            }
        }
        return $totals;
    }
}
