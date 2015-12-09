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

use \Mockery as m;
use TIG\Buckaroo\Test\BaseTest;

class IdealTest extends BaseTest
{
    /**
     * @var \TIG\Buckaroo\Model\Method\Ideal
     */
    protected $object;

    public function testCapture()
    {
        $transactionBuilderMock = m::mock('TIG\Buckaroo\Gateway\Http\TransactionBuilderFactory');

        $this->object = $this->objectManagerHelper->getObject(
            'TIG\Buckaroo\Model\Method\Ideal',
            [
                'transactionBuilderFactory' => $transactionBuilderMock,
            ]
        );

        $paymentInfoMock = m::mock(
            '\Magento\Payment\Model\InfoInterface',
            '\Magento\Sales\Api\Data\OrderPaymentInterface'
        );

        $this->assertInstanceOf('\TIG\Buckaroo\Model\Method\Ideal', $this->object->capture($paymentInfoMock, 1));
    }

    public function testCaptureInvalidArgument()
    {
        $this->object = $this->objectManagerHelper->getObject('TIG\Buckaroo\Model\Method\SepaDirectDebit');

        try {
            $this->object->capture(m::mock('\Magento\Payment\Model\InfoInterface'), 40);
            $this->fail();
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals(
                'Buckaroo requires the payment to be an instance of "\Magento\Sales\Api\Data\OrderPaymentInterface"' .
                ' and "\Magento\Payment\Model\InfoInterface".', $e->getMessage());
        }
    }

    public function testAuthorize()
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $transactionMock = m::mock('TIG\Buckaroo\Gateway\Http\Transaction');

        $transactionBuilderMock = m::mock('\TIG\Buckaroo\Gateway\Http\TransactionBuilderFactory');
        $transactionBuilderMock->shouldReceive('get')->andReturnSelf();
        $transactionBuilderMock->shouldReceive('setOrder')->andReturnSelf();
        $transactionBuilderMock->shouldReceive('setServices')->andReturnSelf();
        $transactionBuilderMock->shouldReceive('setMethod')->andReturnSelf();
        $transactionBuilderMock->shouldReceive('build')->andReturn($transactionMock);

        $validatorFactoryMock = m::mock('TIG\Buckaroo\Model\ValidatorFactory');
        $validatorFactoryMock->shouldReceive('get')->andReturnSelf();
        $validatorFactoryMock->shouldReceive('validate')->andReturnSelf();

        $gatewayMock = m::mock('TIG\Buckaroo\Gateway\Http\Bpe3');
        $gatewayMock->shouldReceive('authorize')->once()->with($transactionMock)->andReturn([]);

        $this->object = $this->objectManagerHelper->getObject(
            'TIG\Buckaroo\Model\Method\Ideal',
            [
                'transactionBuilderFactory' => $transactionBuilderMock,
                'validatorFactory' => $validatorFactoryMock,
                'gateway' => $gatewayMock,
            ]
        );

        $paymentInfoMock = m::mock(
            '\Magento\Payment\Model\InfoInterface',
            '\Magento\Sales\Api\Data\OrderPaymentInterface'
        );
        $paymentInfoMock->shouldReceive('getOrder')->andReturnSelf();
        $paymentInfoMock->shouldReceive('getAdditionalInformation')->andReturnSelf();

        $this->assertInstanceOf('\TIG\Buckaroo\Model\Method\Ideal', $this->object->authorize($paymentInfoMock, 1));
    }

    public function testRefund()
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $transactionMock = m::mock('TIG\Buckaroo\Gateway\Http\Transaction');

        $services = [
            'Name'    => 'ideal',
            'Action'  => 'Refund',
            'Version' => 1,
        ];

        $paymentInfoMock = m::mock(
            '\Magento\Payment\Model\InfoInterface',
            '\Magento\Sales\Api\Data\OrderPaymentInterface'
        );
        $paymentInfoMock->shouldReceive('getOrder')->once()->andReturnSelf();
        $paymentInfoMock->shouldReceive('getAdditionalInformation')->once()->with('buckaroo_transaction_key')->andReturnSelf();

        $transactionBuilderMock = m::mock('\TIG\Buckaroo\Gateway\Http\TransactionBuilderFactory');
        $transactionBuilderMock->shouldReceive('get')->once()->andReturnSelf();
        $transactionBuilderMock->shouldReceive('setOrder')->once()->with($paymentInfoMock)->andReturnSelf();
        $transactionBuilderMock->shouldReceive('setServices')->once()->with($services)->andReturnSelf();
        $transactionBuilderMock->shouldReceive('setMethod')->once()->with('TransactionRequest')->andReturnSelf();
        $transactionBuilderMock->shouldReceive('setOriginalTransactionKey')->once()->with($paymentInfoMock)->andReturnSelf();
        $transactionBuilderMock->shouldReceive('build')->once()->andReturn($transactionMock);

        $gatewayMock = m::mock('TIG\Buckaroo\Gateway\Http\Bpe3');
        $gatewayMock->shouldReceive('refund')->once()->with($transactionMock)->andReturnSelf();

        $validatorFactoryMock = m::mock('TIG\Buckaroo\Model\ValidatorFactory');
        $validatorFactoryMock->shouldReceive('get')->with('transaction_response')->once()->andReturnSelf();
        $validatorFactoryMock->shouldReceive('validate')->with($gatewayMock)->once()->andReturn(true);

        $this->object = $this->objectManagerHelper->getObject(
            'TIG\Buckaroo\Model\Method\Ideal',
            [
                'transactionBuilderFactory' => $transactionBuilderMock,
                'validatorFactory' => $validatorFactoryMock,
                'gateway' => $gatewayMock,
            ]
        );

        $this->assertInstanceOf('\TIG\Buckaroo\Model\Method\Ideal', $this->object->refund($paymentInfoMock, 1));
    }
}