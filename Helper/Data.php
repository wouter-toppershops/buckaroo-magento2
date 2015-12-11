<?php
namespace TIG\Buckaroo\Helper;

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
     * @param (int) $value
     *
     * @return mixed|null
     */
    public function getStatusByValue($value)
    {
        if (array_search($value, $this->statusCodes)) {
            return array_search($value, $this->statusCodes);
        }
        return null;
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
