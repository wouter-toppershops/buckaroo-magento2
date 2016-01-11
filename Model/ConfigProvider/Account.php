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
 * @method mixed getDebugMode()
 * @method mixed getDebugEmail()
 * @method mixed getLimitByIp()
 * @method mixed getFeePercentageMode()
 * @method getOrderStatusPending()
 * @method getOrderStatusNew()
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
    const XPATH_ACCOUNT_DEBUG_MODE              = 'tig_buckaroo/account/debug_mode';
    const XPATH_ACCOUNT_DEBUG_EMAIL             = 'tig_buckaroo/account/debug_email';
    const XPATH_ACCOUNT_LIMIT_BY_IP             = 'tig_buckaroo/account/limit_by_ip';
    const XPATH_ACCOUNT_FEE_PERCENTAGE_MODE     = 'tig_buckaroo/account/fee_percentage_mode';
    const XPATH_ACCOUNT_PAYMENT_FEE_LABEL       = 'tig_buckaroo/account/payment_fee_label';
    const XPATH_ACCOUNT_ORDER_STATUS_NEW        = 'tig_buckaroo/account/order_status_new';
    const XPATH_ACCOUNT_ORDER_STATUS_PENDING    = 'tig_buckaroo/account/order_status_pending';
    const XPATH_ACCOUNT_ORDER_STATUS_SUCCESS    = 'tig_buckaroo/account/order_status_success';
    const XPATH_ACCOUNT_ORDER_STATUS_FAILED     = 'tig_buckaroo/account/order_status_failed';

    protected $objectManager;
    protected $methodConfigProviderFactory;
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \TIG\Buckaroo\Model\ConfigProvider\Method\Factory $methodConfigProviderFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->objectManager = $objectManager;
        $this->methodConfigProviderFactory = $methodConfigProviderFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig($store = null)
    {
        $config = [
            'active'                => $this->getActive($store),
            'secret_key'            => $this->getSecretKey($store),
            'merchant_key'          => $this->getMerchantKey($store),
            'transaction_label'     => $this->getTransactionLabel($store),
            'certificate_file'      => $this->getCertificateFile($store),
            'invoice_email'         => $this->getInvoiceEmail($store),
            'auto_invoice'          => $this->getAutoInvoice($store),
            'auto_invoice_status'   => $this->getAutoInvoiceStatus($store),
            'success_redirect'      => $this->getSuccessRedirect($store),
            'failure_redirect'      => $this->getFailureRedirect($store),
            'cancel_on_failed'      => $this->getCancelOnFailed($store),
            'digital_signature'     => $this->getDigitalSignature($store),
            'debug_mode'            => $this->getDebugMode($store),
            'debug_email'           => $this->getDebugEmail($store),
            'limit_by_ip'           => $this->getLimitByIp($store),
            'fee_percentage_mode'   => $this->getFeePercentageMode($store),
            'payment_fee_label'     => $this->getPaymentFeeLabel($store),
            'order_status_new'      => $this->getOrderStatusNew($store),
            'order_status_pending'  => $this->getOrderStatusPending($store),
            'order_status_success'  => $this->getOrderStatusSuccess($store),
            'order_status_failed'   => $this->getOrderStatusFailed($store),
        ];
        return $config;
    }

    /**
     * Returns the method specific order status when available, or returns the global order status when not.
     *
     * @param null $paymentMethod
     *
     * @return string
     * @throws \TIG\Buckaroo\Exception
     */
    public function getOrderStatusSuccess($paymentMethod = null)
    {
        $orderStatusSuccess = parent::getOrderStatusSuccess();

        /**
         * If a Payment Method is set, get the payment method status
         */
        if (!is_null($paymentMethod)) {
            $methodConfigProvider = $this->getMethodConfigProvider($paymentMethod);

            $activeStatus = $methodConfigProvider->getActiveStatus();
            $methodOrderStatus = $methodConfigProvider->getOrderStatusSuccess();

            if ($activeStatus && !is_null($methodOrderStatus)) {
                $orderStatusSuccess = $methodConfigProvider->getOrderStatusSuccess();
            }
        }
        return $orderStatusSuccess;
    }

    /**
     * Returns the method specific order status when available, or returns the global order status when not.
     *
     * @param null $paymentMethod
     *
     * @return string
     * @throws \TIG\Buckaroo\Exception
     */
    public function getOrderStatusFailed($paymentMethod = null)
    {
        $orderStatusFailed = parent::getOrderStatusFailed();

        /**
         * If a Payment Method is set, get the payment method status
         */
        if (!is_null($paymentMethod)) {
            $methodConfigProvider = $this->getMethodConfigProvider($paymentMethod);

            $activeStatus = $methodConfigProvider->getActiveStatus();
            $methodOrderStatus = $methodConfigProvider->getOrderStatusFailed();

            if ($activeStatus && !is_null($methodOrderStatus)) {
                $orderStatusFailed = $methodConfigProvider->getOrderStatusFailed();
            }

        }
        return $orderStatusFailed;
    }

    /**
     * Gets the config provider for the given payment method.
     *
     * @param $paymentMethod
     *
     * @return Method\ConfigProviderInterface
     * @throws \TIG\Buckaroo\Exception
     */
    protected function getMethodConfigProvider($paymentMethod)
    {
        $array = explode('_', $paymentMethod);
        $methodCode = $array[2];

        return $this->methodConfigProviderFactory->get($methodCode);
    }

}
