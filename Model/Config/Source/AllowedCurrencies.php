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
namespace TIG\Buckaroo\Model\Config\Source;

class AllowedCurrencies implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \TIG\Buckaroo\Model\ConfigProvider\AllowedCurrencies
     */
    protected $configProvider;

    /**
     * @var \Magento\Framework\Locale\TranslatedLists
     */
    protected $listModels;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var \Magento\Framework\Locale\Bundle\CurrencyBundle
     */
    protected $currencyBundle;

    /**
     * @param \TIG\Buckaroo\Model\ConfigProvider\AllowedCurrencies $configProvider
     * @param \Magento\Framework\Locale\Bundle\CurrencyBundle      $currencyBundle
     * @param \Magento\Framework\Locale\ResolverInterface          $localeResolver
     * @param \Magento\Framework\Locale\TranslatedLists            $listModels
     */
    public function __construct(
        \TIG\Buckaroo\Model\ConfigProvider\AllowedCurrencies $configProvider,
        \Magento\Framework\Locale\Bundle\CurrencyBundle $currencyBundle,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Locale\TranslatedLists $listModels
    )
    {
        $this->configProvider = $configProvider;
        $this->currencyBundle = $currencyBundle;
        $this->localeResolver = $localeResolver;
        $this->listModels = $listModels;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $locale = $this->localeResolver->getLocale();
        $translatedCurrencies = $this->currencyBundle->get($locale)['Currencies'] ?: [];

        $output = [];
        foreach($this->configProvider->getAllowedCurrencies() as $currency) {
            $output[] = [
                'value' => $currency,
                'label' => $translatedCurrencies[$currency][1],
            ];
        }

        asort($output);

        return $output;
    }
}