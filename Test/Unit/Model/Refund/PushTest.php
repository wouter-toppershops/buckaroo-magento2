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
 * to servicedesk@totalinternetgroup.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@totalinternetgroup.nl for more information.
 *
 * @copyright   Copyright (c) 2015 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
namespace TIG\Buckaroo\Test\Unit\Model\Refund;

class PushTest extends \TIG\Buckaroo\Test\BaseTest
{
    /**
     * @var \TIG\Buckaroo\Model\Refund\Push
     */
    protected $object;

    /**
     * @var \Mockery\MockInterface
     */
    protected $debugger;

    /**
     * @var \Mockery\MockInterface
     */
    protected $order;

    /**
     * @var \Mockery\MockInterface
     */
    protected $creditmemoFactory;

    /**
     * @var \Mockery\MockInterface
     */
    protected $objectManager;

    /**
     * Setup the base mock objects.
     */
    public function setUp()
    {
        parent::setUp();

        $this->order = \Mockery::mock(\Magento\Sales\Model\Order::class)->makePartial();
        $this->debugger = \Mockery::mock(\TIG\Buckaroo\Debug\Debugger::class)->makePartial();
        $this->debugger->shouldReceive('log')->andReturnSelf();
        $this->objectManager = \Mockery::mock(\Magento\Framework\ObjectManagerInterface::class);
        $this->creditmemoFactory = \Mockery::mock(\Magento\Sales\Model\Order\CreditmemoFactory::class);
        $this->object = $this->objectManagerHelper->getObject(\TIG\Buckaroo\Model\Refund\Push::class, [
            'debugger' => $this->debugger,
            'objectManager' => $this->objectManager,
            'creditmemoFactory' => $this->creditmemoFactory,
        ]);

        // @todo: Check why this attribute is public.
        $this->object->order = $this->order;
    }

    /**
     * Test the happy path of the receiveRefundMethod.
     */
    public function testReceiveRefundPush()
    {
        $id = rand(1, 1000);
        $this->creditmemoFactory->shouldReceive('createByOrder')->once()->andReturnSelf();
        $this->creditmemoFactory->shouldReceive('getItems')->andReturn([]);
        $this->creditmemoFactory->shouldReceive('getAllItems')->andReturn([]);
        $this->creditmemoFactory->shouldReceive('isValidGrandTotal')->andReturn(true);
        $this->creditmemoFactory->shouldReceive('setTransactionId')->once()->with($id);

        $this->order->shouldReceive('getId')->once()->andReturn($id);
        $this->order->shouldReceive('getCreditmemosCollection')->andReturn($this->creditmemoFactory);
        $this->order->shouldReceive('getItemsCollection')->andReturn($this->creditmemoFactory);

        $this->debugger->shouldReceive('addToMessage')->once()->with('Trying to refund order ' . $id . ' out of paymentplaza');
        $this->debugger->shouldReceive('addToMessage')->once()->with('With this refund of 0 the grand total will be refunded.');

        $this->objectManager->shouldReceive('create')->once()->with('Magento\Sales\Api\CreditmemoManagementInterface')->andReturnSelf();
        $this->objectManager->shouldReceive('refund')->once();

        $postData = [
            'brq_currency' => false,
            'brq_amount_credit' => 0,
            'brq_transactions' => $id,
        ];
        $signatureValidation = true;
        $result = $this->object->receiveRefundPush($postData, $signatureValidation, $this->order);

        $this->assertTrue($result);
    }

    /**
     * Test the path of the receiveRefundMethod where the signature is invalid.
     */
    public function testReceiveRefundPushInvalidSignature()
    {
        $this->order->shouldReceive('canCreditmemo')->twice()->andReturn(false);

        try {
            $this->object->receiveRefundPush([], false, $this->order);
            $this->fail();
        } catch(\Exception $e)
        {
            $this->assertInstanceOf(\TIG\Buckaroo\Exception::class, $e);
            $this->assertEquals('Buckaroo refund push validation failed', $e->getMessage());
        }
    }

    public function testCreateCreditMemoInvalidGrandTotal()
    {
        $this->creditmemoFactory->shouldReceive('getItems')->andReturn([]);
        $this->creditmemoFactory->shouldReceive('getAllItems')->andReturn([]);
        $this->creditmemoFactory->shouldReceive('isValidGrandTotal')->andReturn(false);
        $this->creditmemoFactory->shouldReceive('createByOrder')->once()->andReturnSelf();

        $this->order->shouldReceive('getCreditmemosCollection')->andReturn($this->creditmemoFactory);
        $this->order->shouldReceive('getItemsCollection')->andReturn($this->creditmemoFactory);

        $this->debugger->shouldReceive('addToMessage')->once()->with('Buckaroo failed to create the credit memo\'s { The credit memo\'s total must be positive. }');

        $this->object->createCreditmemo();
    }
}