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
namespace TIG\Buckaroo\Model\Config\Backend;

class Certificate extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Filesystem\File\ReadFactory
     */
    protected $readFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\ObjectManagerInterface               $objectManager
     * @param \Magento\Framework\Model\Context                        $context
     * @param \Magento\Framework\Registry                             $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface      $config
     * @param \Magento\Framework\App\Cache\TypeListInterface          $cacheTypeList
     * @param \Magento\Framework\Filesystem\File\ReadFactory          $readFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb           $resourceCollection
     * @param array                                                   $data
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Filesystem\File\ReadFactory $readFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);

        $this->readFactory = $readFactory;
        $this->objectManager = $objectManager;
        $this->scopeConfig = $config;
    }

    /**
     * Save the certificate
     *
     * @return $this
     * @throws \Exception
     */
    public function save()
    {
        //type == application/x-x509-ca-cert
        if (!empty($this->getFieldsetDataValue('certificate_upload')['name'])) {
            $certFile = $this->getFieldsetDataValue('certificate_upload');
            $certLabel = $this->getFieldsetDataValue('certificate_label');

            if (!$this->validExtension($certFile['name'])) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Disallowed file type.'));
            }

            if (strlen(trim($certLabel)) <= 0) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Enter a name for the certificate.'));
            }

            /**
             * Read the configuration contents
             */
            /**
             * @var \Magento\Framework\Filesystem\File\Read $read
             */
            $read = $this->readFactory->create($certFile['tmp_name'], \Magento\Framework\Filesystem\DriverPool::FILE);

            /**
             * @var \TIG\Buckaroo\Model\Certificate $certDB
             */
            $certDB = $this->objectManager->create('TIG\Buckaroo\Model\Certificate');
            $certDB->setCertificate($read->readAll());
            $certDB->setName($certLabel);
            $certDB->save();

            /**
             * Only update the selected certificate when there is a new certificate uploaded, and the user did not
             * change the selected value.
             */
            $oldValue = $this->scopeConfig->getValue(
                'tig_buckaroo/account/certificate_file',
                $this->getScope(),
                $this->getScopeId()
            );
            $newValue = $this->getFieldsetDataValue('certificate_file');

            if ($oldValue == $newValue) {
                /**
                 * Set the current configuration value to this new uploaded certificate.
                 */
                $this->objectManager
                    ->get('\Magento\Framework\App\Config\Storage\WriterInterface')
                    ->save(
                        'tig_buckaroo/account/certificate_file',
                        $certDB->getId(),
                        $this->getScope() ? $this->getScope() : 'default',
                        $this->getScopeId()
                    );
            }
        }

        return $this;
    }

    /**
     * Check if extension is valid
     *
     * @param String $filename Name of uplpaded file
     *
     * @return bool
     */
    protected function validExtension($filename)
    {
        $allowedExtensions = ['pem'];

        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        return in_array(strtolower($extension), $allowedExtensions);
    }
}
