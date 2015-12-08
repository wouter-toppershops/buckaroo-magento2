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

class Ideal extends CcGenericConfigProvider
{

    /**
     * @return array|void
     */
    public function getConfig()
    {

        $config = parent::getConfig();
        //@TODO: get banks dynamic
        $config = array_merge_recursive($config, [
            'payment' => [
                'buckaroo' => [
                    'banks' => [
                        [
                            'name' => 'ABN AMRO',
                            'code' => 'ABNANL2A',
                            'img' => 'ico-abn'
                        ],
                        [
                            'name' => 'ASN Bank',
                            'code' => 'ASNBNL21',
                            'img' => 'ico-asn'
                        ],
                        [
                            'name' => 'ING',
                            'code' => 'INGBNL2A',
                            'img' => 'ico-ing'
                        ],
                        [
                            'name' => 'Rabobank',
                            'code' => 'RABONL2U',
                            'img' => 'ico-rb'
                        ],
                        [
                            'name' => 'SNS Bank',
                            'code' => 'SNSBNL2A',
                            'img' => 'ico-sns'
                        ],
                        [
                            'name' => 'RegioBank',
                            'code' => 'RBRBNL21',
                            'img' => 'ico-regio'
                        ],
                        [

                            'name' => 'Triodos Bank',
                            'code' => 'TRIONL2U',
                            'img' => 'ico-trio'
                        ],
                        [
                            'name' => 'Van Lanschot',
                            'code' => 'FVLBNL22',
                            'img' => 'ico-lans'
                        ],
                        [
                            'name' => 'Knab Bank',
                            'code' => 'KNABNL2H',
                            'img' => 'ico-knab'
                        ],
                    ],
                    'response' => [],
                ],
            ],
        ]);

        return $config;
    }
}
