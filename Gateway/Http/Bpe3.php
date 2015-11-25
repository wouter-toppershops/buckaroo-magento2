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

    public function capture(Transaction $transaction)
    {
        /** @var \TIG\Buckaroo\Gateway\Http\Transfer $transfer */
        $transfer = $this->_objectFactory->create(
            '\TIG\Buckaroo\Gateway\Http\Transfer',
            [
                [], //client config
                [], //headers
                $transaction->getBody(),
                [], //auth,
                $transaction->getmethod(),
                false
            ]
        );

        $this->_client->placeRequest($transfer);
    }

    public function authorize(Transaction $transaction)
    {
        /** @var \TIG\Buckaroo\Gateway\Http\Transfer $transfer */
        $transfer = $this->_objectFactory->create(
            '\TIG\Buckaroo\Gateway\Http\Transfer',
            [
                [], //client config
                [], //headers
                $transaction->getBody(),
                [], //auth,
                $transaction->getmethod(),
                false
            ]
        );

        $this->_client->placeRequest($transfer);
    }

    public function refund(Transaction $transaction)
    {
        /** @var \TIG\Buckaroo\Gateway\Http\Transfer $transfer */
        $transfer = $this->_objectFactory->create(
            '\TIG\Buckaroo\Gateway\Http\Transfer',
            [
                [], //client config
                [], //headers
                $transaction->getBody(),
                [], //auth,
                $transaction->getmethod(),
                false
            ]
        );

        $this->_client->placeRequest($transfer);
    }
}