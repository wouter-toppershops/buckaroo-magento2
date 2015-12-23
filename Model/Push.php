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

namespace TIG\Buckaroo\Model;

use Magento\Framework\Webapi\Rest\Request;
use Magento\Sales\Model\Order;
use TIG\Buckaroo\Api\PushInterface;
use TIG\Buckaroo\Exception;
use \TIG\Buckaroo\Model\Method\AbstractMethod;

/**
 * Class Push
 *
 * @package TIG\Buckaroo\Model
 */
class Push implements PushInterface
{
    /**
     * @var \Magento\Framework\Webapi\Rest\Request $request
     */
    public $request;

    /**
     * @var \TIG\Buckaroo\Model\Validator\Push $validator
     */
    public $validator;

    /**
     * @var \TIG\Buckaroo\Model\Validator\Amount $validateAmount;
     */
    public $validateAmount;

    /**
     * @var Order $order
     */
    public $order;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     */
    public $orderSender;

    /**
     * @var array $postData
     */
    public $postData;

    /**
     * @var array originalPostData
     */
    public $originalPostData;

    /**
     * @var $refundPush
     */
    public $refundPush;

    /**
     * @var \TIG\Buckaroo\Helper\Data
     */
    public $helper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public $scopeConfig;

    /**
     * Push constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface           $objectManager
     * @param \Magento\Framework\Webapi\Rest\Request              $request
     * @param \TIG\Buckaroo\Model\Validator\Push                  $validator
     * @param \TIG\Buckaroo\Model\Validator\Amount                $amountValidator
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     * @param \TIG\Buckaroo\Helper\Data                           $helper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface  $scopeConfig
     * @param \TIG\Buckaroo\Helper\Data                           $helper
     * @param \TIG\Buckaroo\Model\ConfigProvider\Factory          $configProviderFactory
     * @param Refund\Push                                         $refundPush
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Webapi\Rest\Request $request,
        \TIG\Buckaroo\Model\Validator\Push $validator,
        \TIG\Buckaroo\Model\Validator\Amount $amountValidator,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \TIG\Buckaroo\Helper\Data $helper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \TIG\Buckaroo\Helper\Data $helper,
        \TIG\Buckaroo\Model\ConfigProvider\Factory $configProviderFactory,
        \TIG\Buckaroo\Model\Refund\Push $refundPush
    ) {
        $this->objectManager            = $objectManager;
        $this->request                  = $request;
        $this->validator                = $validator;
        $this->orderSender              = $orderSender;
        $this->validateAmount           = $amountValidator;
        $this->helper                   = $helper;
        $this->scopeConfig              = $scopeConfig;
        $this->configProviderFactory    = $configProviderFactory;
        $this->refundPush               = $refundPush;

    }

    /**
     * {@inheritdoc}
     *
     * @todo Once Magento supports variable parameters, modify this method to no longer require a Request object.
     * @todo Debug mailing trough the push flow.
     */
    public function receivePush()
    {
        //Set orginal postdata before setting it to case lower.
        $this->originalPostData = $this->request->getParams();
        //Create post data array, change key values to lower case.
        $this->postData = array_change_key_case($this->request->getParams(), CASE_LOWER);
        //Validate status code and return response
        $response = $this->validator->validateStatusCode($this->postData['brq_statuscode']);
        //Check if the push can be procesed and if the order can be updated IMPORTANT => use the original post data.
        $validSignature = $this->validator->validateSignature($this->originalPostData);
        //Check if the order can recieve further status updates
        $this->order = $this->objectManager->create(Order::class)
            ->loadByIncrementId($this->postData['brq_invoicenumber']);
        if (!$this->order->getId()) {
            // try to get order by transaction id on payment.
            $this->order = $this->getOrderByTransactionKey($this->postData['brq_transactions']);
        }
        $canUpdateOrder = $this->canUpdateOrderStatus();
        //Check if the push is a refund request.
        if (isset($this->postData['brq_amount_credit'])) {
            if ($response['status'] !== 'TIG_BUCKAROO_STATUSCODE_SUCCESS'
                && $this->order->hasInvoices()
            ) {
                return false;
            }
            return $this->refundPush->receiveRefundPush($this->postData, $validSignature, $this->order);
        }

        //Last validation before push can be completed
        if (!$validSignature) {
            return false;
            //If the signature is valid but the order cant be updated, try to add a notification to the order comments.
        } elseif ($validSignature && !$canUpdateOrder) {
            $this->setOrderNotificationNote($response['message']);
            return false;
        }
        //Make sure the transactions key is set.
        $payment     = $this->order->getPayment();
        $originalKey = AbstractMethod::BUCKAROO_ORIGINAL_TRANSACTION_KEY_KEY;

        if (!$payment->getAdditionalInformation($originalKey) && !empty($this->postData['brq_transactions'])
        ) {
            $payment->setAdditionalInformation($originalKey, $this->postData['brq_transactions']);
        }
        $this->processPush($response);
        $this->order->save();

        return true;
    }

    /**
     * Process the push according the response status
     *
     * @param $response
     *
     * @throws Exception
     */
    public function processPush($response)
    {

        /** @var \TIG\Buckaroo\Model\ConfigProvider\States $statesConfig */
        $statesConfig = $this->configProviderFactory->get('states');

        switch ($response['status']) {
            case 'TIG_BUCKAROO_STATUSCODE_TECHNICAL_ERROR':
            case 'TIG_BUCKAROO_STATUSCODE_VALIDATION_FAILURE':
            case 'TIG_BUCKAROO_STATUSCODE_CANCELLED_BY_MERCHANT':
            case 'TIG_BUCKAROO_STATUSCODE_CANCELLED_BY_USER':
            case 'TIG_BUCKAROO_STATUSCODE_FAILED':
                $newStatus = $statesConfig->getOrderStateFailed();
                $this->processFailedPush($newStatus, $response['message']);
                break;
            case 'TIG_BUCKAROO_STATUSCODE_SUCCESS':
                $newStatus = $statesConfig->getOrderStateSuccess();
                $this->processSucceededPush($newStatus, $response['message']);
                break;
            case 'TIG_BUCKAROO_STATUSCODE_NEUTRAL':
                $this->setOrderNotificationNote($response['message']);
                break;
            case 'TIG_BUCKAROO_STATUSCODE_PAYMENT_ON_HOLD':
            case 'TIG_BUCKAROO_STATUSCODE_WAITING_ON_CONSUMER':
            case 'TIG_BUCKAROO_STATUSCODE_PENDING_PROCESSING':
            case 'TIG_BUCKAROO_STATUSCODE_WAITING_ON_USER_INPUT':
                $newStatus = $statesConfig->getOrderStatePending();
                $this->processPendingPaymentPush($newStatus, $response['message']);
                break;
            case 'TIG_BUCKAROO_STATUSCODE_REJECTED':
                $newStatus = $statesConfig->getOrderStateIncorrect();
                $this->processIncorrectPaymentPush($newStatus, $response['message']);
                break;
        }
    }

    /**
     * Sometimes the push does not contain the order id, when thats the case try to get the order by his payment,
     * by using its own transactionkey.
     *
     * @param $transactionId
     * @return bool
     */
    protected function getOrderByTransactionKey($transactionId)
    {
        if ($transactionId) {
            /** @var  \Magento\Sales\Model\Order\Payment\Transaction $transaction */
            $transaction = $this->objectManager->create('Magento\Sales\Model\Order\Payment\Transaction');
            $transaction->load($transactionId, 'txn_id');
            $order = $transaction->getOrder();
            if ($order) {
                return $order;
            }
        }
        return false;
    }

    /**
     * Checks if the order can be updated by checking its state and status.
     *
     * @return bool
     */
    protected function canUpdateOrderStatus()
    {
        //Types of statusses
        $completedStateAndStatus = [Order::STATE_COMPLETE, Order::STATE_COMPLETE];
        $cancelledStateAndStatus = [Order::STATE_CANCELED, Order::STATE_CANCELED];
        $holdedStateAndStatus    = [Order::STATE_HOLDED, Order::STATE_HOLDED];
        $closedStateAndStatus    = [Order::STATE_CLOSED, Order::STATE_CLOSED];
        //Get current state and status of order
        $currentStateAndStatus = [$this->order->getState(), $this->order->getStatus()];
        //If the types are not the same and the order can receive an invoice the order can be udpated by BPE.
        if ($completedStateAndStatus != $currentStateAndStatus &&
           $cancelledStateAndStatus  != $currentStateAndStatus &&
           $holdedStateAndStatus     != $currentStateAndStatus &&
           $closedStateAndStatus     != $currentStateAndStatus &&
           $this->order->canInvoice()
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param $newStatus
     * @param $message
     *
     * @return bool
     */
    public function processFailedPush($newStatus, $message)
    {
        // Create description
        $description = 'Payment push status : '.$message;

        /** @var \TIG\Buckaroo\Model\ConfigProvider\Account $accountConfig */
        $accountConfig = $this->configProviderFactory->get('account');

        $buckarooCancelOnFailed = $accountConfig->getCancelOnFailed();

        if ($this->order->canCancel() && $buckarooCancelOnFailed) {
            $this->order->cancel()->save();
        }

        $this->updateOrderStatus(Order::STATE_CANCELED, $newStatus, $description);

        return true;
    }

    /**
     * @param $newStatus
     * @param $message
     *
     * @return bool
     */
    public function processSucceededPush($newStatus, $message)
    {
        if (!$this->order->getEmailSent()) {
            $this->orderSender->send($this->order);
        }

        // Create description
        $description = 'Payment push status : '.$message;

        // Create invoice
        $this->saveInvoice();

        $this->updateOrderStatus(Order::STATE_PROCESSING, $newStatus, $description);

        return true;
    }

    /**
     * @param $newStatus
     * @param $message
     * @return bool
     */
    public function processIncorrectPaymentPush($newStatus, $message)
    {
        $baseTotal    = round($this->order->getBaseGrandTotal(), 0);
        $orderAmount  = $this->getCorrectOrderAmount();

        /**
         * Determine whether too much or not has been paid
         */
        $description = $this->validateAmount->validate([
            'baseTotal'   => $baseTotal,
            'orderAmount' => $orderAmount,
            'message'     => $message,
            'brq_amount'  => $this->postData['brq_amount']
        ]);

        if (!$description) {
            return false;
        }

        $this->order->hold()->save()->addStatusHistoryComment($description, $newStatus);
        $this->order->save();

        return true;
    }

    /**
     * @param $newStatus
     * @param $message
     *
     * @return bool
     */
    public function processPendingPaymentPush($newStatus, $message)
    {
        $description = 'Payment push status : '.$message;

        $this->updateOrderStatus(Order::STATE_NEW, $newStatus, $description);

        return true;
    }

    /**
     * Try to add an notification note to the order comments.
     * @todo make note available through translations.
     * @todo What will be the notification ? -> Create a class that would create the note dynamic
     *
     * @param $message
     */
    protected function setOrderNotificationNote($message)
    {
        $note = 'Buckaroo attempted to update this order, but failed : ' .$message;
        try {
            $this->order->addStatusHistoryComment($note);
            $this->order->save();
        } catch (Exception $e) {
            // parse exception into debug mail
        }
    }

    /**
     * Updates the orderstate and add a comment.
     *
     * @param $orderState
     * @param $description
     * @param $newStatus
     */
    protected function updateOrderStatus($orderState, $newStatus, $description)
    {
        if ($this->order->getState() ==  $orderState) {
            $this->order->addStatusHistoryComment($description, $newStatus);
        } else {
            $this->order->addStatusHistoryComment($description);
        }
    }

    /**
     * Creates and saves the invoice and adds for each invoice the buckaroo transaction keys
     * Only when the order can be invoiced and has not been invoiced before.
     *
     * @return bool
     */
    protected function saveInvoice()
    {
        if (!$this->order->canInvoice() && $this->order->hasInvoices()) {
            return false;
        }

        $this->addTransactionData();

        $this->order->save();

        // Only when the order can be invoiced and has not been invoiced before.
        if ($this->order->canInvoice() && !$this->order->hasInvoices()) {
            $this->addTransactionData();

            $this->order->save();

            foreach ($this->order->getInvoiceCollection() as $invoice) {
                if (!isset($this->postData['brq_transactions'])) {
                    continue;
                }
                /** @var \Magento\Sales\Model\Order\Invoice $invoice */
                $invoice->setTransactionId($this->postData['brq_transactions'])
                    ->save();
            }

            return true;
        }

        return false;
    }

    /**
     * @return Order\Payment
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addTransactionData()
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $this->order->getPayment();

        $transactionKey = $this->postData['brq_transactions'];

        /**
         * Save the transaction's response as additional info for the transaction.
         */
        $rawInfo = $this->helper->getTransactionAdditionalInfo($this->postData);

        /** @noinspection PhpUndefinedMethodInspection */
        $payment->setTransactionAdditionalInfo(
            \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS,
            $rawInfo
        );

        /**
         * Save the payment's transaction key.
         */
        /** @noinspection PhpUndefinedMethodInspection */
        $payment->setTransactionId($transactionKey . '-capture');
        /** @noinspection PhpUndefinedMethodInspection */
        $payment->setParentTransactionId($transactionKey);
        $payment->setAdditionalInformation(
            \TIG\Buckaroo\Model\Method\AbstractMethod::BUCKAROO_ORIGINAL_TRANSACTION_KEY_KEY,
            $transactionKey
        );

        $payment->registerCaptureNotification($this->order->getBaseGrandTotal());

        return $payment;
    }

    /**
     * Get Correct order amount
     * @return int $orderAmount
     */
    protected function getCorrectOrderAmount()
    {
        if ($this->postData['brq_currency'] == $this->order->getBaseCurrencyCode()) {
            $orderAmount = $this->order->getBaseGrandTotal();
        } else {
            $orderAmount = $this->order->getGrandTotal();
        }

        return $orderAmount;
    }
}
