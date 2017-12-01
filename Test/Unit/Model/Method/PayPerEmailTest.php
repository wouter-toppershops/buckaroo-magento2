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

use Magento\Framework\DataObject;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order\Payment;
use TIG\Buckaroo\Gateway\Http\TransactionBuilder\Order;
use TIG\Buckaroo\Gateway\Http\TransactionBuilderFactory;
use TIG\Buckaroo\Model\ConfigProvider\Method\Factory;
use TIG\Buckaroo\Model\ConfigProvider\Method\PayPerEmail as PayPerEmailConfig;
use TIG\Buckaroo\Model\Method\PayPerEmail;
use TIG\Buckaroo\Test\BaseTest;

class PayPerEmailTest extends BaseTest
{
    protected $instanceClass = PayPerEmail::class;

    /**
     * @return array
     */
    public function assignDataProvider()
    {
        return [
            'no data' => [
                []
            ],
            'with skip validation data' => [
                [
                    'additional_data' => [
                        'buckaroo_skip_validation' => '1',
                    ]
                ]
            ],
            'with form data' => [
                [
                    'additional_data' => [
                        'customer_gender' => 'female',
                        'customer_billingFirstName' => 'TIG',
                        'customer_billingLastName' => 'TEST',
                        'customer_email' => '07/10/1990',
                    ]
                ]
            ],
        ];
    }

    /**
     * @param $data
     *
     * @dataProvider assignDataProvider
     */
    public function testAssignData($data)
    {
        $dataObject = $this->getObject(DataObject::class);
        $dataObject->addData($data);

        $instance = $this->getInstance();

        $infoInstanceMock = $this->getFakeMock(InfoInterface::class)->getMock();
        $instance->setData('info_instance', $infoInstanceMock);

        $result = $instance->assignData($dataObject);
        $this->assertInstanceOf(PayPerEmail::class, $result);
    }

    public function testGetOrderTransactionBuilder()
    {
        $payPerMailConfigMock = $this->getFakeMock(PayPerEmailConfig::class)->getMock();

        $factoryMock = $this->getFakeMock(Factory::class)->setMethods(['get'])->getMock();
        $factoryMock->expects($this->once())->method('get')->with('payperemail')->willReturn($payPerMailConfigMock);

        $infoInstanceMock = $this->getFakeMock(Payment::class)->setMethods(['getOrder'])->getMock();
        $infoInstanceMock->expects($this->once())->method('getOrder');

        $orderTransactionMock = $this->getFakeMock(Order::class)->setMethods(['setMethod'])->getMock();
        $orderTransactionMock->expects($this->once())->method('setMethod')->with('TransactionRequest');

        $transactionBuilderMock = $this->getFakeMock(TransactionBuilderFactory::class)->setMethods(['get'])->getMock();
        $transactionBuilderMock->expects($this->once())
            ->method('get')
            ->with('order')
            ->willReturn($orderTransactionMock);

        $instance = $this->getInstance([
            'configProviderMethodFactory' => $factoryMock,
            'transactionBuilderFactory' => $transactionBuilderMock
        ]);

        $result = $instance->getOrderTransactionBuilder($infoInstanceMock);
        $this->assertInstanceOf(Order::class, $result);

        $services = $result->getServices();
        $this->assertInternalType('array', $services);
        $this->assertEquals('payperemail', $services['Name']);
        $this->assertEquals('PaymentInvitation', $services['Action']);
        $this->assertEquals(1, $services['Version']);
        $this->assertCount(6, $services['RequestParameter']);

        $possibleParameters = ['customergender', 'CustomerEmail', 'CustomerFirstName', 'CustomerLastName', 'MerchantSendsEmail', 'PaymentMethodsAllowed'];

        foreach ($services['RequestParameter'] as $array) {
            $this->assertArrayHasKey('_', $array);
            $this->assertArrayHasKey('Name', $array);
            $this->assertContains($array['Name'], $possibleParameters);
        }
    }

    public function testGetCaptureTransactionBuilder()
    {
        $infoInstanceMock = $this->getFakeMock(InfoInterface::class)->getMock();
        $instance = $this->getInstance();

        $result = $instance->getCaptureTransactionBuilder($infoInstanceMock);
        $this->assertFalse($result);
    }

    public function testGetAuthorizeTransactionBuilder()
    {
        $infoInstanceMock = $this->getFakeMock(InfoInterface::class)->getMock();
        $instance = $this->getInstance();

        $result = $instance->getAuthorizeTransactionBuilder($infoInstanceMock);
        $this->assertFalse($result);
    }

    public function testGetRefundTransactionBuilder()
    {
        $infoInstanceMock = $this->getFakeMock(InfoInterface::class)->getMock();
        $instance = $this->getInstance();

        $result = $instance->getRefundTransactionBuilder($infoInstanceMock);
        $this->assertFalse($result);
    }

    public function testGetVoidTransactionBuilder()
    {
        $infoInstanceMock = $this->getFakeMock(InfoInterface::class)->getMock();
        $instance = $this->getInstance();

        $result = $instance->getVoidTransactionBuilder($infoInstanceMock);
        $this->assertTrue($result);
    }
}
