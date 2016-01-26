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

    public function setCanVoid($value)
    {
        $this->_canVoid = $value;
    }

    public function setCanOrder($value)
    {
        $this->_canOrder = $value;
    }

    public function setCanAuthorize($value)
    {
        $this->_canAuthorize = $value;
    }

    public function setCanCapture($value)
    {
        $this->_canCapture = $value;
    }
}

// @codingStandardsIgnoreStart
class AbstractMethodTest extends \TIG\Buckaroo\Test\BaseTest
// @codingStandardsIgnoreEnd
{
    /**
     * @var \Mockery\MockInterface
     */
    protected $validatorFactory;

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
     * @var \Mockery\MockInterface
     */
    protected $gateway;

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
        $this->validatorFactory = \Mockery::mock(\TIG\Buckaroo\Model\ValidatorFactory::class);
        $this->gateway = \Mockery::mock(\TIG\Buckaroo\Gateway\GatewayInterface::class);

        $this->gateway->shouldReceive('setMode')->withAnyArgs();

        /**
         * We are using the temporary class declared above, but it could be any class extending from the AbstractMethod
         * class.
         */
        $this->object = $this->objectManagerHelper->getObject(AbstractMethod::class, [
            'objectManager' => $this->objectManager,
            'configProviderFactory' => $this->configProvider,
            'scopeConfig' => $this->scopeConfig,
            'configProviderMethodFactory' => $this->configMethodProvider,
            'validatorFactory' => $this->validatorFactory,
            'gateway' => $this->gateway,
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

    /**
     * @dataProvider testProcessInvalidArgumentDataProvider
     *
     * @param $method
     */
    public function testProcessInvalidArgument($method)
    {
        $this->setExpectedException('\InvalidArgumentException');

        $payment = \Mockery::mock(\Magento\Payment\Model\InfoInterface::class);
        /** @var \Magento\Payment\Model\InfoInterface $payment */

        $this->object->$method($payment, 0);
    }

    public function testProcessInvalidArgumentDataProvider()
    {
        return [
            [
                'order',
            ],
            [
                'authorize',
            ],
            [
                'capture',
            ],
            [
                'refund',
            ],
            [
                'void',
            ],
        ];
    }

    /**
     * @param $method
     * @param $canMethod
     *
     * @dataProvider testCantProcessDataProvider
     */
    public function testCantProcess($method, $canMethod)
    {
        $this->setExpectedException(\Magento\Framework\Exception\LocalizedException::class);
        $mockClass = \Magento\Payment\Model\InfoInterface::class
            . ','
            . \Magento\Sales\Api\Data\OrderPaymentInterface::class;

        $payment = \Mockery::mock($mockClass);
        /** @var \Magento\Payment\Model\InfoInterface $payment */

        /** @noinspection PhpUndefinedMethodInspection */
        $this->object->$canMethod(false);
        $this->object->$method($payment, 0);
    }

    public function testCantProcessDataProvider()
    {
        return [
            [
                'order',
                'setCanOrder',
            ],
            [
                'authorize',
                'setCanAuthorize',
            ],
            [
                'capture',
                'setCanCapture',
            ],
            [
                'refund',
                'setCanRefund',
            ],
            [
                'void',
                'setCanVoid',
            ],
        ];
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

    /**
     * @param      $method
     * @param      $setCanMethod
     * @param      $methodTransactionBuilder
     *
     * @param bool $canMethod
     *
     * @dataProvider testTransactionBuilderFalseTrueDataProvider
     */
    public function testTransactionBuilderFalse($method, $setCanMethod, $methodTransactionBuilder, $canMethod = false)
    {
        $this->setExpectedException(\LogicException::class);
        $mockClass = \Magento\Payment\Model\InfoInterface::class
            . ','
            . \Magento\Sales\Api\Data\OrderPaymentInterface::class;
        $payment = \Mockery::mock($mockClass);
        /** @var \Magento\Payment\Model\InfoInterface $payment */

        $stubbedMethods = [$methodTransactionBuilder];

        if ($canMethod) {
            $stubbedMethods[] = $canMethod;
        }

        $partialMock = $this->getPartialObject(
            AbstractMethod::class,
            [
                'objectManager' => $this->objectManager,
                'configProviderFactory' => $this->configProvider,
                'configProviderMethodFactory' => $this->configMethodProvider,
            ],
            $stubbedMethods
        );

        /** @noinspection PhpUndefinedMethodInspection */
        $partialMock->$setCanMethod(true);

        $partialMock->expects($this->once())
            ->method($methodTransactionBuilder)
            ->with($payment)
            ->willReturn(false);

        if ($canMethod) {
            $partialMock->expects($this->any())
                ->method($canMethod)
                ->withAnyParameters()
                ->willReturn(true);
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $partialMock->$method($payment, 0);

        $this->assertSame($payment, $partialMock->payment);
    }

    public function testTransactionBuilderFalseTrueDataProvider()
    {
        return [
            [
                'order',
                'setCanOrder',
                'getOrderTransactionBuilder',
            ],
            [
                'authorize',
                'setCanAuthorize',
                'getAuthorizeTransactionBuilder',
            ],
            [
                'capture',
                'setCanCapture',
                'getCaptureTransactionBuilder',
            ],
            [
                'refund',
                'setCanRefund',
                'getRefundTransactionBuilder',
                'canRefund',
            ],
            [
                'void',
                'setCanVoid',
                'getVoidTransactionBuilder',
            ],
        ];
    }

    /**
     * @param $method
     * @param $setCanMethod
     * @param $methodTransactionBuilder
     *
     * @param $canMethod
     *
     * @dataProvider testTransactionBuilderFalseTrueDataProvider
     */
    public function testTransactionBuilderTrue($method, $setCanMethod, $methodTransactionBuilder, $canMethod = false)
    {
        $mockClass = \Magento\Payment\Model\InfoInterface::class
            . ','
            . \Magento\Sales\Api\Data\OrderPaymentInterface::class;
        $payment = \Mockery::mock($mockClass);
        /** @var \Magento\Payment\Model\InfoInterface $payment */

        $stubbedMethods = [$methodTransactionBuilder];

        if ($canMethod) {
            $stubbedMethods[] = $canMethod;
        }

        $partialMock = $this->getPartialObject(
            AbstractMethod::class,
            [
                'objectManager' => $this->objectManager,
                'configProviderFactory' => $this->configProvider,
                'configProviderMethodFactory' => $this->configMethodProvider,
            ],
            $stubbedMethods
        );

        /** @noinspection PhpUndefinedMethodInspection */
        $partialMock->$setCanMethod(true);

        $partialMock->expects($this->once())
            ->method($methodTransactionBuilder)
            ->with($payment)
            ->willReturn(true);

        if ($canMethod) {
            $partialMock->expects($this->any())
                ->method($canMethod)
                ->withAnyParameters()
                ->willReturn(true);
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertEquals($partialMock, $partialMock->$method($payment, 0));

        $this->assertSame($payment, $partialMock->payment);
    }

    /**
     * @param      $method
     * @param      $setCanMethod
     * @param      $methodTransactionBuilder
     * @param      $methodTransaction
     * @param      $afterMethod
     *
     * @param bool $canMethod
     *
     * @param bool $closeTransaction
     *
     * @param bool $saveId
     *
     * @dataProvider testFullFlowDataProvider
     */
    public function testFullFlow(
        $method,
        $setCanMethod,
        $methodTransactionBuilder,
        $methodTransaction,
        $afterMethod,
        $canMethod = false,
        $closeTransaction = true,
        $saveId = true
    ) {
        $amount = 0;

        $responseObject = new \stdClass();
        $response = [$responseObject];

        $mockClass = \Magento\Payment\Model\InfoInterface::class
            . ','
            . \Magento\Sales\Api\Data\OrderPaymentInterface::class;
        $payment = \Mockery::mock($mockClass);
        /** @var \Magento\Payment\Model\InfoInterface $payment */

        $transaction = \Mockery::mock(\TIG\Buckaroo\Gateway\Http\Transaction::class);

        $registryMock = \Mockery::mock(\Magento\Framework\Registry::class);

        if ($method != 'refund' && $method != 'void') {
            $registryMock->shouldReceive('register')->once()->with('buckaroo_response', $response);
        }

        $transactionBuilderMock = \Mockery::mock(\TIG\Buckaroo\Gateway\Http\TransactionBuilderInterface::class);

        if ($method == 'refund') {
            $transactionBuilderMock->shouldReceive('setAmount')->with($amount)->once()->andReturn($transaction);
        }
        $transactionBuilderMock->shouldReceive('build')->once()->andReturn($transaction);

        $stubbedMethods = [$methodTransaction, 'saveTransactionData', $afterMethod, $methodTransactionBuilder];

        if ($canMethod) {
            $stubbedMethods[] = $canMethod;
        }

        $partialMock = $this->getPartialObject(
            AbstractMethod::class,
            [
                'objectManager' => $this->objectManager,
                'configProviderFactory' => $this->configProvider,
                'configProviderMethodFactory' => $this->configMethodProvider,
                'registry' => $registryMock
            ],
            $stubbedMethods
        );

        $partialMock->$setCanMethod(true);

        $partialMock->expects($this->once())
            ->method($methodTransactionBuilder)
            ->with($payment)
            ->willReturn($transactionBuilderMock);

        $partialMock->expects($this->once())
            ->method($methodTransaction)
            ->with($transaction)
            ->willReturn($response);

        $partialMock->expects($this->once())
            ->method('saveTransactionData')
            ->with($response[0], $payment, $partialMock->$closeTransaction, $saveId);

        $partialMock->expects($this->once())
            ->method($afterMethod)
            ->with($payment, $response);

        if ($canMethod) {
            $partialMock->expects($this->any())
                ->method($canMethod)
                ->withAnyParameters()
                ->willReturn(true);
        }

        $result = $partialMock->$method($payment, $amount);

        $this->assertEquals($partialMock, $result);

        $this->assertSame($payment, $partialMock->payment);
    }

    public function testFullFlowDataProvider()
    {
        return [
            [
                'order',
                'setCanOrder',
                'getOrderTransactionBuilder',
                'orderTransaction',
                'afterOrder',
                false,
                'closeOrderTransaction',
            ],
            [
                'authorize',
                'setCanAuthorize',
                'getAuthorizeTransactionBuilder',
                'authorizeTransaction',
                'afterAuthorize',
                false,
                'closeAuthorizeTransaction',
            ],
            [
                'capture',
                'setCanCapture',
                'getCaptureTransactionBuilder',
                'captureTransaction',
                'afterCapture',
                false,
                'closeCaptureTransaction',
            ],
            [
                'refund',
                'setCanRefund',
                'getRefundTransactionBuilder',
                'refundTransaction',
                'afterRefund',
                'canRefund',
                'closeRefundTransaction',
                false,
            ],
            [
                'void',
                'setCanVoid',
                'getVoidTransactionBuilder',
                'VoidTransaction',
                'afterVoid',
                false,
                'closeCancelTransaction',
                false,
            ],
        ];
    }

    /**
     * @param $method
     *
     * @param $gatewayMethod
     *
     * @dataProvider testProcessTransactionDataProvider
     */
    public function testProcessTransactionResponseNotValid($method, $gatewayMethod)
    {
        $this->setExpectedException(\TIG\Buckaroo\Exception::class);

        $response = [];

        $transactionMock = \Mockery::mock(\TIG\Buckaroo\Gateway\Http\Transaction::class);
        /** @var \TIG\Buckaroo\Gateway\Http\Transaction $transactionMock */

        $this->gateway->shouldReceive($gatewayMethod)
            ->once()
            ->with($transactionMock)
            ->andReturn($response);

        $this->validatorFactory->shouldReceive('get')
            ->once()
            ->with('transaction_response')
            ->andReturnSelf();

        $this->validatorFactory->shouldReceive('validate')->with($response)->andReturn(false);

        $this->object->$method($transactionMock);
    }

    public function testProcessTransactionDataProvider()
    {
        return [
            [
                'orderTransaction',
                'authorize',
            ],
            [
                'authorizeTransaction',
                'authorize',
            ],
            [
                'captureTransaction',
                'capture',
            ],
            [
                'refundTransaction',
                'refund',
            ],
            [
                'voidTransaction',
                'void',
            ],
        ];
    }

    /**
     * @param $method
     *
     * @param $gatewayMethod
     *
     * @dataProvider testProcessTransactionDataProvider
     */
    public function testProcessTransactionResponseStatusNotValid($method, $gatewayMethod)
    {
        $this->setExpectedException(\TIG\Buckaroo\Exception::class);

        $response = [];

        $transactionMock = \Mockery::mock(\TIG\Buckaroo\Gateway\Http\Transaction::class);
        /** @var \TIG\Buckaroo\Gateway\Http\Transaction $transactionMock */

        $this->gateway->shouldReceive($gatewayMethod)
            ->once()
            ->with($transactionMock)
            ->andReturn($response);

        $this->validatorFactory->shouldReceive('get')
            ->once()
            ->with('transaction_response')
            ->andReturnSelf();

        $this->validatorFactory->shouldReceive('validate')->once()->with($response)->andReturn(true);

        $this->validatorFactory->shouldReceive('get')
            ->once()
            ->with('transaction_response_status')
            ->andReturnSelf();
        $this->validatorFactory->shouldReceive('validate')->once()->with($response)->andReturn(false);

        $this->object->$method($transactionMock);
    }

    /**
     * @param $method
     *
     * @param $gatewayMethod
     *
     * @dataProvider testProcessTransactionDataProvider
     */
    public function testProcessTransactionSuccessful($method, $gatewayMethod)
    {
        $response = ['test_response'];

        $transactionMock = \Mockery::mock(\TIG\Buckaroo\Gateway\Http\Transaction::class);
        /** @var \TIG\Buckaroo\Gateway\Http\Transaction $transactionMock */

        $this->gateway->shouldReceive($gatewayMethod)
            ->once()
            ->with($transactionMock)
            ->andReturn($response);

        $this->validatorFactory->shouldReceive('get')
            ->once()
            ->with('transaction_response')
            ->andReturnSelf();

        $this->validatorFactory->shouldReceive('validate')->once()->with($response)->andReturn(true);

        $this->validatorFactory->shouldReceive('get')
            ->once()
            ->with('transaction_response_status')
            ->andReturnSelf();
        $this->validatorFactory->shouldReceive('validate')->once()->with($response)->andReturn(true);

        $this->assertSame($response, $this->object->$method($transactionMock));
    }
}
