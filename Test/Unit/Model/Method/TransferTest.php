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

class TransferTest extends \TIG\Buckaroo\Test\BaseTest
{
    /**
     * @var \TIG\Buckaroo\Model\Method\Transfer
     */
    protected $object;

    /**
     * @var \TIG\Buckaroo\Gateway\Http\TransactionBuilderFactory|\Mockery\MockInterface
     */
    protected $transactionBuilderFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\Mockery\MockInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Payment\Model\InfoInterface|\Magento\Sales\Api\Data\OrderPaymentInterface|\Mockery\MockInterface
     */
    protected $paymentInterface;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\Mockery\MockInterface
     */
    protected $scopeConfig;

    /**
     * @var
     */
    protected $configProviderMethodFactory;

    /**
     * Setup the base mocks.
     */
    public function setUp()
    {
        parent::setUp();

        $this->objectManager               = \Mockery::mock(\Magento\Framework\ObjectManagerInterface::class);
        $this->transactionBuilderFactory   = \Mockery::mock(
            \TIG\Buckaroo\Gateway\Http\TransactionBuilderFactory::class
        );
        $this->scopeConfig                 = \Mockery::mock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->configProviderMethodFactory = \Mockery::mock(\TIG\Buckaroo\Model\ConfigProvider\Method\Factory::class);

        $this->object = $this->objectManagerHelper->getObject(
            \TIG\Buckaroo\Model\Method\Transfer::class, [
            'scopeConfig'                 => $this->scopeConfig,
            'objectManager'               => $this->objectManager,
            'transactionBuilderFactory'   => $this->transactionBuilderFactory,
            'configProviderMethodFactory' => $this->configProviderMethodFactory
        ]
        );

        $this->paymentInterface = \Mockery::mock(
            \Magento\Payment\Model\InfoInterface::class,
            \Magento\Sales\Api\Data\OrderPaymentInterface::class
        );
    }

    /**
     * Test the getOrderTransactionBuilder method.
     */
    public function testGetOrderTransactionBuilder()
    {
        $fixture = [
            'firstname' => 'John',
            'lastname'  => 'Doe',
            'email'     => 'john@doe.com',
        ];

        $order = \Mockery::mock(\TIG\Buckaroo\Gateway\Http\TransactionBuilder\Order::class);
        $order->shouldReceive('getCustomerFirstname')->andReturn($fixture['firstname']);
        $order->shouldReceive('getCustomerLastName')->andReturn($fixture['lastname']);
        $order->shouldReceive('getCustomerEmail')->andReturn($fixture['email']);
        $order->shouldReceive('setOrder')->with($order)->andReturnSelf();
        $order->shouldReceive('setMethod')->with('TransactionRequest')->andReturnSelf();

        $order->shouldReceive('setServices')->andReturnUsing(
            function ($services) use ($fixture, $order) {
                $this->assertEquals($fixture['firstname'], $services['RequestParameter'][0]['_']);
                $this->assertEquals($fixture['lastname'], $services['RequestParameter'][1]['_']);
                $this->assertEquals($fixture['email'], $services['RequestParameter'][2]['_']);

                $this->assertEquals('transfer', $services['Name']);
                $this->assertEquals('Pay', $services['Action']);

                return $order;
            }
        );

        /** @noinspection PhpUndefinedMethodInspection */
        $this->configProviderMethodFactory->shouldReceive('get')->once()->with('transfer')->andReturnSelf();
        /** @noinspection PhpUndefinedMethodInspection */
        $this->configProviderMethodFactory->shouldReceive('getDueDate')->once()->andReturn('7');

        $this->paymentInterface->shouldReceive('getOrder')->andReturn($order);
        $this->transactionBuilderFactory->shouldReceive('get')->with('order')->andReturn($order);

        $infoInterface = \Mockery::mock(\Magento\Payment\Model\InfoInterface::class)->makePartial();

        /** @noinspection PhpUndefinedMethodInspection */
        $this->object->setData('info_instance', $infoInterface);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertEquals($order, $this->object->getOrderTransactionBuilder($this->paymentInterface));
    }

    /**
     * Test the getCaptureTransactionBuilder method.
     */
    public function testGetCaptureTransactionBuilder()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertFalse($this->object->getCaptureTransactionBuilder($this->paymentInterface));
    }

    /**
     * Test the getAuthorizeTransactionBuild method.
     */
    public function testGetAuthorizeTransactionBuilder()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertFalse($this->object->getAuthorizeTransactionBuilder($this->paymentInterface));
    }

    /**
     * Test the getRefundTransactionBuilder method.
     */
    public function testGetRefundTransactionBuilder()
    {
        $fixture = [
            'order' => 'orderrr!',
        ];

        $this->paymentInterface->shouldReceive('getOrder')->andReturn($fixture['order']);
        $this->paymentInterface->shouldReceive('getAdditionalInformation')->with(
            'buckaroo_transaction_key'
        )->andReturn('getAdditionalInformation');

        $this->transactionBuilderFactory->shouldReceive('get')->with('refund')->andReturnSelf();
        $this->transactionBuilderFactory->shouldReceive('setOrder')->with($fixture['order'])->andReturnSelf();
        $this->transactionBuilderFactory->shouldReceive('setServices')->andReturnUsing(
            function ($services) {
                $services['Name']   = 'sofortbanking';
                $services['Action'] = 'Refund';

                return $this->transactionBuilderFactory;
            }
        );
        $this->transactionBuilderFactory->shouldReceive('setMethod')->with('TransactionRequest')->andReturnSelf();
        $this->transactionBuilderFactory->shouldReceive('setOriginalTransactionKey')
                                        ->with('getAdditionalInformation')
                                        ->andReturnSelf();
        $this->transactionBuilderFactory->shouldReceive('setChannel')->with('CallCenter')->andReturnSelf();

        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertEquals(
            $this->transactionBuilderFactory,
            $this->object->getRefundTransactionBuilder($this->paymentInterface)
        );
    }

    /**
     * Test the getVoidTransactionBuild method.
     */
    public function testGetVoidTransactionBuilder()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertTrue($this->object->getVoidTransactionBuilder(''));
    }
}
