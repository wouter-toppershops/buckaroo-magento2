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
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */

namespace TIG\Buckaroo\Model\ConfigProvider;

use \TIG\Buckaroo\Model\ConfigProvider;

class States extends AbstractConfigProvider
{

    /**
     * XPATHs to configuration values for tig_buckaroo_predefined
     */
    const XPATH_STATES                         = 'tig_buckaroo/tig_states';
    const XPATH_STATES_STATE_SUCCESS           = 'tig_buckaroo/tig_states/order_state_success';
    const XPATH_STATES_STATE_FAILED            = 'tig_buckaroo/tig_states/order_state_failed';
    const XPATH_STATES_STATE_PENDINGPAYMENT    = 'tig_buckaroo/tig_states/order_state_pendingpayment';
    const XPATH_STATES_INCORRECT_PAYMENT       = 'tig_buckaroo/tig_states/order_incorrect_payment';
    const XPATH_STATES_DIGITAL_SIGNATURE       = 'tig_buckaroo/tig_states/digital_signature';
    const XPATH_STATES_CANCEL_ON_FAILURE       = 'tig_buckaroo/tig_states/cancel_on_failure';

    /**
     * @return array|void
     */
    public function getConfig()
    {
        $config = [
            'advanced' => $this->getAll(),
        ];
        return $config;
    }

    /**
     * Returns the config value for states/advanced
     *
     * @return mixed
     */
    public function getAll()
    {
        return $this->getConfigFromXpath(self::XPATH_STATES);
    }

    /**
     * Returns the config value for states/advanced/order_state_success
     *
     * @return mixed
     */
    public function getStateSuccess()
    {
        return $this->getConfigFromXpath(self::XPATH_STATES_STATE_SUCCESS);
    }

    /**
     * Returns the config value for states/advanced/order_state_failed
     *
     * @return mixed
     */
    public function getStateFailed()
    {
        return $this->getConfigFromXpath(self::XPATH_STATES_STATE_FAILED);
    }

    /**
     * Returns the config value for states/advanced/order_state_pendingpayment
     *
     * @return mixed
     */
    public function getStatePendingpayment()
    {
        return $this->getConfigFromXpath(self::XPATH_STATES_STATE_PENDINGPAYMENT);
    }

    /**
     * Returns the config value for states/advanced/order_incorrect_payment
     *
     * @return mixed
     */
    public function getIncorrectPayment()
    {
        return $this->getConfigFromXpath(self::XPATH_STATES_INCORRECT_PAYMENT);
    }

    /**
     * Returns the config value for states/advanced/digital_signature
     *
     * @return mixed
     */
    public function getDigitalSignature()
    {
        return $this->getConfigFromXpath(self::XPATH_STATES_DIGITAL_SIGNATURE);
    }

    /**
     * Returns the config value for states/advanced/cancel_on_failure
     *
     * @return mixed
     */
    public function getCancelOnFailure()
    {
        return $this->getConfigFromXpath(self::XPATH_STATES_CANCEL_ON_FAILURE);
    }

}
