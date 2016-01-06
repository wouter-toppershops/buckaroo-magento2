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
     * @param \TIG\Buckaroo\Model\ConfigProvider\Factory        $configProviderFactory
     * @param \Magento\Checkout\Model\Session                   $checkoutSession
     * @param \TIG\Buckaroo\Model\ConfigProvider\Method\Factory $configProviderMethodFactory
     */
    public function __construct(
        \TIG\Buckaroo\Model\ConfigProvider\Factory $configProviderFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \TIG\Buckaroo\Model\ConfigProvider\Method\Factory $configProviderMethodFactory
    ) {
        $this->configProviderFactory = $configProviderFactory;
        $this->configProviderMethodFactory = $configProviderMethodFactory;
        $this->checkoutSession = $checkoutSession;
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

        /** @var \TIG\Buckaroo\Model\ConfigProvider\Account $accountConfig */
        $accountConfig = $this->configProviderFactory->get('account');
        /** @var \TIG\Buckaroo\Model\ConfigProvider\Predefined $predefinedConfig */
        $predefinedConfig = $this->configProviderFactory->get('predefined');
        /** @var \TIG\Buckaroo\Model\ConfigProvider\PrivateKey $privateKeyConfig */
        $privateKeyConfig = $this->configProviderFactory->get('private_key');

        /**
         * active 0 is disabled, 1 is test, 2 is live
         */
        if ($accountConfig->getActive() == 1) {
            $location = $predefinedConfig->getLocationTestWeb();
        } elseif ($accountConfig->getActive() == 2) {
            $methodName = $this->checkoutSession->getQuote()->getPayment()->getMethod();
            $methodNameParts = explode('_', $methodName);
            $methodName = end($methodNameParts);
            /** @var \TIG\Buckaroo\Model\ConfigProvider\Account $accountConfig */
            $methodConfig = $this->configProviderMethodFactory->get($methodName);

            $location = $predefinedConfig->getLocationLiveWeb();
            if ($methodConfig->getActive() == 1) {
                $location = $predefinedConfig->getLocationTestWeb();
            }
        }

        $client->__setLocation($location);
        $client->loadPem($privateKeyConfig->getPrivateKey());

        return $client;
    }
}
