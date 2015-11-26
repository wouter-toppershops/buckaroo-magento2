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
namespace TIG\Buckaroo\Gateway\Http;

class Transaction
{
    /**
     * @var array
     */
    protected $_data = [];

    /**
     * @var array
     */
    protected $_services = [];

    /**
     * @var array
     */
    protected $_additionalParameters = [];

    /**
     * @var array
     */
    protected $_customParameters = [];

    /**
     * @var string
     */
    protected $_method;

    /**
     * @return array
     */
    public function getAdditionalParameters()
    {
        return $this->_additionalParameters;
    }

    /**
     * @param array $additionalParameters
     *
     * @return $this
     */
    public function setAdditionalParameters($additionalParameters)
    {
        $this->_additionalParameters = $additionalParameters;

        return $this;
    }

    /**
     * @return array
     */
    public function getCustomParameters()
    {
        return $this->_customParameters;
    }

    /**
     * @param array $customParameters
     *
     * @return $this
     */
    public function setCustomParameters($customParameters)
    {
        $this->_customParameters = $customParameters;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->_data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getServices()
    {
        return $this->_services;
    }

    /**
     * @param array $services
     *
     * @return $this
     */
    public function setServices($services)
    {
        $this->_services = $services;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * @param string $method
     *
     * @return $this
     */
    public function setMethod($method)
    {
        $this->_method = $method;

        return $this;
    }

    /**
     * @return array
     */
    public function getBody()
    {
        $body = [
            'test' => '1',
            'Currency' => 'EUR',
            'AmountDebit' => 10.00,
            'AmountCredit' => 0,
            'Invoice' => 1,
            'Order' => 1,
            'Description' => 'Test',
            'ClientIP' => [
                '_' => $_SERVER['REMOTE_ADDR'],
                'Type' => 'IPv4',
            ],
            'ReturnURL' => 'http://magento2.cow3299.com/',
            'ReturnURLCancel' => 'http://magento2.cow3299.com/',
            'ReturnURLError' => 'http://magento2.cow3299.com/',
            'ReturnURLReject' => 'http://magento2.cow3299.com/',
            'OriginalTransactionKey' => null,
            'StartRecurrent' => false,
            'PushURL' => 'http://magento2.cow3299.com/index.php/rest/V1/buckaroo/push',
            'Services' => [
                'Global' => null,
                'Service' => [
                    'RequestParameter' => [
                        '_' => 'RABONL2U',
                        'Name' => 'Issuer',
                        'Group' => null,
                    ],
                    'Name' => 'ideal',
                    'Action' => 'Pay',
                    'Version' => 2,
                ]
            ],
        ];

        return $body;
    }

    /**
     * @returns array
     */
    public function getHeaders()
    {
        $headers[] = new \SoapHeader(
            'https://checkout.buckaroo.nl/PaymentEngine/',
            'MessageControlBlock',
            [
                'Id' => '_control',
                'WebsiteKey' => 'SniACG6eSj',
                'Culture' => 'nl-NL',
                'TimeStamp' => 1448553322,
                'Channel' => 'Web',
                'Software' => [
                    'PlatformName' => 'Magento 2',
                    'PlatformVersion' => '2.0.0',
                    'ModuleSupplier' => 'TIG',
                    'ModuleName' => 'Buckaroo',
                    'ModuleVersion' => '0.1.0',
                ]
            ],
            false
        );

        $headers[] = new \SoapHeader(
            'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd',
            'Security',
            [
                'Signature' => [
                    'SignedInfo' => [
                        'CanonicalizationMethod' => [
                            'Algorithm' => 'http://www.w3.org/2001/10/xml-exc-c14n#',
                        ],
                        'SignatureMethod' => [
                            'Algorithm' => 'http://www.w3.org/2000/09/xmldsig#rsa-sha1',
                        ],
                        'Reference' => [
                            [
                                'Transforms' => [
                                    [
                                        'Algorithm' => 'http://www.w3.org/2001/10/xml-exc-c14n#',
                                    ]
                                ],
                                'DigestMethod' => [
                                    'Algorithm' => 'http://www.w3.org/2000/09/xmldsig#sha1',
                                ],
                                'DigestValue' => '',
                                'URI' => '#_body',
                                'Id' => null,
                            ],
                            [
                                'Transforms' => [
                                    [
                                        'Algorithm' => 'http://www.w3.org/2001/10/xml-exc-c14n#',
                                    ]
                                ],
                                'DigestMethod' => [
                                    'Algorithm' => 'http://www.w3.org/2000/09/xmldsig#sha1',
                                ],
                                'DigestValue' => '',
                                'URI' => '#_control',
                                'Id' => null,
                            ]
                        ]
                    ],
                    'SignatureValue' => '',
                ],
                'KeyInfo' => ' ',
            ],
            false
        );

        return $headers;
    }
}