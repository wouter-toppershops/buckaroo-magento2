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
 * @copyright Copyright (c) 2015 Total Internet Group B.V. (http://www.tig.nl)
 * @license   http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
namespace TIG\Buckaroo\Model\ConfigProvider;

use Magento\Checkout\Model\ConfigProviderInterface;
use TIG\Buckaroo\Api\CertificateRepositoryInterface;

class PrivateKey implements ConfigProviderInterface
{
    /**
     * Xpath to the 'certificate_upload' setting.
     */
    const XPATH_CERTIFICATE_ID = 'tig_buckaroo/account/certificate_file';

    /**
     * @var \TIG\Buckaroo\Model\Certificate
     */
    protected $certificate;

    /**
     * @param CertificateRepositoryInterface $certificateRepository
     * @param Account                        $account
     */
    public function __construct(
        CertificateRepositoryInterface $certificateRepository,
        Account $account
    ) {
        $certificateId = $account->getCertificateFile();

        if (!$certificateId) {
            throw new \LogicException('No Buckaroo certificate configured.');
        }

        $this->certificate = $certificateRepository->getById($certificateId);

        if (!$this->certificate->getId()) {
            throw new \LogicException('Invalid Buckaroo certificate configured.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig($store = null)
    {
        $config = [
            'private_key' => $this->getPrivateKey($store),
        ];
        return $config;
    }

    /**
     * Return private key from certificate
     *
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->certificate->getCertificate();
    }
}
