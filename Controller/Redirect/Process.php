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
    /** @var array */
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
     * @var \TIG\Buckaroo\Model\ConfigProvider\Factory
     */
    protected $configProviderFactory;

    /**
     * @var \Magento\Checkout\Model\ConfigProviderInterface
     */
    protected $accountConfig;

    /**
     * @param \Magento\Framework\App\Action\Context      $context
     * @param \TIG\Buckaroo\Helper\Data                  $helper
     * @param \Magento\Checkout\Model\Cart               $cart
     * @param \Magento\Sales\Model\Order                 $order
     * @param \Magento\Quote\Model\Quote                 $quote
     * @param \TIG\Buckaroo\Debug\Debugger               $debugger
     * @param \TIG\Buckaroo\Model\ConfigProvider\Factory $configProviderFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \TIG\Buckaroo\Helper\Data $helper,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Sales\Model\Order $order,
        \Magento\Quote\Model\Quote $quote,
        \TIG\Buckaroo\Debug\Debugger $debugger,
        \TIG\Buckaroo\Model\ConfigProvider\Factory $configProviderFactory
    ) {
        parent::__construct($context);
        $this->helper                   = $helper;
        $this->cart                     = $cart;
        $this->order                    = $order;
        $this->quote                    = $quote;
        $this->debugger                 = $debugger;
        $this->configProviderFactory    = $configProviderFactory;

        $this->accountConfig = $this->configProviderFactory->get('account');
    }

    /**
     * Process action
     *
     * @throws \TIG\Buckaroo\Exception
     *
     * @return void|\Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $this->response = $this->getRequest()->getParams();

        /**
         * Check if there is a valid response. If not, redirect to home.
         */
        if (count($this->response) == 0 || !array_key_exists('brq_statuscode', $this->response)) {
            return $this->_redirect('/');
        }

        $statusCode = (int)$this->response['brq_statuscode'];

        $this->order->loadByIncrementId($this->response['brq_ordernumber']);
        if (!$this->order->getId()) {
            $statusCode = $this->helper->getStatusCode('TIG_BUCKAROO_ORDER_FAILED');
        } else {
            $this->quote->load($this->order->getQuoteId());
        }

        switch ($statusCode) {
            case $this->helper->getStatusCode('TIG_BUCKAROO_STATUSCODE_SUCCESS'):
            case $this->helper->getStatusCode('TIG_BUCKAROO_STATUSCODE_PENDING_PROCESSING'):
                // Set the 'Pending payment status' here
                $pendingStatus = $this->accountConfig->getOrderStatusPending();
                if ($pendingStatus) {
                    $this->order->setStatus($pendingStatus);
                    $this->order->save();
                }

                // Redirect to success page
                $this->redirectSuccess();
                break;
            case $this->helper->getStatusCode('TIG_BUCKAROO_ORDER_FAILED'):
            case $this->helper->getStatusCode('TIG_BUCKAROO_STATUSCODE_FAILED'):
            case $this->helper->getStatusCode('TIG_BUCKAROO_STATUSCODE_REJECTED'):
            case $this->helper->getStatusCode('TIG_BUCKAROO_STATUSCODE_CANCELLED_BY_USER'):
                /*
                 * Something went wrong, so we're going to have to
                 * 1) recreate the quote for the user
                 * 2) cancel the order we had to create to even get here
                 * 3) redirect back to the checkout page to offer the user feedback & the option to try again
                 */
                $this->messageManager->addErrorMessage(
                    __(
                        'Unfortunately an error occurred while processing your payment. Please try again. If this' .
                        ' error persists, please choose a different payment method.'
                    )
                );

                if (!$this->recreateQuote()) {
                    $this->debugger->log('Could not recreate the quote.', \TIG\Buckaroo\Debug\Logger::ERROR);
                }

                if (!$this->cancelOrder()) {
                    $this->debugger->log('Could not cancel the order.', \TIG\Buckaroo\Debug\Logger::ERROR);
                }

                $this->redirectFailure();
                break;
        }

        return;
    }

    /**
     * Make the previous quote active again, so we can offer the user another opportunity to order (since something
     * went wrong)
     *
     * @return bool
     */
    protected function recreateQuote()
    {
        $this->quote->setIsActive('1');
        $this->quote->setTriggerRecollect('1');
        $this->quote->setReservedOrderId(null);

        /** @noinspection PhpUndefinedMethodInspection */
        $this->quote->setBuckarooFee(null);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->quote->setBaseBuckarooFee(null);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->quote->setBuckarooFeeTaxAmount(null);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->quote->setBuckarooFeeBaseTaxAmount(null);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->quote->setBuckarooFeeInclTax(null);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->quote->setBaseBuckarooFeeInclTax(null);

        if ($this->cart->setQuote($this->quote)->save()) {
            return true;
        }
        return false;
    }

    /**
     * If possible, cancel the order
     *
     * @return bool
     */
    protected function cancelOrder()
    {
        // Mostly the push api already canceled the order, so first check in wich state the order is.
        if ($this->order->getState() == \Magento\Sales\Model\Order::STATE_CANCELED) {
            return true;
        }

        if ($this->order->canCancel()) {
            $this->order->cancel();
            return true;
        }
        return false;
    }

    /**
     * Redirect to Success url, which means everything seems to be going fine
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    protected function redirectSuccess()
    {
        $url = $this->accountConfig->getSuccessRedirect();

        return $this->_redirect($url);
    }

    /**
     * Redirect to Failure url, which means we've got a problem
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    protected function redirectFailure()
    {
        $url = $this->accountConfig->getFailureRedirect();

        return $this->_redirect($url);
    }

}
