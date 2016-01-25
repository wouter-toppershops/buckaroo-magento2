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

namespace TIG\Buckaroo\Soap;

class ClientFactory extends \Magento\Framework\Webapi\Soap\ClientFactory
{
    /**
     * @var \TIG\Buckaroo\Model\ConfigProvider\Factory
     */
    public $configProviderFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    public $checkoutSession;

    /**
     * @var \TIG\Buckaroo\Helper\Data
     */
    public $helper;

    /**
     * @param \TIG\Buckaroo\Model\ConfigProvider\Factory $configProviderFactory
     * @param \Magento\Checkout\Model\Session            $checkoutSession
     * @param \TIG\Buckaroo\Helper\Data                  $helper
     */
    public function __construct(
        \TIG\Buckaroo\Model\ConfigProvider\Factory $configProviderFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \TIG\Buckaroo\Helper\Data $helper
    ) {
        $this->configProviderFactory = $configProviderFactory;
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
    }

    /**
     * Factory method for \TIG\Buckaroo\Soap\Client\SoapClientWSSEC
     *
     * @param string $wsdl
     * @param array  $options
     *
     * @return Client\SoapClientWSSEC
     * @throws \TIG\Buckaroo\Exception|\LogicException
     */
    public function create($wsdl, array $options = [])
    {
        $client = new Client\SoapClientWSSEC($wsdl, $options);

        /** @var \TIG\Buckaroo\Model\ConfigProvider\Predefined $predefinedConfig */
        $predefinedConfig = $this->configProviderFactory->get('predefined');

        /** @var \TIG\Buckaroo\Model\ConfigProvider\PrivateKey $privateKeyConfig */
        $privateKeyConfig = $this->configProviderFactory->get('private_key');

        /**
         * active 0 is disabled, 1 is test, 2 is live
         */
        $location = null;

        $mode = $this->helper->getMode($this->checkoutSession->getQuote()->getPayment()->getMethod());
        switch ($mode) {
            case \TIG\Buckaroo\Helper\Data::MODE_LIVE:
                $location = $predefinedConfig->getLocationLiveWeb();
                break;
            case \TIG\Buckaroo\Helper\Data::MODE_TEST:
                $location = $predefinedConfig->getLocationTestWeb();
                break;
            case \TIG\Buckaroo\Helper\Data::MODE_INACTIVE:
                throw new \LogicException("Cannot do a Buckaroo transaction when 'mode' is not set or set to 0.");
            default:
                throw new \TIG\Buckaroo\Exception(
                    __(
                        "Invalid mode set: %1",
                        [
                            $mode
                        ]
                    )
                );
        }

        $client->__setLocation($location);
        $client->loadPem($privateKeyConfig->getPrivateKey());

        return $client;
    }
}
