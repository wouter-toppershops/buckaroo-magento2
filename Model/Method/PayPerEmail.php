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

namespace TIG\Buckaroo\Model\Method;

use TIG\Buckaroo\Model\ConfigProvider\Method\PayPerEmail as PayPerEmailConfig;

class PayPerEmail extends AbstractMethod
{
    /**
     * Payment Code
     */
    const PAYMENT_METHOD_CODE = 'tig_buckaroo_payperemail';

    /**
     * @var string
     */
    public $buckarooPaymentMethodCode = 'payperemail';

    // @codingStandardsIgnoreStart
    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code                    = self::PAYMENT_METHOD_CODE;

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
    protected $_canRefund               = false;

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
    protected $_canRefundInvoicePartial = false;
    // @codingStandardsIgnoreEnd

    /**
     * {@inheritdoc}
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);
        $data = $this->assignDataConvertToArray($data);

        if (isset($data['additional_data']['customer_gender'])) {
            $this->getInfoInstance()
                ->setAdditionalInformation('customer_gender', $data['additional_data']['customer_gender']);
        }

        if (isset($data['additional_data']['customer_billingFirstName'])) {
            $this->getInfoInstance()
                ->setAdditionalInformation(
                    'customer_billingFirstName',
                    $data['additional_data']['customer_billingFirstName']
                );
        }

        if (isset($data['additional_data']['customer_billingLastName'])) {
            $this->getInfoInstance()
                ->setAdditionalInformation(
                    'customer_billingLastName',
                    $data['additional_data']['customer_billingLastName']
                );
        }

        if (isset($data['additional_data']['customer_email'])) {
            $this->getInfoInstance()
                ->setAdditionalInformation('customer_email', $data['additional_data']['customer_email']);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderTransactionBuilder($payment)
    {
        $services = [];
        $services[] = $this->getPayperemailService($payment);

        $cmService = $this->getCmService($payment);
        if (count($cmService) > 0) {
            $services[] = $cmService;
        }

        $transactionBuilder = $this->transactionBuilderFactory->get('order');

        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        $transactionBuilder->setOrder($payment->getOrder())
            ->setServices($services)
            ->setMethod('TransactionRequest');

        return $transactionBuilder;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     *
     * @return array
     */
    private function getPayperemailService($payment)
    {
        /** @var \TIG\Buckaroo\Model\ConfigProvider\Method\PayPerEmail $config */
        $config = $this->configProviderMethodFactory->get('payperemail');

        $services = [
            'Name'             => 'payperemail',
            'Action'           => 'PaymentInvitation',
            'Version'          => 1,
            'RequestParameter' => [
                [
                    '_'    => $payment->getAdditionalInformation('customer_gender'),
                    'Name' => 'customergender',
                ],
                [
                    '_'    => $payment->getAdditionalInformation('customer_email'),
                    'Name' => 'CustomerEmail',
                ],
                [
                    '_'    => $payment->getAdditionalInformation('customer_billingFirstName'),
                    'Name' => 'CustomerFirstName',
                ],
                [
                    '_'    => $payment->getAdditionalInformation('customer_billingLastName'),
                    'Name' => 'CustomerLastName',
                ],
                [
                    '_'    => $config->getSendMail() ? 'false' : 'true',
                    'Name' => 'MerchantSendsEmail',
                ],
                [
                    '_'    => $config->getPaymentMethod(),
                    'Name' => 'PaymentMethodsAllowed',
                ],
            ],
        ];

        return $services;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     *
     * @return array
     */
    private function getCmService($payment)
    {
        /** @var \TIG\Buckaroo\Model\ConfigProvider\Method\PayPerEmail $config */
        $config = $this->configProviderMethodFactory->get('payperemail');

        if (strlen($config->getSchemeKey()) <= 0) {
            return [];
        }

        $services = [
            'Name'             => 'CreditManagement3',
            'Action'           => 'CreateCombinedInvoice',
            'Version'          => 1,
            'RequestParameter' => $this->getCmRequestParameters($payment)
        ];

        return $services;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     *
     * @return array
     */
    private function getCmRequestParameters($payment)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();

        $requestParameters = [
            [
                '_'    => $order->getBillingAddress()->getEmail(),
                'Name' => 'Code',
                'Group' => 'Debtor',
            ],
            [
                '_'    => $order->getBillingAddress()->getEmail(),
                'Name' => 'Email',
                'Group' => 'Email',
            ],
            [
                '_'    => $order->getBillingAddress()->getTelephone(),
                'Name' => 'Mobile',
                'Group' => 'Phone',
            ],
        ];

        $ungroupedParameters = $this->getUngroupedCmParameters($order);
        $requestParameters = array_merge($requestParameters, $ungroupedParameters);

        $personParameters = $this->getPersonCmParameters($payment);
        $requestParameters = array_merge($requestParameters, $personParameters);

        $addressParameters = $this->getAddressCmParameters($order->getBillingAddress());
        $requestParameters = array_merge($requestParameters, $addressParameters);

        $companyParameters = $this->getCompanyCmParameters($order->getBillingAddress());
        $requestParameters = array_merge($requestParameters, $companyParameters);

        return $requestParameters;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     *
     * @return array
     */
    private function getUngroupedCmParameters($order)
    {
        /** @var \TIG\Buckaroo\Model\ConfigProvider\Method\PayPerEmail $config */
        $config = $this->configProviderMethodFactory->get('payperemail');

        $ungroupedParameters = [
            [
                '_'    => $order->getGrandTotal(),
                'Name' => 'InvoiceAmount',
            ],
            [
                '_'    => $order->getTaxAmount(),
                'Name' => 'InvoiceAmountVAT',
            ],
            [
                '_'    => date('Y-m-d'),
                'Name' => 'InvoiceDate',
            ],
            [
                '_'    => date('Y-m-d', strtotime('+' . $config->getDueDate() . ' day', time())),
                'Name' => 'DueDate',
            ],
            [
                '_'    => $config->getSchemeKey(),
                'Name' => 'SchemeKey',
            ],
            [
                '_'    => $config->getMaxStepIndex(),
                'Name' => 'MaxStepIndex',
            ],
            [
                '_'    => $config->getPaymentMethod(),
                'Name' => 'AllowedServices',
            ],
            [
                '_'    => $config->getPaymentMethodAfterExpiry(),
                'Name' => 'AllowedServicesAfterDueDate',
            ],
        ];

        return $ungroupedParameters;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     *
     * @return array
     */
    private function getPersonCmParameters($payment)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();

        $personParameters = [
            [
                '_'    => strtolower($order->getBillingAddress()->getCountryId()),
                'Name' => 'Culture',
                'Group' => 'Person',
            ],
            [
                '_'    => $order->getBillingAddress()->getFirstname(),
                'Name' => 'FirstName',
                'Group' => 'Person',
            ],
            [
                '_'    => $order->getBillingAddress()->getLastname(),
                'Name' => 'LastName',
                'Group' => 'Person',
            ],
            [
                '_'    => $payment->getAdditionalInformation('customer_gender'),
                'Name' => 'Gender',
                'Group' => 'Person',
            ],
        ];

        return $personParameters;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderAddressInterface $billingAddress
     *
     * @return array
     */
    private function getAddressCmParameters($billingAddress)
    {
        $address = $this->getCmAddress($billingAddress->getStreet());

        $addressParameters = [
            [
                '_'    => $address['street'],
                'Name' => 'Street',
                'Group' => 'Address',
            ],
            [
                '_'    => $address['house_number'],
                'Name' => 'HouseNumber',
                'Group' => 'Address',
            ],
            [
                '_'    => $billingAddress->getPostcode(),
                'Name' => 'Zipcode',
                'Group' => 'Address',
            ],
            [
                '_'    => $billingAddress->getCity(),
                'Name' => 'City',
                'Group' => 'Address',
            ],
            [
                '_'    => $billingAddress->getCountryId(),
                'Name' => 'Country',
                'Group' => 'Address',
            ],
        ];

        if (!empty($address['number_addition']) && strlen($address['number_addition']) > 0) {
            $addressParameters[] = [
                '_'    => $address['number_addition'],
                'Name' => 'HouseNumberSuffix',
                'Group' => 'Address'
            ];
        }

        return $addressParameters;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderAddressInterface $billingAddress
     *
     * @return array
     */
    private function getCompanyCmParameters($billingAddress)
    {
        $requestParameters = [];
        $company = $billingAddress->getCompany();

        if (strlen($company) <= 0) {
            return $requestParameters;
        }

        $requestParameters = [
            [
                '_' => strtolower($billingAddress->getCountryId()),
                'Name' => 'Culture',
                'Group' => 'Company'
            ],
            [
                '_' => $company,
                'Name' => 'Name',
                'Group' => 'Company'
            ]
        ];

        return $requestParameters;
    }

    /**
     * @param $street
     *
     * @return array
     */
    private function getCmAddress($street)
    {
        if (is_array($street)) {
            $street = implode(' ', $street);
        }

        $addressRegexResult = preg_match(
            '#\A(.*?)\s+(\d+[a-zA-Z]{0,1}\s{0,1}[-]{1}\s{0,1}\d*[a-zA-Z]{0,1}|\d+[a-zA-Z-]{0,1}\d*[a-zA-Z]{0,1})#',
            $street,
            $matches
        );
        if (!$addressRegexResult || !is_array($matches)) {
            $addressData = array(
                'street'           => $street,
                'house_number'          => '',
                'number_addition' => '',
            );

            return $addressData;
        }

        $streetname = '';
        $housenumber = '';
        $housenumberExtension = '';
        if (isset($matches[1])) {
            $streetname = $matches[1];
        }

        if (isset($matches[2])) {
            $housenumber = $matches[2];
        }

        if (!empty($housenumber)) {
            $housenumber = trim($housenumber);
            $housenumberRegexResult = preg_match('#^([\d]+)(.*)#s', $housenumber, $matches);
            if ($housenumberRegexResult && is_array($matches)) {
                if (isset($matches[1])) {
                    $housenumber = $matches[1];
                }

                if (isset($matches[2])) {
                    $housenumberExtension = trim($matches[2]);
                }
            }
        }

        $addressData = array(
            'street'          => $streetname,
            'house_number'    => $housenumber,
            'number_addition' => $housenumberExtension,
        );

        return $addressData;
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
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getVoidTransactionBuilder($payment)
    {
        return true;
    }
}
