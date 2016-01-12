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

use TIG\Buckaroo\Exception;

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
     * @var \TIG\Buckaroo\Debug\Debugger $debugger
     */
    public $debugger;

    /**
     * Bpe3 constructor.
     *
     * @param \TIG\Buckaroo\Gateway\Http\Client\Soap $client
     * @param \Magento\Framework\Data\ObjectFactory  $objectFactory
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
     * @throws \Exception
     */
    public function refund(Transaction $transaction)
    {
        $return = false;

        /** @var \TIG\Buckaroo\Model\ConfigProvider\Refund $refundConfig */
        $refundConfig = $this->configProviderFactory->get('refund');
        if ($refundConfig->getEnabled()) {
            $return = $this->doRequest($transaction);
        }

        $this->debugger->addToMessage('Failed to refund because the configuration is set to disabled')->log();

        return $return;
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
     * @param Transaction $transaction
     *
     * @return array
     * @throws \Exception
     */
    protected function doRequest(Transaction $transaction)
    {
        /** @var \TIG\Buckaroo\Gateway\Http\Transfer $transfer */
        $transfer = $this->objectFactory->create(
            '\TIG\Buckaroo\Gateway\Http\Transfer',
            [
                'clientConfig' => [
                    'wsdl' => 'https://checkout.buckaroo.nl/soap/soap.svc?wsdl'
                ],
                'headers'  => $transaction->getHeaders(),
                'body'     => $transaction->getBody(),
                'auth'     => [], //auth,
                'method'   => $transaction->getMethod(),
                'uri'      => 'https://testcheckout.buckaroo.nl/soap/',
                'encode'   => false
            ]
        );

        return $this->client->placeRequest($transfer);
    }
}
