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
 * @copyright Copyright (c) 2015 Total Internet Group B.V. (http://www.tig.nl)
 * @license   http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
namespace TIG\Buckaroo\Gateway\Http;

class Bpe3 implements \TIG\Buckaroo\Gateway\GatewayInterface
{
    /**
     * @var \Magento\Payment\Gateway\Http\Client\Soap
     */
    protected $client;

    /**
     * @var \Magento\Framework\Data\ObjectFactory
     */
    protected $objectFactory;

    /**
     * @var \TIG\Buckaroo\Model\ConfigProvider\Factory #configProviderFactory
     */
    protected $configProviderFactory;

    /**
     * @var int
     */
    protected $mode;

    /**
     * @var \TIG\Buckaroo\Debug\Debugger $debugger
     */
    public $debugger;

    /**
     * Bpe3 constructor.
     *
     * @param \TIG\Buckaroo\Gateway\Http\Client\Soap     $client
     * @param \Magento\Framework\Data\ObjectFactory      $objectFactory
     * @param \TIG\Buckaroo\Model\ConfigProvider\Factory $configProviderFactory
     * @param \TIG\Buckaroo\Debug\Debugger               $debugger
     */
    public function __construct(
        \TIG\Buckaroo\Gateway\Http\Client\Soap $client,
        \Magento\Framework\Data\ObjectFactory $objectFactory,
        \TIG\Buckaroo\Model\ConfigProvider\Factory $configProviderFactory,
        \TIG\Buckaroo\Debug\Debugger $debugger
    ) {
        $this->client                = $client;
        $this->objectFactory         = $objectFactory;
        $this->configProviderFactory = $configProviderFactory;
        $this->debugger              = $debugger;
    }

    /**
     * @param int $mode
     *
     * @return $this
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * @param \TIG\Buckaroo\Gateway\Http\Transaction $transaction
     *
     * @return array
     * @throws \Exception
     */
    public function order(Transaction $transaction)
    {
        return $this->doRequest($transaction);
    }

    /**
     * @param \TIG\Buckaroo\Gateway\Http\Transaction $transaction
     *
     * @return array
     * @throws \Exception
     */
    public function capture(Transaction $transaction)
    {
        return $this->doRequest($transaction);
    }

    /**
     * @param \TIG\Buckaroo\Gateway\Http\Transaction $transaction
     *
     * @return array
     * @throws \Exception
     */
    public function authorize(Transaction $transaction)
    {
        return $this->doRequest($transaction);
    }

    /**
     * @param \TIG\Buckaroo\Gateway\Http\Transaction $transaction
     *
     * @return array
     * @throws \Exception|\TIG\Buckaroo\Exception
     */
    public function refund(Transaction $transaction)
    {
        /**
         * @var \TIG\Buckaroo\Model\ConfigProvider\Refund $refundConfig
         */
        $refundConfig = $this->configProviderFactory->get('refund');

        if ($refundConfig->getEnabled()) {
            return $this->doRequest($transaction);
        }

        $this->debugger->addToMessage('Failed to refund because the configuration is set to disabled')->log();
        throw new \TIG\Buckaroo\Exception(__("Online refunds are currently disabled for Buckaroo payment methods."));
    }

    /**
     * @param \TIG\Buckaroo\Gateway\Http\Transaction $transaction
     *
     * @return array
     * @throws \Exception
     */
    public function cancel(Transaction $transaction)
    {
        return $this->void($transaction);
    }

    /**
     * @param \TIG\Buckaroo\Gateway\Http\Transaction $transaction
     *
     * @return array
     * @throws \Exception
     */
    public function void(Transaction $transaction)
    {
        return $this->doRequest($transaction);
    }

    /**
     * @return string
     *
     * @throws \TIG\Buckaroo\Exception|\LogicException
     */
    protected function getWsdl()
    {
        if (!$this->mode) {
            throw new \LogicException("Cannot do a Buckaroo transaction when 'mode' is not set or set to 0.");
        }

        /**
         * @var \TIG\Buckaroo\Model\ConfigProvider\Predefined $predefinedConfig
         */
        $predefinedConfig = $this->configProviderFactory->get('predefined');

        switch ($this->mode) {
            case \TIG\Buckaroo\Helper\Data::MODE_TEST:
                $wsdl = $predefinedConfig->getWsdlTestWeb();
                break;
            case \TIG\Buckaroo\Helper\Data::MODE_LIVE:
                $wsdl = $predefinedConfig->getWsdlLiveWeb();
                break;
            default:
                throw new \TIG\Buckaroo\Exception(
                    __(
                        "Invalid mode set: %1",
                        [
                            $this->mode
                        ]
                    )
                );
        }

        return $wsdl;
    }

    /**
     * @param Transaction $transaction
     *
     * @return array
     * @throws \Exception
     */
    public function doRequest(Transaction $transaction)
    {
        /**
         * @var \Magento\Payment\Gateway\Http\Transfer $transfer
         */
        $transfer = $this->objectFactory->create(
            '\Magento\Payment\Gateway\Http\Transfer',
            [
                'clientConfig' => [
                    'wsdl' => $this->getWsdl()
                ],
                'headers'  => $transaction->getHeaders(),
                'body'     => $transaction->getBody(),
                'auth'     => [], // The authorization is done by the request headers and encryption.
                'method'   => $transaction->getMethod(),
                'uri'      => '', // The URI is part of the wsdl file.
                'encode'   => false
            ]
        );

        $this->client->setStore($transaction->getStore());

        return $this->client->placeRequest($transfer);
    }
}
