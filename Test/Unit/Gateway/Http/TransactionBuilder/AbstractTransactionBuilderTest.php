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
 * @copyright Copyright (c) 2016 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license   http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
namespace TIG\Buckaroo\Test\Unit\Gateway\Http\TransactionBuilder;

use TIG\Buckaroo\Test\BaseTest;

class AbstractTransactionBuilderTest extends BaseTest
{
    /**
     * @var \TIG\Buckaroo\Gateway\Http\TransactionBuilder\AbstractTransactionBuilderMock
     */
    protected $object;

    /**
     * @var \TIG\Buckaroo\Model\ConfigProvider\Factory|\Mockery\MockInterface
     */
    protected $configProvider;

    public function setUp()
    {
        parent::setUp();

        $this->configProvider = \Mockery::mock(\TIG\Buckaroo\Model\ConfigProvider\Factory::class);

        $this->object = $this->objectManagerHelper
            ->getObject(AbstractTransactionBuilderMock::class, ['configProviderFactory' => $this->configProvider]);
    }

    public function testOriginalTransactionKey()
    {
        $value = 'testString';
        $this->object->setOriginalTransactionKey($value);

        $this->assertEquals($value, $this->object->getOriginalTransactionKey());
    }

    public function testChannel()
    {
        $value = 'testString';
        $this->object->setChannel($value);

        $this->assertEquals($value, $this->object->getChannel());
    }

    public function testAmount()
    {
        $value = 'testString';
        $this->object->setAmount($value);

        $this->assertEquals($value, $this->object->getAmount());
    }

    public function testInvoiceId()
    {
        $value = 'testString';
        $this->object->setInvoiceId($value);

        $this->assertEquals($value, $this->object->getInvoiceId());
    }

    public function testCurrency()
    {
        $value = 'testString';
        $this->object->setCurrency($value);

        $this->assertEquals($value, $this->object->getCurrency());
    }

    public function testOrder()
    {
        $value = 'testString';
        $this->object->setOrder($value);

        $this->assertEquals($value, $this->object->getOrder());
    }

    public function testServices()
    {
        $value = 'testString';
        $this->object->setServices($value);

        $this->assertEquals($value, $this->object->getServices());
    }

    public function testCustomVars()
    {
        $value = 'testString';
        $this->object->setCustomVars($value);

        $this->assertEquals($value, $this->object->getCustomVars());
    }

    public function testMethod()
    {
        $value = 'testString';
        $this->object->setMethod($value);

        $this->assertEquals($value, $this->object->getMethod());
    }

    public function testType()
    {
        $value = 'testString';
        $this->object->setType($value);

        $this->assertEquals($value, $this->object->getType());
    }

    public function testGetHeaders()
    {
        $merchantKey = uniqid();

        $account = \Mockery::mock('\TIG\Buckaroo\Model\ConfigProvider\Account');
        $account->shouldReceive('getMerchantKey')->once()->andReturn($merchantKey);
        $this->configProvider->shouldReceive('get')->once()->with('account')->andReturn($account);

        $order = \Mockery::mock(\Magento\Sales\Model\Order::class);
        $order->shouldReceive('getStore')->once();

        $this->object->setOrder($order);

        $result = $this->object->GetHeaders();

        $this->assertCount(2, $result);
        $this->assertEquals('https://checkout.buckaroo.nl/PaymentEngine/', $result[0]->namespace);
        $this->assertEquals($merchantKey, $result[0]->data['WebsiteKey']);

        foreach ($result as $header) {
            $this->assertInstanceOf(\SoapHeader::class, $header);
        }
    }
}
