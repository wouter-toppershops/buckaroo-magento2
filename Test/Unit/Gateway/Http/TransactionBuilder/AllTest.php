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
namespace TIG\Buckaroo\Test\Unit\Gateway\Http\TransactionBuilder;

use Mockery as m;
use TIG\Buckaroo\Test\BaseTest;
use TIG\Buckaroo\Gateway\Http\TransactionBuilder\Order;

class AllTest extends BaseTest
{
    /**
     * @var \Magento\Sales\Model\Order|m\MockInterface
     */
    protected $order;

    /**
     * @var Order
     */
    protected $object;

    /**
     * @var \TIG\Buckaroo\Model\ConfigProvider\Factory|m\MockInterface
     */
    protected $configProvider;

    /**
     * Setup the required dependencies
     */
    public function setUp()
    {
        parent::setUp();

        $this->order = m::mock('Magento\Sales\Model\Order');
        $this->configProvider = m::mock('\TIG\Buckaroo\Model\ConfigProvider\Factory');

        $this->object = $this->objectManagerHelper->getObject(Order::class, [
            'configProviderFactory' => $this->configProvider,
        ]);

        $this->object->setOrder($this->order);
    }

    /**
     * Test the getBody method.
     */
    public function testGetBody()
    {
        $expected = [
            'Currency' => 'EUR',
            'AmountDebit' => 50,
            'AmountCredit' => 0,
            'Invoice' => 999,
            'Order' => 999,
            'Description' => 'transactionLabel',
            'ClientIP' => [
                '_' => '127.0.0.1',
                'Type' => 'IPv4',
            ],
        ];

        $account = m::mock('\TIG\Buckaroo\Model\ConfigProvider\Account');
        $account->shouldReceive('getTransactionLabel')->andReturn($expected['Description']);
        $this->configProvider->shouldReceive('get')->once()->with('account')->andReturn($account);

        $this->order->shouldReceive('getOrderCurrencyCode')->once()->andReturn($expected['Currency']);
        $this->order->shouldReceive('getBaseGrandTotal')->once()->andReturn($expected['AmountDebit']);
        $this->order->shouldReceive('getIncrementId')->twice()->andReturn($expected['Invoice']);
        $this->order->shouldReceive('getRemoteIp')->twice()->andReturn($expected['ClientIP']['_']);

        $result = $this->object->getBody();

        foreach($expected as $key => $value)
        {
            $this->assertEquals($value, $result[$key]);
        }
    }
}