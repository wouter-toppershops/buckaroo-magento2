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
     * @var null
     */
    public $remoteAddress               = null;

    /**
     * {@inheritdoc}
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);

        if (!is_array($data)) {
            $data = $data->convertToArray();
        }

        /** @todo check the request if its set-payment-information, the data is unknown */
        if (isset($data['additional_data']['termsCondition'])) {
            $additionalData = $data['additional_data'];
            $this->getInfoInstance()->setAdditionalInformation('termsCondition', $additionalData['termsCondition']);
            $this->getInfoInstance()->setAdditionalInformation('customer_gender', $additionalData['customer_gender']);
            $this->getInfoInstance()->setAdditionalInformation('customer_billingName', $additionalData['customer_billingName']);
            $this->getInfoInstance()->setAdditionalInformation('customer_DoB', $additionalData['customer_DoB']);
            $this->getInfoInstance()->setAdditionalInformation('customer_iban', $additionalData['customer_iban']);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderTransactionBuilder($payment)
    {
        $transactionBuilder = $this->transactionBuilderFactory->get('order');

        /** @var \TIG\Buckaroo\Model\ConfigProvider\Method\Afterpay $afterpayConfig */
        $afterpayConfig = $this->configProviderMethodFactory->get('afterpay');
        /** @noinspection PhpUndefinedMethodInspection */
        $services = [
            'Name'             => $afterpayConfig->getPaymentMethodName(),
            'Action'           => 'Pay',
            'Version'          => 2,
            'RequestParameter' =>
                $this->getAfterPayRequestParameters($payment),
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
                '_'    => $this->isAddressDataDifferent($payment),
                'Name' => 'AddressesDiffer'
            ],
        ];

        // If the shipping address is not the same as the billing it will be merged inside the data array.
        if ($this->isAddressDataDifferent($payment)) {
            $requestData = array_merge($requestData, $this->getRequestShippingData($payment));
        }

        // Merge the customer data; ip, iban and terms condition.
        $requestData = array_merge($requestData, $this->getRequestCustomerData($payment));
        // Merge the article data; products and fee's
        $requestData = array_merge($requestData, $this->getRequestArticlesData());
        // Merge the business data
        $requestData = array_merge($requestData, $this->getRequestBusinessData());

        return $requestData;
    }

    /**
     * Get request Business data
     *
     * @return array
     * @throws \TIG\Buckaroo\Exception
     */
    public function getRequestBusinessData()
    {
        /** @var \TIG\Buckaroo\Model\ConfigProvider\Method\Afterpay $afterPayConfig */
        $afterPayConfig = $this->configProviderMethodFactory
            ->get(\TIG\Buckaroo\Model\Method\Afterpay::PAYMENT_METHOD_CODE);


        if ($afterPayConfig->getBusiness() == 2 || $afterPayConfig->getBusiness() == 3) {
            $requestData = [];
            /** @todo, need to setAdditionalInformation about:
             *  - CompanyCOCRegistration (KVK)
             *  - CompanyName
             *  - CostCentre
             *  - VatNumber (BTW)
             */
        } else {
            $requestData = [
                [
                    '_'    => false,
                    'Name' => 'B2B'
                ]
            ];
        }

        return $requestData;
    }

    /**
     * Get Article lines
     *
     * @return array
     */
    public function getRequestArticlesData()
    {
        /** @var \Magento\Eav\Model\Entity\Collection\AbstractCollection|array $cartData */
        $cartData = $this->objectManager->create('Magento\Checkout\Model\Cart')->getItems();

        // Set loop variables
        $articles = [];
        $count    = 1;

        foreach ($cartData as $item) {
            if (empty($item)) {
                continue;
            }
            $article = [
                [
                    '_'    => $item->getQty() . ' x ' . $item->getName(),
                    'Name' => 'ArticleDescription'
                ],
                [
                    '_'    => $item->getProductId(),
                    'Name' => 'ArticleId'
                ],
                [
                    '_'    => 1, //Always 1 since the qty is parsed inside the description
                    'Name' => 'ArticleQuantitye'
                ],
                [
                    '_'    => $this->calculateProductPrice($item),
                    'Name' => 'ArticleUnitPrice'
                ],
                [
                    '_'    => $this->getTaxCategory($item->getTaxClassId()),
                    'Name' => 'ArticleVatcategory'
                ]
            ];

            $articles[$count] = $article;

            if ($count < self::AFTERPAY_MAX_ARTICLE_COUNT) {
                $count++;
                continue;
            }

            break;
        }
        // Some dirty logic to get the latest key and add +1 to it.
        $latestKey = (int)key(end($articles)) + 1;

        $serviceLine = $this->getServiceCostLine();

        if (!empty($serviceLine)) {
            $articles[$latestKey] = $serviceLine;
        }

        return ['Articles' => $articles];
    }

    /**
     * @param $productItem
     *
     * @return mixed
     */
    public function calculateProductPrice($productItem)
    {
        $productPrice = ($productItem->getBasePrice() * $productItem->getQty())
            + $productItem->getBaseTaxAmount();

        return $productPrice;
    }

    /**
     * Get the service cost lines (buckfee)
     *
     * @return array
     */
    public function getServiceCostLine()
    {
        /** @var \TIG\Buckaroo\Helper\PaymentFee $feeHelper */
        $feeHelper = $this->objectManager->create('\TIG\Buckaroo\Helper\PaymentFee');

        $buckfee    = $feeHelper->getBuckarooFee();
        $buckfeeTax = $feeHelper->getBuckarooFeeTax();

        $article = [];
        if (false !== $buckfee && (double)$buckfee > 0) {

            $article = [
                [
                    '_'    => 'Servicekosten',
                    'Name' => 'ArticleDescription'
                ],
                [
                    '_'    => 1,
                    'Name' => 'ArticleId'
                ],
                [
                    '_'    => 1,
                    'Name' => 'ArticleQuantity'
                ],
                [
                    '_'    => round($buckfee + $buckfeeTax, 2),
                    'Name' => 'ArticleUnitPrice'
                ],
                [
                    '_'    => $this->getTaxCategory(
                        $feeHelper->getBuckarooFeeTaxClass(\Magento\Store\Model\ScopeInterface::SCOPE_STORE)
                    ),
                    'Name' => 'ArticleUnitPrice'
                ]
            ];

        }

        return $article;
    }

    /**
     * @param $taxClassId
     *
     * @return int
     * @throws \TIG\Buckaroo\Exception
     */
    public function getTaxCategory($taxClassId)
    {
        $taxCategory = 4;

        if (!$taxClassId) {
            return $taxCategory;
        }
        /** @var \TIG\Buckaroo\Model\ConfigProvider\Method\Afterpay $afterPayConfig */
        $afterPayConfig = $this->configProviderMethodFactory
            ->get(\TIG\Buckaroo\Model\Method\Afterpay::PAYMENT_METHOD_CODE);

        $highClasses   = explode(',', $afterPayConfig->getHighTaxClasses());
        $middleClasses = explode(',', $afterPayConfig->getMiddleTaxClasses());
        $lowClasses    = explode(',', $afterPayConfig->getLowTaxClasses());
        $zeroClasses   = explode(',', $afterPayConfig->getZeroTaxClasses());

        if (in_array($taxClassId, $highClasses)) {
            $taxCategory = 1;
        } else if (in_array($taxClassId, $middleClasses)) {
            $taxCategory = 5;
        } else if (in_array($taxClassId, $lowClasses)) {
            $taxCategory = 2;
        } else if (in_array($taxClassId, $zeroClasses)) {
            $taxCategory = 3;
        } else {
            // No classes == 4
            $taxCategory = 4;
        }

        return $taxCategory;
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
                '_'    => $this->getRemoteAddress(),
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
    public function isAddressDataDifferent($payment)
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

        $arrayDiff = array_diff($filteredBillingAddress, $filteredShippingAddress);

        if (empty($arrayDiff)) {
            return false;
        }

        return true;
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

    /**
     * @param bool $ipToLong
     * @param array $alternativeHeaders
     *
     * @return bool|int|mixed|null|\Zend\Stdlib\ParametersInterface
     */
    public function getRemoteAddress($ipToLong = false, $alternativeHeaders = [])
    {
        if ($this->remoteAddress === null) {
            foreach ($alternativeHeaders as $var) {
                if ($this->request->getServer($var, false)) {
                    $this->remoteAddress = $this->request->getServer($var);
                    break;
                }
            }

            if (!$this->remoteAddress) {
                $this->remoteAddress = $this->request->getServer('REMOTE_ADDR');
            }
        }

        if (!$this->remoteAddress) {
            return false;
        }

        return $ipToLong ? ip2long($this->remoteAddress) : $this->remoteAddress;
    }
}
