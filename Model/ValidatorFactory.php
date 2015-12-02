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
 * @copyright   Copyright (c) 2015 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */

namespace TIG\Buckaroo\Model;

class ValidatorFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var array
     */
    protected $_validators;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $validators
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $validators = []
    ) {
        $this->_objectManager = $objectManager;
        $this->_validators = $validators;
    }

    /**
     * Retrieve proper transaction builder for the specified transaction type.
     *
     * @param string $validatorType
     * @return \TIG\Buckaroo\Model\ValidatorInterface
     * @throws \LogicException|\TIG\Buckaroo\Exception
     */
    public function get($validatorType)
    {
        if (empty($this->_validators)) {
            throw new \LogicException('Validator adapter is not set.');
        }
        foreach ($this->_validators as $validatorMetaData) {
            $validatorMetaDataType = $validatorMetaData['type'];
            if ($validatorMetaDataType == $validatorType) {
                $validatorClass = $validatorMetaData['model'];
                break;
            }
        }

        if (!isset($validatorClass) || empty($validatorClass)) {
            throw new \TIG\Buckaroo\Exception(
                new \Magento\Framework\Phrase(
                    'Unknown validator type requested: %1.',
                    [$validatorType]
                )
            );
        }

        $validator = $this->_objectManager->get($validatorClass);
        if (!$validator instanceof \TIG\Buckaroo\Model\ValidatorInterface) {
            throw new \LogicException(
                'The transaction builder must implement "TIG\Buckaroo\Model\ValidatorInterface".'
            );
        }
        return $validator;
    }
}