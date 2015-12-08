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

namespace TIG\Buckaroo\Model\Validator;

/**
 * Class Push
 *
 * @package TIG\Buckaroo\Model\Validator
 */
class Push implements \TIG\Buckaroo\Model\ValidatorInterface
{

    const BUCKAROO_SUCCESS           = 'BUCKAROO_SUCCESS';
    const BUCKAROO_FAILED            = 'BUCKAROO_FAILED';
    const BUCKAROO_ERROR             = 'BUCKAROO_ERROR';
    const BUCKAROO_NEUTRAL           = 'BUCKAROO_NEUTRAL';
    const BUCKAROO_PENDING_PAYMENT   = 'BUCKAROO_PENDING_PAYMENT';
    const BUCKAROO_INCORRECT_PAYMENT = 'BUCKAROO_INCORRECT_PAYMENT';
    const BUCKAROO_REJECTED          = 'BUCKAROO_REJECTED';

    /**
     *  List of possible response codes sent by buckaroo.
     *  This is the list for the BPE 3.0 gateway.
     *  @var array $bpeResponseCodes
     */
    public $bpeResponseCodes = array(
        190 => array(
            'message' => 'Success',
            'status'  => self::BUCKAROO_SUCCESS,
        ),
        490 => array(
            'message' => 'Payment failure',
            'status'  => self::BUCKAROO_FAILED,
        ),
        491 => array(
            'message' => 'Validation error',
            'status'  => self::BUCKAROO_FAILED,
        ),
        492 => array(
            'message' => 'Technical error',
            'status'  => self::BUCKAROO_ERROR,
        ),
        690 => array(
            'message' => 'Payment rejected',
            'status'  => self::BUCKAROO_REJECTED,
        ),
        790 => array(
            'message' => 'Waiting for user input',
            'status'  => self::BUCKAROO_PENDING_PAYMENT,
        ),
        791 => array(
            'message' => 'Waiting for processor',
            'status'  => self::BUCKAROO_PENDING_PAYMENT,
        ),
        792 => array(
            'message' => 'Waiting on consumer action',
            'status'  => self::BUCKAROO_PENDING_PAYMENT,
        ),
        793 => array(
            'message' => 'Payment on hold',
            'status'  => self::BUCKAROO_PENDING_PAYMENT,
        ),
        890 => array(
            'message' => 'Cancelled by consumer',
            'status'  => self::BUCKAROO_FAILED,
        ),
        891 => array(
            'message' => 'Cancelled by merchant',
            'status'  => self::BUCKAROO_FAILED,
        ),
    );

    /**
     * Checks if the status code is returned by the bpe push and is valid.
     * @param $code
     *
     * @return Array
     */
    public function validateStatusCode($code)
    {
        if ( isset($this->bpeResponseCodes[$code]) ) {
            return $this->bpeResponseCodes[$code];
        }else{
            return array(
                'message' => 'Onbekende responsecode: ' . $code,
                'status'  => self::BUCKAROO_NEUTRAL,
                'code'    => $code,
            );
        }
    }

    /**
     * @param $signature
     *
     * @todo build the validator that determines the signature using array sorting and the SHA1 hash algorithm
     * @return bool
     */
    public function validateSignature($signature)
    {
        return true;
    }

}