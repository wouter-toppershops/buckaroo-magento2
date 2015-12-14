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

namespace TIG\Buckaroo\Model\Validator;

use \TIG\Buckaroo\Model\ValidatorInterface;
use \Magento\Framework\Pricing\Helper\Data as PricingHelper;

/**
 * Class Amount
 *
 * @package TIG\Buckaroo\Model\Validator
 */
class Amount implements ValidatorInterface
{
    public $pricingHelper;

    /**
     * @param PricingHelper $priceHelper
     */
    public function __construct(PricingHelper $priceHelper)
    {
        $this->pricingHelper = $priceHelper;
    }
    /**
     * @param $data
     *
     * @return boolean
     */
    public function validate($data)
    {
        return true;
    }

    /**
     * @param $baseTotal
     * @param $orderAmount
     * @param $message
     * @param $brq_amount
     *
     * @return bool|string
     */
    public function validatePayment($baseTotal, $orderAmount, $message, $brq_amount)
    {
        $description  = '<b> ' .$message .' :</b><br/>';
        /**
         * Determine whether too much or not has been paid
         * @todo Move this over to an new validator class.
         */
        if ($baseTotal > $brq_amount) {
            $description .= __(
                'Not enough paid: %1 has been transfered. Order grand total was: %2.',
                $this->pricingHelper->currency($brq_amount, true, false),
                $this->pricingHelper->currency($orderAmount, true, false)
            );
        } elseif ($baseTotal < $brq_amount) {
            $description .= __(
                'Too much paid: %1 has been transfered. Order grand total was: %2.',
                $this->pricingHelper->currency($brq_amount, true, false),
                $this->pricingHelper->currency($orderAmount, true, false)
            );
        } else {
            $description = false;
        }
        return $description;
    }
}