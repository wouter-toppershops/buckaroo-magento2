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
     * @param \TIG\Buckaroo\Model\ConfigProvider\Factory $configProviderFactory
     */
    public function __construct(
        \TIG\Buckaroo\Model\ConfigProvider\Factory $configProviderFactory
    ) {
        $this->configProviderFactory = $configProviderFactory;
    }

    /**
     * Factory method for \TIG\Buckaroo\Soap\Client\SoapClientWSSEC
     *
     * @param string $wsdl
     * @param array $options
     *
     * @return Client\SoapClientWSSEC
     */
    public function create($wsdl, array $options = [])
    {
        $client = new Client\SoapClientWSSEC($wsdl, $options);

        $accountConfig = $this->configProviderFactory->get('account');
        $predefinedConfig = $this->configProviderFactory->get('predefined');
        $privateKeyConfig = $this->configProviderFactory->get('private_key');

        if ($accountConfig->getActive() == 1) {
            $location = $predefinedConfig->getLocationsTest();
        } elseif ($accountConfig->getActive() == 2) {
            $location = $predefinedConfig->getLocationsLive();
        }

        $client->__setLocation($location);
        $client->loadPem($privateKeyConfig->getConfig()['private_key']);

        return $client;
    }
}
