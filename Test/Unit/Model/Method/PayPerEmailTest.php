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
namespace TIG\Buckaroo\Test\Unit\Model\Method;

use Magento\Framework\DataObject;
use Magento\Payment\Model\InfoInterface;
use TIG\Buckaroo\Model\Method\PayPerEmail;
use TIG\Buckaroo\Test\BaseTest;

class PayPerEmailTest extends BaseTest
{
    protected $instanceClass = PayPerEmail::class;

    /**
     * @return array
     */
    public function assignDataProvider()
    {
        return [
            'no data' => [
                []
            ],
            'with skip validation data' => [
                [
                    'additional_data' => [
                        'buckaroo_skip_validation' => '1',
                    ]
                ]
            ],
            'with form data' => [
                [
                    'additional_data' => [
                        'customer_gender' => 'female',
                        'customer_billingFirstName' => 'TIG',
                        'customer_billingLastName' => 'TEST',
                        'customer_email' => '07/10/1990',
                    ]
                ]
            ],
        ];
    }

    /**
     * @param $data
     *
     * @dataProvider assignDataProvider
     */
    public function testAssignData($data)
    {
        $dataObject = $this->getObject(DataObject::class);
        $dataObject->addData($data);

        $instance = $this->getInstance();

        $infoInstanceMock = $this->getFakeMock(InfoInterface::class)->getMock();
        $instance->setData('info_instance', $infoInstanceMock);

        $result = $instance->assignData($dataObject);
        $this->assertInstanceOf(PayPerEmail::class, $result);
    }
}
