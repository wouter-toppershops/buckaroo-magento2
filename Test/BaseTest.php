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
namespace TIG\Buckaroo\Test;

use Mockery as m;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class BaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    protected $object;

    public function setUp()
    {
        /** Require functions.php to be able to use the translate function */
        require_once __DIR__ .'/../../../../app/functions.php';

        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');

        $this->objectManagerHelper = new ObjectManager($this);
    }

    /**
     * Test the assignData method. In its root it is the same for every payment method.
     *
     * @param $fixture
     *
     * @return $this
     */
    public function assignDataTest($fixture)
    {
        if (!array_key_exists('buckaroo_skip_validation', $fixture)) {
            $fixture['buckaroo_skip_validation'] = false;
        }

        $data = \Mockery::mock(\Magento\Framework\DataObject::class);
        $infoInterface = \Mockery::mock(\Magento\Payment\Model\InfoInterface::class)->makePartial();

        foreach ($fixture as $key => $value) {
            $camelCase = preg_replace_callback(
                "/(?:^|_)([a-z])/",
                function ($matches) {
                    return strtoupper($matches[1]);
                },
                $key
            );

            $data->shouldReceive('get' . $camelCase)->andReturn($value);
            $infoInterface->shouldReceive('setAdditionalInformation')->with($key, $value);
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $this->object->setData('info_instance', $infoInterface);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertEquals($this->object, $this->object->assignData($data));

        return $this;
    }

    public function getPartialObject($object, $arguments = array(), $mockMethods = array())
    {
        $constructorArgs = $this->objectManagerHelper->getConstructArguments($object, $arguments);

        $mock = $this->getMock($object, $mockMethods, $constructorArgs);

        return $mock;
    }

    public function tearDown()
    {
        m::close();
    }
}
