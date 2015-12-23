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
namespace TIG\Buckaroo\Model\Config\Source;

class Certificates implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \TIG\Buckaroo\Model\CertificateFactory $certificateModel
     */
    protected $_modelCertificateFactory;

    /**
     * @param \TIG\Buckaroo\Model\CertificateFactory $modelCertificateFactory
     */
    public function __construct(
        \TIG\Buckaroo\Model\CertificateFactory $modelCertificateFactory
    ) {
        $this->_modelCertificateFactory = $modelCertificateFactory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $certificateData = $this->getCertificateData();

        $options = [];

        if (count($certificateData) <= 0) {
            $options[] = [
                'value' => '',
                'label' => __('You have not yet uploaded any certificate files')
            ];

            return $options;
        }

        $options[] = ['value' => '', 'label' => __('No certificate selected')];

        foreach ($certificateData as $index => $data) {
            $options[] = [
                'value' => $data['entity_id'],
                'label' => $data['name'] . ' (' . $data['created_at'] . ')'
            ];
        }

        return $options;
    }

    /**
     * Get a list of all stored certificates
     *
     * @return array
     */
    protected function getCertificateData()
    {
        $certificateModel = $this->_modelCertificateFactory->create();
        $certificateCollection = $certificateModel->getCollection();

        return $certificateCollection->getData();
    }
}