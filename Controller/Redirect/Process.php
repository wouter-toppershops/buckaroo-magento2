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
                // Redirect to success page
                $this->redirectToSuccessPage();
                break;
            case $this->helper->getStatusCode('TIG_BUCKAROO_ORDER_FAILED'):
            case $this->helper->getStatusCode('TIG_BUCKAROO_STATUSCODE_FAILED'):
            case $this->helper->getStatusCode('TIG_BUCKAROO_STATUSCODE_REJECTED'):
            case $this->helper->getStatusCode('TIG_BUCKAROO_STATUSCODE_CANCELLED_BY_USER'):
                // Recreate quote from order
                if (!$this->recreateQuote()) {
                    throw new \TIG\Buckaroo\Exception(
                        new \Magento\Framework\Phrase(
                            'Could not recreqte the quote. Did not cancel the order (%1).',
                            $this->order->getId()
                        )
                    );
                }
                // Cancel order
                if (!$this->cancelOrder()) {
                    throw new \TIG\Buckaroo\Exception(
                        new \Magento\Framework\Phrase(
                            'Could not cancel the order (%1).',
                            $this->order->getId()
                        )
                    );
                }
                // And redirect back to checkout with our new quote
                $this->redirectToCheckout();
                break;
        }
        return;
    }

    protected function redirectToSuccessPage() {
        return $this->_redirect('checkout/onepage/success');
    }

    protected function recreateQuote() {
        $this->quote->setIsActive('1');
        $this->quote->setTriggerRecollect('1');
        if ($this->cart->setQuote($this->quote)->save()) {
            return true;
        }
        return false;
    }

    protected function cancelOrder() {
        if ($this->order->canCancel()) {
            $this->order->cancel();
            return true;
        }
        return false;
    }

    protected function redirectToCheckout() {
        return $this->_redirect('checkout');
    }

}
