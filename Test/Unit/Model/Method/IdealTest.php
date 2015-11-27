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

class IdealTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \TIG\Buckaroo\Model\Method\Ideal
     */
    protected $_object;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManagerHelper;

    public function testCapture()
    {
        $this->_objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $transactionMock = m::mock('TIG\Buckaroo\Gateway\Http\Transaction');
        $transactionMock->shouldReceive('setMethod')->andReturnSelf();

        $gatewayMock = m::mock('TIG\Buckaroo\Gateway\Http\Bpe3');
        $gatewayMock->shouldReceive('capture')->once()->with($transactionMock)->andReturnSelf();

        $this->_object = $this->_objectManagerHelper->getObject(
            'TIG\Buckaroo\Model\Method\Ideal',
            [
                'transaction' => $transactionMock,
                'gateway' => $gatewayMock,
            ]
        );

        $this->_objectManagerHelper = new ObjectManager($this);
        /** @var  $paymentInfoMock \Magento\Payment\Model\InfoInterface */
        $paymentInfoMock = $this->_objectManagerHelper->getObject(
            'Magento\Payment\Model\Info'
        );

        $this->assertInstanceOf('\TIG\Buckaroo\Model\Method\Ideal', $this->_object->capture($paymentInfoMock, 1));
    }

    public function testAuthorize()
    {
        $this->_objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $transactionMock = m::mock('TIG\Buckaroo\Gateway\Http\Transaction');
        $transactionMock->shouldReceive('setMethod')->andReturnSelf();

        $gatewayMock = m::mock('TIG\Buckaroo\Gateway\Http\Bpe3');
        $gatewayMock->shouldReceive('authorize')->once()->with($transactionMock)->andReturnSelf();

        $this->_object = $this->_objectManagerHelper->getObject(
            'TIG\Buckaroo\Model\Method\Ideal',
            [
                'transaction' => $transactionMock,
                'gateway' => $gatewayMock,
            ]
        );

        $this->_objectManagerHelper = new ObjectManager($this);
        /** @var  $paymentInfoMock \Magento\Payment\Model\InfoInterface */
        $paymentInfoMock = $this->_objectManagerHelper->getObject(
            'Magento\Payment\Model\Info'
        );

        $this->assertInstanceOf('\TIG\Buckaroo\Model\Method\Ideal', $this->_object->authorize($paymentInfoMock, 1));
    }

    public function testRefund()
    {
        $this->_objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $transactionMock = m::mock('TIG\Buckaroo\Gateway\Http\Transaction');
        $transactionMock->shouldReceive('setMethod')->andReturnSelf();

        $gatewayMock = m::mock('TIG\Buckaroo\Gateway\Http\Bpe3');
        $gatewayMock->shouldReceive('refund')->once()->with($transactionMock)->andReturnSelf();

        $this->_object = $this->_objectManagerHelper->getObject(
            'TIG\Buckaroo\Model\Method\Ideal',
            [
                'transaction' => $transactionMock,
                'gateway' => $gatewayMock,
            ]
        );

        $this->_objectManagerHelper = new ObjectManager($this);
        /** @var  $paymentInfoMock \Magento\Payment\Model\InfoInterface */
        $paymentInfoMock = $this->_objectManagerHelper->getObject(
            'Magento\Payment\Model\Info'
        );

        $this->assertInstanceOf('\TIG\Buckaroo\Model\Method\Ideal', $this->_object->refund($paymentInfoMock, 1));
    }

    public function tearDown()
    {
        m::close();
    }
}