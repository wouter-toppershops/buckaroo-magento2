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
 * @copyright Copyright (c) 2015 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license   http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
namespace TIG\Buckaroo\Test\Unit\Model\Config\Backend;

class CertificateTest extends \TIG\Buckaroo\Test\BaseTest
{
    /**
     * @var \Mockery\MockInterface|\Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Mockery\MockInterface|\Magento\Framework\Filesystem\File\ReadFactory
     */
    protected $scopeConfig;

    /**
     * @var \Mockery\MockInterface|\Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $readFactory;

    /**
     * @var \TIG\Buckaroo\Model\Config\Backend\Certificate
     */
    protected $object;

    protected $uploadFixture = [
        'name' => 'validfilename.pem',
        'tmp_name' => 'asdfkljasdljasfjldi',
        'content' => 'adef',
        'label' => 'certificatelabel',
    ];

    /**
     * Setup the base mocks.
     */
    public function setUp()
    {
        parent::setUp();

        $this->objectManager = \Mockery::mock(\Magento\Framework\ObjectManagerInterface::class);
        $this->readFactory = \Mockery::mock(\Magento\Framework\Filesystem\File\ReadFactory::class);
        $this->scopeConfig = \Mockery::mock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $this->object = $this->objectManagerHelper->getObject(
            \TIG\Buckaroo\Model\Config\Backend\Certificate::class, [
            'objectManager' => $this->objectManager,
            'readFactory' => $this->readFactory,
            'scopeConfig' => $this->scopeConfig,
            ]
        );
    }

    /**
     * Test with no value.
     *
     * @throws \Exception
     */
    public function testNoValue()
    {
        $this->assertInstanceOf(\TIG\Buckaroo\Model\Config\Backend\Certificate::class, $this->object->save());
    }

    /**
     * Test the function with an invalid file extension
     */
    public function testWrongFileType()
    {
        $this->object->setData('fieldset_data', ['certificate_upload'=>['name'=>'wrongfilename.abc']]);

        try {
            $this->object->save();
            $this->fail();
        } catch (\Exception $e) {
            $this->assertNotFalse('Disallowed file type.', $e->getMessage());
            $this->assertInstanceOf(\Magento\Framework\Exception\LocalizedException::class, $e);
        }
    }

    /**
     * Test the path without a filename.
     */
    public function testMissingName()
    {
        $this->object->setData('fieldset_data', ['certificate_upload'=>['name'=>'validfilename.pem']]);

        try {
            $this->object->save();
            $this->fail();
        } catch (\Exception $e) {
            $this->assertEquals('Enter a name for the certificate.', $e->getMessage());
            $this->assertInstanceOf(\Magento\Framework\Exception\LocalizedException::class, $e);
        }
    }

    protected function uploadMock()
    {
        $certificateModel = \Mockery::mock('TIG\Buckaroo\Model\Certificate')->makePartial();
        $certificateModel->shouldReceive('setCertificate')->with($this->uploadFixture['content']);
        $certificateModel->shouldReceive('setName')->once()->with($this->uploadFixture['label']);
        $certificateModel->shouldReceive('save')->once();

        $this->objectManager
            ->shouldReceive('create')
            ->once()
            ->with('TIG\Buckaroo\Model\Certificate')
            ->andReturn($certificateModel);

        $this->readFactory
            ->shouldReceive('create')
            ->with($this->uploadFixture['tmp_name'], \Magento\Framework\Filesystem\DriverPool::FILE)
            ->andReturnSelf();
        $this->readFactory->shouldReceive('readAll')->andReturn($this->uploadFixture['content']);
    }

    public function testCertificateUpload()
    {
        $this->markTestSkipped('Needs revision');

        $this->object->setData(
            'fieldset_data', [
            'certificate_upload'=> $this->uploadFixture,
            'certificate_label' => $this->uploadFixture['label'],
            ]
        );

        $this->uploadMock();

        $this->objectManager
            ->shouldReceive('get')
            ->with('\Magento\Framework\App\Config\Storage\WriterInterface')
            ->andReturnSelf();

        $this->objectManager->shouldReceive('save')->once();

        $this->object->save();
    }
}
