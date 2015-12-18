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
namespace TIG\Buckaroo\Test\Unit\Observer;

use Magento\Framework\Event\Observer;
use Mockery as m;
use TIG\Buckaroo\Test\BaseTest;
use TIG\Buckaroo\Observer\SetBuckarooFeeToRefund;

class SetBuckarooFeeToRefundTest extends BaseTest
{
    /**
     * @var SetBuckarooFeeToRefund
     */
    protected $object;

    /**
     * @var m\MockInterface|Observer
     */
    protected $observer;

    /**
     * @var m\MockInterface|Observer
     */
    protected $creditmemo;

    /**
     * Setup the basic mock object.
     */
    public function setUp()
    {
        parent::setUp();

        $this->object = $this->objectManagerHelper->getObject(SetBuckarooFeeToRefund::class);
        $this->observer = m::mock(Observer::class);

        $this->creditmemo = m::mock();
        $this->observer->shouldReceive('getEvent')->twice()->andReturnSelf();
        $this->observer->shouldReceive('getCreditmemo')->once()->andReturn($this->creditmemo);
    }

    /**
     * Test the happy path. No Buckaroo Payment Fee
     */
    public function testInvoiceRegisterHappyPath()
    {
        $this->observer->shouldReceive('getInput')->once()->andReturnNull();

        $this->object->execute($this->observer);
    }

    /**
     * Test the flow when there are reward points.
     */
    public function testInvoiceRegisterWithRewardPoints()
    {
        $this->observer->shouldReceive('getInput')->once()->andReturn([
            'refund_reward_points' => 30,
            'refund_reward_points_enable' => true,
        ]);

        $this->creditmemo->shouldReceive('getBuckarooFee')->andReturn(10);
        $this->creditmemo->shouldReceive('setBuckarooFee')->once()->with(10);

        $this->object->execute($this->observer);
    }
}