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
namespace TIG\Buckaroo\Model\ConfigProvider\Method;

use Magento\Framework\View\Asset\Repository;
use Magento\Checkout\Model\ConfigProviderInterface as CheckoutConfigProvider;

abstract class AbstractConfigProvider extends \TIG\Buckaroo\Model\ConfigProvider\AbstractConfigProvider
    implements CheckoutConfigProvider, ConfigProviderInterface
{
    /**
     * The asset repository to generate the correct url to our assets.
     *
     * @var Repository
     */
    protected $assetRepo;

    /**
     * The list of issuers. This is filled by the child classes.
     *
     * @var array
     */
    protected $issuers = [];

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var array|null
     */
    protected $allowedCurrencies = null;

    /**
     * @param Repository                                         $assetRepo
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \TIG\Buckaroo\Model\ConfigProvider\Factory         $configProviderFactory
     */
    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \TIG\Buckaroo\Model\ConfigProvider\Factory $configProviderFactory
    ) {
        $this->assetRepo = $assetRepo;
        $this->scopeConfig = $scopeConfig;
        $this->configProviderFactory = $configProviderFactory;

        if (!$this->allowedCurrencies) {
            $allowedCurrenciesConfig = $this->configProviderFactory->get('allowed_currencies');
            $this->allowedCurrencies = $allowedCurrenciesConfig->getAllowedCurrencies();
        }
    }

    /**
     * Retrieve the list of issuers.
     *
     * @return array
     */
    public function getIssuers()
    {
        return $this->issuers;
    }

    /**
     * Format the issuers list so the img index is filled with the correct url.
     *
     * @return array
     */
    protected function formatIssuers()
    {
        $issuers = array_map(
            function ($issuer) {
                $issuer['img'] = $this->getImageUrl('ico-' . $issuer['code']);

                return $issuer;
            },
            $this->getIssuers()
        );

        return $issuers;
    }

    /**
     * Generate the url to the desired asset.
     *
     * @param $imgName
     *
     * @return string
     */
    protected function getImageUrl($imgName)
    {
        return $this->assetRepo->getUrl('TIG_Buckaroo::images/' . $imgName . '.png');
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentFee()
    {
        return false;
    }

    /**
     * @return array
     */
    public function getAllowedCurrencies()
    {
        return $this->allowedCurrencies;
    }

}
