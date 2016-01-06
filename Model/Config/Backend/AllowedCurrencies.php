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
 * @copyright   Copyright (c) 2015 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
namespace TIG\Buckaroo\Model\Config\Backend;

/**
 * @method mixed getValue()
 */
class AllowedCurrencies extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \TIG\Buckaroo\Model\ConfigProvider\AllowedCurrencies
     */
    protected $allowedCurrenciesConfig;

    /**
     * @var \TIG\Buckaroo\Model\ConfigProvider\Method\Factory
     */
    protected $configProviderMethodFactory;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var \Magento\Framework\Locale\Bundle\CurrencyBundle
     */
    protected $currencyBundle;

    /**
     * @param \Magento\Framework\Model\Context                        $context
     * @param \Magento\Framework\Registry                             $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface      $config
     * @param \Magento\Framework\App\Cache\TypeListInterface          $cacheTypeList
     * @param \TIG\Buckaroo\Model\ConfigProvider\AllowedCurrencies    $allowedCurrenciesConfig
     * @param \TIG\Buckaroo\Model\ConfigProvider\Method\Factory       $configProviderMethodFactory
     * @param \Magento\Framework\Locale\Bundle\CurrencyBundle         $currencyBundle
     * @param \Magento\Framework\Locale\ResolverInterface             $localeResolver
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb           $resourceCollection
     * @param array                                                   $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \TIG\Buckaroo\Model\ConfigProvider\AllowedCurrencies $allowedCurrenciesConfig,
        \TIG\Buckaroo\Model\ConfigProvider\Method\Factory $configProviderMethodFactory,
        \Magento\Framework\Locale\Bundle\CurrencyBundle $currencyBundle,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);

        $this->configProviderMethodFactory = $configProviderMethodFactory;
        $this->allowedCurrenciesConfig = $allowedCurrenciesConfig;
        $this->currencyBundle = $currencyBundle;
        $this->localeResolver = $localeResolver;
    }

    /**
     * Check that the value contains valid currencies.
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save()
    {
        $method = $this->getCurrentMethodName();
        $currencies = $this->allowedCurrenciesConfig->getAllowedCurrencies();
        if ($method) {
            $methodConfig = $this->configProviderMethodFactory->get($method);
            $currencies = $methodConfig->getAllowedCurrencies();
        }


        $value = (array)$this->getValue();

        foreach ($value as $currency) {
            if (!in_array($currency, $currencies)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please enter a valid currency: "%1".', $this->getCurrencyTranslation($currency))
                );
            }
        }

        return parent::save();
    }

    /**
     * Checks if there is a translation for this currency. If not, returns the original value to show it to the user.
     *
     * @param $currency
     *
     * @return mixed
     */
    protected function getCurrencyTranslation($currency)
    {
        $output = $currency;
        $locale = $this->localeResolver->getLocale();
        $translatedCurrencies = $this->currencyBundle->get($locale)['Currencies'] ?: [];

        if (array_key_exists($currency, $translatedCurrencies)) {
            $output = $translatedCurrencies[$currency][1];
        }

        return $output;
    }

    /**
     * Extract the method name from the current config path
     *
     * @return mixed
     */
    protected function getCurrentMethodName()
    {
        $path = $this->getPath();
        if (!$path) {
            return null;
        }

        $pathParts = explode('/', $path);
        $method = $pathParts[1];
        $methodParts = explode('_', $method);
        return end($methodParts);
    }

}