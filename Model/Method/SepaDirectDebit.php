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

namespace TIG\Buckaroo\Model\Method;

use Magento\Sales\Model\Order\Payment;

class SepaDirectDebit extends AbstractMethod
{
    /**
     * Payment Code
     */
    const PAYMENT_METHOD_CODE = 'tig_buckaroo_sepadirectdebit';

    /**
     * @var string
     */
    public $buckarooPaymentMethodCode = 'sepadirectdebit';

    // @codingStandardsIgnoreStart
    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_CODE;

    /**
     * @var bool
     */
    protected $_isGateway               = true;

    /**
     * @var bool
     */
    protected $_canOrder                = true;

    /**
     * @var bool
     */
    protected $_canAuthorize            = false;

    /**
     * @var bool
     */
    protected $_canCapture              = false;

    /**
     * @var bool
     */
    protected $_canCapturePartial       = false;

    /**
     * @var bool
     */
    protected $_canRefund               = true;

    /**
     * @var bool
     */
    protected $_canVoid                 = true;

    /**
     * @var bool
     */
    protected $_canUseInternal          = true;

    /**
     * @var bool
     */
    protected $_canUseCheckout          = true;

    /**
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;
    // @codingStandardsIgnoreEnd

    /**
     * @var bool
     */
    protected $usesRedirect            = false;

    /**
     * {@inheritdoc}
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);

        if (is_array($data)) {
            $this->getInfoInstance()->setAdditionalInformation('customer_bic', $data['customer_bic']);
            $this->getInfoInstance()->setAdditionalInformation('customer_iban', $data['customer_iban']);
            $this->getInfoInstance()->setAdditionalInformation('customer_account_name', $data['customer_account_name']);
        } elseif ($data instanceof \Magento\Framework\DataObject) {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->getInfoInstance()->setAdditionalInformation('customer_bic', $data->getCustomerBic());
            /** @noinspection PhpUndefinedMethodInspection */
            $this->getInfoInstance()->setAdditionalInformation('customer_iban', $data->getCustomerIban());
            /** @noinspection PhpUndefinedMethodInspection */
            $this->getInfoInstance()->setAdditionalInformation(
                'customer_account_name',
                $data->getCustomerAccountName()
            );
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderTransactionBuilder($payment)
    {
        $transactionBuilder = $this->transactionBuilderFactory->get('order');

        $services = [
            'Name'             => 'sepadirectdebit',
            'Action'           => 'Pay',
            'Version'          => 1,
            'RequestParameter' => [
                [
                    '_'    => $this->getInfoInstance()->getAdditionalInformation('customer_account_name'),
                    'Name' => 'customeraccountname',
                ],
                [
                    '_'    => $this->getInfoInstance()->getAdditionalInformation('customer_iban'),
                    'Name' => 'CustomerIBAN',
                ],
            ],
        ];

        if ($this->getInfoInstance()->getAdditionalInformation('customer_bic')) {
            $services[0]['RequestParameter'][0][] = [
                '_'    => $this->getInfoInstance()->getAdditionalInformation('customer_bic'),
                'Name' => 'CustomerBIC',
            ];
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $transactionBuilder->setOrder($payment->getOrder())
                           ->setServices($services)
                           ->setMethod('TransactionRequest');

        return $transactionBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getCaptureTransactionBuilder($payment)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeTransactionBuilder($payment)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getRefundTransactionBuilder($payment)
    {
        $transactionBuilder = $this->transactionBuilderFactory->get('refund');

        $services = [

            'Name' => 'sepadirectdebit',
            'Action' => 'Refund',
            'Version' => 1,

        ];

        $requestParams = $this->addExtraFields($this->_code);
        $services = array_merge($services, $requestParams);

        /** @noinspection PhpUndefinedMethodInspection */
        $transactionBuilder->setOrder($payment->getOrder())
                           ->setServices($services)
                           ->setMethod('TransactionRequest')
                           ->setOriginalTransactionKey(
                               $payment->getAdditionalInformation(self::BUCKAROO_ORIGINAL_TRANSACTION_KEY_KEY)
                           )
                           ->setChannel('CallCenter');

        return $transactionBuilder;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Api\Data\OrderPaymentInterface $payment
     * @param array|\StdCLass                                                                    $response
     *
     * @return $this
     */
    protected function afterAuthorize($payment, $response)
    {
        if (!empty($response[0]->ConsumerMessage) && $response[0]->ConsumerMessage->MustRead == 1) {
            $consumerMessage = $response[0]->ConsumerMessage;

            $this->messageManager->addSuccessMessage(
                __($consumerMessage->Title)
            );
            $this->messageManager->addSuccessMessage(
                __($consumerMessage->PlainText)
            );
        }

        return parent::afterAuthorize($payment, $response);
    }

    /**
     * {@inheritdoc}
     */
    public function getVoidTransactionBuilder($payment)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function validate()
    {
        parent::validate();

        $paymentInfo = $this->getInfoInstance();

        $skipValidation = $paymentInfo->getAdditionalInformation('buckaroo_skip_validation');
        if ($skipValidation) {
            return $this;
        }

        $customerBic = $paymentInfo->getAdditionalInformation('customer_bic');
        $customerIban = $paymentInfo->getAdditionalInformation('customer_iban');
        $customerAccountName = $paymentInfo->getAdditionalInformation('customer_account_name');

        if (empty($customerAccountName) || str_word_count($customerAccountName) < 2) {
            throw new \TIG\Buckaroo\Exception(__('Please enter a valid bank account holder name'));
        }
        if ($paymentInfo instanceof Payment) {
            $billingCountry = $paymentInfo->getOrder()->getBillingAddress()->getCountryId();
        } else {
            $billingCountry = $paymentInfo->getQuote()->getBillingAddress()->getCountryId();
        }

        if ($billingCountry == 'NL') {
            $ibanValidator = $this->objectManager->create(\Zend\Validator\Iban::class);
            if (empty($customerIban) || !$ibanValidator->isValid($customerIban)) {
                throw new \TIG\Buckaroo\Exception(__('Please enter a valid bank account number'));
            }
        } else {
            if (!preg_match(self::BIC_NUMBER_REGEX, $customerBic)) {
                throw new \TIG\Buckaroo\Exception(__('Please enter a valid BIC number'));
            }
        }

        return $this;
    }
}
