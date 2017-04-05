<?php
/**
 *
 *          ..::..
 *     ..::::::::::::..
 *   ::'''''':''::'''''::
 *   ::..  ..:  :  ....::
 *   ::::  :::  :  :   ::
 *   ::::  :::  :  ''' ::
 *   ::::..:::..::.....::
 *     ''::::::::::::''
 *          ''::''
 *
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
namespace TIG\Buckaroo\Test\Unit\Model;

use TIG\Buckaroo\Model\Invoice;
use TIG\Buckaroo\Model\InvoiceRepository;
use TIG\Buckaroo\Test\BaseTest;

class InvoiceRepositoryTest extends BaseTest
{
    protected $instanceClass = InvoiceRepository::class;

    public function testSave()
    {
        $invoiceMock = $this->getFakeMock(Invoice::class)->getMock();

        $instance = $this->getInstance();
        $result = $instance->save($invoiceMock);

        $this->assertEquals($invoiceMock, $result);
    }

    public function testDelete()
    {
        $invoiceMock = $this->getFakeMock(Invoice::class)->getMock();

        $instance = $this->getInstance();
        $result = $instance->delete($invoiceMock);

        $this->assertTrue($result);
    }
}
