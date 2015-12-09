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

        if (!$payment->getAdditionalInformation($originalKey && !empty($this->_postData['brq_transactions']))
        ) {
            $payment->setAdditionalInformation($originalKey, $this->_postData['brq_transactions']);
        }

        /** @var  $newStates  @todo built the method that sets and gets the new states of the order.
         */
        $newState = 'completed';

        switch ( $response['status'] ) {
            case ValidatorPush::BUCKAROO_ERROR:
            case ValidatorPush::BUCKAROO_FAILED:
                $this->_processFailedPush($newState, $response['message']);
                break;
            case ValidatorPush::BUCKAROO_SUCCESS:
                $this->_processSuccededPush($newState, $response['message']);
                break;
            case ValidatorPush::BUCKAROO_NEUTRAL:
                $this->_setOrderNotifactionNote($response['message']);
                break;
            case ValidatorPush::BUCKAROO_PENDING_PAYMENT:
                $this->_processPendingPaymentPush($newState, $response['message']);
                break;
            case ValidatorPush::BUCKAROO_INCORRECT_PAYMENT:
                $this->_processIncorrectPaymentPush($newState, $response['message']);
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
        $completedStateAndStatus = array(self::ORDER_TYPE_COMPLETED, self::ORDER_TYPE_COMPLETED);
        $cancelledStateAndStatus = array(self::ORDER_TYPE_CANCELED, self::ORDER_TYPE_CANCELED);
        $holdedStateAndStatus    = array(self::ORDER_TYPE_HOLDED, self::ORDER_TYPE_HOLDED);
        $closedStateAndStatus    = array(self::ORDER_TYPE_CLOSED, self::ORDER_TYPE_CLOSED);
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
     * @param $newStates
     * @param $message
     *
     * @return bool
     */
    protected function _processFailedPush($newStates, $message)
    {
        //Create discription
        $discription = ''.$message;

        /** @todo Check if the order can cancel ? */
        $this->_updateOrderState(Order::STATE_CANCELED, $newStates, $discription);

        return true;
    }

    /**
     * @param $newStates
     * @param $message
     *
     * @return bool
     */
    protected function _processSuccededPush($newStates, $message)
    {
        if( !$this->_order->getEmailSent() ) {
            // Sent new order mail.
        }

        //Create discription
        $discription = ''.$message;

        /** @todo Auto Invoice ? */
        $this->_updateOrderState(Order::STATE_PROCESSING, $newStates, $discription);

        return true;
    }

    /**
     * @param $newStates
     * @param $message
     * @todo well, as you can see, named the method, didn't built it yet.
     * @return bool
     */
    protected function _processIncorrectPaymentPush($newStates, $message)
    {
        //Check if the paid amount is correct
        //hold the order
        return true;
    }

    /**
     * @param $newStates
     * @param $message
     *
     * @return bool
     */
    protected function _processPendingPaymentPush($newStates, $message)
    {
        $discription = ''.$message;

        $this->_updateOrderState(Order::STATE_NEW, $newStates, $discription);

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
     * @param $newState
     */
    protected function _updateOrderState($orderState, $description, $newState)
    {
        if ( $this->_order->getState() ==  $orderState) {
            $this->_order->addStatusHistoryComment($description, $newState)->save();
            $this->_order->setStatus($newState)->save();
        } else {
            $this->_order->addStatusHistoryComment($description)->save();
        }
    }
}