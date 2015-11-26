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

namespace TIG\Buckaroo\Model\Method;

use Magento\Payment\Model\InfoInterface;
use TIG\Buckaroo\Gateway\Http\Transaction;

abstract class AbstractMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * @var \TIG\Buckaroo\Gateway\GatewayInterface
     */
    protected $_gateway;

    /**
     * @var \TIG\Buckaroo\Gateway\Http\Transaction
     */
    protected $_transaction;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \TIG\Buckaroo\Gateway\GatewayInterface $gateway = null,
        \TIG\Buckaroo\Gateway\Http\Transaction $transaction = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );

        $this->_gateway = $gateway;
        $this->_transaction = $transaction;
    }

    /**
     * @param InfoInterface $payment
     * @param float         $amount
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function authorize(InfoInterface $payment, $amount)
    {
        parent::authorize($payment, $amount);

        $transaction = $this->_getAuthorizeTransaction();

        $this->_gateway->authorize($transaction);
        return $this;
    }

    /**
     * @param InfoInterface $payment
     * @param float         $amount
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function capture(InfoInterface $payment, $amount)
    {
        parent::capture($payment, $amount);

        $transaction = $this->_getCaptureTransaction();

        $this->_gateway->capture($transaction);
        return $this;
    }

    /**
     * @param InfoInterface $payment
     * @param float         $amount
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function refund(InfoInterface $payment, $amount)
    {
        parent::refund($payment, $amount);

        $transaction = $this->_getRefundTransaction();

        $this->_gateway->refund($transaction);
        return $this;
    }

    /**
     * @return Transaction
     */
    protected abstract function _getAuthorizeTransaction();

    /**
     * @return Transaction
     */
    protected abstract function _getCaptureTransaction();

    /**
     * @return Transaction
     */
    protected abstract function _getRefundTransaction();
}