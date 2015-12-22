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

/**
 * @method mixed getActive()
 * @method mixed getSecretKey()
 * @method mixed getMerchantKey()
 * @method mixed getTransactionLabel()
 * @method mixed getCertificateFile()
 * @method mixed getInvoiceEmail()
 * @method mixed getAutoInvoice()
 * @method mixed getAutoInvoiceStatus()
 * @method mixed getSuccessRedirect()
 * @method mixed getFailureRedirect()
 * @method mixed getCancelOnFailed()
 * @method mixed getDigitalSignature()
 * @method mixed getDebugEmail()
 * @method mixed getLimitByIp()
 * @method mixed getFeePercentageMode()
 */
class Account extends AbstractConfigProvider
{

    /**
     * XPATHs to configuration values for tig_buckaroo_account
     */
    const XPATH_ACCOUNT_ACTIVE                  = 'tig_buckaroo/account/active';
    const XPATH_ACCOUNT_SECRET_KEY              = 'tig_buckaroo/account/secret_key';
    const XPATH_ACCOUNT_MERCHANT_KEY            = 'tig_buckaroo/account/merchant_key';
    const XPATH_ACCOUNT_TRANSACTION_LABEL       = 'tig_buckaroo/account/transaction_label';
    const XPATH_ACCOUNT_CERTIFICATE_FILE        = 'tig_buckaroo/account/certificate_file';
    const XPATH_ACCOUNT_INVOICE_EMAIL           = 'tig_buckaroo/account/invoice_email';
    const XPATH_ACCOUNT_AUTO_INVOICE            = 'tig_buckaroo/account/auto_invoice';
    const XPATH_ACCOUNT_AUTO_INVOICE_STATUS     = 'tig_buckaroo/account/auto_invoice_status';
    const XPATH_ACCOUNT_SUCCESS_REDIRECT        = 'tig_buckaroo/account/success_redirect';
    const XPATH_ACCOUNT_FAILURE_REDIRECT        = 'tig_buckaroo/account/failure_redirect';
    const XPATH_ACCOUNT_CANCEL_ON_FAILED        = 'tig_buckaroo/account/cancel_on_failed';
    const XPATH_ACCOUNT_DIGITAL_SIGNATURE       = 'tig_buckaroo/account/digital_signature';
    const XPATH_ACCOUNT_DEBUG_EMAIL             = 'tig_buckaroo/account/debug_email';
    const XPATH_ACCOUNT_LIMIT_BY_IP             = 'tig_buckaroo/account/limit_by_ip';
    const XPATH_ACCOUNT_FEE_PERCENTAGE_MODE     = 'tig_buckaroo/account/fee_percentage_mode';

    /**
     * @return array|void
     */
    public function getConfig()
    {
        $config = [
            'active'                => $this->getActive(),
            'secret_key'            => $this->getSecretKey(),
            'merchant_key'          => $this->getMerchantKey(),
            'transaction_label'     => $this->getTransactionLabel(),
            'certificate_file'      => $this->getCertificateFile(),
            'invoice_email'         => $this->getInvoiceEmail(),
            'auto_invoice'          => $this->getAutoInvoice(),
            'auto_invoice_status'   => $this->getAutoInvoiceStatus(),
            'success_redirect'      => $this->getSuccessRedirect(),
            'failure_redirect'      => $this->getFailureRedirect(),
            'cancel_on_failed'      => $this->getCancelOnFailed(),
            'digital_signature'     => $this->getDigitalSignature(),
            'debug_email'           => $this->getDebugEmail(),
            'limit_by_ip'           => $this->getLimitByIp(),
            'fee_percentage_mode'   => $this->getFeePercentageMode(),
        ];
        return $config;
    }

}
