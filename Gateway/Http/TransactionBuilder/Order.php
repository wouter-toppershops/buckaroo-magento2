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

namespace TIG\Buckaroo\Gateway\Http\TransactionBuilder;

class Order extends AbstractTransactionBuilder
{
    /**
     * @throws \TIG\Buckaroo\Exception
     */
    protected function setOrderCurrencyAndAmount()
    {
        /** @var \TIG\Buckaroo\Model\Method\AbstractMethod $methodInstance */
        $methodInstance = $this->order->getPayment()->getMethodInstance();
        $method = $methodInstance->buckarooPaymentMethodCode;

        $configProvider = $this->configProviderMethodFactory->get($method);
        $allowedCurrencies = $configProvider->getAllowedCurrencies();

        if (in_array($this->order->getOrderCurrencyCode(), $allowedCurrencies)) {
            $this->amount = $this->order->getGrandTotal();
            $this->currency = $this->order->getOrderCurrencyCode();
        } elseif (in_array($this->order->getBaseCurrencyCode(), $allowedCurrencies)) {
            $this->amount = $this->order->getBaseGrandTotal();
            $this->currency = $this->order->getBaseCurrencyCode();
        } else {
            throw new \TIG\Buckaroo\Exception(
                __("The selected payment method does not support the selected currency or the store's base currency.")
            );
        }

        $this->invoiceId = $this->order->getIncrementId();
    }

    /**
     * @return array
     */
    public function getBody()
    {
        if ($this->amount < 0.01 || !$this->currency) {
            $this->setOrderCurrencyAndAmount();
        }

        $creditAmount = 0;
        if ($this->getType() == 'void') {
            $creditAmount = $this->amount;
            $this->amount = 0;
        }

        $order = $this->getOrder();

        if (!$this->invoiceId) {
            $this->invoiceId = $order->getIncrementId();
        }

        /** @var \TIG\Buckaroo\Model\ConfigProvider\Account $accountConfig */
        $accountConfig = $this->configProviderFactory->get('account');

        $ip = $order->getRemoteIp();
        if (!$ip) {
            $ip = $this->remoteAddress->getRemoteAddress();
        }

        $processUrl = $this->urlBuilder->getRouteUrl('buckaroo/redirect/process');

        $body = [
            'Currency' => $this->currency,
            'AmountDebit' => $this->amount,
            'AmountCredit' => $creditAmount,
            'Invoice' => $this->invoiceId,
            'Order' => $order->getIncrementId(),
            'Description' => $accountConfig->getTransactionLabel(),
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

        return $body;
    }
}
