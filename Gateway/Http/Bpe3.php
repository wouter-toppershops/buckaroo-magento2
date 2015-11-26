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

use Magento\Payment\Gateway\Http\Client\Soap;
use Magento\Framework\Data\ObjectFactory;
use TIG\Buckaroo\Gateway\GatewayInterface;

class Bpe3 implements GatewayInterface
{
    /**
     * @var \Magento\Payment\Gateway\Http\Client\Soap
     */
    protected $_client;

    /**
     * @var \Magento\Framework\Data\ObjectFactory
     */
    protected $_objectFactory;

    /**
     * Bpe3 constructor.
     *
     * @param \Magento\Payment\Gateway\Http\Client\Soap $client
     * @param \Magento\Framework\Data\ObjectFactory     $objectFactory
     */
    public function __construct(
        Soap $client,
        ObjectFactory $objectFactory
    )
    {
        $this->_client = $client;
        $this->_objectFactory = $objectFactory;
    }

    /**
     * @param \TIG\Buckaroo\Gateway\Http\Transaction $transaction
     *
     * @return array
     * @throws \Exception
     */
    public function capture(Transaction $transaction)
    {
        return $this->_doRequest($transaction);
    }

    /**
     * @param \TIG\Buckaroo\Gateway\Http\Transaction $transaction
     *
     * @return array
     * @throws \Exception
     */
    public function authorize(Transaction $transaction)
    {
        return $this->_doRequest($transaction);
    }

    /**
     * @param \TIG\Buckaroo\Gateway\Http\Transaction $transaction
     *
     * @return array
     * @throws \Exception
     */
    public function refund(Transaction $transaction)
    {
        return $this->_doRequest($transaction);
    }

    /**
     * @param Transaction $transaction
     *
     * @return array
     * @throws \Exception
     */
    protected function _doRequest(Transaction $transaction)
    {
        /** @var \TIG\Buckaroo\Gateway\Http\Transfer $transfer */
        $transfer = $this->_objectFactory->create(
            '\TIG\Buckaroo\Gateway\Http\Transfer',
            [
                'clientConfig' => [
                    'wsdl' => 'https://checkout.buckaroo.nl/soap/soap.svc?wsdl'
                ],
                'headers'  => $this->_getHeaders(),
                'body'     => $transaction->getBody(),
                'auth'     => [], //auth,
                'method'   => $transaction->getMethod(),
                'uri'      => 'https://testcheckout.buckaroo.nl/soap/',
                'encode'   => false
            ]
        );

        /**
         * @param array $clientConfig
         * @param array $headers
         * @param array|string $body
         * @param array $auth
         * @param string $method
         * @param string $uri
         * @param bool $encode
         */
        return $this->_client->placeRequest($transfer);
    }

    /**
     * @returns array
     */
    protected function _getHeaders()
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
                        ]
                    ]
                ],
                'SignatureValue' => '',
                'KeyInfo' => null,
            ],
            false
        );

        return $headers;
    }
}