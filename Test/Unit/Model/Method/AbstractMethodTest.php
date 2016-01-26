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
namespace TIG\Buckaroo\Test\Unit\Model\Method;

/**
 * Class AbstractMethod. Temporary for testing only.
 *
 * @package TIG\Buckaroo\Test\Unit\Model\Method
 */
class AbstractMethod extends \TIG\Buckaroo\Model\Method\AbstractMethod
{
    // @codingStandardsIgnoreStart
    protected $_code = 'tig_buckaroo_test';
    // @codingStandardsIgnoreEnd

    public function getOrderTransactionBuilder($payment)
    {

    }

    public function getAuthorizeTransactionBuilder($payment)
    {

    }

    public function getCaptureTransactionBuilder($payment)
    {

    }

    public function getRefundTransactionBuilder($payment)
    {

    }

    public function getVoidTransactionBuilder($payment)
    {

    }

    public function setCanRefund($value)
    {
        $this->_canRefund = $value;
    }

    public function setCanOrder($value)
    {
        $this->_canOrder = $value;
    }
}

// @codingStandardsIgnoreStart
class AbstractMethodTest extends \TIG\Buckaroo\Test\BaseTest
// @codingStandardsIgnoreEnd
{
    /**
     * @var \Mockery\MockInterface
     */
    protected $objectManager;

    /**
     * @var \Mockery\MockInterface
     */
    protected $configProvider;

    /**
     * @var \Mockery\MockInterface
     */
    protected $configMethodProvider;

    /**
     * @var \TIG\Buckaroo\Model\Method\AbstractMethod
     */
    protected $object;

    /**
     * @var \Mockery\MockInterface
     */
    protected $scopeConfig;

    /**
     * @var \Mockery\MockInterface
     */
    protected $account;

    /**
     * Setup the standard mocks
     */
    public function setUp()
    {
        parent::setUp();

        $this->objectManager = \Mockery::mock(\Magento\Framework\ObjectManagerInterface::class);
        $this->scopeConfig = \Mockery::mock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->account = \Mockery::mock(\TIG\Buckaroo\Model\ConfigProvider\Account::class);
        $this->configProvider = \Mockery::mock(\TIG\Buckaroo\Model\ConfigProvider\Factory::class);
        $this->configMethodProvider = \Mockery::mock(\TIG\Buckaroo\Model\ConfigProvider\Method\Factory::class);
        $this->configProvider->shouldReceive('get')->with('account')->andReturn($this->account);

        /**
         * We are using the temporary class declared above, but it could be any class extending from the AbstractMethod
         * class.
         */
        $this->object = $this->objectManagerHelper->getObject(AbstractMethod::class, [
            'objectManager' => $this->objectManager,
            'configProviderFactory' => $this->configProvider,
            'scopeConfig' => $this->scopeConfig,
            'configProviderMethodFactory' => $this->configMethodProvider,
        ]);
    }

    /**
     * @param int    $active
     * @param int    $maxAmount
     * @param int    $minAmount
     * @param null   $limitByIp
     * @param string $allowedCurrencies
     *
     * @return $this
     */
    public function getValues(
        $active = 1,
        $maxAmount = 80,
        $minAmount = 80,
        $limitByIp = null,
        $allowedCurrencies = 'ABC,DEF'
    ) {
        $this->scopeConfig->shouldReceive('getValue')
            ->with('payment/tig_buckaroo_test/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 1)
            ->andReturn($active);
        $this->scopeConfig->shouldReceive('getValue')
            ->with('payment/tig_buckaroo_test/max_amount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 1)
            ->andReturn($maxAmount);
        $this->scopeConfig->shouldReceive('getValue')
            ->with('payment/tig_buckaroo_test/min_amount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 1)
            ->andReturn($minAmount);
        $this->scopeConfig->shouldReceive('getValue')
            ->with('payment/tig_buckaroo_test/limit_by_ip', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null)
            ->andReturn($limitByIp);
        $this->scopeConfig->shouldReceive('getValue')
            ->with(
                'payment/tig_buckaroo_test/allowed_currencies',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null
            )
            ->andReturn($allowedCurrencies);

        $this->account->shouldReceive('getActive')->once()->andReturn(1);
        $this->account->shouldReceive('getLimitByIp')->once()->andReturn(1);

        return $this;
    }

    /**
     * Test what happens if the payment method is disabled.
     */
    public function testIsAvailableDisabled()
    {
        /** @var \Magento\Quote\Api\Data\CartInterface|\Mockery\MockInterface $quote */
        $quote = \Mockery::mock(\Magento\Quote\Api\Data\CartInterface::class);

        $this->account->shouldReceive('getActive')->once()->andReturn(0);
        $result = $this->object->isAvailable($quote);

        $this->assertFalse($result);
    }

    /**
     * Test what happens if the payment method is disabled.
     */
    public function testIsAvailableAdminhtmlDisabled()
    {
        /** @var \Magento\Quote\Api\Data\CartInterface|\Mockery\MockInterface $quote */
        $quote = \Mockery::mock(\Magento\Quote\Api\Data\CartInterface::class);

        $this->account->shouldReceive('getActive')->once()->andReturn(1);

        $this->objectManager->shouldReceive('get')->with('Magento\Framework\App\State')->andReturnSelf();
        $this->objectManager->shouldReceive('getAreaCode')->andReturn('adminhtml');

        $partialMock = $this->getPartialObject(
            AbstractMethod::class,
            [
                'objectManager' => $this->objectManager,
                'configProviderFactory' => $this->configProvider,
            ],
            array('getConfigData')
        );

        $partialMock->expects($this->once())->method('getConfigData')->with('available_in_backend')->willReturn(0);

        /**
         * @var \TIG\Buckaroo\Model\Method\AbstractMethod $partialMock
         */
        $result = $partialMock->isAvailable($quote);

        $this->assertFalse($result);
    }

    /**
     * Test what happens if the allow by ip option is on, but our ip is not in the list.
     */
    public function testIsAvailableInvalidIp()
    {
        $this->getValues();

        /** @var \Magento\Quote\Api\Data\CartInterface|\Mockery\MockInterface $quote */
        $quote = \Mockery::mock(\Magento\Quote\Api\Data\CartInterface::class);
        $quote->shouldReceive('getStoreId')->once()->andReturn(1);

        $this->scopeConfig->shouldReceive('getValue');

        $developerHelper = \Mockery::mock(\Magento\Developer\Helper\Data::class);
        $developerHelper->shouldReceive('isDevAllowed')->once()->with(1)->andReturn(false);

        $stateMock = \Mockery::mock(\Magento\Framework\App\State::class);
        $stateMock->shouldReceive('getAreaCode')->once()->andReturn('frontend');

        $this->objectManager->shouldReceive('create')
            ->once()
            ->with(\Magento\Developer\Helper\Data::class)
            ->andReturn($developerHelper);
        $this->objectManager->shouldReceive('get')
            ->once()
            ->with(\Magento\Framework\App\State::class)
            ->andReturn($stateMock);

        $result = $this->object->isAvailable($quote);

        $this->assertFalse($result);
    }

    /**
     * Test what happens if the allow by ip option is on, and our ip is in the list.
     */
    public function testIsAvailableValidIp()
    {
        $this->getValues(1, null, null);

        /** @var \Magento\Quote\Api\Data\CartInterface|\Mockery\MockInterface $quote */
        $quote = \Mockery::mock(\Magento\Quote\Api\Data\CartInterface::class);
        $quote->shouldReceive('getStoreId')->andReturn(1);
        $quote->shouldReceive('getGrandTotal')->once()->andReturn(60);
        $quote->shouldReceive('getCurrency')->once()->andReturnSelf();
        $quote->shouldReceive('getStoreCurrencyCode')->once()->andReturn('ABC');

        $this->scopeConfig->shouldReceive('getValue')->andReturn(1);

        $developerHelper = \Mockery::mock(\Magento\Developer\Helper\Data::class);
        $developerHelper->shouldReceive('isDevAllowed')->once()->with(1)->andReturn(true);

        $stateMock = \Mockery::mock(\Magento\Framework\App\State::class);
        $stateMock->shouldReceive('getAreaCode')->once()->andReturn('frontend');

        $this->objectManager->shouldReceive('create')
            ->once()
            ->with(\Magento\Developer\Helper\Data::class)
            ->andReturn($developerHelper);
        $this->objectManager->shouldReceive('get')
            ->once()
            ->with(\Magento\Framework\App\State::class)
            ->andReturn($stateMock);

        $result = $this->object->isAvailable($quote);

        $this->assertTrue($result);
    }

    /**
     * Test what happens if we exceed the maximum amount. The method should be hidden.
     */
    public function testIsAvailableExceedsMaximum()
    {
        $this->getValues();

        /** @var \Magento\Quote\Api\Data\CartInterface|\Mockery\MockInterface $quote */
        $quote = \Mockery::mock(\Magento\Quote\Api\Data\CartInterface::class);
        $quote->shouldReceive('getStoreId')->andReturn(1);
        $quote->shouldReceive('getGrandTotal')->once()->andReturn(90);

        $developerHelper = \Mockery::mock(\Magento\Developer\Helper\Data::class);
        $developerHelper->shouldReceive('isDevAllowed')->once()->with(1)->andReturn(true);

        $stateMock = \Mockery::mock(\Magento\Framework\App\State::class);
        $stateMock->shouldReceive('getAreaCode')->once()->andReturn('frontend');

        $this->objectManager->shouldReceive('create')
            ->once()
            ->with(\Magento\Developer\Helper\Data::class)
            ->andReturn($developerHelper);
        $this->objectManager->shouldReceive('get')
            ->once()
            ->with(\Magento\Framework\App\State::class)
            ->andReturn($stateMock);

        $result = $this->object->isAvailable($quote);

        $this->assertFalse($result);
    }

    /**
     * Test what happens if we exceed the minimum amount. The method should be hidden.
     */
    public function testIsAvailableExceedsMinimum()
    {
        $this->getValues();

        /** @var \Magento\Quote\Api\Data\CartInterface|\Mockery\MockInterface $quote */
        $quote = \Mockery::mock(\Magento\Quote\Api\Data\CartInterface::class);
        $quote->shouldReceive('getStoreId')->andReturn(1);
        $quote->shouldReceive('getGrandTotal')->once()->andReturn(60);

        $developerHelper = \Mockery::mock(\Magento\Developer\Helper\Data::class);
        $developerHelper->shouldReceive('isDevAllowed')->once()->with(1)->andReturn(true);

        $stateMock = \Mockery::mock(\Magento\Framework\App\State::class);
        $stateMock->shouldReceive('getAreaCode')->once()->andReturn('frontend');

        $this->objectManager->shouldReceive('create')
            ->once()->with(\Magento\Developer\Helper\Data::class)
            ->andReturn($developerHelper);
        $this->objectManager->shouldReceive('get')
            ->once()
            ->with(\Magento\Framework\App\State::class)
            ->andReturn($stateMock);

        $result = $this->object->isAvailable($quote);

        $this->assertFalse($result);
    }

    /**
     * Test what happens if we exceed the minimum amount. The method should be hidden.
     */
    public function testIsAvailableNotAllowedCurrency()
    {
        $this->getValues(1, null, null);

        /** @var \Magento\Quote\Api\Data\CartInterface|\Mockery\MockInterface $quote */
        $quote = \Mockery::mock(\Magento\Quote\Api\Data\CartInterface::class);
        $quote->shouldReceive('getStoreId')->andReturn(1);
        $quote->shouldReceive('getGrandTotal')->once()->andReturn(90);
        $quote->shouldReceive('getCurrency')->once()->andReturnSelf();
        $quote->shouldReceive('getStoreCurrencyCode')->once()->andReturn('EUR');

        $developerHelper = \Mockery::mock(\Magento\Developer\Helper\Data::class);
        $developerHelper->shouldReceive('isDevAllowed')->once()->with(1)->andReturn(true);

        $stateMock = \Mockery::mock(\Magento\Framework\App\State::class);
        $stateMock->shouldReceive('getAreaCode')->once()->andReturn('frontend');

        $this->objectManager->shouldReceive('create')
            ->once()
            ->with(\Magento\Developer\Helper\Data::class)
            ->andReturn($developerHelper);
        $this->objectManager->shouldReceive('get')
            ->once()
            ->with(\Magento\Framework\App\State::class)
            ->andReturn($stateMock);

        $result = $this->object->isAvailable($quote);

        $this->assertFalse($result);
    }

    /**
     * Test what happens if we exceed the minimum amount. The method should be hidden.
     */
    public function testIsAvailableAllowedCurrency()
    {
        $this->getValues(1, null, null);

        /** @var \Magento\Quote\Api\Data\CartInterface|\Mockery\MockInterface $quote */
        $quote = \Mockery::mock(\Magento\Quote\Api\Data\CartInterface::class);
        $quote->shouldReceive('getStoreId')->andReturn(1);
        $quote->shouldReceive('getGrandTotal')->once()->andReturn(90);
        $quote->shouldReceive('getCurrency')->once()->andReturnSelf();
        $quote->shouldReceive('getStoreCurrencyCode')->once()->andReturn('ABC');

        $developerHelper = \Mockery::mock(\Magento\Developer\Helper\Data::class);
        $developerHelper->shouldReceive('isDevAllowed')->once()->with(1)->andReturn(true);

        $stateMock = \Mockery::mock(\Magento\Framework\App\State::class);
        $stateMock->shouldReceive('getAreaCode')->once()->andReturn('frontend');

        $this->objectManager->shouldReceive('create')
            ->once()
            ->with(\Magento\Developer\Helper\Data::class)
            ->andReturn($developerHelper);
        $this->objectManager->shouldReceive('get')
            ->once()
            ->with(\Magento\Framework\App\State::class)
            ->andReturn($stateMock);

        $result = $this->object->isAvailable($quote);

        $this->assertTrue($result);
    }

    public function testCanRefundParentFalse()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->object->setCanRefund(false);

        $this->assertFalse($this->object->canRefund());
    }

    /**
     * @dataProvider refundEnabledDisabledDataProvider
     *
     * @param $enabled
     */
    public function testCanRefundNotEnabled($enabled)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->object->setCanRefund(true);

        $this->configProvider->shouldReceive('get')->andReturnSelf();
        $this->configProvider->shouldReceive('getEnabled')->andReturn($enabled);

        $this->assertEquals($enabled, $this->object->canRefund());
    }

    public function refundEnabledDisabledDataProvider()
    {
        return [
            [
                true,
            ],
            [
                false,
            ],
        ];
    }

    public function testOrderInvalidArgument()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $payment = \Mockery::mock(\Magento\Payment\Model\InfoInterface::class);
        /** @var \Magento\Payment\Model\InfoInterface $payment */

        $this->object->order($payment, 0);
    }

    public function testCantOrder()
    {
        $this->setExpectedException('\Magento\Framework\Exception\LocalizedException');
        $mockClass = \Magento\Payment\Model\InfoInterface::class
            . ','
            . \Magento\Sales\Api\Data\OrderPaymentInterface::class;
        $payment = \Mockery::mock($mockClass);
        /** @var \Magento\Payment\Model\InfoInterface $payment */

        /** @noinspection PhpUndefinedMethodInspection */
        $this->object->setCanOrder(false);
        $this->object->order($payment, 0);
    }

    public function testGetConfigDataRedirectUrl()
    {
        $expectedUrl = 'test.com';

        $this->object->orderPlaceRedirectUrl = $expectedUrl;

        $result = $this->object->getConfigData('order_place_redirect_url');

        $this->assertEquals($expectedUrl, $result);
    }

    public function testGetTitleNoConfigProvider()
    {
        $method = 'tig_buckaroo_abstract_test';
        $expectedTitle = 'tig_buckaroo_abstract_test_title';
        $this->configMethodProvider->shouldReceive('has')->with($method)->andReturn(false);

        $partialMock = $this->getPartialObject(
            AbstractMethod::class,
            [
                'objectManager' => $this->objectManager,
                'configProviderFactory' => $this->configProvider,
                'configProviderMethodFactory' => $this->configMethodProvider,
            ],
            array('getConfigData')
        );

        $partialMock->expects($this->once())->method('getConfigData')->with('title')->willReturn($expectedTitle);

        /**
         * @var \TIG\Buckaroo\Model\Method\AbstractMethod $partialMock
         */
        $partialMock->buckarooPaymentMethodCode = $method;


        $this->assertEquals($expectedTitle, $partialMock->getTitle());
    }

    /**
     * @dataProvider testGetTitleNoPaymentFeeDataProvider
     *
     * @param $title
     * @param $expectedTitle
     * @param $fee
     */
    public function testGetTitleNoPaymentFee($title, $expectedTitle, $fee)
    {
        $method = 'tig_buckaroo_abstract_test';

        $this->configMethodProvider->shouldReceive('has')->with($method)->andReturn(true);
        $this->configMethodProvider->shouldReceive('get')->with($method)->andReturnSelf();
        $this->configMethodProvider->shouldReceive('getPaymentFee')->andReturn($fee);

        $priceHelperMock = \Mockery::mock(\Magento\Framework\Pricing\Helper\Data::class);
        $priceHelperMock->shouldReceive('currency')->with($fee, true, false)->andReturn($fee);

        $partialMock = $this->getPartialObject(
            AbstractMethod::class,
            [
                'objectManager' => $this->objectManager,
                'configProviderFactory' => $this->configProvider,
                'configProviderMethodFactory' => $this->configMethodProvider,
                'priceHelper' => $priceHelperMock,
            ],
            array('getConfigData')
        );

        $partialMock->expects($this->once())->method('getConfigData')->with('title')->willReturn($title);
        /**
         * @var \TIG\Buckaroo\Model\Method\AbstractMethod $partialMock
         */

        $partialMock->buckarooPaymentMethodCode = $method;

        $this->assertEquals($expectedTitle, $partialMock->getTitle());
    }

    public function testGetTitleNoPaymentFeeDataProvider()
    {
        return [
            [
                'tig_buckaroo_abstract_test_title',
                'tig_buckaroo_abstract_test_title',
                false,
            ],
            [
                'tig_buckaroo_abstract_test_title',
                'tig_buckaroo_abstract_test_title + 5.00',
                '5.00',
            ],
            [
                'tig_buckaroo_abstract_test_title',
                'tig_buckaroo_abstract_test_title + 5%',
                '5%',
            ],
        ];
    }

    public function testOrderTransactionBuilderFalse()
    {
        $this->setExpectedException('\LogicException');
        $mockClass = \Magento\Payment\Model\InfoInterface::class
            . ','
            . \Magento\Sales\Api\Data\OrderPaymentInterface::class;
        $payment = \Mockery::mock($mockClass);
        /** @var \Magento\Payment\Model\InfoInterface $payment */

        $partialMock = $this->getPartialObject(
            AbstractMethod::class,
            [
                'objectManager' => $this->objectManager,
                'configProviderFactory' => $this->configProvider,
                'configProviderMethodFactory' => $this->configMethodProvider,
            ],
            array('getOrderTransactionBuilder')
        );

        /** @noinspection PhpUndefinedMethodInspection */
        $partialMock->setCanOrder(true);

        $partialMock->expects($this->once())
            ->method('getOrderTransactionBuilder')
            ->with($payment)
            ->willReturn(false);

        /** @noinspection PhpUndefinedMethodInspection */
        $partialMock->order($payment, 0);

        $this->assertSame($payment, $partialMock->payment);
    }

    public function testOrderTransactionBuilderTrue()
    {
        $mockClass = \Magento\Payment\Model\InfoInterface::class
            . ','
            . \Magento\Sales\Api\Data\OrderPaymentInterface::class;
        $payment = \Mockery::mock($mockClass);
        /** @var \Magento\Payment\Model\InfoInterface $payment */

        $partialMock = $this->getPartialObject(
            AbstractMethod::class,
            [
                'objectManager' => $this->objectManager,
                'configProviderFactory' => $this->configProvider,
                'configProviderMethodFactory' => $this->configMethodProvider,
            ],
            array('getOrderTransactionBuilder')
        );

        /** @noinspection PhpUndefinedMethodInspection */
        $partialMock->setCanOrder(true);

        $partialMock->expects($this->once())
            ->method('getOrderTransactionBuilder')
            ->with($payment)
            ->willReturn(true);

        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertEquals($partialMock, $partialMock->order($payment, 0));

        $this->assertSame($payment, $partialMock->payment);
    }


    public function testOrderFullFlow()
    {
        $responseObject = new \stdClass();
        $response = [$responseObject];

        $mockClass = \Magento\Payment\Model\InfoInterface::class
            . ','
            . \Magento\Sales\Api\Data\OrderPaymentInterface::class;
        $payment = \Mockery::mock($mockClass);
        /** @var \Magento\Payment\Model\InfoInterface $payment */

        $transaction = \Mockery::mock('\TIG\Buckaroo\Gateway\Http\Transaction');

        $registryMock = \Mockery::mock('\Magento\Framework\Registry');
        $registryMock->shouldReceive('register')->once()->with('buckaroo_response', $response);

        $transactionBuilderMock = \Mockery::mock('\TIG\Buckaroo\Gateway\Http\TransactionBuilderInterface');
        $transactionBuilderMock->shouldReceive('build')->once()->andReturn($transaction);

        $partialMock = $this->getPartialObject(
            AbstractMethod::class,
            [
                'objectManager' => $this->objectManager,
                'configProviderFactory' => $this->configProvider,
                'configProviderMethodFactory' => $this->configMethodProvider,
                'registry' => $registryMock
            ],
            array('orderTransaction', 'saveTransactionData', 'afterOrder', 'getOrderTransactionBuilder')
        );

        $partialMock->setCanOrder(true);

        $partialMock->expects($this->once())
            ->method('getOrderTransactionBuilder')
            ->with($payment)
            ->willReturn($transactionBuilderMock);

        $partialMock->expects($this->once())
            ->method('orderTransaction')
            ->with($transaction)
            ->willReturn($response);

        $partialMock->expects($this->once())
            ->method('saveTransactionData')
            ->with($response[0], $payment, true, true);

        $partialMock->expects($this->once())
            ->method('afterOrder')
            ->with($payment, $response);

        $result = $partialMock->order($payment, 0);

        $this->assertEquals($partialMock, $result);

        $this->assertSame($payment, $partialMock->payment);
    }
}
