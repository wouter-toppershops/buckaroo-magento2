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
 * to servicedesk@totalinternetgroup.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@totalinternetgroup.nl for more information.
 *
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */

namespace TIG\Buckaroo\Model\ConfigProvider;

use \Magento\Checkout\Model\ConfigProviderInterface;

class Account implements ConfigProviderInterface
{

    /**
     * XPATHs to configuration values for tig_buckaroo_account
     */
    const XPATH_ACCOUNT_ACTIVE              = 'payment/tig_buckaroo_account/active';
    const XPATH_ACCOUNT_SECRET_KEY          = 'payment/tig_buckaroo_account/secret_key';
    const XPATH_ACCOUNT_MERCHANT_KEY        = 'payment/tig_buckaroo_account/merchant_key';
    const XPATH_ACCOUNT_TRANSACTION_LABEL   = 'payment/tig_buckaroo_account/transaction_label';
    const XPATH_ACCOUNT_ADVANCED            = 'payment/tig_buckaroo_account/advanced';

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return array|void
     */
    public function getConfig()
    {
        $config = [
            'active'            => $this->getActive(),
            'secret_key'        => $this->getSecretKey(),
            'merchant_key'      => $this->getMerchantKey(),
            'transaction_label' => $this->getTransactionLabel(),
            'advanced'          => $this->getAdvanced(),
        ];
        return $config;
    }

    /**
     * Returns the config value for account/active
     *
     * @return mixed
     */
    public function getActive()
    {
        return $this->getConfigFromXpath(self::XPATH_ACCOUNT_ACTIVE);
    }

    /**
     * Returns the config value for account/secret_key
     *
     * @return mixed
     */
    public function getSecretKey()
    {
        return $this->getConfigFromXpath(self::XPATH_ACCOUNT_SECRET_KEY);
    }

    /**
     * Returns the config value for account/merchant_key
     *
     * @return mixed
     */
    public function getMerchantKey()
    {
        return $this->getConfigFromXpath(self::XPATH_ACCOUNT_MERCHANT_KEY);
    }

    /**
     * Returns the config value for account/transaction_label
     *
     * @return mixed
     */
    public function getTransactionLabel()
    {
        return $this->getConfigFromXpath(self::XPATH_ACCOUNT_TRANSACTION_LABEL);
    }

    /**
     * Returns the config value for account/advanced
     *
     * @return mixed
     */
    public function getAdvanced()
    {
        return $this->getConfigFromXpath(self::XPATH_ACCOUNT_ADVANCED);
    }

    /**
     * Returns the config value for the given Xpath
     *
     * @return mixed
     */
    protected function getConfigFromXpath($xpath)
    {
        return $this->scopeConfig->getValue(
            $xpath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

}
