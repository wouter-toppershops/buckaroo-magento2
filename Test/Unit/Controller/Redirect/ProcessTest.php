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
namespace TIG\Buckaroo\Test\Unit\Controller\Redirect;

use Mockery as m;
use TIG\Buckaroo\Test\BaseTest;
use TIG\Buckaroo\Controller\Redirect\Process;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;

class ProcessTest extends BaseTest
{
    /**
     * @var Process
     */
    protected $controller;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var m\MockInterface
     */
    protected $request;

    public function setUp()
    {
        parent::setUp();

        $this->request = m::mock(RequestInterface::class);
        $this->context = $this->objectManagerHelper->getObject(Context::class, [
            'request' => $this->request
        ]);

        $this->controller = $this->objectManagerHelper->getObject(Process::class, [
            'context' => $this->context
        ]);
    }

    public function testExecute()
    {
        $this->request->shouldReceive('getParams')->andReturn(['brq_ordernumber' => null, 'brq_statuscode' => null]);

        $this->controller->execute();
    }
}