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
 * @copyright   Copyright (c) 2016 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
namespace TIG\Buckaroo\Test\Unit\Model\Quote\Total;

class BuckarooFeeTest extends \TIG\Buckaroo\Test\BaseTest
{
    /**
     * @var \TIG\Buckaroo\Model\Total\Quote\BuckarooFee
     */
    protected $object;

    /**
     * @var \Mockery\MockInterface
     */
    protected $debugger;

    /**
     * @var \Mockery\MockInterface
     */
    protected $order;

    /**
     * @var \Mockery\MockInterface
     */
    protected $objectManager;

    /**
     * @var \Mockery\MockInterface
     */
    protected $configProviderFactory;

    /**
     * @var \Mockery\MockInterface
     */
    protected $configProviderMethodFactory;

    /**
     * @var \Mockery\MockInterface
     */
    protected $priceCurrency;

    /**
     * Setup the base mock objects.
     */
    public function setUp()
    {
        parent::setUp();

        $this->priceCurrency = \Mockery::mock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);
        $this->objectManager = \Mockery::mock(\Magento\Framework\ObjectManagerInterface::class);
        $this->configProviderFactory = \Mockery::mock(\TIG\Buckaroo\Model\ConfigProvider\Factory::class);
        $this->configProviderMethodFactory = \Mockery::mock(\TIG\Buckaroo\Model\ConfigProvider\Method\Factory::class);

        $this->object = $this->objectManagerHelper->getObject(\TIG\Buckaroo\Model\Total\Quote\BuckarooFee::class, [
            'configProviderFactory' => $this->configProviderFactory,
            'configProviderMethodFactory' => $this->configProviderMethodFactory,
            'priceCurrency' => $this->priceCurrency,
        ]);
    }

    /**
     * @dataProvider baseFeePercentageDataProvider
     *
     * @param $paymentCode
     * @param $fee
     * @param $feeMode
     * @param $quoteMethod
     * @param $quoteAmount
     * @param $expectedValue
     */
    public function testGetBaseFeeCalculatesPercentageOnCorrectTotal(
        $paymentCode,
        $fee,
        $feeMode,
        $quoteMethod,
        $quoteAmount,
        $expectedValue
    ) {
        /** @var \TIG\Buckaroo\Model\Method\AbstractMethod $paymentMethod */
        $paymentMethod = \Mockery::mock(\TIG\Buckaroo\Model\Method\AbstractMethod::class);
        $paymentMethod->buckarooPaymentMethodCode = $paymentCode;

        $quote = \Mockery::mock(\Magento\Quote\Model\Quote::class);
        $quote->shouldReceive('getShippingAddress')->andReturnSelf();
        $quote->shouldReceive($quoteMethod)->andReturn($quoteAmount);
        /** @var \Magento\Quote\Model\Quote $quote */

        $this->configProviderMethodFactory->shouldReceive('has')->with($paymentCode)->andReturn(true);
        $this->configProviderMethodFactory->shouldReceive('get')->with($paymentCode)->andReturnSelf();
        $this->configProviderMethodFactory->shouldReceive('getPaymentFee')->atleast()->once()->andReturn($fee);

        $this->configProviderFactory->shouldReceive('get')->with('account')->andReturnSelf();
        $this->configProviderFactory->shouldReceive('getFeePercentageMode')->atleast()->once()->andReturn($feeMode);

        $this->assertEquals($expectedValue, $this->object->getBaseFee($paymentMethod, $quote));
    }

    public function baseFeePercentageDataProvider()
    {
        return [
            [
                'tig_buckaroo_ideal',
                '10%',
                'subtotal',
                'getBaseSubtotal',
                45.0000,
                4.5000
            ],
            [
                'tig_buckaroo_ideal',
                '9%',
                'subtotal_incl_tax',
                'getBaseSubtotalTotalInclTax',
                45.0000,
                4.0500
            ],
            [
                'tig_buckaroo_ideal',
                '2%',
                'grandtotal',
                'getBaseGrandTotal',
                45.0000,
                0.9000
            ],
        ];
    }
}
