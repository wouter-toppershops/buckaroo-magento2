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
namespace TIG\Buckaroo\Test\Unit\Model\ConfigProvider\Method;

class TransferTest extends \TIG\Buckaroo\Test\BaseTest
{
    /**
     * @var \TIG\Buckaroo\Model\ConfigProvider\Method\Transfer
     */
    protected $object;

    /**
     * @var \Mockery\MockInterface
     */
    protected $scopeConfig;

    /**
     * Setup our dependencies
     */
    public function setUp()
    {
        parent::setUp();

        $this->scopeConfig = \Mockery::mock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->object = $this->objectManagerHelper->getObject(\TIG\Buckaroo\Model\ConfigProvider\Method\Transfer::class, [
            'scopeConfig' => $this->scopeConfig,
        ]);
    }

    /**
     * Helper function to set the return value fromt the getValue method.
     *
     * @param $value
     *
     * @return $this
     */
    protected function paymentFeeConfig($value)
    {
        $this->scopeConfig
            ->shouldReceive('getValue')
            ->with(
                \TIG\Buckaroo\Model\ConfigProvider\Method\Transfer::XPATH_TRANSFER_PAYMENT_FEE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ->andReturn($value);

        return $this;
    }

    /**
     * Test what happens when the payment method is disabled.
     */
    public function testInactive()
    {
        $this->scopeConfig->shouldReceive('getValue')->andReturn(false);

        $this->assertEquals([], $this->object->getConfig());
    }

    /**
     * Test that the config returns the right values
     */
    public function testGetConfig()
    {
        $sendEmail = '1';
        $this->scopeConfig->shouldReceive('getValue')->with('payment/tig_buckaroo_transfer/active')->andReturn(true);
        $this->scopeConfig->shouldReceive('getValue')->with('payment/tig_buckaroo_transfer/send_email')->andReturn($sendEmail);
        $this->scopeConfig->shouldReceive('getValue')->andReturn(false);

        $result = $this->object->getConfig();

        $this->assertTrue(array_key_exists('payment', $result));
        $this->assertTrue(array_key_exists('buckaroo', $result['payment']));
        $this->assertTrue(array_key_exists('transfer', $result['payment']['buckaroo']));
        $this->assertEquals($sendEmail, $result['payment']['buckaroo']['transfer']['sendEmail']);
    }

    /**
     * Test what is returned by the getPaymentFee method with a value of 10
     */
    public function testGetPaymentFee()
    {
        $value = '10';
        $this->paymentFeeConfig($value);

        $this->assertEquals($value, $this->object->getPaymentFee());
    }

    /**
     * Test what is returned by the getPaymentFee when not set
     */
    public function testGetPaymentFeeNull()
    {
        $value = null;
        $this->paymentFeeConfig($value);

        $this->assertFalse($this->object->getPaymentFee());
    }

    /**
     * Test what is returned by the getPaymentFee method when it is negative
     */
    public function testGetPaymentFeeNegative()
    {
        $value = '-10';
        $this->paymentFeeConfig($value);

        $this->assertEquals($value, $this->object->getPaymentFee());
    }

    /**
     * Test what is returned by the getPaymentFee method when the config value is empty
     */
    public function testGetPaymentFeeEmpty()
    {
        $value = '';
        $this->paymentFeeConfig($value);

        $this->assertFalse($this->object->getPaymentFee());
    }
}
