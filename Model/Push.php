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

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Sales\Model\Order;
use TIG\Buckaroo\Api\PushInterface;
use TIG\Buckaroo\Exception;
use \TIG\Buckaroo\Model\Validator\Push as ValidatorPush;

/**
 * Class Push
 *
 * @package TIG\Buckaroo\Model
 */
class Push implements PushInterface
{
    const ORDER_TYPE_COMPLETED = 'complete';
    const ORDER_TYPE_CANCELED  = 'canceled';
    const ORDER_TYPE_HOLDED    = 'holded';
    const ORDER_TYPE_CLOSED    = 'closed';
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var array
     */
    protected $_postData;

    /**
     * \TIG\Buckaroo\Model\Validator\Push
     * @var $_validator
     */
    protected $_validator;

    /** @var Order $order */
    protected $_order;
    /**
     * Push constructor.
     *
     * @param ObjectManagerInterface                 $objectManager
     * @param \Magento\Framework\Webapi\Rest\Request $request
     * @param \TIG\Buckaroo\Model\Validator\Push     $validator
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Request $request,
        ValidatorPush $validator
    )
    {
        $this->objectManager = $objectManager;
        $this->request       = $request;
        $this->_validator    = $validator;

    }

    /**
     * {@inheritdoc}
     *
     * @todo Once Magento supports variable parameters, modify this method to no longer require a Request object.
     * @todo Debug mailing trough the push flow.
     * @todo Check if the amount equals the amount of the order.
     */
    public function receivePush()
    {
        //Create post data array, change key values to lower case.
        $this->_postData = array_change_key_case($this->request->getParams(), CASE_LOWER);
        //Validate status code and return response
        $response        = $this->_validator->validateStatusCode($this->_postData['brq_statuscode']);
        //Check if the push can be procesed and if the order can be updtated.
        $validSignature  = $this->_validator->validateSignature($this->_postData['brq_signature']);
        //Check if the order can recieve further status updates
        $this->_order    = $this->objectManager->create(Order::class)
                                               ->loadByIncrementId($this->_postData['brq_invoicenumber']);
        if ( !$this->_order->getId() ) {
            // try to get order by transaction id on payment.
            $this->_getOrderByTransactionKey($this->_postData['brq_transactions']);
        }
        $canUpdateOrder = $this->_canUpdateOrderStatus();

        //Last validation before push can be completed
        if (!$validSignature) {
            return false;
            //If the signature is valid but the order cant be updated, try to add a notification to the order comments.
        } elseif ($validSignature && !$canUpdateOrder) {
            $this->_setOrderNotifactionNote($response['message']);
            return false;
        }

        //Make sure the transactions key is set.
        $payment     = $this->_order->getPayment();
        $originalKey = \TIG\Buckaroo\Model\Method\AbstractMethod::BUCKAROO_ORIGINAL_TRANSACTION_KEY_KEY;

        if (!$payment->getAdditionalInformation($originalKey) && !empty($this->_postData['brq_transactions'])
        ) {
            $payment->setAdditionalInformation($originalKey, $this->_postData['brq_transactions']);
        }

        /** @var  $newStates  @todo built the method that sets and gets the new states of the order.
         */
        $newStatus = 'closed';

        switch ( $response['status'] ) {
            case 'TIG_BUCKAROO_STATUSCODE_TECHNICAL_ERROR':
            case 'TIG_BUCKAROO_STATUSCODE_VALIDATION_FAILURE':
            case 'TIG_BUCKAROO_STATUSCODE_CANCELLED_BY_MERCHANT':
            case 'TIG_BUCKAROO_STATUSCODE_CANCELLED_BY_USER':
            case 'TIG_BUCKAROO_STATUSCODE_FAILED':
                $this->_processFailedPush($newStatus, $response['message']);
                break;
            case 'TIG_BUCKAROO_STATUSCODE_SUCCESS':
                $this->_processSuccededPush($newStatus, $response['message']);
                break;
            case 'TIG_BUCKAROO_STATUSCODE_NEUTRAL':
                $this->_setOrderNotifactionNote($response['message']);
                break;
            case 'TIG_BUCKAROO_STATUSCODE_PAYMENT_ON_HOLD':
            case 'TIG_BUCKAROO_STATUSCODE_WAITING_ON_CONSUMER':
            case 'TIG_BUCKAROO_STATUSCODE_PENDING_PROCESSING':
            case 'TIG_BUCKAROO_STATUSCODE_WAITING_ON_USER_INPUT':
                $this->_processPendingPaymentPush($newStatus, $response['message']);
                break;
            case 'TIG_BUCKAROO_STATUSCODE_REJECTED':
                $this->_processIncorrectPaymentPush($newStatus, $response['message']);
                break;
        }

        return true;
    }

    /**
     * Sometimes the push does not contain the order id, when thats the case try to get the order by his payment,
     * by using its own transactionkey.
     *
     * @todo well, as you can see, named the method, didn't built it yet.
     * @param $transaction
     *
     * @return bool
     */
    protected function _getOrderByTransactionKey($transaction)
    {
        if ($transaction) {
            //get invoice by transaction
            // And get order by invoice
        }
        return false;
    }

    /**
     * Checks if the order can be updated by checking his state and status.
     * @return bool
     */
    protected function _canUpdateOrderStatus()
    {
        //Types of statusses
        $completedStateAndStatus = [self::ORDER_TYPE_COMPLETED, self::ORDER_TYPE_COMPLETED];
        $cancelledStateAndStatus = [self::ORDER_TYPE_CANCELED, self::ORDER_TYPE_CANCELED];
        $holdedStateAndStatus    = [self::ORDER_TYPE_HOLDED, self::ORDER_TYPE_HOLDED];
        $closedStateAndStatus    = [self::ORDER_TYPE_CLOSED, self::ORDER_TYPE_CLOSED];
        //Get current state and status of order
        $currentStateAndStatus = array($this->_order->getState(), $this->_order->getStatus());
        //If the types are not the same and the order can receive an invoice the order can be udpated by BPE.
        if (
           $completedStateAndStatus != $currentStateAndStatus &&
           $cancelledStateAndStatus != $currentStateAndStatus &&
           $holdedStateAndStatus    != $currentStateAndStatus &&
           $closedStateAndStatus    != $currentStateAndStatus &&
           $this->_order->canInvoice()
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
    protected function _processFailedPush($newStatus, $message)
    {
        //Create discription
        $discription = ''.$message;

        /** @todo Check if the order can cancel ? */
        $this->_updateOrderStatus(Order::STATE_CANCELED, $newStatus, $discription);

        return true;
    }

    /**
     * @param $newStatus
     * @param $message
     *
     * @return bool
     */
    protected function _processSuccededPush($newStatus, $message)
    {
        if( !$this->_order->getEmailSent() ) {
            // Sent new order mail.
        }
        //Create discription
        $discription = ''.$message;

        //Create invoice
        $this->_saveInvoice();

        $this->_updateOrderStatus(Order::STATE_PROCESSING, $newStatus, $discription);

        return true;
    }

    /**
     * @param $newStatus
     * @param $message
     * @todo well, as you can see, named the method, didn't built it yet.
     * @return bool
     */
    protected function _processIncorrectPaymentPush($newStatus, $message)
    {
        //Check if the paid amount is correct
        //hold the order
        return true;
    }

    /**
     * @param $newStatus
     * @param $message
     *
     * @return bool
     */
    protected function _processPendingPaymentPush($newStatus, $message)
    {
        $discription = ''.$message;

        $this->_updateOrderStatus(Order::STATE_NEW, $newStatus, $discription);

        return true;
    }

    /**
     * Try to add an notifaction note to the order comments.
     * @todo make note available trought translations.
     * @todo What will be the notifactionnote ?
     *
     * @param $message
     */
    protected function _setOrderNotifactionNote($message)
    {
        $note  = 'Buckaroo attempted to update this order, but failed : ' .$message;
        try {
            $this->_order->addStatusHistoryComment($note)->save();
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
    protected function _updateOrderStatus($orderState, $newStatus, $description)
    {
        if ( $this->_order->getState() ==  $orderState) {
            $this->_order->addStatusHistoryComment($description, $newStatus)->save();
            $this->_order->setStatus($newStatus)->save();
        } else {
            $this->_order->addStatusHistoryComment($description)->save();
        }
    }

    /**
     * Creates and saves the invoice and adds for each invoice the buckaroo transaction keys
     *
     * @return bool
     */
    protected function _saveInvoice()
    {
        //Only when the order can be invoiced and has not been invoiced before.
        if( $this->_order->canInvoice() && !$this->_order->hasInvoices() ) {
            $payment = $this->_order->getPayment();
            $payment->registerCaptureNotification($this->_order->getBaseGrandTotal());
            $this->_order->save();

            foreach($this->_order->getInvoiceCollection() as $invoice)
            {
                if (!isset($this->_postData['brq_transactions'])) {
                    continue;
                }
                $invoice->setTransactionId($this->_postData['brq_transactions'])
                    ->save();
            }
            return true;
        }
        return false;
    }
}