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
 * @copyright   Copyright (c) 2015 TIG B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */

namespace TIG\Buckaroo\Controller\Redirect;

class Process extends \Magento\Framework\App\Action\Action
{

    protected $response;

    /**
     * @var \Magento\Sales\Model\Order $order
     */
    protected $order;

    /**
     * @var \Magento\Quote\Model\Quote $quote
     */
    protected $quote;

    /**
     * @var \TIG\Buckaroo\Helper\Data $helper
     */
    protected $helper;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * Process action
     *
     * @return $this
     */
    public function execute()
    {
        $this->helper = $this->_objectManager->create('\TIG\Buckaroo\Helper\Data');
        $this->cart = $this->_objectManager->create('\Magento\Checkout\Model\Cart');

        $this->response = $this->getRequest()->getParams();
        $statusCode = (int)$this->response['brq_statuscode'];
        $incrementId = $this->response['brq_ordernumber'];

        $this->order = $this->_objectManager->create('\Magento\Sales\Model\Order')->loadByIncrementId($this->response['brq_ordernumber']);
        if (!$this->order->getId()) {
            $statusCode = self::TIG_BUCKAROO_ORDER_FAILED;
        }
        $this->quote = $this->_objectManager->create('\Magento\Quote\Model\Quote')->load($this->order->getQuoteId());

        \Zend_Debug::dump($this->response);

//        $statusCode = 490;

        switch ($statusCode) {
            case $this->helper->getStatusCode('TIG_BUCKAROO_STATUSCODE_SUCCESS'):
            case $this->helper->getStatusCode('TIG_BUCKAROO_STATUSCODE_PENDING_PROCESSING'):
                // Complete order
                if ($this->completeOrder()) {
                    echo 'completed order<br />';
                } else {
                    die('could not complete order');
                }
                // And redirect to success page because we've won at e-commerce
                $this->redirectToSuccessPage();
                break;
            case $this->helper->getStatusCode('TIG_BUCKAROO_ORDER_FAILED'):
            case $this->helper->getStatusCode('TIG_BUCKAROO_STATUSCODE_FAILED'):
            case $this->helper->getStatusCode('TIG_BUCKAROO_STATUSCODE_REJECTED'):
            case $this->helper->getStatusCode('TIG_BUCKAROO_STATUSCODE_CANCELLED_BY_USER'):
                // Recreate quote from order
                if ($this->recreateQuote()) {
                    echo 'recreated quote from order<br />';
                } else {
                    die('could not recreate quote from order');
                }
                // Cancel order
                if ($this->cancelOrder()) {
                    echo 'canceled order<br />';
                } else {
                    die('could not cancel order');
                }
                // And redirect back to checkout with our new quote
                $this->redirectToCheckout();
                break;
        }
        return;
    }

    protected function redirectToSuccessPage() {
        echo 'REDIRECT TO SUCCESS PAGE';
    }

    protected function redirectToCheckout() {
        echo 'REDIRECT TO CHECKOUT<br />';
    }

    protected function completeOrder() {
        echo 'COMPLETE ORDER<br />';
    }

    protected function cancelOrder() {
        return false;
    }

    protected function recreateQuote() {
        $this->quote->setIsActive('1');
        $this->quote->setTriggerRecollect('1');
        if ($this->cart->setQuote($this->quote)->save()) {
            echo $this->cart->getQuote()->getId();
            return true;
        }
        return false;
    }

}
