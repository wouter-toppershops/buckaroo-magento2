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
 * @copyright   Copyright (c) Total Internet Group B.V. https://tig.nl/copyright
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
namespace TIG\Buckaroo\Test\Unit\Model\Method;

use Magento\Developer\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Pricing\Helper\Data as PriceHelperData;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Model\ScopeInterface;
use TIG\Buckaroo\Model\ConfigProvider\Account;
use TIG\Buckaroo\Model\ConfigProvider\Factory;
use TIG\Buckaroo\Model\ConfigProvider\Method\Factory as MethodFactory;

/**
 * Class AbstractMethodTest
 *
 */

// @codingStandardsIgnoreStart
class AbstractMethodTest extends \TIG\Buckaroo\Test\BaseTest
// @codingStandardsIgnoreEnd
{
    protected $instanceClass = AbstractMethodMock::class;

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
     * @var \TIG\Buckaroo\Test\Unit\Model\Method\AbstractMethodMock
     */
    protected $object;

    /**
     * @var \Mockery\MockInterface
     */
    protected $scopeConfig;

    /**
     * @var \Mockery\MockInterface|Data
     */
    protected $developmentHelper;

    /**
     * @var \Mockery\MockInterface
     */
    protected $account;

    /**
     * @var \Mockery\MockInterface
     */
    protected $gateway;

    /**
     * @var \Mockery\MockInterface
     */
    protected $helper;

    /**
     * @var \Mockery\MockInterface
     */
    protected $request;

    /**
     * @var \Mockery\MockInterface
     */
    protected $refundFieldsFactory;

    /**
     * Setup the standard mocks
     */
    public function setUp()
    {
        parent::setUp();

        $this->objectManager = \Mockery::mock(\Magento\Framework\ObjectManagerInterface::class);
        $this->scopeConfig = \Mockery::mock(ScopeConfigInterface::class);
        $this->developmentHelper = \Mockery::mock(Data::class);
        $this->account = \Mockery::mock(Account::class);
        $this->configProvider = \Mockery::mock(Factory::class);
        $this->configMethodProvider = \Mockery::mock(MethodFactory::class);
        $this->configProvider->shouldReceive('get')->with('account')->andReturn($this->account);
        $this->validatorFactory = \Mockery::mock(\TIG\Buckaroo\Model\ValidatorFactory::class);
        $this->gateway = \Mockery::mock(\TIG\Buckaroo\Gateway\GatewayInterface::class);
        $this->helper = \Mockery::mock(\TIG\Buckaroo\Helper\Data::class);
        $this->request = \Mockery::mock(\Magento\Framework\App\RequestInterface::class);
        $this->refundFieldsFactory = \Mockery::mock(\TIG\Buckaroo\Model\RefundFieldsFactory::class);

        $mode = \TIG\Buckaroo\Helper\Data::MODE_TEST;
        $this->helper->shouldReceive('getMode')->andReturn($mode);
        $this->gateway->shouldReceive('setMode')->with($mode);

        /**
         * We are using the temporary class declared above, but it could be any class extending from the AbstractMethod
         * class.
         */
        $this->object = $this->objectManagerHelper->getObject(
            AbstractMethodMock::class,
            [
            'objectManager' => $this->objectManager,
            'configProviderFactory' => $this->configProvider,
            'scopeConfig' => $this->scopeConfig,
            'developmentHelper' => $this->developmentHelper,
            'configProviderMethodFactory' => $this->configMethodProvider,
            'validatorFactory' => $this->validatorFactory,
            'gateway' => $this->gateway,
            'helper' => $this->helper,
            'request' => $this->request,
            'refundFieldsFactory' => $this->refundFieldsFactory,
            ]
        );
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
            ->with('payment/tig_buckaroo_test/active', ScopeInterface::SCOPE_STORE, 1)
            ->andReturn($active);
        $this->scopeConfig->shouldReceive('getValue')
            ->with('payment/tig_buckaroo_test/max_amount', ScopeInterface::SCOPE_STORE, 1)
            ->andReturn($maxAmount);
        $this->scopeConfig->shouldReceive('getValue')
            ->with('payment/tig_buckaroo_test/min_amount', ScopeInterface::SCOPE_STORE, 1)
            ->andReturn($minAmount);
        $this->scopeConfig->shouldReceive('getValue')
            ->with('payment/tig_buckaroo_test/limit_by_ip', ScopeInterface::SCOPE_STORE, null)
            ->andReturn($limitByIp);
        $this->scopeConfig->shouldReceive('getValue')
            ->with(
                'payment/tig_buckaroo_test/allowed_currencies',
                ScopeInterface::SCOPE_STORE,
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
        /**
         * @var CartInterface|\Mockery\MockInterface $quote
         */
        $quote = \Mockery::mock(CartInterface::class);

        $this->account->shouldReceive('getActive')->once()->andReturn(0);
        $result = $this->object->isAvailable($quote);

        $this->assertFalse($result);
    }

    /**
     * Test what happens if the payment method is disabled.
     */
    public function testIsAvailableAdminhtmlDisabled()
    {
        $quoteMock = $this->getFakeMock(CartInterface::class, true);

        $accountConfigMock = $this->getFakeMock(Account::class)->setMethods(['getActive'])->getMock();
        $accountConfigMock->expects($this->once())->method('getActive')->willReturn(1);

        $configProviderMock = $this->getFakeMock(Factory::class)->setMethods(['get'])->getMock();
        $configProviderMock->expects($this->once())->method('get')->with('account')->willReturn($accountConfigMock);

        $appStateMock = $this->getFakeMock(State::class)->setMethods(['getAreaCode'])->getMock();
        $appStateMock->expects($this->once())->method('getAreaCode')->willReturn('adminhtml');

        $contextMock = $this->getFakeMock(Context::class)->setMethods(['getAppState'])->getMock();
        $contextMock->expects($this->once())->method('getAppState')->willReturn($appStateMock);

        $scopeConfigMock = $this->getFakeMock(ScopeConfigInterface::class)
            ->setMethods(['getValue'])
            ->getMockForAbstractClass();
        $scopeConfigMock->expects($this->exactly(2))->method('getValue')
            ->with('payment/tig_buckaroo_test/available_in_backend', ScopeInterface::SCOPE_STORE, null)
            ->willReturn(0);

        $instance = $this->getInstance([
            'context' => $contextMock,
            'scopeConfig' => $scopeConfigMock,
            'configProviderFactory' => $configProviderMock
        ]);
        $result = $instance->isAvailable($quoteMock);
        $this->assertFalse($result);
    }

    /**
     * Test what happens if the allow by ip option is on, but our ip is not in the list.
     */
    public function testIsAvailableInvalidIp()
    {
        $quoteMock = $this->getFakeMock(CartInterface::class, true);

        $accountConfigMock = $this->getFakeMock(Account::class)->setMethods(['getActive', 'getLimitByIp'])->getMock();
        $accountConfigMock->expects($this->once())->method('getActive')->willReturn(1);
        $accountConfigMock->expects($this->once())->method('getLimitByIp')->willReturn(1);

        $configProviderMock = $this->getFakeMock(Factory::class)->setMethods(['get'])->getMock();
        $configProviderMock->expects($this->once())->method('get')->with('account')->willReturn($accountConfigMock);

        $appStateMock = $this->getFakeMock(State::class)->setMethods(['getAreaCode'])->getMock();
        $appStateMock->expects($this->once())->method('getAreaCode')->willReturn('frontend');

        $contextMock = $this->getFakeMock(Context::class)->setMethods(['getAppState'])->getMock();
        $contextMock->expects($this->once())->method('getAppState')->willReturn($appStateMock);

        $developmentHelperMock = $this->getFakeMock(Data::class)->setMethods(['isDevAllowed'])->getMock();
        $developmentHelperMock->expects($this->once())->method('isDevAllowed')->with(null)->willReturn(false);

        $instance = $this->getInstance([
            'context' => $contextMock,
            'developmentHelper' => $developmentHelperMock,
            'configProviderFactory' => $configProviderMock
        ]);
        $result = $instance->isAvailable($quoteMock);
        $this->assertFalse($result);
    }

    /**
     * Test what happens if we exceed the maximum amount. The method should be hidden.
     */
    public function testIsAvailableExceedsMaximum()
    {
        $quoteMock = $this->getFakeMock(CartInterface::class)->setMethods(['getGrandTotal'])->getMockForAbstractClass();
        $quoteMock->expects($this->once())->method('getGrandTotal')->willReturn(25);

        $accountConfigMock = $this->getFakeMock(Account::class)->setMethods(['getActive'])->getMock();
        $accountConfigMock->expects($this->once())->method('getActive')->willReturn(1);

        $configProviderMock = $this->getFakeMock(Factory::class)->setMethods(['get'])->getMock();
        $configProviderMock->expects($this->once())->method('get')->with('account')->willReturn($accountConfigMock);

        $appStateMock = $this->getFakeMock(State::class)->setMethods(['getAreaCode'])->getMock();
        $appStateMock->expects($this->once())->method('getAreaCode')->willReturn('frontend');

        $contextMock = $this->getFakeMock(Context::class)->setMethods(['getAppState'])->getMock();
        $contextMock->expects($this->once())->method('getAppState')->willReturn($appStateMock);

        $scopeConfigMock = $this->getFakeMock(ScopeConfigInterface::class)
            ->setMethods(['getValue'])
            ->getMockForAbstractClass();
        $scopeConfigMock->expects($this->exactly(3))->method('getValue')
            ->withConsecutive(
                ['payment/tig_buckaroo_test/limit_by_ip', ScopeInterface::SCOPE_STORE, null],
                ['payment/tig_buckaroo_test/max_amount', ScopeInterface::SCOPE_STORE, null],
                ['payment/tig_buckaroo_test/min_amount', ScopeInterface::SCOPE_STORE, null]
            )
            ->willReturnOnConsecutiveCalls(0, 20, 10);

        $instance = $this->getInstance([
            'context' => $contextMock,
            'scopeConfig' => $scopeConfigMock,
            'configProviderFactory' => $configProviderMock
        ]);
        $result = $instance->isAvailable($quoteMock);
        $this->assertFalse($result);
    }

    /**
     * Test what happens if we exceed the minimum amount. The method should be hidden.
     */
    public function testIsAvailableExceedsMinimum()
    {
        $quoteMock = $this->getFakeMock(CartInterface::class)->setMethods(['getGrandTotal'])->getMockForAbstractClass();
        $quoteMock->expects($this->once())->method('getGrandTotal')->willReturn(5);

        $accountConfigMock = $this->getFakeMock(Account::class)->setMethods(['getActive'])->getMock();
        $accountConfigMock->expects($this->once())->method('getActive')->willReturn(1);

        $configProviderMock = $this->getFakeMock(Factory::class)->setMethods(['get'])->getMock();
        $configProviderMock->expects($this->once())->method('get')->with('account')->willReturn($accountConfigMock);

        $appStateMock = $this->getFakeMock(State::class)->setMethods(['getAreaCode'])->getMock();
        $appStateMock->expects($this->once())->method('getAreaCode')->willReturn('frontend');

        $contextMock = $this->getFakeMock(Context::class)->setMethods(['getAppState'])->getMock();
        $contextMock->expects($this->once())->method('getAppState')->willReturn($appStateMock);

        $scopeConfigMock = $this->getFakeMock(ScopeConfigInterface::class)
            ->setMethods(['getValue'])
            ->getMockForAbstractClass();
        $scopeConfigMock->expects($this->exactly(3))->method('getValue')
            ->withConsecutive(
                ['payment/tig_buckaroo_test/limit_by_ip', ScopeInterface::SCOPE_STORE, null],
                ['payment/tig_buckaroo_test/max_amount', ScopeInterface::SCOPE_STORE, null],
                ['payment/tig_buckaroo_test/min_amount', ScopeInterface::SCOPE_STORE, null]
            )
            ->willReturnOnConsecutiveCalls(0, 20, 10);

        $instance = $this->getInstance([
            'context' => $contextMock,
            'scopeConfig' => $scopeConfigMock,
            'configProviderFactory' => $configProviderMock
        ]);
        $result = $instance->isAvailable($quoteMock);
        $this->assertFalse($result);
    }

    /**
     * Test what happens if we exceed the minimum amount. The method should be hidden.
     */
    public function testIsAvailableNotAllowedCurrency()
    {
        $quoteMock = $this->getFakeMock(CartInterface::class)
            ->setMethods(['getGrandTotal', 'getCurrency', 'getQuoteCurrencyCode'])
            ->getMockForAbstractClass();
        $quoteMock->expects($this->once())->method('getGrandTotal')->willReturn(15);
        $quoteMock->expects($this->once())->method('getCurrency')->willReturnSelf();
        $quoteMock->expects($this->once())->method('getQuoteCurrencyCode')->willReturn('GBP');

        $accountConfigMock = $this->getFakeMock(Account::class)->setMethods(['getActive'])->getMock();
        $accountConfigMock->expects($this->once())->method('getActive')->willReturn(1);

        $configProviderMock = $this->getFakeMock(Factory::class)->setMethods(['get'])->getMock();
        $configProviderMock->expects($this->once())->method('get')->with('account')->willReturn($accountConfigMock);

        $appStateMock = $this->getFakeMock(State::class)->setMethods(['getAreaCode'])->getMock();
        $appStateMock->expects($this->once())->method('getAreaCode')->willReturn('frontend');

        $contextMock = $this->getFakeMock(Context::class)->setMethods(['getAppState'])->getMock();
        $contextMock->expects($this->once())->method('getAppState')->willReturn($appStateMock);

        $scopeConfigMock = $this->getFakeMock(ScopeConfigInterface::class)
            ->setMethods(['getValue'])
            ->getMockForAbstractClass();
        $scopeConfigMock->expects($this->exactly(4))->method('getValue')
            ->withConsecutive(
                ['payment/tig_buckaroo_test/limit_by_ip', ScopeInterface::SCOPE_STORE, null],
                ['payment/tig_buckaroo_test/max_amount', ScopeInterface::SCOPE_STORE, null],
                ['payment/tig_buckaroo_test/min_amount', ScopeInterface::SCOPE_STORE, null],
                ['payment/tig_buckaroo_test/allowed_currencies', ScopeInterface::SCOPE_STORE, null]
            )
            ->willReturnOnConsecutiveCalls(0, 20, 10, 'EUR,USD');

        $instance = $this->getInstance([
            'context' => $contextMock,
            'scopeConfig' => $scopeConfigMock,
            'configProviderFactory' => $configProviderMock
        ]);
        $result = $instance->isAvailable($quoteMock);
        $this->assertFalse($result);
    }

    /**
     * Test what happens if the method is available
     */
    public function testIsAvailableValid()
    {
        $quoteMock = $this->getFakeMock(CartInterface::class)
            ->setMethods(['getGrandTotal', 'getCurrency', 'getQuoteCurrencyCode'])
            ->getMockForAbstractClass();
        $quoteMock->expects($this->once())->method('getGrandTotal')->willReturn(15);
        $quoteMock->expects($this->once())->method('getCurrency')->willReturnSelf();
        $quoteMock->expects($this->once())->method('getQuoteCurrencyCode')->willReturn('EUR');

        $accountConfigMock = $this->getFakeMock(Account::class)->setMethods(['getActive'])->getMock();
        $accountConfigMock->expects($this->once())->method('getActive')->willReturn(1);

        $configProviderMock = $this->getFakeMock(Factory::class)->setMethods(['get'])->getMock();
        $configProviderMock->expects($this->once())->method('get')->with('account')->willReturn($accountConfigMock);

        $appStateMock = $this->getFakeMock(State::class)->setMethods(['getAreaCode'])->getMock();
        $appStateMock->expects($this->once())->method('getAreaCode')->willReturn('frontend');

        $eventManagerMock = $this->getFakeMock(ManagerInterface::class)->setMethods(['dispatch'])->getMock();
        $eventManagerMock->expects($this->once())->method('dispatch');

        $contextMock = $this->getFakeMock(Context::class)->setMethods(['getAppState', 'getEventDispatcher'])->getMock();
        $contextMock->expects($this->once())->method('getAppState')->willReturn($appStateMock);
        $contextMock->expects($this->once())->method('getEventDispatcher')->willReturn($eventManagerMock);

        $scopeConfigMock = $this->getFakeMock(ScopeConfigInterface::class)
            ->setMethods(['getValue'])
            ->getMockForAbstractClass();
        $scopeConfigMock->expects($this->exactly(5))->method('getValue')
            ->withConsecutive(
                ['payment/tig_buckaroo_test/limit_by_ip', ScopeInterface::SCOPE_STORE, null],
                ['payment/tig_buckaroo_test/max_amount', ScopeInterface::SCOPE_STORE, null],
                ['payment/tig_buckaroo_test/min_amount', ScopeInterface::SCOPE_STORE, null],
                ['payment/tig_buckaroo_test/allowed_currencies', ScopeInterface::SCOPE_STORE, null],
                ['payment/tig_buckaroo_test/active', ScopeInterface::SCOPE_STORE, null]
            )
            ->willReturnOnConsecutiveCalls(0, 20, 10, 'EUR,USD', true);

        $instance = $this->getInstance([
            'context' => $contextMock,
            'scopeConfig' => $scopeConfigMock,
            'configProviderFactory' => $configProviderMock
        ]);
        $result = $instance->isAvailable($quoteMock);
        $this->assertTrue($result);
    }

    public function testCanRefundParentFalse()
    {
        /**
         * @noinspection PhpUndefinedMethodInspection
         */
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
        /**
         * @noinspection PhpUndefinedMethodInspection
         */
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
     * @dataProvider processInvalidArgumentDataProvider
     *
     * @param $method
     */
    public function testProcessInvalidArgument($method)
    {
        $this->setExpectedException('\InvalidArgumentException');

        $payment = \Mockery::mock(\Magento\Payment\Model\InfoInterface::class);
        /**
         * @var \Magento\Payment\Model\InfoInterface $payment
         */

        $this->object->$method($payment, 0);
    }

    public function processInvalidArgumentDataProvider()
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
     * @dataProvider cantProcessDataProvider
     */
    public function testCantProcess($method, $canMethod)
    {
        $this->markTestSkipped('Check why dataset #4 causes issues with void() sometimes');

        $this->setExpectedException(\Magento\Framework\Exception\LocalizedException::class);
        $mockClass = \Magento\Payment\Model\InfoInterface::class
            . ','
            . \Magento\Sales\Api\Data\OrderPaymentInterface::class;

        $payment = \Mockery::mock($mockClass);
        /**
         * @var \Magento\Payment\Model\InfoInterface $payment
         */

        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        $this->object->$canMethod(false);
        $this->object->$method($payment, 0);
    }

    public function cantProcessDataProvider()
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

        $configProviderFactoryMock = $this->getFakeMock(MethodFactory::class)->setMethods(['has'])->getMock();
        $configProviderFactoryMock->expects($this->once())->method('has')->with($method)->willReturn(false);

        $scopeConfigMock = $this->getFakeMock(ScopeConfigInterface::class)
            ->setMethods(['getValue'])
            ->getMockForAbstractClass();
        $scopeConfigMock->expects($this->once())->method('getValue')
            ->with('payment/tig_buckaroo_test/title', ScopeInterface::SCOPE_STORE, null)
            ->willReturn($expectedTitle);

        $instance = $this->getInstance([
            'configProviderMethodFactory' => $configProviderFactoryMock,
            'scopeConfig' => $scopeConfigMock
        ]);
        $instance->buckarooPaymentMethodCode = $method;

        $result = $instance->getTitle();
        $this->assertEquals($expectedTitle, $result);
    }

    /**
     * @dataProvider getTitleNoPaymentFeeDataProvider
     *
     * @param $title
     * @param $expectedTitle
     * @param $fee
     */
    public function testGetTitlePaymentFee($title, $expectedTitle, $fee)
    {
        $method = 'tig_buckaroo_abstract_test';

        $configProviderFactoryMock = $this->getFakeMock(MethodFactory::class)
            ->setMethods(['has', 'get', 'getPaymentFee'])
            ->getMock();
        $configProviderFactoryMock->expects($this->once())->method('has')->with($method)->willReturn(true);
        $configProviderFactoryMock->expects($this->once())->method('get')->with($method)->willReturnSelf();
        $configProviderFactoryMock->expects($this->once())->method('getPaymentFee')->willReturn($fee);

        $priceHelperMock = $this->getFakeMock(PriceHelperData::class)->setMethods(['currency'])->getMock();
        $priceHelperMock->method('currency')->with($fee, true, false)->willReturn($fee);

        $scopeConfigMock = $this->getFakeMock(ScopeConfigInterface::class)
            ->setMethods(['getValue'])
            ->getMockForAbstractClass();
        $scopeConfigMock->expects($this->once())->method('getValue')
            ->with('payment/tig_buckaroo_test/title', ScopeInterface::SCOPE_STORE, null)
            ->willReturn($title);

        $instance = $this->getInstance([
            'configProviderMethodFactory' => $configProviderFactoryMock,
            'priceHelper' => $priceHelperMock,
            'scopeConfig' => $scopeConfigMock
        ]);
        $instance->buckarooPaymentMethodCode = $method;

        $result = $instance->getTitle();
        $this->assertEquals($expectedTitle, $result);
    }

    public function getTitleNoPaymentFeeDataProvider()
    {
        return [
            'no fee' => [
                'tig_buckaroo_abstract_test_title',
                'tig_buckaroo_abstract_test_title',
                false,
            ],
            'fixed fee' => [
                'tig_buckaroo_abstract_test_title',
                'tig_buckaroo_abstract_test_title + 5.00',
                '5.00',
            ],
            'percentage fee' => [
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
     * @dataProvider transactionBuilderFalseTrueDataProvider
     */
    public function testTransactionBuilderFalse($method, $setCanMethod, $methodTransactionBuilder, $canMethod = false)
    {
        $this->markTestSkipped();
        
        $this->setExpectedException(\LogicException::class);
        $mockClass = \Magento\Payment\Model\InfoInterface::class
            . ','
            . \Magento\Sales\Api\Data\OrderPaymentInterface::class;
        $payment = \Mockery::mock($mockClass);
        /**
         * @var \Magento\Payment\Model\InfoInterface $payment
         */

        $stubbedMethods = [$method, $setCanMethod, $methodTransactionBuilder];

        if ($canMethod) {
            $stubbedMethods[] = $canMethod;
        }

        $partialMock = $this->getPartialObject(
            AbstractMethodMock::class,
            [
                'objectManager' => $this->objectManager,
                'configProviderFactory' => $this->configProvider,
                'configProviderMethodFactory' => $this->configMethodProvider,
            ],
            $stubbedMethods
        );

        /**
         * @noinspection PhpUndefinedMethodInspection
         */
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

        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        $partialMock->$method($payment, 0);

        /**
         * @noinspection PhpUndefinedFieldInspection
         */
        $this->assertSame($payment, $partialMock->payment);
    }

    public function transactionBuilderFalseTrueDataProvider()
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
     * @dataProvider transactionBuilderFalseTrueDataProvider
     */
    public function testTransactionBuilderTrue($method, $setCanMethod, $methodTransactionBuilder, $canMethod = false)
    {
        $this->markTestSkipped();
        
        $mockClass = \Magento\Payment\Model\InfoInterface::class
            . ','
            . \Magento\Sales\Api\Data\OrderPaymentInterface::class;
        $payment = \Mockery::mock($mockClass);
        /**
         * @var \Magento\Payment\Model\InfoInterface $payment
         */

        $stubbedMethods = [$method, $setCanMethod, $methodTransactionBuilder];

        if ($canMethod) {
            $stubbedMethods[] = $canMethod;
        }

        $partialMock = $this->getPartialObject(
            AbstractMethodMock::class,
            [
                'objectManager' => $this->objectManager,
                'configProviderFactory' => $this->configProvider,
                'configProviderMethodFactory' => $this->configMethodProvider,
            ],
            $stubbedMethods
        );

        /**
         * @noinspection PhpUndefinedMethodInspection
         */
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

        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        $this->assertEquals($partialMock, $partialMock->$method($payment, 0));

        /** @noinspection PhpUndefinedFieldInspection */
        $this->assertSame($payment, $partialMock->payment);
    }

    /**
     * @param      $method
     * @param      $setCanMethod
     * @param      $methodTransactionBuilder
     * @param      $methodTransaction
     * @param      $afterMethodEvent
     *
     * @param bool $canMethod
     *
     * @param bool $closeTransaction
     *
     * @param bool $saveId
     *
     * @dataProvider fullFlowDataProvider
     */
    public function testFullFlow(
        $method,
        $setCanMethod,
        $methodTransactionBuilder,
        $methodTransaction,
        $afterMethodEvent,
        $canMethod = false,
        $closeTransaction = true,
        $saveId = true
    ) {
        $this->markTestSkipped('Check why dataset #4 causes issues with saveTransactionData() sometimes');

        $amount = 0;

        $responseObject = new \stdClass();
        $response = [$responseObject];

        $mockClass = \Magento\Payment\Model\InfoInterface::class
            . ','
            . \Magento\Sales\Api\Data\OrderPaymentInterface::class;
        $payment = \Mockery::mock($mockClass);
        /**
         * @var \Magento\Payment\Model\InfoInterface $payment
         */

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

        $stubbedMethods = [$methodTransaction, 'saveTransactionData', $methodTransactionBuilder];

        if ($canMethod) {
            $stubbedMethods[] = $canMethod;
        }

        $eventManagerMock = \Mockery::mock(ManagerInterface::class);
        $eventManagerMock->shouldReceive('dispatch')->once()->with(
            $afterMethodEvent,
            [
                'payment' => $payment,
                'response' => $response
            ]
        );

        $partialMock = $this->getPartialObject(
            AbstractMethodMock::class,
            [
                'objectManager' => $this->objectManager,
                'configProviderFactory' => $this->configProvider,
                'configProviderMethodFactory' => $this->configMethodProvider,
                'registry' => $registryMock
            ],
            $stubbedMethods
        );

        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        $partialMock->setEventManager($eventManagerMock);

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

        if ($canMethod) {
            $partialMock->expects($this->any())
                ->method($canMethod)
                ->withAnyParameters()
                ->willReturn(true);
        }

        if ($method == 'authorize') {
            $this->configMethodProvider->shouldReceive('get')->withAnyArgs()->andReturnSelf();
            $this->configMethodProvider->shouldReceive('getAllowedCurrencies')->once()->withAnyArgs()->andReturnSelf();

            /**
             * @noinspection PhpUndefinedMethodInspection
             */
            $payment->shouldReceive('getCurrencyCode')->once()->andReturn(false);
            /**
             * @noinspection PhpUndefinedMethodInspection
             */
            $payment->shouldReceive('setIsFraudDetected')->once()->with(false);
        }

        $result = $partialMock->$method($payment, $amount);

        $this->assertEquals($partialMock, $result);

        /**
         * @noinspection PhpUndefinedFieldInspection
         */
        $this->assertSame($payment, $partialMock->payment);
    }

    public function fullFlowDataProvider()
    {
        return [
            [
                'order',
                'setCanOrder',
                'getOrderTransactionBuilder',
                'orderTransaction',
                'tig_buckaroo_method_order_after',
                false,
                'closeOrderTransaction',
            ],
            [
                'authorize',
                'setCanAuthorize',
                'getAuthorizeTransactionBuilder',
                'authorizeTransaction',
                'tig_buckaroo_method_authorize_after',
                false,
                'closeAuthorizeTransaction',
            ],
            [
                'capture',
                'setCanCapture',
                'getCaptureTransactionBuilder',
                'captureTransaction',
                'tig_buckaroo_method_capture_after',
                false,
                'closeCaptureTransaction',
            ],
            [
                'refund',
                'setCanRefund',
                'getRefundTransactionBuilder',
                'refundTransaction',
                'tig_buckaroo_method_refund_after',
                'canRefund',
                'closeRefundTransaction',
                false,
            ],
            [
                'void',
                'setCanVoid',
                'getVoidTransactionBuilder',
                'VoidTransaction',
                'tig_buckaroo_method_void_after',
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
     * @dataProvider processTransactionDataProvider
     */
    public function testProcessTransactionResponseNotValid($method, $gatewayMethod)
    {
        $this->setExpectedException(\TIG\Buckaroo\Exception::class);

        $response = [];

        $transactionMock = \Mockery::mock(\TIG\Buckaroo\Gateway\Http\Transaction::class);
        /**
         * @var \TIG\Buckaroo\Gateway\Http\Transaction $transactionMock
         */

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

    public function processTransactionDataProvider()
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
     * @dataProvider processTransactionDataProvider
     */
    public function testProcessTransactionResponseStatusNotValid($method, $gatewayMethod)
    {
        $this->setExpectedException(\TIG\Buckaroo\Exception::class);

        $response = [];

        $transactionMock = \Mockery::mock(\TIG\Buckaroo\Gateway\Http\Transaction::class);
        /**
         * @var \TIG\Buckaroo\Gateway\Http\Transaction $transactionMock
         */

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
     * @dataProvider processTransactionDataProvider
     */
    public function testProcessTransactionSuccessful($method, $gatewayMethod)
    {
        $response = ['test_response'];

        $transactionMock = \Mockery::mock(\TIG\Buckaroo\Gateway\Http\Transaction::class);
        /**
         * @var \TIG\Buckaroo\Gateway\Http\Transaction $transactionMock
         */

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

    public function testCancel()
    {
        $this->markTestSkipped();
              
        $mockClass = \Magento\Payment\Model\InfoInterface::class
            . ','
            . \Magento\Sales\Api\Data\OrderPaymentInterface::class;
        $payment = \Mockery::mock($mockClass);
        /**
         * @var \Magento\Payment\Model\InfoInterface $payment
         */

        $partialMock = $this->getPartialObject(
            AbstractMethodMock::class,
            [],
            ['void', 'cancel']
        );

        $partialMock->expects($this->once())->method('void')->with($payment)->willReturnSelf();

        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        $this->assertSame($partialMock, $partialMock->cancel($payment));
    }

    public function testGetTransactionAdditionalInfo()
    {
        $data = [];
        $this->helper->shouldReceive('getTransactionAdditionalInfo')->once()->with($data)->andReturn([]);
        $result = $this->object->getTransactionAdditionalInfo($data);
        $this->assertInternalType('array', $result);
    }

    public function testSaveTransactionDataResponseKeyEmpty()
    {
        $response = new \stdClass();
        $mockClass = \Magento\Payment\Model\InfoInterface::class
            . ','
            . \Magento\Sales\Api\Data\OrderPaymentInterface::class;
        $payment = \Mockery::mock($mockClass);
        /**
         * @var \Magento\Payment\Model\InfoInterface $payment
         */

        $result = $this->object->saveTransactionData($response, $payment, true, false);
        $this->assertEquals($payment, $result);
    }

    /**
     * @param $close
     * @param $saveId
     *
     * @dataProvider saveTransactionDataDataProvider
     */
    public function testSaveTransactionData($close, $saveId)
    {
        $this->markTestSkipped();
        
        $response = new \stdClass();
        $key = 'test_transaction_key';
        $response->Key = $key;

        $arrayResponse = json_decode(json_encode($response), true);
        $mockClass = \Magento\Payment\Model\InfoInterface::class
            . ','
            . \Magento\Sales\Api\Data\OrderPaymentInterface::class;
        $payment = \Mockery::mock($mockClass);

        $payment->shouldReceive('setTransactionAdditionalInfo')
            ->once()
            ->with(
                \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS,
                $arrayResponse
            );

        $payment->shouldReceive('setIsTransactionClosed')->once()->with($close);
        $payment->shouldReceive('setTransactionId')->once()->with($key);

        if ($saveId) {
            $payment->shouldReceive('setAdditionalInformation')
                ->once()
                ->with(AbstractMethodMock::BUCKAROO_ORIGINAL_TRANSACTION_KEY_KEY, $key);
        }
        /**
         * @var \Magento\Payment\Model\InfoInterface $payment
         */

        $partialMock = $this->getPartialObject(
            AbstractMethodMock::class,
            [],
            ['getTransactionAdditionalInfo','saveTransactionData']
        );

        $partialMock->expects($this->once())
            ->method('getTransactionAdditionalInfo')
            ->with($arrayResponse)
            ->willReturn($arrayResponse);

        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        $partialMock->saveTransactionData($response, $payment, $close, $saveId);
    }

    public function saveTransactionDataDataProvider()
    {
        return [
            [
                true,
                true,
            ],
            [
                true,
                false,
            ],
            [
                false,
                true,
            ],
            [
                false,
                false,
            ],
        ];
    }

    public function testAddExtraFields()
    {
        $testCode = 'testCode';
        $testValue = 'testValue';

        $params = [
            'creditmemo' => [
                $testCode => $testValue
            ],
        ];

        $extraFields = [
            [
                'code' => $testCode,
            ],
        ];

        $expectedServices = [
            'RequestParameter' => [
                [
                    '_' => $testValue,
                    'Name' => $testCode,
                ],
            ],
        ];

        $this->request->shouldReceive('getParams')->once()->andReturn($params);
        $this->refundFieldsFactory->shouldReceive('get')
            ->with($this->object->getCode())
            ->once()
            ->andReturn($extraFields);

        $this->assertEquals($expectedServices, $this->object->addExtraFields($this->object->getCode()));
    }

    public function testCreateCreditNoteRequest()
    {
        $infoInstanceMock = $this->getFakeMock(Payment::class)
            ->setMethods(['getAdditionalInformation', 'setAdditionalInformation'])
            ->getMock();
        $infoInstanceMock->expects($this->exactly(3))
            ->method('getAdditionalInformation')
            ->withConsecutive(
                ['buckaroo_cm3_invoice_key'],
                [AbstractMethodMock::BUCKAROO_ORIGINAL_TRANSACTION_KEY_KEY],
                ['buckaroo_failed_authorize']
            )
            ->willReturnOnConsecutiveCalls('abc', 'def', 1);
        $infoInstanceMock->expects($this->once())
            ->method('setAdditionalInformation')
            ->with(AbstractMethodMock::BUCKAROO_ORIGINAL_TRANSACTION_KEY_KEY, 'def');

        $result = $this->object->createCreditNoteRequest($infoInstanceMock);
        $this->assertInstanceOf(AbstractMethodMock::class, $result);
    }
}
