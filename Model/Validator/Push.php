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

namespace TIG\Buckaroo\Model\Validator;

use \TIG\Buckaroo\Model\ValidatorInterface;

/**
 * Class Push
 *
 * @package TIG\Buckaroo\Model\Validator
 */
class Push implements ValidatorInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public $scopeConfig;

    /**
     * @var \TIG\Buckaroo\Model\ConfigProvider\Factory $configProviderFactory
     */
    public $configProviderFactory;

    /**
     * @var \TIG\Buckaroo\Helper\Data $helper
     */
    public $helper;

    /**
     * @var \TIG\Buckaroo\Debug\Debugger $debugger
     */
    public $debugger;

    public $bpeResponseMessages = [
        190 => 'Success',
        490 => 'Payment failure',
        491 => 'Validation error',
        492 => 'Technical error',
        690 => 'Payment rejected',
        790 => 'Waiting for user input',
        791 => 'Waiting for processor',
        792 => 'Waiting on consumer action',
        793 => 'Payment on hold',
        890 => 'Cancelled by consumer',
        891 => 'Cancelled by merchant'
    ];

    /**
     * @param \TIG\Buckaroo\Helper\Data                          $helper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \TIG\Buckaroo\Model\ConfigProvider\Factory         $configProviderFactory
     * @param \TIG\Buckaroo\Debug\Debugger                       $debugger
     */
    public function __construct(
        \TIG\Buckaroo\Helper\Data $helper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \TIG\Buckaroo\Model\ConfigProvider\Factory $configProviderFactory,
        \TIG\Buckaroo\Debug\Debugger $debugger
    ) {
        $this->helper                   = $helper;
        $this->scopeConfig              = $scopeConfig;
        $this->configProviderFactory    = $configProviderFactory;
        $this->debugger                 = $debugger;
    }

    /**
     * @param $data
     *
     * @return bool
     */
    public function validate($data)
    {
        return true;
    }

    /**
     * Checks if the status code is returned by the bpe push and is valid.
     *
     * @param $code
     *
     * @return array
     */
    public function validateStatusCode($code)
    {
        if (null !== $this->helper->getStatusByValue($code)
            && isset($this->bpeResponseMessages[$code])
        ) {
            return [
                'message' => $this->bpeResponseMessages[$code],
                'status'  => $this->helper->getStatusByValue($code),
                'code'    => $code,
            ];
        } else {
            return [
                'message' => 'Onbekende responsecode: ' . $code,
                'status'  => 'TIG_BUCKAROO_STATUSCODE_NEUTRAL',
                'code'    => $code,
            ];
        }
    }

    /**
     * Generate/calculate the signature with the buckaroo config value and check if thats equal to the signature
     * received from the push
     *
     * @param $postData
     *
     * @return bool
     */
    public function validateSignature($postData)
    {
        if (!isset($postData['brq_signature'])) {
            return false;
        }

        $signature = $this->calculateSignature($postData);

        if ($signature !== $postData['brq_signature']) {
            return false;
        }

        return true;
    }

    /**
     * Determines the signature using array sorting and the SHA1 hash algorithm
     *
     * @param $postData
     *
     * @return string
     */
    protected function calculateSignature($postData)
    {
        $copyData = $postData;
        unset($copyData['brq_signature']);

        $sortableArray = $this->buckarooArraySort($copyData);

        $signatureString = '';

        foreach ($sortableArray as $brq_key => $value) {
            if ('brq_SERVICE_masterpass_CustomerPhoneNumber' !== $brq_key
                && 'brq_SERVICE_masterpass_ShippingRecipientPhoneNumber' !== $brq_key
            ) {
                $value = urldecode($value);
            }

            $signatureString .= $brq_key. '=' . $value;
        }

        /**
         * @var \TIG\Buckaroo\Model\ConfigProvider\Account $accountConfig
         */
        $accountConfig = $this->configProviderFactory->get('account');

        $digitalSignature = $accountConfig->getSecretKey();

        $signatureString .= $digitalSignature;

        $signature = SHA1($signatureString);

        $this->debugger->addToMessage($signature);

        return $signature;
    }

    /**
     * Sort the array so that the signature can be calculated identical to the way buckaroo does.
     *
     * @param $arrayToUse
     *
     * @return array $sortableArray
     */
    protected function buckarooArraySort($arrayToUse)
    {
        $arrayToSort   = [];
        $originalArray = [];

        foreach ($arrayToUse as $key => $value) {
            $arrayToSort[strtolower($key)]   = $value;
            $originalArray[strtolower($key)] = $key;
        }

        ksort($arrayToSort);

        $sortableArray = [];

        foreach ($arrayToSort as $key => $value) {
            $key = $originalArray[$key];
            $sortableArray[$key] = $value;
        }

        return $sortableArray;
    }
}
