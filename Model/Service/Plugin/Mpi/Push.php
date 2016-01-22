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

namespace TIG\Buckaroo\Model\Service\Plugin\Mpi;

class Push
{
    /**
     * @var \TIG\Buckaroo\Model\ConfigProvider\Method\Factory
     */
    protected $configProviderMethodFactory;

    /**
     * @param \TIG\Buckaroo\Model\ConfigProvider\Method\Factory  $configProviderMethodFactory
     */
    public function __construct(
        \TIG\Buckaroo\Model\ConfigProvider\Method\Factory $configProviderMethodFactory
    ) {
        $this->configProviderMethodFactory = $configProviderMethodFactory;
    }

    /**
     * @param \TIG\Buckaroo\Model\Push $push
     * @param boolean                  $result
     *
     * @return boolean
     */
    public function afterProcessSucceededPush(
        \TIG\Buckaroo\Model\Push $push,
        $result
    ) {
        $payment = $push->order->getPayment();
        $method = $payment->getMethod();

        if (strpos($method, 'tig_buckaroo') === false) {
            return $this;
        }

        /** @var \TIG\Buckaroo\Model\Method\AbstractMethod $paymentMethodInstance */
        $paymentMethodInstance = $payment->getMethodInstance();
        $card = $paymentMethodInstance->getInfoInstance()->getAdditionalInformation('card_type');

        if (empty($push->postData["brq_service_{$card}_authentication"])
            || empty($push->postData["brq_service_{$card}_enrolled"])
        ) {
            return $result;
        }

        if ($push->postData["brq_service_{$card}_authentication"] != 'Y') {
            switch ($card) {
                case 'maestro':
                    /** @var \TIG\Buckaroo\Model\ConfigProvider\Method\Creditcard $configProvider */
                    $configProvider = $this->configProviderMethodFactory ->get('creditcard');
                    $putOrderOnHold = (bool) $configProvider->getMaestroUnsecureHold();
                    break;
                case 'visa':
                    /** @var \TIG\Buckaroo\Model\ConfigProvider\Method\Creditcard $configProvider */
                    $configProvider = $this->configProviderMethodFactory ->get('creditcard');
                    $putOrderOnHold = (bool) $configProvider->getVisaUnsecureHold();
                    break;
                case 'mastercard':
                    /** @var \TIG\Buckaroo\Model\ConfigProvider\Method\Creditcard $configProvider */
                    $configProvider = $this->configProviderMethodFactory ->get('creditcard');
                    $putOrderOnHold = (bool) $configProvider->getMastercardUnsecureHold();
                    break;
                default:
                    $putOrderOnHold = false;
                    break;
            }

            if ($putOrderOnHold) {
                $push->order
                    ->hold()
                    ->addStatusHistoryComment(
                        __('Order has been put on hold, because it is unsecure.')
                    );

                $push->order->save();
            }
        }

        $paymentMethodInstance->getInfoInstance()->setAdditionalInformation(
            'buckaroo_mpi_status',
            [
                'enrolled'       => $push->postData["brq_service_{$card}_authentication"],
                'authentication' => 'test',
            ]
        );

        return $result;
    }
}
