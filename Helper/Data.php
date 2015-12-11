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

/**
 * Class Data
 *
 * @package TIG\Buckaroo\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * TIG_Buckaroo status codes
     *
     * @var array $statusCode
     */
    protected $statusCodes = [
        'TIG_BUCKAROO_STATUSCODE_SUCCESS'               => 190,
        'TIG_BUCKAROO_STATUSCODE_FAILED'                => 490,
        'TIG_BUCKAROO_STATUSCODE_VALIDATION_FAILURE'    => 491,
        'TIG_BUCKAROO_STATUSCODE_TECHNICAL_ERROR'       => 492,
        'TIG_BUCKAROO_STATUSCODE_REJECTED'              => 690,
        'TIG_BUCKAROO_STATUSCODE_WAITING_ON_USER_INPUT' => 790,
        'TIG_BUCKAROO_STATUSCODE_PENDING_PROCESSING'    => 791,
        'TIG_BUCKAROO_STATUSCODE_WAITING_ON_CONSUMER'   => 792,
        'TIG_BUCKAROO_STATUSCODE_PAYMENT_ON_HOLD'       => 793,
        'TIG_BUCKAROO_STATUSCODE_CANCELLED_BY_USER'     => 890,
        'TIG_BUCKAROO_STATUSCODE_CANCELLED_BY_MERCHANT' => 891,

        /**
         * Codes below are created by TIG, not by Buckaroo.
         */
        'TIG_BUCKAROO_ORDER_FAILED'                     => 11014,
    ];

    /**
     * Return the requested status $code, or null if not found
     *
     * @param $code
     *
     * @return int|null
     */
    public function getStatusCode($code)
    {
        if (isset($this->statusCodes[$code])) {
            return $this->statusCodes[$code];
        }
        return null;
    }

    /**
     * Return the requested status key with the value, or null if not found
     *
     * @param int $value
     *
     * @return mixed|null
     */
    public function getStatusByValue($value)
    {
        $result = array_search($value, $this->statusCodes);
        if (!$result) {
            $result = null;
        }
        return $result;
    }

    /**
     * Return all status codes currently set
     *
     * @return array
     */
    public function getStatusCodes()
    {
        return $this->statusCodes;
    }
}
