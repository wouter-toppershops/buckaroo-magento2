<?php
/**
 *
 *          ..::..
 *     ..::::::::::::..
 *   ::'''''':''::'''''::
 *   ::..  ..:  :  ....::
 *   ::::  :::  :  :   ::
 *   ::::  :::  :  ''' ::
 *   ::::..:::..::.....::
 *     ''::::::::::::''
 *          ''::''
 *
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
 * @copyright   Copyright (c) Total Internet Group B.V. https://tig.nl/copyright
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
namespace TIG\Buckaroo\Test\Unit\Model\Method;

use Magento\Framework\App\Config;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\DataObject;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Payment;
use TIG\Buckaroo\Gateway\Http\TransactionBuilder\Order as TransactionBuilderOrder;
use TIG\Buckaroo\Gateway\Http\TransactionBuilderFactory;
use TIG\Buckaroo\Model\ConfigProvider\Method\Factory;
use TIG\Buckaroo\Model\ConfigProvider\Method\PaymentGuarantee as ConfigProviderPaymentGuarantee;
use TIG\Buckaroo\Model\Method\PaymentGuarantee;
use TIG\Buckaroo\Test\BaseTest;

class PaymentGuaranteeTest extends BaseTest
{
    protected $instanceClass = PaymentGuarantee::class;

    /**
     * @return array
     */
    public function assignDataProvider()
    {
        return [
            'no data' => [
                '2.0.5',
                []
            ],
            'version 2.0.0' => [
                '2.0.0',
                [
                    'termsCondition' => '1',
                    'customer_gender' => 'male',
                    'customer_billingName' => 'TIG',
                    'customer_DoB' => '1990-01-01',
                    'customer_iban' => 'NL12345'
                ]
            ],
            'version 2.1.0' => [
                '2.1.0',
                [
                    'additional_data' => [
                        'termsCondition' => '0',
                        'customer_gender' => 'female',
                        'customer_billingName' => 'TIG',
                        'customer_DoB' => '1990-10-07',
                        'customer_iban' => 'BE67890'
                    ]
                ]
            ],
        ];
    }

    /**
     * @param $version
     * @param $data
     *
     * @dataProvider assignDataProvider
     */
    public function testAssignData($version, $data)
    {
        $productMetadataMock = $this->getFakeMock(ProductMetadata::class)->setMethods(['getVersion'])->getMock();
        $productMetadataMock->expects($this->once())->method('getVersion')->willReturn($version);

        $dataObject = $this->getObject(DataObject::class);
        $dataObject->addData($data);

        $instance = $this->getInstance(['productMetadata' => $productMetadataMock]);

        $infoInstanceMock = $this->getFakeMock(InfoInterface::class)->getMock();
        $instance->setData('info_instance', $infoInstanceMock);

        $result = $instance->assignData($dataObject);
        $this->assertInstanceOf(PaymentGuarantee::class, $result);
    }

    /**
     * @return array
     */
    public function canCaptureProvider()
    {
        return [
            'can capture' => [
                'capture',
                true
            ],
            'can not capture' => [
                'order',
                false
            ]
        ];
    }

    /**
     * @param $paymentAction
     * @param $expected
     *
     * @dataProvider canCaptureProvider
     */
    public function testCanCapture($paymentAction, $expected)
    {
        $scopeConfigMock = $this->getFakeMock(Config::class)->setMethods(['getValue'])->getMock();
        $scopeConfigMock->expects($this->once())->method('getValue')->willReturn($paymentAction);

        $instance = $this->getInstance(['scopeConfig' => $scopeConfigMock]);
        $result = $instance->canCapture();

        $this->assertEquals($expected, $result);
    }

    public function testGetOrderTransactionBuilder()
    {
        $orderMock = $this->getOrderMock();

        $infoInstanceMock = $this->getFakeMock(Payment::class)->setMethods(['getOrder'])->getMock();
        $infoInstanceMock->expects($this->atLeastOnce())->method('getOrder')->willReturn($orderMock);

        $instance = $this->getTransactionInstance();
        $result = $instance->getOrderTransactionBuilder($infoInstanceMock);

        $this->assertInstanceOf(TransactionBuilderOrder::class, $result);
        $this->assertInstanceOf(Order::class, $result->getOrder());
        $this->assertEquals('TransactionRequest', $result->getMethod());

        $services = $result->getServices();
        $this->assertInternalType('array', $services);
        $this->assertEquals('paymentguarantee', $services['Name']);
        $this->assertEquals('PaymentInvitation', $services['Action']);
    }

    public function testGetCaptureTransactionBuilder()
    {
        $orderMock = $this->getOrderMock();

        $infoInstanceMock = $this->getFakeMock(Payment::class)->setMethods(['getOrder'])->getMock();
        $infoInstanceMock->expects($this->atLeastOnce())->method('getOrder')->willReturn($orderMock);

        $instance = $this->getTransactionInstance();
        $result = $instance->getCaptureTransactionBuilder($infoInstanceMock);

        $this->assertInstanceOf(TransactionBuilderOrder::class, $result);
        $this->assertInstanceOf(Order::class, $result->getOrder());
        $this->assertEquals('TransactionRequest', $result->getMethod());

        $services = $result->getServices();
        $this->assertInternalType('array', $services);
        $this->assertEquals('paymentguarantee', $services['Name']);
        $this->assertEquals('PartialInvoice', $services['Action']);
        $this->assertArrayHasKey('RequestParameter', $services);
    }

    public function testGetAuthorizeTransactionBuilder()
    {
        $orderMock = $this->getOrderMock();

        $infoInstanceMock = $this->getFakeMock(Payment::class)->setMethods(['getOrder'])->getMock();
        $infoInstanceMock->expects($this->atLeastOnce())->method('getOrder')->willReturn($orderMock);

        $instance = $this->getTransactionInstance();
        $result = $instance->getAuthorizeTransactionBuilder($infoInstanceMock);

        $this->assertInstanceOf(TransactionBuilderOrder::class, $result);
        $this->assertInstanceOf(Order::class, $result->getOrder());
        $this->assertEquals('TransactionRequest', $result->getMethod());

        $services = $result->getServices();
        $this->assertInternalType('array', $services);
        $this->assertEquals('paymentguarantee', $services['Name']);
        $this->assertEquals('Order', $services['Action']);
        $this->assertArrayHasKey('RequestParameter', $services);
    }

    public function testGetVoidTransactionBuilder()
    {
        $infoInstanceMock = $this->getFakeMock(Payment::class)->getMock();
        $instance = $this->getInstance();

        $result = $instance->getVoidTransactionBuilder($infoInstanceMock);
        $this->assertFalse($result);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getOrderMock()
    {
        $orderAddressMock = $this->getFakeMock(Address::class)
            ->setMethods(['getFirstName', 'getStreet', 'getData'])
            ->getMock();
        $orderAddressMock->expects($this->any())->method('getData')->willReturn([]);

        $orderMock = $this->getFakeMock(Order::class)->getMock();
        $orderMock->expects($this->once())->method('getBillingAddress')->willReturn($orderAddressMock);
        $orderMock->expects($this->once())->method('getShippingAddress')->willReturn($orderAddressMock);
        $orderMock->expects($this->atLeastOnce())->method('hasInvoices')->willReturn(false);

        return $orderMock;
    }

    /**
     * @return object
     */
    private function getTransactionInstance()
    {
        $transactionOrderMock = $this->getFakeMock(TransactionBuilderOrder::class)->setMethods(null)->getMock();

        $transactionBuilderMock = $this->getFakeMock(TransactionBuilderFactory::class)->setMethods(['get'])->getMock();
        $transactionBuilderMock->expects($this->once())->method('get')->willReturn($transactionOrderMock);

        $configGuaranteeMock = $this->getFakeMock(ConfigProviderPaymentGuarantee::class)->getMock();

        $configProviderMock = $this->getFakeMock(Factory::class)->setMethods(['get'])->getMock();
        $configProviderMock->expects($this->once())->method('get')->willReturn($configGuaranteeMock);

        $transactionInstance = $this->getInstance([
            'transactionBuilderFactory' => $transactionBuilderMock,
            'configProviderMethodFactory' => $configProviderMock
        ]);

        return $transactionInstance;
    }
}
