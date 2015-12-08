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
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */

namespace TIG\Buckaroo\Model\ConfigProvider;

use Magento\Payment\Model\CcGenericConfigProvider;

class Creditcard extends CcGenericConfigProvider
{
    /**#@+
     * Creditcard service codes.
     */
    const CREDITCARD_SERVICE_CODE_MASTERCARD    = 'mastercard';
    const CREDITCARD_SERVICE_CODE_VISA          = 'visa';
    const CREDITCARD_SERVICE_CODE_AMEX          = 'Amex';
    const CREDITCARD_SERVICE_CODE_MAESTRO       = 'maestro';
    const CREDITCARD_SERVICE_CODE_VPAY          = 'Vpay';
    const CREDITCARD_SERVICE_CODE_VISAELECTRON  = 'visaelectron';
    const CREDITCARD_SERVICE_CODE_CARTEBLEUE    = 'cartebleuevisa';
    const CREDITCARD_SERVICE_CODE_CARTEBANCAIRE = 'cartebancaire';
    /**#@-*/

    /**
     * @return array|void
     */
    public function getConfig()
    {

        $config = parent::getConfig();
        //@TODO: get cards dynamic
        $config = array_merge_recursive($config, [
            'payment' => [
                'buckaroo' => [
                    'creditcards' => [
                        [
                            'name' => 'American Express',
                            'code' => self::CREDITCARD_SERVICE_CODE_AMEX,
                            'img' => 'ico-ae'
                        ],
                        [
                            'name' => 'Carte Bancaire',
                            'code' => self::CREDITCARD_SERVICE_CODE_CARTEBANCAIRE,
                            'img' => 'ico-cb'
                        ],
                        [
                            'name' => 'Carte Bleue',
                            'code' => self::CREDITCARD_SERVICE_CODE_CARTEBLEUE,
                            'img' => 'ico-cbl'
                        ],
                        [
                            'name' => 'Maestro',
                            'code' => self::CREDITCARD_SERVICE_CODE_MAESTRO,
                            'img' => 'ico-mae'
                        ],
                        [
                            'name' => 'MasterCard',
                            'code' => self::CREDITCARD_SERVICE_CODE_MASTERCARD,
                            'img' => 'ico-mc'
                        ],
                        [
                            'name' => 'VISA',
                            'code' => self::CREDITCARD_SERVICE_CODE_VISA,
                            'img' => 'ico-vi'
                        ],
                        [
                            'name' => 'VISA Electron',
                            'code' => self::CREDITCARD_SERVICE_CODE_VISAELECTRON,
                            'img' => 'ico-ve'
                        ],
                        [
                            'name' => 'VPay',
                            'code' => self::CREDITCARD_SERVICE_CODE_VPAY,
                            'img' => 'ico-vp'
                        ],
                    ],
                    'response' => [],
                ],
            ],
        ]);

        return $config;
    }
}
