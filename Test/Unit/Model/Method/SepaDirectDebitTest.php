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

class SepaDirectDebitTest extends \TIG\Buckaroo\Test\BaseTest
{
    /**
     * @var \TIG\Buckaroo\Model\Method\SepaDirectDebit
     */
    protected $object;

    /**
     * @var \Mockery\MockInterface
     */
    protected $objectManager;

    /**
     * @var \TIG\Buckaroo\Gateway\Http\TransactionBuilderFactory|\Mockery\MockInterface
     */
    protected $transactionBuilderFactory;

    /**
     * Setup the base mocks.
     */
    public function setUp()
    {
        parent::setUp();

        $this->objectManager             = \Mockery::mock(\Magento\Framework\ObjectManagerInterface::class);
        $this->transactionBuilderFactory = \Mockery::mock(\TIG\Buckaroo\Gateway\Http\TransactionBuilderFactory::class)
                                                   ->makePartial();

        $this->object = $this->objectManagerHelper->getObject(
            \TIG\Buckaroo\Model\Method\SepaDirectDebit::class,
            [
                'objectManager'             => $this->objectManager,
                'transactionBuilderFactory' => $this->transactionBuilderFactory,
            ]
        );
    }

    /**
     * Create a mock of the info instance with some defaults.
     *
     * @param string $country
     * @param string $class
     *
     * @return \Mockery\MockInterface
     */
    public function getInfoInstanceMock($country = 'NL', $class = 'Magento\Payment\Model\Info')
    {
        $infoInstanceMock = \Mockery::mock($class);
        $infoInstanceMock
            ->shouldReceive($class == 'Magento\Payment\Model\Info' ? 'getQuote' : 'getOrder')
            ->andReturnSelf();
        $infoInstanceMock->shouldReceive('getBillingAddress')->andReturnSelf();
        $infoInstanceMock->shouldReceive('getCountryId')->andReturn($country);

        return $infoInstanceMock;
    }

    /**
     * Test the assignData method.
     */
    public function testAssignData()
    {
        $fixture = [
            'customer_bic'          => 'bicc',
            'customer_iban'         => 'ibann',
            'customer_account_name' => 'myname',
        ];

        $this->assignDataTest($fixture);
    }

    /**
     * Test the getOrderTransactionBuilder method.
     */
    public function testGetOrderTransactionBuilder()
    {
        $fixture = [
            'customer_account_name' => 'firstname',
            'customer_iban'         => 'ibanio',
            'customer_bic'          => 'biccc',
            'order'                 => 'orderrr!',
        ];

        $payment = \Mockery::mock(
            \Magento\Payment\Model\InfoInterface::class,
            \Magento\Sales\Api\Data\OrderPaymentInterface::class
        );

        $payment->shouldReceive('getOrder')->andReturn($fixture['order']);

        $order = \Mockery::mock(\TIG\Buckaroo\Gateway\Http\TransactionBuilder\Order::class); // ->makePartial();
        $order->shouldReceive('setOrder')->with($fixture['order'])->andReturnSelf();
        $order->shouldReceive('setMethod')->with('TransactionRequest')->andReturnSelf();

        $order->shouldReceive('setServices')->andReturnUsing(
            function ($services) use ($fixture, $order) {
                $this->assertEquals('sepadirectdebit', $services['Name']);
                $this->assertEquals($fixture['customer_bic'], $services[0]['RequestParameter'][0][0]['_']);
                $this->assertEquals($fixture['customer_iban'], $services['RequestParameter'][1]['_']);
                $this->assertEquals($fixture['customer_account_name'], $services['RequestParameter'][0]['_']);

                return $order;
            }
        );

        $this->transactionBuilderFactory->shouldReceive('get')->with('order')->andReturn($order);

        $infoInterface = \Mockery::mock(\Magento\Payment\Model\InfoInterface::class)->makePartial();
        $infoInterface->shouldReceive('getAdditionalInformation')
                      ->with('customer_account_name')
                      ->andReturn($fixture['customer_account_name']);
        $infoInterface->shouldReceive('getAdditionalInformation')
                      ->with('customer_iban')
                      ->andReturn($fixture['customer_iban']);
        $infoInterface->shouldReceive('getAdditionalInformation')
                      ->with('customer_bic')
                      ->andReturn($fixture['customer_bic']);

        $this->object->setData('info_instance', $infoInterface);
        $this->assertEquals($order, $this->object->getOrderTransactionBuilder($payment));
    }

    /**
     * Test the getCaptureTransactionBuilder method.
     */
    public function testGetCaptureTransactionBuilder()
    {
        $this->assertFalse($this->object->getCaptureTransactionBuilder(''));
    }

    /**
     * Test the getAuthorizeTransactionBuild method.
     */
    public function testGetAuthorizeTransactionBuilder()
    {
        $this->assertFalse($this->object->getAuthorizeTransactionBuilder(''));
    }

    /**
     * Test the getVoidTransactionBuild method.
     */
    public function testGetVoidTransactionBuilder()
    {
        $this->assertTrue($this->object->getVoidTransactionBuilder(''));
    }

    /**
     * Test the getRefundTransactionBuilder method.
     */
    public function testGetRefundTransactionBuilder()
    {
        $payment = \Mockery::mock(
            \Magento\Payment\Model\InfoInterface::class,
            \Magento\Sales\Api\Data\OrderPaymentInterface::class
        );

        $payment->shouldReceive('getOrder')->andReturn('orderr');
        $payment->shouldReceive('getAdditionalInformation')->with(
            \TIG\Buckaroo\Model\Method\SepaDirectDebit::BUCKAROO_ORIGINAL_TRANSACTION_KEY_KEY
        )->andReturn('getAdditionalInformation');

        $this->transactionBuilderFactory->shouldReceive('get')->with('refund')->andReturnSelf();
        $this->transactionBuilderFactory->shouldReceive('setOrder')->with('orderr')->andReturnSelf();
        $this->transactionBuilderFactory->shouldReceive('setServices')->with(
            [
                'Name'    => 'sepadirectdebit',
                'Action'  => 'Refund',
                'Version' => 1,
            ]
        )->andReturnSelf();
        $this->transactionBuilderFactory->shouldReceive('setMethod')->with('TransactionRequest')->andReturnSelf();
        $this->transactionBuilderFactory->shouldReceive('setOriginalTransactionKey')
                                        ->with('getAdditionalInformation')
                                        ->andReturnSelf();
        $this->transactionBuilderFactory->shouldReceive('setChannel')->with('CallCenter')->andReturnSelf();

        $this->assertEquals($this->transactionBuilderFactory, $this->object->getRefundTransactionBuilder($payment));
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
        $infoInstanceMock->shouldReceive('getAdditionalInformation')->with('customer_account_name')->once()->andReturn(
            'first name'
        );
        $infoInstanceMock->shouldReceive('getAdditionalInformation')->with('buckaroo_skip_validation')->once()
                         ->andReturn(false);

        $ibanValidator = \Mockery::mock(\Zend\Validator\Iban::class);
        $ibanValidator->shouldReceive('isValid')->once()->with($iban)->andReturn(true);

        $this->objectManager->shouldReceive('create')->once()->with(\Zend\Validator\Iban::class)->andReturn(
            $ibanValidator
        );

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
        $infoInstanceMock->shouldReceive('getAdditionalInformation')->with('customer_account_name')->once()->andReturn(
            'first'
        );
        $infoInstanceMock->shouldReceive('getAdditionalInformation')->with('buckaroo_skip_validation')->once()
                         ->andReturn(false);

        $this->object->setData('info_instance', $infoInstanceMock);

        try {
            $this->object->validate();
            $this->fail();
        } catch (\Exception $e) {
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
        $infoInstanceMock->shouldReceive('getAdditionalInformation')->with('customer_account_name')->once()->andReturn(
            'first name'
        );
        $infoInstanceMock->shouldReceive('getAdditionalInformation')->with('buckaroo_skip_validation')->once()
                         ->andReturn(false);

        $this->object->setData('info_instance', $infoInstanceMock);

        try {
            $this->object->validate();
            $this->fail();
        } catch (\Exception $e) {
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
        $infoInstanceMock->shouldReceive('getAdditionalInformation')->with('customer_account_name')->once()->andReturn(
            'first name'
        );
        $infoInstanceMock->shouldReceive('getAdditionalInformation')->with('buckaroo_skip_validation')->once()
                         ->andReturn(false);

        $ibanValidator = \Mockery::mock(\Zend\Validator\Iban::class);
        $ibanValidator->shouldReceive('isValid')->once()->with($iban)->andReturn(false);

        $this->objectManager->shouldReceive('create')->once()->with(\Zend\Validator\Iban::class)->andReturn(
            $ibanValidator
        );

        $this->object->setData('info_instance', $infoInstanceMock);

        try {
            $this->object->validate();
            $this->fail();
        } catch (\Exception $e) {
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
        $infoInstanceMock->shouldReceive('getAdditionalInformation')->with('customer_account_name')->once()->andReturn(
            'first name'
        );
        $infoInstanceMock->shouldReceive('getAdditionalInformation')->with('buckaroo_skip_validation')->once()
                         ->andReturn(false);

        $ibanValidator = \Mockery::mock(\Zend\Validator\Iban::class);
        $ibanValidator->shouldReceive('isValid')->once()->with($iban)->andReturn(true);

        $this->objectManager->shouldReceive('create')->once()->with(\Zend\Validator\Iban::class)->andReturn(
            $ibanValidator
        );
        $this->object->setData('info_instance', $infoInstanceMock);

        $this->assertEquals($this->object, $this->object->validate());
    }

    /**
     * Test what happens when the validation is skipped.
     */
    public function testValidateSkip()
    {
        $infoInstanceMock = $this->getInfoInstanceMock('NL', 'Magento\Sales\Model\Order\Payment');
        $infoInstanceMock->shouldReceive('getAdditionalInformation')->with('buckaroo_skip_validation')->once()
                         ->andReturn(true);

        $this->object->setData('info_instance', $infoInstanceMock);

        $this->assertEquals($this->object, $this->object->validate());
    }
}
