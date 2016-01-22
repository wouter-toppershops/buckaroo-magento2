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

use Mockery as m;
use TIG\Buckaroo\Test\BaseTest;
use TIG\Buckaroo\Model\ConfigProvider\Method\Creditcard;
use Magento\Framework\View\Asset\Repository;

class CreditcardTest extends BaseTest
{
    /**
     * @var Creditcard
     */
    protected $object;

    /**
     * @var m\MockInterface
     */
    protected $assetRepository;

    public function setUp()
    {
        parent::setUp();

        $this->assetRepository = m::mock(Repository::class);
        $this->object = $this->objectManagerHelper->getObject(Creditcard::class, [
            'assetRepo' => $this->assetRepository
        ]);
    }

    public function testGetImageUrl()
    {
        $shouldReceive = $this->assetRepository
            ->shouldReceive('getUrl')
            ->with(\Mockery::type('string'));

        $options = $this->object->getConfig();

        $shouldReceive->times(count($options['payment']['buckaroo']['creditcard']['cards']));

        $this->assertTrue(array_key_exists('payment', $options));
        $this->assertTrue(array_key_exists('buckaroo', $options['payment']));
        $this->assertTrue(array_key_exists('creditcard', $options['payment']['buckaroo']));
        $this->assertTrue(array_key_exists('cards', $options['payment']['buckaroo']['creditcard']));
    }
}
