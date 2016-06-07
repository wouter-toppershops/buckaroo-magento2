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

class Afterpay extends AbstractMethod
{
    /**
     * Payment Code
     */
    const PAYMENT_METHOD_CODE = 'tig_buckaroo_afterpay';

    /**
     * Max articles that can be handled by afterpay
     */
    const AFTERPAY_MAX_ARTICLE_COUNT = 99;

    /**
     * @var string
     */
    public $buckarooPaymentMethodCode = 'afterpay';

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
    public $usesRedirect                = false;

    /**
     * {@inheritdoc}
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);

        if (!is_array($data)) {
            $data = $data->convertToArray();
        }
        /**
         * @todo: Isset validation also needs addition information about the b2b.
         */
        $this->getInfoInstance()->setAdditionalInformation('termsCondition', $data['termsCondition']);
        $this->getInfoInstance()->setAdditionalInformation('customer_gender', $data['customer_gender']);
        $this->getInfoInstance()->setAdditionalInformation('customer_billingName', $data['customer_billingName']);
        $this->getInfoInstance()->setAdditionalInformation('customer_DoB', $data['customer_DoB']);
        $this->getInfoInstance()->setAdditionalInformation('customer_iban', $data['customer_iban']);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderTransactionBuilder($payment)
    {
        $this->getRequestArticlesData($payment);
        $transactionBuilder = $this->transactionBuilderFactory->get('order');

        /** @var \TIG\Buckaroo\Model\ConfigProvider\Method\Afterpay $afterpayConfig */
        $afterpayConfig = $this->configProviderMethodFactory->get('afterpay');
        /** @noinspection PhpUndefinedMethodInspection */
        $services = [
            'Name'             => $afterpayConfig->getPaymentMethodName(),
            'Action'           => 'Pay',
            'Version'          => 2,
            'RequestParameter' =>
                $this->getAfterPayRequestParameters($payment)
            ,
        ];

        /** @noinspection PhpUndefinedMethodInspection */
        $transactionBuilder->setOrder($payment->getOrder())
            ->setServices($services)
            ->setMethod('TransactionRequest');

        return $transactionBuilder;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     *
     * @return array
     * @throws \TIG\Buckaroo\Exception
     */
    public function getAfterPayRequestParameters($payment)
    {
        // First data to set is the billing address data.
        $requestData = [
            $this->getRequestBillingData($payment),
            // Data variable to let afterpay know if the addresses are the same.
            [
                '_'    => $this->isAddressDataTheSame($payment),
                'Name' => 'AddressesDiffer'
            ],
        ];

        // If the shipping address is not the same as the billing it will be merged inside the data array.
        if (!$this->isAddressDataTheSame($payment)) {
            $requestData = array_merge($requestData, $this->getRequestShippingData($payment));
        }

        // Merge the customer data; ip, iban and terms condition.
        $requestData = array_merge($requestData, $this->getRequestCustomerData($payment));

        return $requestData;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     */
    public function getRequestArticlesData($payment)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order    = $payment->getOrder();
        $products = $order->getAllItems();

        // Set loop variables
        $articles = [];
        $count    = 1;

        foreach ($products as $item) {
            /** @var \Magento\Sales\Model\Order\Item[] $item */
            if (empty($item)) {
                continue;
            }

        }

    }

    /**
     * @param \Magento\Sales\Model\Order\Item[] $productItem
     */
    public function calculateProductPrice($productItem)
    {
        $productPrice = ($productItem->getBasePrice());
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     *
     * @return array
     */
    public function getRequestBillingData($payment)
    {
        /** @var \Magento\Sales\Api\Data\OrderAddressInterface $billingAddress */
        $billingAddress = $payment->getOrder()->getBillingAddress();
        $streetFormat   = $this->formatStreet($billingAddress->getStreet());

        $billingData = [
            [
                '_'    => $billingAddress->getFirstname(),
                'Name' => 'BillingTitle',
            ],
            [
                '_'    => $payment->getAdditionalInformation('customer_gender'),
                'Name' => 'BillingGender',
            ],
            [
                '_'    => strtoupper(substr($billingAddress->getFirstname(), 0, 1)),
                'Name' => 'BillingInitials',
            ],
            [
                '_'    => $billingAddress->getLastName(),
                'Name' => 'BillingLastName',
            ],
            [
                '_'    => $payment->getAdditionalInformation('customer_DoB'),
                'Name' => 'BillingBirthDate',
            ],
            [
                '_'    => $streetFormat['street'],
                'Name' => 'BillingStreet',
            ],
            [
                '_'    => $streetFormat['house_number'],
                'Name' => 'BillingHouseNumber',
            ],
            [
                '_'    => $streetFormat['number_addition'],
                'Name' => 'BillingHouseNumberSuffix',
            ],
            [
                '_'    => $billingAddress->getPostcode(),
                'Name' => 'BillingPostalCode',
            ],
            [
                '_'    => $billingAddress->getCity(),
                'Name' => 'BillingCity',
            ],
            [
                '_'    => $billingAddress->getCountryId(),
                'Name' => 'BillingCountry',
            ],
            [
                '_'    => $billingAddress->getEmail(),
                'Name' => 'BillingEmail',
            ],
            [
                '_'    => $billingAddress->getTelephone(),
                'Name' => 'BillingPhoneNumber',
            ],
            [
                '_'    => $billingAddress->getCountryId(),
                'Name' => 'BillingLanguage',
            ],
        ];

        return $billingData;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     *
     * @return array
     */
    public function getRequestShippingData($payment)
    {
        /** @var \Magento\Sales\Api\Data\OrderAddressInterface $shippingAddress */
        $shippingAddress = $payment->getOrder()->getShippingAddress();
        $streetFormat    = $this->formatStreet($shippingAddress->getStreet());

        $shippingData = [
            [
                '_'    => $shippingAddress->getFirstname(),
                'Name' => 'BillingTitle',
            ],
            [
                '_'    => $payment->getAdditionalInformation('customer_gender'),
                'Name' => 'BillingGender',
            ],
            [
                '_'    => strtoupper(substr($shippingAddress->getFirstname(), 0, 1)),
                'Name' => 'BillingInitials',
            ],
            [
                '_'    => $shippingAddress->getLastName(),
                'Name' => 'BillingLastName',
            ],
            [
                '_'    => $payment->getAdditionalInformation('customer_DoB'),
                'Name' => 'BillingBirthDate',
            ],
            [
                '_'    => $streetFormat['street'],
                'Name' => 'BillingStreet',
            ],
            [
                '_'    => $streetFormat['house_number'],
                'Name' => 'BillingHouseNumber',
            ],
            [
                '_'    => $streetFormat['number_addition'],
                'Name' => 'BillingHouseNumberSuffix',
            ],
            [
                '_'    => $shippingAddress->getPostcode(),
                'Name' => 'BillingPostalCode',
            ],
            [
                '_'    => $shippingAddress->getCity(),
                'Name' => 'BillingCity',
            ],
            [
                '_'    => $shippingAddress->getCountryId(),
                'Name' => 'BillingCountry',
            ],
            [
                '_'    => $shippingAddress->getEmail(),
                'Name' => 'BillingEmail',
            ],
            [
                '_'    => $shippingAddress->getTelephone(),
                'Name' => 'BillingPhoneNumber',
            ],
            [
                '_'    => $shippingAddress->getCountryId(),
                'Name' => 'BillingLanguage',
            ],
        ];

        return $shippingData;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     *
     * @return array
     */
    public function getRequestCustomerData($payment)
    {
        $customerData = [
            [
                // Only required if afterpay paymentmethod is acceptgiro.
                '_'    => $payment->getAdditionalInformation('customer_iban'),
                'Name' => 'CustomerAccountNumber',
            ],
            [
                '_'    => $this->remoteAddress->getRemoteAddress(),
                'Name' => 'CustomerIPAddress',
            ],
            [
                '_'    => $payment->getAdditionalInformation('termsCondition'),
                'Name' => 'Accept',
            ]
        ];

        return $customerData;
    }

    /**
     * Method to compare two addresses from the payment.
     * Returns true if they are the same.
     *
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     *
     * @return boolean
     */
    public function isAddressDataTheSame($payment)
    {
        $billingAddress  = $payment->getOrder()->getBillingAddress()->getData();
        $shippingAddress = $payment->getOrder()->getShippingAddress()->getData();

        $keysToExclude = [
            'prefix',
            'telephone',
            'fax',
            'created_at',
            'email',
            'customer_address_id',
            'vat_request_success',
            'vat_request_date',
            'vat_request_id',
            'vat_is_valid',
            'vat_id',
            'address_type'
        ];

        $filteredBillingAddress  = array_diff_key($billingAddress, array_flip($keysToExclude));
        $filteredShippingAddress = array_diff_key($shippingAddress, array_flip($keysToExclude));

        if (empty(array_diff($filteredBillingAddress, $filteredShippingAddress))) {
            return true;
        }

        return false;
    }

    /**
     * @param $street
     *
     * @return array
     */
    public function formatStreet($street)
    {
        // Street is always an array since it is parsed with two field objects.
        // Nondeless it could be that only the first field is parsed to the array
        if (isset($street[1])) {
            $street = $street[0] . ' ' . $street[1];
        } else {
            $street = $street[0];
        }

        $format = [
          'house_number'    => '',
          'number_addition' => '',
          'street'          => $street
        ];

        if (preg_match('#^(.*?)([0-9]+)(.*)#s', $street[0], $matches)) {
            // Check if the number is at the beginning of streetname
            if ('' == $matches[1]) {
                $format['house_number'] = trim($matches[2]);
                $format['street']       = trim($matches[3]);
            } else {
                $format['street']          = trim($matches[1]);
                $format['house_number']    = trim($matches[2]);
                $format['number_addition'] = trim($matches[3]);
            }
        }

        return $format;
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
            'Name'    => 'afterpay',
            'Action'  => 'Refund',
            'Version' => 1,
        ];

        $requestParams = $this->addExtraFields($this->_code);
        $services = array_merge($services, $requestParams);

        /** @noinspection PhpUndefinedMethodInspection */
        $transactionBuilder->setOrder($payment->getOrder())
            ->setServices($services)
            ->setMethod('TransactionRequest')
            ->setOriginalTransactionKey($payment->getAdditionalInformation('buckaroo_transaction_key'));

        return $transactionBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getVoidTransactionBuilder($payment)
    {
        return true;
    }
}
