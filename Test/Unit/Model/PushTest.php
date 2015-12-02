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
namespace TIG\Buckaroo\Test\Unit\Model\Method;

use Magento\Sales\Model\Order;
use \Mockery as m;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\ObjectManagerInterface;

class PushTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \TIG\Buckaroo\Model\Push
     */
    protected $_object;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManagerHelper;

    protected function setUp()
    {
        $this->_objectManagerHelper = new ObjectManager($this);
    }

    public function testReceivePush()
    {
        $id = 1;
        $requestMock = m::mock('\Magento\Framework\Webapi\Rest\Request');
        $requestMock->shouldReceive('getParams')->once()->andReturn(['brq_invoicenumber'=>$id]);

        $order = m::mock(Order::class);
        $order->shouldReceive('load')->with($id)->andReturnSelf();
        $order->shouldReceive('getId')->andReturn($id);
        $order->shouldReceive('setStatus')->with('complete')->andReturnSelf();
        $order->shouldReceive('save')->andReturn(true);

        $objectManager = m::mock(ObjectManagerInterface::class);
        $objectManager->shouldReceive('create')->with(Order::class)->andReturn($order);

        $this->_object = $this->_objectManagerHelper->getObject(
            'TIG\Buckaroo\Model\Push',
            [
                'objectManager' => $objectManager,
                'request' => $requestMock
            ]
        );

        $result = $this->_object->receivePush();
        $this->assertTrue($result);
    }

    public function testReceivePushWithNonExistingId()
    {
        $id = 1;
        $requestMock = m::mock('\Magento\Framework\Webapi\Rest\Request');
        $requestMock->shouldReceive('getParams')->once()->andReturn(['brq_invoicenumber'=>$id]);

        $order = m::mock(Order::class);
        $order->shouldReceive('load')->with($id)->andReturnSelf();
        $order->shouldReceive('getId')->andReturn(null);
        $order->shouldReceive('setStatus')->with('complete')->andReturnSelf();
        $order->shouldReceive('save')->andReturn(true);

        $objectManager = m::mock(ObjectManagerInterface::class);
        $objectManager->shouldReceive('create')->with(Order::class)->andReturn($order);

        $this->_object = $this->_objectManagerHelper->getObject(
            'TIG\Buckaroo\Model\Push',
            [
                'objectManager' => $objectManager,
                'request' => $requestMock
            ]
        );

        $result = $this->_object->receivePush();
        $this->assertFalse($result);
    }

    protected function tearDown()
    {
        m::close();
    }
}