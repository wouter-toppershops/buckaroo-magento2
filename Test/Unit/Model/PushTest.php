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
 * @copyright Copyright (c) 2016 Total Internet Group B.V. (http://www.tig.nl)
 * @license   http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
namespace TIG\Buckaroo\Test\Unit\Model;

use Magento\Payment\Model\MethodInterface;
use Magento\Sales\Model\Order\Payment;

class PushTest extends \TIG\Buckaroo\Test\BaseTest
{
    /**
     * @var \TIG\Buckaroo\Model\Push
     */
    protected $object;

    /**
     * @var \Mockery\MockInterface
     */
    protected $objectManager;

    /**
     * @var \Mockery\MockInterface
     */
    protected $request;

    /**
     * @var \Mockery\MockInterface
     */
    protected $helper;

    /**
     * @var \Mockery\MockInterface
     */
    protected $configAccount;

    /**
     * @var \Mockery\MockInterface
     */
    public $debugger;

    /**
     * @var \Mockery\MockInterface
     */
    public $orderSender;

    /**
     * Setup the standard mocks
     */
    public function setUp()
    {
        parent::setUp();

        $this->objectManager = \Mockery::mock(\Magento\Framework\ObjectManagerInterface::class);
        $this->request = \Mockery::mock(\Magento\Framework\Webapi\Rest\Request::class);
        $this->helper = \Mockery::mock(\TIG\Buckaroo\Helper\Data::class);
        $this->configAccount = \Mockery::mock(\TIG\Buckaroo\Model\ConfigProvider\Account::class);
        $this->debugger = \Mockery::mock(\TIG\Buckaroo\Debug\Debugger::class);

        /**
         * Needed to deal with __destruct
         */
        $this->debugger->shouldReceive('log')->andReturnSelf();

        $this->orderSender = \Mockery::mock(\Magento\Sales\Model\Order\Email\Sender\OrderSender::class);

        /**
         * We are using the temporary class declared above, but it could be any class extending from the AbstractMethod
         * class.
         */
        $this->object = $this->objectManagerHelper->getObject(
            \TIG\Buckaroo\Model\Push::class,
            [
                'objectManager' => $this->objectManager,
                'request' => $this->request,
                'helper' => $this->helper,
                'configAccount' => $this->configAccount,
                'debugger' => $this->debugger,
                'orderSender' => $this->orderSender,
            ]
        );
    }

    /**
     * @param $state
     *
     * @dataProvider testProcessPendingPaymentPushDataProvider
     */
    public function testProcessPendingPaymentPush($state)
    {
        $message = 'testMessage';
        $status = 'testStatus';

        $expectedDescription = 'Payment push status : '.$message;

        $pendingPaymentState = \Magento\Sales\Model\Order::STATE_PROCESSING;

        $orderMock = \Mockery::mock(\Magento\Sales\Model\Order::class);
        $orderMock->shouldReceive('getState')->atLeast(1)->andReturn($state);

        if ($state == $pendingPaymentState) {
            $orderMock->shouldReceive('addStatusHistoryComment')->once()->with($expectedDescription, $status);
        } else {
            $orderMock->shouldReceive('addStatusHistoryComment')->once()->with($expectedDescription);
        }
        $this->object->order = $orderMock;

        $this->assertTrue($this->object->processPendingPaymentPush($status, $message));
    }

    public function testProcessPendingPaymentPushDataProvider()
    {
        return [
            [
                \Magento\Sales\Model\Order::STATE_PROCESSING,
            ],
            [
                \Magento\Sales\Model\Order::STATE_NEW,
            ],
        ];
    }

    /**
     * @param $state
     * @param $canCancel
     * @param $cancelOnFailed
     *
     * @dataProvider testProcessFailedPushDataProvider
     */
    public function testProcessFailedPush($state, $canCancel, $cancelOnFailed)
    {
        $message = 'testMessage';
        $status = 'testStatus';

        $expectedDescription = 'Payment status : '.$message;

        $canceledPaymentState = \Magento\Sales\Model\Order::STATE_CANCELED;

        $this->configAccount->shouldReceive('getCancelOnFailed')->andReturn($cancelOnFailed);

        $orderMock = \Mockery::mock(\Magento\Sales\Model\Order::class);
        $orderMock->shouldReceive('getState')->atLeast(1)->andReturn($state);
        $orderMock->shouldReceive('getStore')->once()->andReturnSelf();

        if ($state == $canceledPaymentState) {
            $orderMock->shouldReceive('addStatusHistoryComment')->once()->with($expectedDescription, $status);
        } else {
            $orderMock->shouldReceive('addStatusHistoryComment')->once()->with($expectedDescription);
        }

        if ($cancelOnFailed) {
            $methodInstanceMock = $this->getMockForAbstractClass(MethodInterface::class);
            $paymentMock = $this->getMockBuilder(Payment::class)
                ->disableOriginalConstructor()
                ->setMethods(['getMethodInstance'])
                ->getMock();
            $paymentMock->method('getMethodInstance')->willReturn($methodInstanceMock);

            $orderMock->shouldReceive('canCancel')->once()->andReturn($canCancel);
            $orderMock->shouldReceive('getPayment')->times((int)$canCancel)->andReturn($paymentMock);
            if ($canCancel) {
                $this->debugger->shouldReceive('addToMessage')->withAnyArgs()->andReturnSelf();
                $this->debugger->shouldReceive('log')->andReturnSelf();
                $orderMock->shouldReceive('cancel')->once()->andReturnSelf();
                $orderMock->shouldReceive('save')->once()->andReturnSelf();
            }
        }

        $this->object->order = $orderMock;

        $this->assertTrue($this->object->processFailedPush($status, $message));
    }

    public function testProcessFailedPushDataProvider()
    {
        return [
            [
                \Magento\Sales\Model\Order::STATE_CANCELED,
                true,
                true,
            ],
            [
                \Magento\Sales\Model\Order::STATE_CANCELED,
                true,
                false,
            ],
            [
                \Magento\Sales\Model\Order::STATE_CANCELED,
                false,
                true,
            ],
            [
                \Magento\Sales\Model\Order::STATE_CANCELED,
                false,
                false,
            ],
            [
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                true,
                true,
            ],
            [
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                true,
                false,
            ],
            [
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                false,
                true,
            ],
            [
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                false,
                false,
            ],
        ];
    }

    /**
     * @param      $state
     * @param      $orderEmailSent
     * @param      $sendOrderConfirmationEmail
     * @param      $paymentAction
     * @param      $amount
     * @param bool                       $textAmount
     * @param bool                       $autoInvoice
     * @param bool                       $orderCanInvoice
     * @param bool                       $orderHasInvoices
     * @param bool                       $postData
     *
     * @dataProvider processSucceededPushDataProvider
     */
    public function testProcessSucceededPush(
        $state,
        $orderEmailSent,
        $sendOrderConfirmationEmail,
        $paymentAction,
        $amount,
        $textAmount,
        $autoInvoice = false,
        $orderCanInvoice = false,
        $orderHasInvoices = false,
        $postData = false
    ) {
        $message = 'testMessage';
        $status = 'testStatus';

        /**
         * Only orders with this state should have their status updated
         */
        $successPaymentState = \Magento\Sales\Model\Order::STATE_PROCESSING;

        /**
         * Set config values on config provider mock
         */
        $this->configAccount->shouldReceive('getOrderConfirmationEmail')
            ->andReturn($sendOrderConfirmationEmail);
        $this->configAccount->shouldReceive('getAutoInvoice')->andReturn($autoInvoice);
        $this->configAccount->shouldReceive('getInvoiceEmail');

        /**
         * Build an order mock and set several non mandatory method calls
         */
        $orderMock = \Mockery::mock(\Magento\Sales\Model\Order::class);
        $orderMock->shouldReceive('getEmailSent')->andReturn($orderEmailSent);
        $orderMock->shouldReceive('getGrandTotal')->andReturn($amount);
        $orderMock->shouldReceive('getBaseGrandTotal')->andReturn($amount);
        $orderMock->shouldReceive('getTotalDue')->andReturn($amount);
        $orderMock->shouldReceive('getStore')->andReturnSelf();

        /**
         * The order state has to be checked at least once
         */
        $orderMock->shouldReceive('getState')->atLeast(1)->andReturn($state);

        /**
         * If order email is not sent and order email should be sent, expect sending of order email
         */
        if (!$orderEmailSent && $sendOrderConfirmationEmail) {
            $this->orderSender->shouldReceive('send')->with($orderMock);
        }

        /**
         * Build a payment mock and set the payment action
         */
        $paymentMock = \Mockery::mock(Payment::class);
        $paymentMock->shouldReceive('getMethodInstance')->andReturnSelf();
        $paymentMock->shouldReceive('getConfigData')->with('payment_action')->andReturn($paymentAction);
        $paymentMock->shouldReceive('getConfigData');
        $paymentMock->shouldReceive('getMethod');

        /**
         * Build a currency mock
         */
        $currencyMock = \Mockery::mock(\Magento\Directory\Model\Currency::class);
        $currencyMock->shouldReceive('formatTxt')->andReturn($textAmount);

        /**
         * Update order mock with payment and currency mock
         */
        $orderMock->shouldReceive('getPayment')->andReturn($paymentMock);
        $orderMock->shouldReceive('getBaseCurrency')->andReturn($currencyMock);

        /**
         * If no auto invoicing is required, or if auto invoice is required and the order can be invoiced and
         *  has no invoices, expect a status update
         */
        if (!$autoInvoice || ($autoInvoice && $orderCanInvoice && !$orderHasInvoices)) {
            if ($paymentAction != 'authorize') {
                $expectedDescription = 'Payment status : <strong>' . $message . "</strong><br/>";
                $expectedDescription .= 'Total amount of ' . $textAmount . ' has been paid';
            } else {
                $expectedDescription = 'Authorization status : <strong>' . $message . "</strong><br/>";
                $expectedDescription .= 'Total amount of ' . $textAmount . ' has been ' .
                    'authorized. Please create an invoice to capture the authorized amount.';
            }

            /**
             * Only orders with the success state should have their status updated
             */
            if ($state == $successPaymentState) {
                $orderMock->shouldReceive('addStatusHistoryComment')->once()->with($expectedDescription, $status);
            } else {
                $orderMock->shouldReceive('addStatusHistoryComment')->once()->with($expectedDescription);
            }
        }

        /**
         * Build a PHP_Unit (not Mockery) partial mock to capture internal calls to public method addTransactionData
         */
        $objectMock = $this->getPartialObject(
            get_class($this->object),
            [
                'objectManager' => $this->objectManager,
                'request' => $this->request,
                'helper' => $this->helper,
                'configAccount' => $this->configAccount,
                'debugger' => $this->debugger,
                'orderSender' => $this->orderSender,
            ],
            ['addTransactionData']
        );

        /**
         * If autoInvoice is required, also test protected method saveInvoice
         */
        if ($autoInvoice) {
            $orderMock->shouldReceive('canInvoice')->andReturn($orderCanInvoice);
            $orderMock->shouldReceive('hasInvoices')->andReturn($orderHasInvoices);

            if (!$orderCanInvoice || $orderHasInvoices) {
                /**
                 * If order cannot be invoiced or if order already has invoices, expect an exception
                 */
                $this->setExpectedException(\TIG\Buckaroo\Exception::class);
                $this->debugger->shouldReceive('addToMessage')->withAnyArgs();
            } else {
                /**
                 * Order can be invoice, so test invoice flow
                 */
                $objectMock->expects($this->once())->method('addTransactionData');

                /**
                 * Payment should receive register capture notification only once and payment should be saved
                 */
                $paymentMock->shouldReceive('registerCaptureNotification')->once()->with($amount);
                $paymentMock->shouldReceive('save')->once()->withNoArgs();

                /**
                 * Order should be saved at least once
                 */
                $orderMock->shouldReceive('save')->atLeast(1)->withNoArgs();

                /**
                 * @noinspection PhpUndefinedFieldInspection
                 */
                $objectMock->postData = $postData;

                $invoiceMock = \Mockery::mock(\Magento\Sales\Model\Order\Invoice::class);
                $invoiceMock->shouldReceive('getEmailSent')->andReturn(false);

                /**
                 * Invoice collection should be array iterable so a simple array is used for a mock collection
                 */
                $orderMock->shouldReceive('getInvoiceCollection')->andReturn([$invoiceMock]);

                /**
                 * If key brq_transactions is set in postData, invoice should expect a transaction id to be set
                 */
                if (isset($postData['brq_transactions'])) {
                    $invoiceMock->shouldReceive('setTransactionId')
                        ->with($postData['brq_transactions'])
                        ->andReturnSelf();
                    $invoiceMock->shouldReceive('save');
                }
            }
        }

        /**
         * @noinspection PhpUndefinedFieldInspection
         */
        $objectMock->order = $orderMock;

        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        $this->assertTrue($objectMock->processSucceededPush($status, $message));
    }

    public function processSucceededPushDataProvider()
    {
        return [
            /**
             * CANCELED && AUTHORIZE
             */
            0 => [
                /**
                 * $state
                 */
                \Magento\Sales\Model\Order::STATE_CANCELED,
                /**
                 * $orderEmailSent
                 */
                true,
                /**
                 * $sendOrderConfirmationEmail
                 */
                true,
                /**
                 * $paymentAction
                 */
                'authorize',
                /**
                 * $amount
                 */
                '15.95',
                /**
                 * $textAmount
                 */
                '$15.95',
                /**
                 * $autoInvoice
                 */
                false,
                /**
                 * $orderCanInvoice
                 */
                false,
                /**
                 * $orderHasInvoices
                 */
                false,
                /**
                 * $postData
                 */
                false,
            ],
            1 => [
                /**
                 * $state
                 */
                \Magento\Sales\Model\Order::STATE_CANCELED,
                /**
                 * $orderEmailSent
                 */
                false,
                /**
                 * $sendOrderConfirmationEmail
                 */
                true,
                /**
                 * $paymentAction
                 */
                'authorize',
                /**
                 * $amount
                 */
                '15.95',
                /**
                 * $textAmount
                 */
                '$15.95',
                /**
                 * $autoInvoice
                 */
                false,
                /**
                 * $orderCanInvoice
                 */
                false,
                /**
                 * $orderHasInvoices
                 */
                false,
                /**
                 * $postData
                 */
                false,
            ],
            2 => [
                /**
                 * $state
                 */
                \Magento\Sales\Model\Order::STATE_CANCELED,
                /**
                 * $orderEmailSent
                 */
                true,
                /**
                 * $sendOrderConfirmationEmail
                 */
                false,
                /**
                 * $paymentAction
                 */
                'authorize',
                /**
                 * $amount
                 */
                '15.95',
                /**
                 * $textAmount
                 */
                '$15.95',
                /**
                 * $autoInvoice
                 */
                false,
                /**
                 * $orderCanInvoice
                 */
                false,
                /**
                 * $orderHasInvoices
                 */
                false,
                /**
                 * $postData
                 */
                false,
            ],
            3 => [
                /**
                 * $state
                 */
                \Magento\Sales\Model\Order::STATE_CANCELED,
                /**
                 * $orderEmailSent
                 */
                false,
                /**
                 * $sendOrderConfirmationEmail
                 */
                false,
                /**
                 * $paymentAction
                 */
                'authorize',
                /**
                 * $amount
                 */
                '15.95',
                /**
                 * $textAmount
                 */
                '$15.95',
                /**
                 * $autoInvoice
                 */
                false,
                /**
                 * $orderCanInvoice
                 */
                false,
                /**
                 * $orderHasInvoices
                 */
                false,
                /**
                 * $postData
                 */
                false,
            ],
            /**
             * CANCELED && NOT AUTHORIZE
             */
            4 => [
                /**
                 * $state
                 */
                \Magento\Sales\Model\Order::STATE_CANCELED,
                /**
                 * $orderEmailSent
                 */
                true,
                /**
                 * $sendOrderConfirmationEmail
                 */
                true,
                /**
                 * $paymentAction
                 */
                'not_authorize',
                /**
                 * $amount
                 */
                '15.95',
                /**
                 * $textAmount
                 */
                '$15.95',
                /**
                 * $autoInvoice
                 */
                false,
                /**
                 * $orderCanInvoice
                 */
                false,
                /**
                 * $orderHasInvoices
                 */
                false,
                /**
                 * $postData
                 */
                false,
            ],
            5 => [
                /**
                 * $state
                 */
                \Magento\Sales\Model\Order::STATE_CANCELED,
                /**
                 * $orderEmailSent
                 */
                false,
                /**
                 * $sendOrderConfirmationEmail
                 */
                true,
                /**
                 * $paymentAction
                 */
                'not_authorize',
                /**
                 * $amount
                 */
                '15.95',
                /**
                 * $textAmount
                 */
                '$15.95',
                /**
                 * $autoInvoice
                 */
                false,
                /**
                 * $orderCanInvoice
                 */
                false,
                /**
                 * $orderHasInvoices
                 */
                false,
                /**
                 * $postData
                 */
                false,
            ],
            6 => [
                /**
                 * $state
                 */
                \Magento\Sales\Model\Order::STATE_CANCELED,
                /**
                 * $orderEmailSent
                 */
                true,
                /**
                 * $sendOrderConfirmationEmail
                 */
                false,
                /**
                 * $paymentAction
                 */
                'not_authorize',
                /**
                 * $amount
                 */
                '15.95',
                /**
                 * $textAmount
                 */
                '$15.95',
                /**
                 * $autoInvoice
                 */
                false,
                /**
                 * $orderCanInvoice
                 */
                false,
                /**
                 * $orderHasInvoices
                 */
                false,
                /**
                 * $postData
                 */
                false,
            ],
            7 => [
                /**
                 * $state
                 */
                \Magento\Sales\Model\Order::STATE_CANCELED,
                /**
                 * $orderEmailSent
                 */
                false,
                /**
                 * $sendOrderConfirmationEmail
                 */
                false,
                /**
                 * $paymentAction
                 */
                'not_authorize',
                /**
                 * $amount
                 */
                '15.95',
                /**
                 * $textAmount
                 */
                '$15.95',
                /**
                 * $autoInvoice
                 */
                false,
                /**
                 * $orderCanInvoice
                 */
                false,
                /**
                 * $orderHasInvoices
                 */
                false,
                /**
                 * $postData
                 */
                false,
            ],
            /**
             * CANCELED && NOT AUTHORIZE && AUTO INVOICE
             */
            8 => [
                /**
                 * $state
                 */
                \Magento\Sales\Model\Order::STATE_CANCELED,
                /**
                 * $orderEmailSent
                 */
                true,
                /**
                 * $sendOrderConfirmationEmail
                 */
                true,
                /**
                 * $paymentAction
                 */
                'not_authorize',
                /**
                 * $amount
                 */
                '15.95',
                /**
                 * $textAmount
                 */
                '$15.95',
                /**
                 * $autoInvoice
                 */
                true,
                /**
                 * $orderCanInvoice
                 */
                false,
                /**
                 * $orderHasInvoices
                 */
                false,
                /**
                 * $postData
                 */
                false,
            ],
            9 => [
                /**
                 * $state
                 */
                \Magento\Sales\Model\Order::STATE_CANCELED,
                /**
                 * $orderEmailSent
                 */
                true,
                /**
                 * $sendOrderConfirmationEmail
                 */
                true,
                /**
                 * $paymentAction
                 */
                'not_authorize',
                /**
                 * $amount
                 */
                '15.95',
                /**
                 * $textAmount
                 */
                '$15.95',
                /**
                 * $autoInvoice
                 */
                true,
                /**
                 * $orderCanInvoice
                 */
                true,
                /**
                 * $orderHasInvoices
                 */
                false,
                /**
                 * $postData
                 */
                false,
            ],
            10 => [
                /**
                 * $state
                 */
                \Magento\Sales\Model\Order::STATE_CANCELED,
                /**
                 * $orderEmailSent
                 */
                true,
                /**
                 * $sendOrderConfirmationEmail
                 */
                true,
                /**
                 * $paymentAction
                 */
                'not_authorize',
                /**
                 * $amount
                 */
                '15.95',
                /**
                 * $textAmount
                 */
                '$15.95',
                /**
                 * $autoInvoice
                 */
                true,
                /**
                 * $orderCanInvoice
                 */
                true,
                /**
                 * $orderHasInvoices
                 */
                false,
                /**
                 * $postData
                 */
                ['brq_transactions' => 'test_transaction_id'],
            ],
            11 => [
                /**
                 * $state
                 */
                \Magento\Sales\Model\Order::STATE_CANCELED,
                /**
                 * $orderEmailSent
                 */
                true,
                /**
                 * $sendOrderConfirmationEmail
                 */
                true,
                /**
                 * $paymentAction
                 */
                'not_authorize',
                /**
                 * $amount
                 */
                '15.95',
                /**
                 * $textAmount
                 */
                '$15.95',
                /**
                 * $autoInvoice
                 */
                true,
                /**
                 * $orderCanInvoice
                 */
                false,
                /**
                 * $orderHasInvoices
                 */
                true,
                /**
                 * $postData
                 */
                false,
            ],
            12 => [
                /**
                 * $state
                 */
                \Magento\Sales\Model\Order::STATE_CANCELED,
                /**
                 * $orderEmailSent
                 */
                true,
                /**
                 * $sendOrderConfirmationEmail
                 */
                true,
                /**
                 * $paymentAction
                 */
                'not_authorize',
                /**
                 * $amount
                 */
                '15.95',
                /**
                 * $textAmount
                 */
                '$15.95',
                /**
                 * $autoInvoice
                 */
                true,
                /**
                 * $orderCanInvoice
                 */
                true,
                /**
                 * $orderHasInvoices
                 */
                true,
                /**
                 * $postData
                 */
                false,
            ],
            /**
             * PROCESSING && AUTHORIZE
             */
            13 => [
                /**
                 * $state
                 */
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                /**
                 * $orderEmailSent
                 */
                true,
                /**
                 * $sendOrderConfirmationEmail
                 */
                true,
                /**
                 * $paymentAction
                 */
                'authorize',
                /**
                 * $amount
                 */
                '15.95',
                /**
                 * $textAmount
                 */
                '$15.95',
                /**
                 * $autoInvoice
                 */
                false,
                /**
                 * $orderCanInvoice
                 */
                false,
                /**
                 * $orderHasInvoices
                 */
                false,
                /**
                 * $postData
                 */
                false,
            ],
            14 => [
                /**
                 * $state
                 */
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                /**
                 * $orderEmailSent
                 */
                false,
                /**
                 * $sendOrderConfirmationEmail
                 */
                true,
                /**
                 * $paymentAction
                 */
                'authorize',
                /**
                 * $amount
                 */
                '15.95',
                /**
                 * $textAmount
                 */
                '$15.95',
                /**
                 * $autoInvoice
                 */
                false,
                /**
                 * $orderCanInvoice
                 */
                false,
                /**
                 * $orderHasInvoices
                 */
                false,
                /**
                 * $postData
                 */
                false,
            ],
            15 => [
                /**
                 * $state
                 */
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                /**
                 * $orderEmailSent
                 */
                true,
                /**
                 * $sendOrderConfirmationEmail
                 */
                false,
                /**
                 * $paymentAction
                 */
                'authorize',
                /**
                 * $amount
                 */
                '15.95',
                /**
                 * $textAmount
                 */
                '$15.95',
                /**
                 * $autoInvoice
                 */
                false,
                /**
                 * $orderCanInvoice
                 */
                false,
                /**
                 * $orderHasInvoices
                 */
                false,
                /**
                 * $postData
                 */
                false,
            ],
            16 => [
                /**
                 * $state
                 */
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                /**
                 * $orderEmailSent
                 */
                false,
                /**
                 * $sendOrderConfirmationEmail
                 */
                false,
                /**
                 * $paymentAction
                 */
                'authorize',
                /**
                 * $amount
                 */
                '15.95',
                /**
                 * $textAmount
                 */
                '$15.95',
                /**
                 * $autoInvoice
                 */
                false,
                /**
                 * $orderCanInvoice
                 */
                false,
                /**
                 * $orderHasInvoices
                 */
                false,
                /**
                 * $postData
                 */
                false,
            ],
            /**
             * PROCESSING && NOT AUTHORIZE
             */
            17 => [
                /**
                 * $state
                 */
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                /**
                 * $orderEmailSent
                 */
                true,
                /**
                 * $sendOrderConfirmationEmail
                 */
                true,
                /**
                 * $paymentAction
                 */
                'not_authorize',
                /**
                 * $amount
                 */
                '15.95',
                /**
                 * $textAmount
                 */
                '$15.95',
                /**
                 * $autoInvoice
                 */
                false,
                /**
                 * $orderCanInvoice
                 */
                false,
                /**
                 * $orderHasInvoices
                 */
                false,
                /**
                 * $postData
                 */
                false,
            ],
            18 => [
                /**
                 * $state
                 */
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                /**
                 * $orderEmailSent
                 */
                false,
                /**
                 * $sendOrderConfirmationEmail
                 */
                true,
                /**
                 * $paymentAction
                 */
                'not_authorize',
                /**
                 * $amount
                 */
                '15.95',
                /**
                 * $textAmount
                 */
                '$15.95',
                /**
                 * $autoInvoice
                 */
                false,
                /**
                 * $orderCanInvoice
                 */
                false,
                /**
                 * $orderHasInvoices
                 */
                false,
                /**
                 * $postData
                 */
                false,
            ],
            19 => [
                /**
                 * $state
                 */
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                /**
                 * $orderEmailSent
                 */
                true,
                /**
                 * $sendOrderConfirmationEmail
                 */
                false,
                /**
                 * $paymentAction
                 */
                'not_authorize',
                /**
                 * $amount
                 */
                '15.95',
                /**
                 * $textAmount
                 */
                '$15.95',
                /**
                 * $autoInvoice
                 */
                false,
                /**
                 * $orderCanInvoice
                 */
                false,
                /**
                 * $orderHasInvoices
                 */
                false,
                /**
                 * $postData
                 */
                false,
            ],
            20 => [
                /**
                 * $state
                 */
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                /**
                 * $orderEmailSent
                 */
                false,
                /**
                 * $sendOrderConfirmationEmail
                 */
                false,
                /**
                 * $paymentAction
                 */
                'not_authorize',
                /**
                 * $amount
                 */
                '15.95',
                /**
                 * $textAmount
                 */
                '$15.95',
                /**
                 * $autoInvoice
                 */
                false,
                /**
                 * $orderCanInvoice
                 */
                false,
                /**
                 * $orderHasInvoices
                 */
                false,
                /**
                 * $postData
                 */
                false,
            ],
            /**
             * PROCESSING && NOT AUTHORIZE && AUTO INVOICE
             */
            21 => [
                /**
                 * $state
                 */
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                /**
                 * $orderEmailSent
                 */
                true,
                /**
                 * $sendOrderConfirmationEmail
                 */
                true,
                /**
                 * $paymentAction
                 */
                'not_authorize',
                /**
                 * $amount
                 */
                '15.95',
                /**
                 * $textAmount
                 */
                '$15.95',
                /**
                 * $autoInvoice
                 */
                true,
                /**
                 * $orderCanInvoice
                 */
                false,
                /**
                 * $orderHasInvoices
                 */
                false,
                /**
                 * $postData
                 */
                false,
            ],
            22 => [
                /**
                 * $state
                 */
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                /**
                 * $orderEmailSent
                 */
                true,
                /**
                 * $sendOrderConfirmationEmail
                 */
                true,
                /**
                 * $paymentAction
                 */
                'not_authorize',
                /**
                 * $amount
                 */
                '15.95',
                /**
                 * $textAmount
                 */
                '$15.95',
                /**
                 * $autoInvoice
                 */
                true,
                /**
                 * $orderCanInvoice
                 */
                true,
                /**
                 * $orderHasInvoices
                 */
                false,
                /**
                 * $postData
                 */
                false,
            ],
            23 => [
                /**
                 * $state
                 */
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                /**
                 * $orderEmailSent
                 */
                true,
                /**
                 * $sendOrderConfirmationEmail
                 */
                true,
                /**
                 * $paymentAction
                 */
                'not_authorize',
                /**
                 * $amount
                 */
                '15.95',
                /**
                 * $textAmount
                 */
                '$15.95',
                /**
                 * $autoInvoice
                 */
                true,
                /**
                 * $orderCanInvoice
                 */
                true,
                /**
                 * $orderHasInvoices
                 */
                false,
                /**
                 * $postData
                 */
                ['brq_transactions' => 'test_transaction_id'],
            ],
            24 => [
                /**
                 * $state
                 */
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                /**
                 * $orderEmailSent
                 */
                true,
                /**
                 * $sendOrderConfirmationEmail
                 */
                true,
                /**
                 * $paymentAction
                 */
                'not_authorize',
                /**
                 * $amount
                 */
                '15.95',
                /**
                 * $textAmount
                 */
                '$15.95',
                /**
                 * $autoInvoice
                 */
                true,
                /**
                 * $orderCanInvoice
                 */
                false,
                /**
                 * $orderHasInvoices
                 */
                true,
                /**
                 * $postData
                 */
                false,
            ],
            25 => [
                /**
                 * $state
                 */
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                /**
                 * $orderEmailSent
                 */
                true,
                /**
                 * $sendOrderConfirmationEmail
                 */
                true,
                /**
                 * $paymentAction
                 */
                'not_authorize',
                /**
                 * $amount
                 */
                '15.95',
                /**
                 * $textAmount
                 */
                '$15.95',
                /**
                 * $autoInvoice
                 */
                true,
                /**
                 * $orderCanInvoice
                 */
                true,
                /**
                 * $orderHasInvoices
                 */
                true,
                /**
                 * $postData
                 */
                false,
            ],
        ];
    }
}
