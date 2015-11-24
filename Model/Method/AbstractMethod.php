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

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\AbstractMethod as MagentoAbstractMethod;
use Magento\Payment\Model\Method\Logger;
use TIG\Buckaroo\Model\Gateway\Transaction;
use TIG\Buckaroo\Model\GatewayInterface;

abstract class AbstractMethod extends MagentoAbstractMethod
{
    /**
     * @var \TIG\Buckaroo\Model\GatewayInterface
     */
    protected $_gateway;

    /**
     * @var \TIG\Buckaroo\Model\Gateway\Transaction
     */
    protected $_transaction;

    /**
     * Ideal constructor.
     *
     * @param Context                    $context
     * @param Registry                   $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory      $customAttributeFactory
     * @param Data                       $paymentData
     * @param ScopeConfigInterface       $scopeConfig
     * @param Logger                     $logger
     * @param AbstractResource|null      $resource
     * @param AbstractDb|null            $resourceCollection
     * @param array                      $data
     * @param GatewayInterface|null      $gateway
     * @param Transaction|null           $transaction
     */
    public function __construct(Context $context,
                                Registry $registry,
                                ExtensionAttributesFactory $extensionFactory,
                                AttributeValueFactory $customAttributeFactory,
                                Data $paymentData,
                                ScopeConfigInterface $scopeConfig,
                                Logger $logger,
                                AbstractResource $resource = null,
                                AbstractDb $resourceCollection = null,
                                array $data = [],
                                GatewayInterface $gateway = null,
                                Transaction $transaction = null
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
     * @return null|Transaction
     */
    protected abstract function _getAuthorizeTransaction();

    /**
     * @return null|Transaction
     */
    protected abstract function _getCaptureTransaction();

    /**
     * @return null|Transaction
     */
    protected abstract function _getRefundTransaction();
}