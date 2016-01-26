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
}
