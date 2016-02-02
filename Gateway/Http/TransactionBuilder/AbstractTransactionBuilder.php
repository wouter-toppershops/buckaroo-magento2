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

namespace TIG\Buckaroo\Gateway\Http\TransactionBuilder;

abstract class AbstractTransactionBuilder implements \TIG\Buckaroo\Gateway\Http\TransactionBuilderInterface
{
    /**
     * Module supplier.
     */
    const MODULE_SUPPLIER = 'TIG';

    /**
     * Module code.
     */
    const MODULE_CODE = 'TIG_Buckaroo';

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var array
     */
    protected $services;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \TIG\Buckaroo\Model\ConfigProvider\Factory
     */
    protected $configProviderFactory;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $remoteAddress;

    /**
     * @var \TIG\Buckaroo\Model\ConfigProvider\Method\Factory
     */
    protected $configProviderMethodFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var bool
     */
    protected $startRecurrent = false;

    /**
     * @var null|string
     */
    protected $originalTransactionKey = null;

    /**
     * @var null|string
     */
    protected $channel = 'Web';

    /**
     * @var int
     */
    public $amount;

    /**
     * @var string
     */
    public $currency;

    /**
     * {@inheritdoc}
     */
    public function setOriginalTransactionKey($originalTransactionKey)
    {
        $this->originalTransactionKey = $originalTransactionKey;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getOriginalTransactionKey()
    {
        return $this->originalTransactionKey;
    }

    /**
     * {@inheritdoc}
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param boolean $startRecurrent
     *
     * @return $this
     */
    public function setStartRecurrent($startRecurrent)
    {
        $this->startRecurrent = $startRecurrent;

        return $this;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     *
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     *
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * TransactionBuilder constructor.
     *
     * @param \Magento\Framework\App\ProductMetadataInterface      $productMetadata
     * @param \Magento\Framework\Module\ModuleListInterface        $moduleList
     * @param \Magento\Framework\UrlInterface                      $urlBuilder
     * @param \TIG\Buckaroo\Model\ConfigProvider\Factory           $configProviderFactory
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param \TIG\Buckaroo\Model\ConfigProvider\Method\Factory    $configProviderMethodFactory
     * @param \Magento\Framework\ObjectManagerInterface            $objectManager
     * @param null                                                 $amount
     * @param null                                                 $currency
     */
    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\UrlInterface $urlBuilder,
        \TIG\Buckaroo\Model\ConfigProvider\Factory $configProviderFactory,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \TIG\Buckaroo\Model\ConfigProvider\Method\Factory $configProviderMethodFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $amount = null,
        $currency = null
    ) {
        $this->productMetadata             = $productMetadata;
        $this->moduleList                  = $moduleList;
        $this->urlBuilder                  = $urlBuilder;
        $this->configProviderFactory       = $configProviderFactory;
        $this->remoteAddress               = $remoteAddress;
        $this->configProviderMethodFactory = $configProviderMethodFactory;
        $this->objectManager               = $objectManager;

        if ($amount !== null) {
            $this->amount = $amount;
        }

        if ($currency !== null) {
            $this->currency = $currency;
        }
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * {@inheritdoc}
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return array
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * {@inheritdoc}
     */
    public function setServices($services)
    {
        $this->services = $services;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * {@inheritdoc}
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return \TIG\Buckaroo\Gateway\Http\Transaction
     */
    public function build()
    {
        $transaction = $this->objectManager->create(
            '\TIG\Buckaroo\Gateway\Http\Transaction',
            [
                'body'    => $this->getBody(),
                'headers' => $this->getHeaders(),
                'method'  => $this->getMethod(),
            ]
        );

        return $transaction;
    }

    /**
     * @return array
     */
    abstract public function getBody();

    /**
     * @returns array
     */
    public function getHeaders()
    {
        $module = $this->moduleList->getOne(self::MODULE_CODE);

        /** @var \TIG\Buckaroo\Model\ConfigProvider\Account $accountConfig */
        $accountConfig = $this->configProviderFactory->get('account');

        $headers[] = new \SoapHeader(
            'https://checkout.buckaroo.nl/PaymentEngine/',
            'MessageControlBlock',
            [
                'Id'                => '_control',
                'WebsiteKey'        => $accountConfig->getMerchantKey(),
                'Culture'           => 'nl-NL',
                'TimeStamp'         => time(),
                'Channel'           => $this->channel,
                'Software'          => [
                    'PlatformName'      => $this->productMetadata->getName()
                                         . ' - '
                                         . $this->productMetadata->getEdition(),
                    'PlatformVersion'   => $this->productMetadata->getVersion(),
                    'ModuleSupplier'    => self::MODULE_SUPPLIER,
                    'ModuleName'        => $module['name'],
                    'ModuleVersion'     => $module['setup_version'],
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
