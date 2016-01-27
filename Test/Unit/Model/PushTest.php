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
 * @copyright   Copyright (c) 2016 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
namespace TIG\Buckaroo\Test\Unit\Model;

class Push extends \TIG\Buckaroo\Test\BaseTest
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
    protected $configProviderFactory;

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
        $this->configProviderFactory = \Mockery::mock(\TIG\Buckaroo\Model\ConfigProvider\Factory::class);
        $this->debugger = \Mockery::mock(\TIG\Buckaroo\Debug\Debugger::class);

        /** Needed to deal with __destruct */
        $this->debugger->shouldReceive('log')->andReturnSelf();

        $this->orderSender = \Mockery::mock(\Magento\Sales\Model\Order\Email\Sender\OrderSender::class);

        /**
         * We are using the temporary class declared above, but it could be any class extending from the AbstractMethod
         * class.
         */
        $this->object = $this->objectManagerHelper->getObject(\TIG\Buckaroo\Model\Push::class, [
            'objectManager' => $this->objectManager,
            'request' => $this->request,
            'helper' => $this->helper,
            'configProviderFactory' => $this->configProviderFactory,
            'debugger' => $this->debugger,
            'orderSender' => $this->orderSender,
        ]);
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

        $this->configProviderFactory->shouldReceive('get')->with('account')->andReturnSelf();
        $this->configProviderFactory->shouldReceive('getCancelOnFailed')->andReturn($cancelOnFailed);

        $orderMock = \Mockery::mock(\Magento\Sales\Model\Order::class);
        $orderMock->shouldReceive('getState')->atLeast(1)->andReturn($state);

        if ($state == $canceledPaymentState) {
            $orderMock->shouldReceive('addStatusHistoryComment')->once()->with($expectedDescription, $status);
        } else {
            $orderMock->shouldReceive('addStatusHistoryComment')->once()->with($expectedDescription);
        }

        if ($cancelOnFailed) {
            $orderMock->shouldReceive('canCancel')->once()->andReturn($canCancel);
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
     * @param bool $textAmount
     * @param bool $autoInvoice
     * @param bool $orderCanInvoice
     * @param bool $orderHasInvoices
     * @param bool $postData
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

        $successPaymentState = \Magento\Sales\Model\Order::STATE_PROCESSING;

        $this->configProviderFactory->shouldReceive('get')->with('account')->andReturnSelf();
        $this->configProviderFactory->shouldReceive('getOrderConfirmationEmail')
            ->andReturn($sendOrderConfirmationEmail);



        $orderMock = \Mockery::mock(\Magento\Sales\Model\Order::class);
        $orderMock->shouldReceive('getEmailSent')->andReturn($orderEmailSent);
        $orderMock->shouldReceive('getBaseGrandTotal')->andReturn($amount);
        $orderMock->shouldReceive('getState')->atLeast(1)->andReturn($state);

        if (!$orderEmailSent && $sendOrderConfirmationEmail) {
            $this->orderSender->shouldReceive('send')->with($orderMock);
        }

        $orderMock->shouldReceive('getPayment')->andReturnSelf();
        $orderMock->shouldReceive('getMethodInstance')->andReturnSelf();
        $orderMock->shouldReceive('getConfigData')->with('payment_action')->andReturn($paymentAction);
        $orderMock->shouldReceive('getBaseCurrency')->andReturnSelf();
        $orderMock->shouldReceive('formatTxt')->andReturn($textAmount);

        if ($paymentAction != 'authorize') {
            $expectedDescription = 'Payment status : <strong>' . $message . "</strong><br/>";
            $expectedDescription .= 'Total amount of ' . $textAmount . ' has been paid';
        } else {
            $expectedDescription = 'Authorization status : <strong>' . $message . "</strong><br/>";
            $expectedDescription .= 'Total amount of ' . $textAmount . ' has been ' .
                'authorized. Please create an invoice to capture the authorized amount.';
        }

        if ($state == $successPaymentState) {
            $orderMock->shouldReceive('addStatusHistoryComment')->once()->with($expectedDescription, $status);
        } else {
            $orderMock->shouldReceive('addStatusHistoryComment')->once()->with($expectedDescription);
        }

        $this->configProviderFactory->shouldReceive('getAutoInvoice')->andReturn($autoInvoice);

        $objectMock = $this->getPartialObject(
            get_class($this->object),
            [
                'objectManager' => $this->objectManager,
                'request' => $this->request,
                'helper' => $this->helper,
                'configProviderFactory' => $this->configProviderFactory,
                'debugger' => $this->debugger,
                'orderSender' => $this->orderSender,
            ],
            ['addTransactionData']
        );

        if ($autoInvoice) {
            $orderMock->shouldReceive('canInvoice')->andReturn($orderCanInvoice);
            $orderMock->shouldReceive('hasInvoices')->andReturn($orderHasInvoices);

            if (!$orderCanInvoice && $orderHasInvoices) {
                $this->setExpectedException(\TIG\Buckaroo\Exception::class);
                $this->debugger->shouldReceive('addToMessage')->withAnyArgs();
            } else {
                $orderMock->shouldReceive('save')->andReturnSelf();

                if ($orderCanInvoice && !$orderHasInvoices) {
                    $orderMock->shouldReceive('save')->andReturnSelf();
                    $objectMock->expects($this->exactly(2))->method('addTransactionData');

                    /** @noinspection PhpUndefinedFieldInspection */
                    $objectMock->postData = $postData;

                    $invoiceMock = \Mockery::mock(\Magento\Sales\Model\Order\Invoice::class);

                    $orderMock->shouldReceive('getInvoiceCollection')->andReturn([$invoiceMock]);

                    if (isset($postData['brq_transactions'])) {
                        $invoiceMock->shouldReceive('setTransactionId')
                            ->with($postData['brq_transactions'])
                            ->andReturnSelf();
                        $invoiceMock->shouldReceive('save');
                    }
                } else {
                    $objectMock->expects($this->once())->method('addTransactionData');
                }
            }

        }

        /** @noinspection PhpUndefinedFieldInspection */
        $objectMock->order = $orderMock;

        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertTrue($objectMock->processSucceededPush($status, $message));
    }

    public function processSucceededPushDataProvider()
    {
        return [
            /** CANCELED && AUTHORIZE */
            [
                /** $state */
                \Magento\Sales\Model\Order::STATE_CANCELED,
                /** $orderEmailSent */
                true,
                /** $sendOrderConfirmationEmail */
                true,
                /** $paymentAction */
                'authorize',
                /** $amount */
                '15.95',
                /** $textAmount */
                '$15.95',
                /** $autoInvoice */
                false,
                /** $orderCanInvoice */
                false,
                /** $orderHasInvoices */
                false,
                /** $postData */
                false,
            ],
            [
                /** $state */
                \Magento\Sales\Model\Order::STATE_CANCELED,
                /** $orderEmailSent */
                false,
                /** $sendOrderConfirmationEmail */
                true,
                /** $paymentAction */
                'authorize',
                /** $amount */
                '15.95',
                /** $textAmount */
                '$15.95',
                /** $autoInvoice */
                false,
                /** $orderCanInvoice */
                false,
                /** $orderHasInvoices */
                false,
                /** $postData */
                false,
            ],
            [
                /** $state */
                \Magento\Sales\Model\Order::STATE_CANCELED,
                /** $orderEmailSent */
                true,
                /** $sendOrderConfirmationEmail */
                false,
                /** $paymentAction */
                'authorize',
                /** $amount */
                '15.95',
                /** $textAmount */
                '$15.95',
                /** $autoInvoice */
                false,
                /** $orderCanInvoice */
                false,
                /** $orderHasInvoices */
                false,
                /** $postData */
                false,
            ],
            [
                /** $state */
                \Magento\Sales\Model\Order::STATE_CANCELED,
                /** $orderEmailSent */
                false,
                /** $sendOrderConfirmationEmail */
                false,
                /** $paymentAction */
                'authorize',
                /** $amount */
                '15.95',
                /** $textAmount */
                '$15.95',
                /** $autoInvoice */
                false,
                /** $orderCanInvoice */
                false,
                /** $orderHasInvoices */
                false,
                /** $postData */
                false,
            ],


            /** CANCELED && NOT AUTHORIZE */

            [
                /** $state */
                \Magento\Sales\Model\Order::STATE_CANCELED,
                /** $orderEmailSent */
                true,
                /** $sendOrderConfirmationEmail */
                true,
                /** $paymentAction */
                'not_authorize',
                /** $amount */
                '15.95',
                /** $textAmount */
                '$15.95',
                /** $autoInvoice */
                false,
                /** $orderCanInvoice */
                false,
                /** $orderHasInvoices */
                false,
                /** $postData */
                false,
            ],
            [
                /** $state */
                \Magento\Sales\Model\Order::STATE_CANCELED,
                /** $orderEmailSent */
                false,
                /** $sendOrderConfirmationEmail */
                true,
                /** $paymentAction */
                'not_authorize',
                /** $amount */
                '15.95',
                /** $textAmount */
                '$15.95',
                /** $autoInvoice */
                false,
                /** $orderCanInvoice */
                false,
                /** $orderHasInvoices */
                false,
                /** $postData */
                false,
            ],
            [
                /** $state */
                \Magento\Sales\Model\Order::STATE_CANCELED,
                /** $orderEmailSent */
                true,
                /** $sendOrderConfirmationEmail */
                false,
                /** $paymentAction */
                'not_authorize',
                /** $amount */
                '15.95',
                /** $textAmount */
                '$15.95',
                /** $autoInvoice */
                false,
                /** $orderCanInvoice */
                false,
                /** $orderHasInvoices */
                false,
                /** $postData */
                false,
            ],
            [
                /** $state */
                \Magento\Sales\Model\Order::STATE_CANCELED,
                /** $orderEmailSent */
                false,
                /** $sendOrderConfirmationEmail */
                false,
                /** $paymentAction */
                'not_authorize',
                /** $amount */
                '15.95',
                /** $textAmount */
                '$15.95',
                /** $autoInvoice */
                false,
                /** $orderCanInvoice */
                false,
                /** $orderHasInvoices */
                false,
                /** $postData */
                false,
            ],


            /** CANCELED && NOT AUTHORIZE && AUTO INVOICE*/

            [
                /** $state */
                \Magento\Sales\Model\Order::STATE_CANCELED,
                /** $orderEmailSent */
                true,
                /** $sendOrderConfirmationEmail */
                true,
                /** $paymentAction */
                'not_authorize',
                /** $amount */
                '15.95',
                /** $textAmount */
                '$15.95',
                /** $autoInvoice */
                true,
                /** $orderCanInvoice */
                false,
                /** $orderHasInvoices */
                false,
                /** $postData */
                false,
            ],
            [
                /** $state */
                \Magento\Sales\Model\Order::STATE_CANCELED,
                /** $orderEmailSent */
                true,
                /** $sendOrderConfirmationEmail */
                true,
                /** $paymentAction */
                'not_authorize',
                /** $amount */
                '15.95',
                /** $textAmount */
                '$15.95',
                /** $autoInvoice */
                true,
                /** $orderCanInvoice */
                true,
                /** $orderHasInvoices */
                false,
                /** $postData */
                false,
            ],
            [
                /** $state */
                \Magento\Sales\Model\Order::STATE_CANCELED,
                /** $orderEmailSent */
                true,
                /** $sendOrderConfirmationEmail */
                true,
                /** $paymentAction */
                'not_authorize',
                /** $amount */
                '15.95',
                /** $textAmount */
                '$15.95',
                /** $autoInvoice */
                true,
                /** $orderCanInvoice */
                true,
                /** $orderHasInvoices */
                false,
                /** $postData */
                ['brq_transactions' => 'test_transaction_id'],
            ],
            [
                /** $state */
                \Magento\Sales\Model\Order::STATE_CANCELED,
                /** $orderEmailSent */
                true,
                /** $sendOrderConfirmationEmail */
                true,
                /** $paymentAction */
                'not_authorize',
                /** $amount */
                '15.95',
                /** $textAmount */
                '$15.95',
                /** $autoInvoice */
                true,
                /** $orderCanInvoice */
                false,
                /** $orderHasInvoices */
                true,
                /** $postData */
                false,
            ],
            [
                /** $state */
                \Magento\Sales\Model\Order::STATE_CANCELED,
                /** $orderEmailSent */
                true,
                /** $sendOrderConfirmationEmail */
                true,
                /** $paymentAction */
                'not_authorize',
                /** $amount */
                '15.95',
                /** $textAmount */
                '$15.95',
                /** $autoInvoice */
                true,
                /** $orderCanInvoice */
                true,
                /** $orderHasInvoices */
                true,
                /** $postData */
                false,
            ],


            /** PROCESSING && AUTHORIZE */
            [
                /** $state */
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                /** $orderEmailSent */
                true,
                /** $sendOrderConfirmationEmail */
                true,
                /** $paymentAction */
                'authorize',
                /** $amount */
                '15.95',
                /** $textAmount */
                '$15.95',
                /** $autoInvoice */
                false,
                /** $orderCanInvoice */
                false,
                /** $orderHasInvoices */
                false,
                /** $postData */
                false,
            ],
            [
                /** $state */
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                /** $orderEmailSent */
                false,
                /** $sendOrderConfirmationEmail */
                true,
                /** $paymentAction */
                'authorize',
                /** $amount */
                '15.95',
                /** $textAmount */
                '$15.95',
                /** $autoInvoice */
                false,
                /** $orderCanInvoice */
                false,
                /** $orderHasInvoices */
                false,
                /** $postData */
                false,
            ],
            [
                /** $state */
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                /** $orderEmailSent */
                true,
                /** $sendOrderConfirmationEmail */
                false,
                /** $paymentAction */
                'authorize',
                /** $amount */
                '15.95',
                /** $textAmount */
                '$15.95',
                /** $autoInvoice */
                false,
                /** $orderCanInvoice */
                false,
                /** $orderHasInvoices */
                false,
                /** $postData */
                false,
            ],
            [
                /** $state */
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                /** $orderEmailSent */
                false,
                /** $sendOrderConfirmationEmail */
                false,
                /** $paymentAction */
                'authorize',
                /** $amount */
                '15.95',
                /** $textAmount */
                '$15.95',
                /** $autoInvoice */
                false,
                /** $orderCanInvoice */
                false,
                /** $orderHasInvoices */
                false,
                /** $postData */
                false,
            ],


            /** PROCESSING && NOT AUTHORIZE */

            [
                /** $state */
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                /** $orderEmailSent */
                true,
                /** $sendOrderConfirmationEmail */
                true,
                /** $paymentAction */
                'not_authorize',
                /** $amount */
                '15.95',
                /** $textAmount */
                '$15.95',
                /** $autoInvoice */
                false,
                /** $orderCanInvoice */
                false,
                /** $orderHasInvoices */
                false,
                /** $postData */
                false,
            ],
            [
                /** $state */
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                /** $orderEmailSent */
                false,
                /** $sendOrderConfirmationEmail */
                true,
                /** $paymentAction */
                'not_authorize',
                /** $amount */
                '15.95',
                /** $textAmount */
                '$15.95',
                /** $autoInvoice */
                false,
                /** $orderCanInvoice */
                false,
                /** $orderHasInvoices */
                false,
                /** $postData */
                false,
            ],
            [
                /** $state */
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                /** $orderEmailSent */
                true,
                /** $sendOrderConfirmationEmail */
                false,
                /** $paymentAction */
                'not_authorize',
                /** $amount */
                '15.95',
                /** $textAmount */
                '$15.95',
                /** $autoInvoice */
                false,
                /** $orderCanInvoice */
                false,
                /** $orderHasInvoices */
                false,
                /** $postData */
                false,
            ],
            [
                /** $state */
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                /** $orderEmailSent */
                false,
                /** $sendOrderConfirmationEmail */
                false,
                /** $paymentAction */
                'not_authorize',
                /** $amount */
                '15.95',
                /** $textAmount */
                '$15.95',
                /** $autoInvoice */
                false,
                /** $orderCanInvoice */
                false,
                /** $orderHasInvoices */
                false,
                /** $postData */
                false,
            ],


            /** PROCESSING && NOT AUTHORIZE && AUTO INVOICE*/

            [
                /** $state */
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                /** $orderEmailSent */
                true,
                /** $sendOrderConfirmationEmail */
                true,
                /** $paymentAction */
                'not_authorize',
                /** $amount */
                '15.95',
                /** $textAmount */
                '$15.95',
                /** $autoInvoice */
                true,
                /** $orderCanInvoice */
                false,
                /** $orderHasInvoices */
                false,
                /** $postData */
                false,
            ],
            [
                /** $state */
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                /** $orderEmailSent */
                true,
                /** $sendOrderConfirmationEmail */
                true,
                /** $paymentAction */
                'not_authorize',
                /** $amount */
                '15.95',
                /** $textAmount */
                '$15.95',
                /** $autoInvoice */
                true,
                /** $orderCanInvoice */
                true,
                /** $orderHasInvoices */
                false,
                /** $postData */
                false,
            ],
            [
                /** $state */
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                /** $orderEmailSent */
                true,
                /** $sendOrderConfirmationEmail */
                true,
                /** $paymentAction */
                'not_authorize',
                /** $amount */
                '15.95',
                /** $textAmount */
                '$15.95',
                /** $autoInvoice */
                true,
                /** $orderCanInvoice */
                true,
                /** $orderHasInvoices */
                false,
                /** $postData */
                ['brq_transactions' => 'test_transaction_id'],
            ],
            [
                /** $state */
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                /** $orderEmailSent */
                true,
                /** $sendOrderConfirmationEmail */
                true,
                /** $paymentAction */
                'not_authorize',
                /** $amount */
                '15.95',
                /** $textAmount */
                '$15.95',
                /** $autoInvoice */
                true,
                /** $orderCanInvoice */
                false,
                /** $orderHasInvoices */
                true,
                /** $postData */
                false,
            ],
            [
                /** $state */
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                /** $orderEmailSent */
                true,
                /** $sendOrderConfirmationEmail */
                true,
                /** $paymentAction */
                'not_authorize',
                /** $amount */
                '15.95',
                /** $textAmount */
                '$15.95',
                /** $autoInvoice */
                true,
                /** $orderCanInvoice */
                true,
                /** $orderHasInvoices */
                true,
                /** $postData */
                false,
            ],
        ];
    }
}
