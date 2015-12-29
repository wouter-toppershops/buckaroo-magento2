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
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use TIG\Buckaroo\Test\BaseTest;

class SepaDirectDebitTest extends BaseTest
{
    /**
     * @var \TIG\Buckaroo\Model\Method\Ideal
     */
    protected $object;

    /**
     * Create a mock of the info instance with some defaults.
     *
     * @param string $country
     * @param string $class
     *
     * @return m\MockInterface
     */
    public function getInfoInstanceMock($country = 'NL', $class = 'Magento\Payment\Model\Info')
    {
        $infoInstanceMock = m::mock($class);
        $infoInstanceMock
            ->shouldReceive($class == 'Magento\Payment\Model\Info' ? 'getQuote' : 'getOrder')
            ->andReturnSelf();
        $infoInstanceMock->shouldReceive('getBillingAddress')->andReturnSelf();
        $infoInstanceMock->shouldReceive('getCountryId')->andReturn($country);

        return $infoInstanceMock;
    }

    /**
     * Test the happy path.
     *
     * @throws \Magento\Framework\Validator\Exception
     */
    public function testValidateHappyPath()
    {
        $iban = 'NL91ABNA0417164300';

        $infoInstanceMock = $this->getInfoInstanceMock();
        $infoInstanceMock->shouldReceive('getAdditionalInformation')->with('customer_bic')->once()->andReturnNull();
        $infoInstanceMock->shouldReceive('getAdditionalInformation')->with('customer_iban')->once()->andReturn($iban);
        $infoInstanceMock->shouldReceive('getAdditionalInformation')->with('customer_account_name')->once()->andReturn('first name');
        $infoInstanceMock->shouldReceive('getAdditionalInformation')->with('buckaroo_skip_validation')->once()->andReturn(false);

        $ibanValidator = m::mock(\Zend\Validator\Iban::class);
        $ibanValidator->shouldReceive('isValid')->once()->with($iban)->andReturn(true);

        $objectManager = m::mock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManager->shouldReceive('create')->once()->with(\Zend\Validator\Iban::class)->andReturn($ibanValidator);

        $this->object = $this->objectManagerHelper->getObject('TIG\Buckaroo\Model\Method\SepaDirectDebit', [
            'objectManager' => $objectManager,
        ]);

        $this->object->setData('info_instance', $infoInstanceMock);

        $this->assertEquals($this->object, $this->object->validate());
    }

    /**
     * Test the validation with an invalid account name.
     */
    public function testValidateInvalidAccountName()
    {
        $infoInstanceMock = $this->getInfoInstanceMock();
        $infoInstanceMock->shouldReceive('getAdditionalInformation')->with('customer_bic')->once()->andReturnNull();
        $infoInstanceMock->shouldReceive('getAdditionalInformation')->with('customer_iban')->once()->andReturn();
        $infoInstanceMock->shouldReceive('getAdditionalInformation')->with('customer_account_name')->once()->andReturn('first');
        $infoInstanceMock->shouldReceive('getAdditionalInformation')->with('buckaroo_skip_validation')->once()->andReturn(false);

        $this->object = $this->objectManagerHelper->getObject('TIG\Buckaroo\Model\Method\SepaDirectDebit');
        $this->object->setData('info_instance', $infoInstanceMock);

        try {
            $this->object->validate();
            $this->fail();
        } catch(\Exception $e) {
            $this->assertInstanceOf(\Exception::class, $e);
        }
    }

    /**
     * Test the path with an invalid IBAN.
     */
    public function testValidateInvalidIban()
    {
        $iban = 'wrong';

        $infoInstanceMock = $this->getInfoInstanceMock('BE');
        $infoInstanceMock->shouldReceive('getAdditionalInformation')->with('customer_bic')->once()->andReturnNull();
        $infoInstanceMock->shouldReceive('getAdditionalInformation')->with('customer_iban')->once()->andReturn($iban);
        $infoInstanceMock->shouldReceive('getAdditionalInformation')->with('customer_account_name')->once()->andReturn('first name');
        $infoInstanceMock->shouldReceive('getAdditionalInformation')->with('buckaroo_skip_validation')->once()->andReturn(false);

        $this->object = $this->objectManagerHelper->getObject('TIG\Buckaroo\Model\Method\SepaDirectDebit');
        $this->object->setData('info_instance', $infoInstanceMock);

        try {
            $this->object->validate();
            $this->fail();
        } catch(\Exception $e) {
            $this->assertInstanceOf(\Exception::class, $e);
        }
    }

    /**
     * Test the path with a non-NL account and an non-valid BIC number.
     */
    public function testValidateInvalidBic()
    {
        $iban = 'wrong';

        $infoInstanceMock = $this->getInfoInstanceMock();
        $infoInstanceMock->shouldReceive('getAdditionalInformation')->with('customer_bic')->once()->andReturnNull();
        $infoInstanceMock->shouldReceive('getAdditionalInformation')->with('customer_iban')->once()->andReturn($iban);
        $infoInstanceMock->shouldReceive('getAdditionalInformation')->with('customer_account_name')->once()->andReturn('first name');
        $infoInstanceMock->shouldReceive('getAdditionalInformation')->with('buckaroo_skip_validation')->once()->andReturn(false);

        $ibanValidator = m::mock(\Zend\Validator\Iban::class);
        $ibanValidator->shouldReceive('isValid')->once()->with($iban)->andReturn(false);

        $objectManager = m::mock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManager->shouldReceive('create')->once()->with(\Zend\Validator\Iban::class)->andReturn($ibanValidator);

        $this->object = $this->objectManagerHelper->getObject('TIG\Buckaroo\Model\Method\SepaDirectDebit', [
            'objectManager' => $objectManager,
        ]);

        $this->object->setData('info_instance', $infoInstanceMock);

        try {
            $this->object->validate();
            $this->fail();
        } catch(\Exception $e) {
            $this->assertInstanceOf(\Exception::class, $e);
        }
    }

    /**
     * Test the path with a Payment instance instead of the quote instance.
     *
     * @throws \Magento\Framework\Validator\Exception
     */
    public function testValidatePaymentInstance()
    {
        $iban = 'NL91ABNA0417164300';

        $infoInstanceMock = $this->getInfoInstanceMock('NL', 'Magento\Sales\Model\Order\Payment');
        $infoInstanceMock->shouldReceive('getAdditionalInformation')->with('customer_bic')->once()->andReturnNull();
        $infoInstanceMock->shouldReceive('getAdditionalInformation')->with('customer_iban')->once()->andReturn($iban);
        $infoInstanceMock->shouldReceive('getAdditionalInformation')->with('customer_account_name')->once()->andReturn('first name');
        $infoInstanceMock->shouldReceive('getAdditionalInformation')->with('buckaroo_skip_validation')->once()->andReturn(false);

        $ibanValidator = m::mock(\Zend\Validator\Iban::class);
        $ibanValidator->shouldReceive('isValid')->once()->with($iban)->andReturn(true);

        $objectManager = m::mock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManager->shouldReceive('create')->once()->with(\Zend\Validator\Iban::class)->andReturn($ibanValidator);

        $this->object = $this->objectManagerHelper->getObject('TIG\Buckaroo\Model\Method\SepaDirectDebit', [
            'objectManager' => $objectManager,
        ]);

        $this->object->setData('info_instance', $infoInstanceMock);

        $this->assertEquals($this->object, $this->object->validate());
    }

    public function testCapture()
    {
        $transactionMock = m::mock('TIG\Buckaroo\Gateway\Http\Transaction');

        $paymentInfoMock = m::mock(
            '\Magento\Payment\Model\InfoInterface',
            '\Magento\Sales\Api\Data\OrderPaymentInterface'
        );

        $paymentInfoMock->shouldReceive('getAdditionalInformation')->times(5)->andReturnSelf();
        $paymentInfoMock->shouldReceive('getOrder')->once()->andReturnSelf();

        $transactionBuilderMock = m::mock('TIG\Buckaroo\Gateway\Http\TransactionBuilderFactory');
        $transactionBuilderMock->shouldReceive('get')->once()->andReturnSelf();
        $transactionBuilderMock->shouldReceive('setOrder')->once()->andReturnSelf();
        $transactionBuilderMock->shouldReceive('setServices')->once()->andReturnSelf();
        $transactionBuilderMock->shouldReceive('setMethod')->once()->andReturnSelf();
        $transactionBuilderMock->shouldReceive('build')->once()->andReturn($transactionMock);

        $validatorFactoryMock = m::mock('TIG\Buckaroo\Model\ValidatorFactory');
        $validatorFactoryMock->shouldReceive('get')->andReturnSelf();
        $validatorFactoryMock->shouldReceive('validate')->andReturnSelf();

        $gatewayMock = m::mock('TIG\Buckaroo\Gateway\Http\Bpe3');
        $gatewayMock->shouldReceive('capture')->once()->with($transactionMock)->andReturnSelf();

        $this->object = $this->objectManagerHelper->getObject(
            'TIG\Buckaroo\Model\Method\SepaDirectDebit',
            [
                'transactionBuilderFactory' => $transactionBuilderMock,
                'validatorFactory' => $validatorFactoryMock,
                'gateway' => $gatewayMock,
            ]
        );

        $this->object->setData('info_instance', $paymentInfoMock);

        $this->markTestIncomplete('Unable to get by the parent::capture($payment, $amount); method.');

        $this->assertInstanceOf('\TIG\Buckaroo\Model\Method\SepaDirectDebit', $this->object->capture($paymentInfoMock, 1));
    }

    public function testAuthorize()
    {
        $transactionMock = m::mock('TIG\Buckaroo\Gateway\Http\Transaction');

        $transactionBuilderMock = m::mock('TIG\Buckaroo\Gateway\Http\TransactionBuilder');
        $transactionBuilderMock->shouldReceive('setOrder')->andReturnSelf();
        $transactionBuilderMock->shouldReceive('setServices')->andReturnSelf();
        $transactionBuilderMock->shouldReceive('setMethod')->andReturnSelf();
        $transactionBuilderMock->shouldReceive('build')->andReturn($transactionMock);

        $gatewayMock = m::mock('TIG\Buckaroo\Gateway\Http\Bpe3');
        $gatewayMock->shouldReceive('authorize')->once()->with($transactionMock)->andReturnSelf();

        /** @var \TIG\Buckaroo\Model\Method\SepaDirectDebit object */
        $this->object = $this->objectManagerHelper->getObject(
            'TIG\Buckaroo\Model\Method\SepaDirectDebit',
            [
                'transactionBuilder' => $transactionBuilderMock,
                'gateway' => $gatewayMock,
            ]
        );

        $this->objectManagerHelper = new ObjectManager($this);
        $paymentInfoMock = m::mock(
            '\Magento\Payment\Model\InfoInterface',
            '\Magento\Sales\Api\Data\OrderPaymentInterface'
        );

        $this->object->setData('info_instance', $paymentInfoMock);

        $this->markTestIncomplete(
            'Unable to get pass the parent::authorize() method due to canAuthorize always returns false'
        );

        $this->assertInstanceOf('\TIG\Buckaroo\Model\Method\SepaDirectDebit', $this->object->authorize($paymentInfoMock, 1));
    }

    public function testRefund()
    {
        $this->markTestIncomplete('Development still in progress');

        $transactionMock = m::mock('TIG\Buckaroo\Gateway\Http\Transaction');

        $paymentInfoMock = m::mock(
            '\Magento\Payment\Model\InfoInterface',
            '\Magento\Sales\Api\Data\OrderPaymentInterface'
        );

        $paymentInfoMock->shouldReceive('getOrder')->with()->once()->andReturnSelf();

        $transactionBuilderMock = m::mock('TIG\Buckaroo\Gateway\Http\TransactionBuilderFactory');
        $transactionBuilderMock->shouldReceive('get')->with('refund')->once()->andReturnSelf();
        $transactionBuilderMock->shouldReceive('setOrder')->andReturnSelf();
        $transactionBuilderMock->shouldReceive('setServices')->andReturnSelf();
        $transactionBuilderMock->shouldReceive('setMethod')->andReturnSelf();
        $transactionBuilderMock->shouldReceive('build')->andReturn($transactionMock);

        $gatewayMock = m::mock('TIG\Buckaroo\Gateway\Http\Bpe3');
        $gatewayMock->shouldReceive('refund')->once()->with($transactionMock)->andReturnSelf();

        $validatorFactoryMock = m::mock('TIG\Buckaroo\Model\ValidatorFactory');
        $validatorFactoryMock->shouldReceive('get')->andReturnSelf();
        $validatorFactoryMock->shouldReceive('validate')->andReturnSelf();

        $this->object = $this->objectManagerHelper->getObject(
            'TIG\Buckaroo\Model\Method\SepaDirectDebit',
            [
                'transactionBuilderFactory' => $transactionBuilderMock,
                'validatorFactory' => $validatorFactoryMock,
                'gateway' => $gatewayMock,
            ]
        );

        $this->assertInstanceOf('\TIG\Buckaroo\Model\Method\SepaDirectDebit', $this->object->refund($paymentInfoMock, 1));
    }
}