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

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Item;
use Magento\Sales\Model\Order\Payment;
use TIG\Buckaroo\Model\Method\Afterpay2;
use TIG\Buckaroo\Test\BaseTest;

class Afterpay2Test extends BaseTest
{
    public $instanceClass = Afterpay2::class;

    public function testGetCreditmemoArticleData()
    {
        $itemMock = $this->getFakeMock(Item::class)->getMock();
        $itemMock->expects($this->atLeastOnce())->method('getRowTotal')->willReturn(10);

        $creditmemoMock = $this->getFakeMock(Creditmemo::class)->getMock();
        $creditmemoMock->expects($this->once())->method('getAllItems')->willReturn([$itemMock]);

        $orderMock = $this->getFakeMock(Order::class)->getMock();
        $orderMock->expects($this->any())->method('hasCreditmemos')->willReturn(0);

        $paymentMock = $this->getFakeMock(Payment::class)->getMock();
        $paymentMock->expects($this->any())->method('getOrder')->willReturn($creditmemoMock);
        $paymentMock->expects($this->once())->method('getCreditmemo')->willReturn($creditmemoMock);

        $instance = $this->getInstance();
        $result = $instance->getCreditmemoArticleData($paymentMock);

        $this->assertInternalType('array', $result);
        $this->assertCount(5, $result);
        $this->assertArrayHasKey('_', $result[0]);
        $this->assertArrayHasKey('Name', $result[0]);
        $this->assertArrayHasKey('GroupID', $result[0]);
    }

    /**
     * @return array
     */
    public function getFailureMessageFromMethodProvider()
    {
        return [
            'incorrect transaction type' => [
                (Object)[
                    'TransactionType' => 'C013'
                ],
                ''
            ],
            'correct transaction type with colon' => [
                (Object)[
                    'TransactionType' => 'C011',
                    'Status' => (Object)[
                        'SubCode' => (Object)[
                            '_' => 'An error occured: Het telefoonnummer is onjuist'
                        ]
                    ]
                ],
                'Het telefoonnummer is onjuist'
            ],
            'correct transaction type without colon' => [
                (Object)[
                    'TransactionType' => 'C016',
                    'Status' => (Object)[
                        'SubCode' => (Object)[
                            '_' => 'De geboortedatum is onjuist'
                        ]
                    ]
                ],
                'De geboortedatum is onjuist'
            ]
        ];
    }

    /**
     * @param $transactionResponse
     * @param $expected
     *
     * @dataProvider getFailureMessageFromMethodProvider
     */
    public function testGetFailureMessageFromMethod($transactionResponse, $expected)
    {
        $instance = $this->getInstance();
        $result = $this->invokeArgs('getFailureMessageFromMethod', [$transactionResponse], $instance);

        $this->assertEquals($expected, $result);
    }
}
