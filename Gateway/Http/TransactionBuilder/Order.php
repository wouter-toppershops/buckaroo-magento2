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
 * @copyright Copyright (c) 2015 Total Internet Group B.V. (http://www.tig.nl)
 * @license   http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */

namespace TIG\Buckaroo\Gateway\Http\TransactionBuilder;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\UrlInterface;
use TIG\Buckaroo\Model\ConfigProvider\Account;
use TIG\Buckaroo\Model\ConfigProvider\Method\Factory;

class Order extends AbstractTransactionBuilder
{
    /** @var UrlInterface */
    protected $urlBuilder;

    /** @var RemoteAddress */
    protected $remoteAddress;

    /** @var Factory */
    protected $configProviderMethodFactory;

    /**
     * @param ProductMetadataInterface $productMetadata
     * @param ModuleListInterface      $moduleList
     * @param Account                  $configProviderAccount
     * @param ObjectManagerInterface   $objectManager
     * @param UrlInterface             $urlBuilder
     * @param RemoteAddress            $remoteAddress
     * @param Factory                  $configProviderMethodFactory
     * @param null                     $amount
     * @param null                     $currency
     */
    public function __construct(
        ProductMetadataInterface $productMetadata,
        ModuleListInterface $moduleList,
        Account $configProviderAccount,
        ObjectManagerInterface $objectManager,
        UrlInterface $urlBuilder,
        RemoteAddress $remoteAddress,
        Factory $configProviderMethodFactory,
        $amount = null,
        $currency = null
    ) {
        parent::__construct($productMetadata, $moduleList, $configProviderAccount, $objectManager, $amount, $currency);

        $this->urlBuilder = $urlBuilder;
        $this->remoteAddress = $remoteAddress;
        $this->configProviderMethodFactory = $configProviderMethodFactory;
    }

    /**
     * @return array
     */
    public function getBody()
    {
        if (!$this->getCurrency()) {
            $this->setOrderCurrency();
        }

        if ($this->getAmount() < 0.01) {
            $this->setOrderAmount();
        }

        $creditAmount = 0;
        if ($this->getType() == 'void') {
            $creditAmount = $this->getAmount();
            $this->setAmount(0);
        }

        $order = $this->getOrder();

        $ip = $order->getRemoteIp();
        if (!$ip) {
            $ip = $this->remoteAddress->getRemoteAddress();
        }

        // Some of the plaza gateway requests do not support IPv6.
        if (strpos($ip, ':') !== false) {
            $ip = '127.0.0.'.rand(1, 100);
        }

        $processUrl = $this->urlBuilder->getRouteUrl('buckaroo/redirect/process');

        $body = [
            'Currency' => $this->getCurrency(),
            'AmountDebit' => $this->getAmount(),
            'AmountCredit' => $creditAmount,
            'Invoice' => $this->getInvoiceId(),
            'Order' => $order->getIncrementId(),
            'Description' => $this->configProviderAccount->getTransactionLabel(),
            'ClientIP' => (object)[
                '_' => $ip,
                'Type' => strpos($ip, ':') === false ? 'IPv4' : 'IPv6',
            ],
            'ReturnURL' => $processUrl,
            'ReturnURLCancel' => $processUrl,
            'ReturnURLError' => $processUrl,
            'ReturnURLReject' => $processUrl,
            'OriginalTransactionKey' => $this->originalTransactionKey,
            'StartRecurrent' => $this->startRecurrent,
            'PushURL' => $this->urlBuilder->getDirectUrl('rest/V1/buckaroo/push'),
            'Services' => (object)[
                'Service' => $this->getServices()
            ],
        ];

        $services = $this->getServices();
        if (isset($services['Name']) && isset($services['Action'])) {
            if ($services['Name'] == 'paymentguarantee' && $services['Action'] == 'Order') {
                unset($body['Invoice']);
            }

            if ($services['Name'] == 'paymentguarantee' && $services['Action'] == 'PartialInvoice') {
                unset($body['OriginalTransactionKey']);
            }
        }

        $customVars = $this->getCustomVars();
        if (count($customVars) > 0) {
            foreach ($customVars as $key => $val) {
                $body[$key] = $val;
            }
        }

        return $body;
    }

    /**
     * @return array
     * @throws \TIG\Buckaroo\Exception
     */
    private function getAllowedCurrencies()
    {
        /**
         * @var \TIG\Buckaroo\Model\Method\AbstractMethod $methodInstance
         */
        $methodInstance = $this->order->getPayment()->getMethodInstance();
        $method = $methodInstance->buckarooPaymentMethodCode;

        $configProvider = $this->configProviderMethodFactory->get($method);
        return $configProvider->getAllowedCurrencies();
    }

    /**
     * @return $this
     */
    private function setOrderAmount()
    {
        if ($this->getCurrency() == $this->order->getOrderCurrencyCode()) {
            return $this->setAmount($this->order->getGrandTotal());
        }

        return $this->setAmount($this->order->getBaseGrandTotal());
    }

    /**
     * @return $this
     * @throws \TIG\Buckaroo\Exception
     */
    private function setOrderCurrency()
    {
        if (in_array($this->order->getOrderCurrencyCode(), $this->getAllowedCurrencies())) {
            return $this->setCurrency($this->order->getOrderCurrencyCode());
        }

        if (in_array($this->order->getBaseCurrencyCode(), $this->getAllowedCurrencies())) {
            return $this->setCurrency($this->order->getBaseCurrencyCode());
        }

        throw new \TIG\Buckaroo\Exception(
            __("The selected payment method does not support the selected currency or the store's base currency.")
        );
    }
}
