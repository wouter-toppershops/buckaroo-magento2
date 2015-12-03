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

namespace TIG\Buckaroo\Gateway\Http\Client;

use Magento\Framework\Webapi\Soap\ClientFactory;
use Magento\Payment\Gateway\Http\ConverterInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;

class Soap implements \Magento\Payment\Gateway\Http\ClientInterface
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var ConverterInterface | null
     */
    protected $converter;

    /**
     * @var ClientFactory
     */
    protected $clientFactory;

    /**
     * @param Logger $logger
     * @param ClientFactory $clientFactory
     * @param ConverterInterface | null $converter
     */
    public function __construct(
        Logger $logger,
        \TIG\Buckaroo\Soap\ClientFactory $clientFactory,
        ConverterInterface $converter = null
    ) {
        $this->logger = $logger;
        $this->converter = $converter;
        $this->clientFactory = $clientFactory;
    }

    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     * @return array
     * @throws \Magento\Payment\Gateway\Http\ClientException
     * @throws \Magento\Payment\Gateway\Http\ConverterException
     * @throws \Exception
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $this->logger->debug(['request' => $transferObject->getBody()]);

        $client = $this->clientFactory->create(
            $transferObject->getClientConfig()['wsdl'],
            ['trace' => true]
        );

        try {
            $client->__setSoapHeaders($transferObject->getHeaders());

            $response = $client->__soapCall(
                $transferObject->getMethod(),
                [$transferObject->getBody()]
            );

            $result = $this->converter
                ? $this->converter->convert(
                    $response
                )
                : [$response];

            $result['request_xml'] = $client->__getLastRequest();
            $result['response_xml'] = $client->__getLastResponse();

            $this->logger->debug(['response' => $result]);
        } catch (\Exception $e) {
            $this->logger->debug(['trace' => $client->__getLastRequest()]);
            throw $e;
        }

        return $result;
    }
}