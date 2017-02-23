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
     * Business methods that will be used in afterpay.
     */
    const BUSINESS_METHOD_B2C = 1;
    const BUSINESS_METHOD_B2B = 2;

    /**
     * Check if the tax calculation includes tax.
     */
    const TAX_CALCULATION_INCLUDES_TAX = 'tax/calculation/price_includes_tax';

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
    protected $_canAuthorize            = true;

    /**
     * @var bool
     */
    protected $_canCapture              = true;

    /**
     * @var bool
     */
    protected $_canCapturePartial       = true;

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
    protected $_canRefundInvoicePartial = false;
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
     * @var bool
     */
    public $closeAuthorizeTransaction   = false;

    /**
     * {@inheritdoc}
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);
        $data = $this->assignDataConvertAllVersionsArray($data);

        if (isset($data['additional_data']['termsCondition'])) {
            $additionalData = $data['additional_data'];
            $this->getInfoInstance()->setAdditionalInformation('termsCondition', $additionalData['termsCondition']);
            $this->getInfoInstance()->setAdditionalInformation('customer_gender', $additionalData['customer_gender']);
            $this->getInfoInstance()->setAdditionalInformation(
                'customer_billingName',
                $additionalData['customer_billingName']
            );
            $this->getInfoInstance()->setAdditionalInformation('customer_DoB', $additionalData['customer_DoB']);
            $this->getInfoInstance()->setAdditionalInformation('customer_iban', $additionalData['customer_iban']);
            if (isset($additionalData['selectedBusiness'])
                && $additionalData['selectedBusiness'] == self::BUSINESS_METHOD_B2B
            ) {
                $this->getInfoInstance()->setAdditionalInformation('COCNumber', $additionalData['COCNumber']);
                $this->getInfoInstance()->setAdditionalInformation('CompanyName', $additionalData['CompanyName']);
                $this->getInfoInstance()->setAdditionalInformation('CostCenter', $additionalData['CostCenter']);
                $this->getInfoInstance()->setAdditionalInformation('VATNumber', $additionalData['VATNumber']);
                $this->getInfoInstance()->setAdditionalInformation(
                    'selectedBusiness',
                    $additionalData['selectedBusiness']
                );
            }
        }

        return $this;
    }

    /**
     * Check capture availability
     *
     * @return bool
     * @api
     */
    public function canCapture()
    {
        if ($this->getConfigData('payment_action') == 'order') {
            return false;
        }
        return $this->_canCapture;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     *
     * @return bool|string
     * @throws \TIG\Buckaroo\Exception
     */
    public function getPaymentMethodName($payment)
    {
        /**
 * @var \TIG\Buckaroo\Model\ConfigProvider\Method\Afterpay $afterpayConfig 
*/
        $afterpayConfig = $this->configProviderMethodFactory->get('afterpay');

        $methodName = $afterpayConfig->getPaymentMethodName();

        if ($payment->getAdditionalInformation('selectedBusiness')) {
            $methodName = $afterpayConfig->getPaymentMethodName($payment->getAdditionalInformation('selectedBusiness'));
        }

        return $methodName;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderTransactionBuilder($payment)
    {
        $transactionBuilder = $this->transactionBuilderFactory->get('order');

        /**
 * @noinspection PhpUndefinedMethodInspection 
*/
        $services = [
            'Name'             => $this->getPaymentMethodName($payment),
            'Action'           => 'Pay',
            'Version'          => 1,
            'RequestParameter' =>
                $this->getAfterPayRequestParameters($payment),
        ];

        /**
 * @noinspection PhpUndefinedMethodInspection 
*/
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
        $transactionBuilder = $this->transactionBuilderFactory->get('order');

        $capturePartial = false;

        $order = $payment->getOrder();
        $order_id = $order->getId();

        $totalOrder = $order->getBaseGrandTotal();
        $numberOfInvoices = $order->hasInvoices();

        // loop through invoices to get the last one (=current invoice)
        if ($numberOfInvoices) {
            $oInvoiceCollection = $order->getInvoiceCollection();

            $i = 0;
            foreach ($oInvoiceCollection as $oInvoice) {
                if (++$i !== $numberOfInvoices) {
                    continue;
                }

                $currentInvoice = $oInvoice;
                $currentInvoiceTotal = $oInvoice->getBaseGrandTotal();
            }
        }

        if ($totalOrder == $currentInvoiceTotal && $numberOfInvoices == 1) {
            //full capture
            $capturePartial = false;
        } else {
            //partial capture
            $capturePartial = true;
        }

        $services = [
            'Name'             => $this->getPaymentMethodName($payment),
            'Action'           => 'Capture',
            'Version'          => 1
        ];

        // always get articles from invoice
        $articles = $this->getInvoiceArticleData($currentInvoice);

        // For the first invoice possible add payment fee
        if (is_array($articles) && $numberOfInvoices == 1) {
            $includesTax = $this->_scopeConfig->getValue(static::TAX_CALCULATION_INCLUDES_TAX);
            $serviceLine = $this->getServiceCostLine((count($articles)/5)+1, $payment, $includesTax);
            $articles = array_merge($articles, $serviceLine);
        }

        $services['RequestParameter'] = $articles;


        /**
 * @noinspection PhpUndefinedMethodInspection 
*/
        $transactionBuilder->setOrder($payment->getOrder())
            ->setServices($services)
            ->setAmount($currentInvoiceTotal)
            ->setMethod('TransactionRequest')
            ->setCurrency($this->payment->getOrder()->getOrderCurrencyCode())
            ->setOriginalTransactionKey(
                $payment->getAdditionalInformation(
                    self::BUCKAROO_ORIGINAL_TRANSACTION_KEY_KEY
                )
            );


        // Partial Capture Settings
        if ($capturePartial) {
            /**
 * @noinspection PhpUndefinedMethodInspection 
*/
            $transactionBuilder->setInvoiceId(
                $payment->getOrder()->getIncrementId(). '-' .
                $numberOfInvoices . '-' . substr(md5(date("YMDHis")), 0, 6)
            )
                ->setOriginalTransactionKey(
                    $payment->getParentTransactionId()
                );
        }

        return $transactionBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeTransactionBuilder($payment)
    {
        $transactionBuilder = $this->transactionBuilderFactory->get('order');

        $services = [
            'Name'             => $this->getPaymentMethodName($payment),
            'Action'           => 'Authorize',
            'Version'          => 1,
            'RequestParameter' =>
                $this->getAfterPayRequestParameters($payment),
        ];

        /**
 * @noinspection PhpUndefinedMethodInspection 
*/
        $transactionBuilder->setOrder($payment->getOrder())
            ->setServices($services)
            ->setMethod('TransactionRequest');

        return $transactionBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getVoidTransactionBuilder($payment)
    {
        $transactionBuilder = $this->transactionBuilderFactory->get('order');

        $services = [
            'Name'             => $this->getPaymentMethodName($payment),
            'Action'           => 'CancelAuthorize',
            'Version'          => 1,
        ];

        /**
 * @noinspection PhpUndefinedMethodInspection 
*/
        $transactionBuilder->setOrder($payment->getOrder())
            ->setAmount(0)
            ->setType('void')
            ->setServices($services)
            ->setMethod('TransactionRequest')
            ->setOriginalTransactionKey(
                $payment->getAdditionalInformation(
                    self::BUCKAROO_ORIGINAL_TRANSACTION_KEY_KEY
                )
            );

        return $transactionBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getRefundTransactionBuilder($payment)
    {
        $transactionBuilder = $this->transactionBuilderFactory->get('refund');

        $services = [
            'Name'    => $this->getPaymentMethodName($payment),
            'Action'  => 'Refund',
            'Version' => 1,
        ];

        $requestParams = $this->addExtraFields($this->_code);
        $services = array_merge($services, $requestParams);

        /**
 * @noinspection PhpUndefinedMethodInspection 
*/
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
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     *
     * @return array
     * @throws \TIG\Buckaroo\Exception
     */
    public function getAfterPayRequestParameters($payment)
    {
        // First data to set is the billing address data.
        $requestData = $this->getRequestBillingData($payment);

        $isDifferent = 'false';
        // If the shipping address is not the same as the billing it will be merged inside the data array.
        if ($this->isAddressDataDifferent($payment)) {
            $isDifferent = 'true';
            $requestData = array_merge($requestData, $this->getRequestShippingData($payment));
        }

        $requestData = array_merge(
            $requestData,
            [
                // Data variable to let afterpay know if the addresses are the same.
                [
                    '_'    => $isDifferent,
                    'Name' => 'AddressesDiffer'
                ]
            ]
        );

        // Merge the customer data; ip, iban and terms condition.
        $requestData = array_merge($requestData, $this->getRequestCustomerData($payment));
        // Merge the business data
        $requestData = array_merge($requestData, $this->getRequestBusinessData($payment));
        // Merge the article data; products and fee's
        $requestData = $this->getRequestArticlesData($requestData, $payment);

        return $requestData;
    }

    /**
     * Get request Business data
     *
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     *
     * @return array
     * @throws \TIG\Buckaroo\Exception
     */
    public function getRequestBusinessData($payment)
    {
        if ($payment->getAdditionalInformation('selectedBusiness') == self::BUSINESS_METHOD_B2B) {
            $requestData = [
                [
                    '_'    => 'true',
                    'Name' => 'B2B'
                ],
                [
                    '_'    => $payment->getAdditionalInformation('COCNumber'),
                    'Name' => 'CompanyCOCRegistration'
                ],
                [
                    '_'    => $payment->getAdditionalInformation('CompanyName'),
                    'Name' => 'CompanyName'
                ],
                [
                    '_'    => $payment->getAdditionalInformation('CostCenter'),
                    'Name' => 'CostCentre'
                ],
                [
                    '_'    => $payment->getAdditionalInformation('VATNumber'),
                    'Name' => 'VatNumber'
                ],
            ];
        } else {
            $requestData = [
                [
                    '_'    => 'false',
                    'Name' => 'B2B'
                ]
            ];
        }

        return $requestData;
    }

    /**
     * @param $requestData
     * @param $payment
     *
     * @return array
     */
    public function getRequestArticlesData($requestData, $payment)
    {
        $includesTax = $this->_scopeConfig->getValue(static::TAX_CALCULATION_INCLUDES_TAX);

        /**
 * @var \Magento\Eav\Model\Entity\Collection\AbstractCollection|array $cartData 
*/
        $cartData = $this->objectManager->create('Magento\Checkout\Model\Cart')->getItems();

        // Set loop variables
        $articles = $requestData;
        $count    = 1;

        foreach ($cartData as $item) {
            // Child objects of configurable products should not be requested because afterpay will fail on unit prices.
            if (empty($item) || $this->calculateProductPrice($item, $includesTax) == 0) {
                continue;
            }

            $article = $this->getArticleArrayLine(
                $count,
                $item->getQty() . ' x ' . $item->getName(),
                $item->getProductId(),
                1,
                $this->calculateProductPrice($item, $includesTax),
                $this->getTaxCategory($item->getTaxClassId())
            );

            $articles = array_merge($articles, $article);

            if ($count < self::AFTERPAY_MAX_ARTICLE_COUNT) {
                $count++;
                continue;
            }

            break;
        }

        $serviceLine = $this->getServiceCostLine($count, $payment, $includesTax);

        if (!empty($serviceLine)) {
            $requestData = array_merge($articles, $serviceLine);
            $count++;
        } else {
            $requestData = $articles;
        }

        $discountline = $this->getDiscountLine($count, $payment);

        if (!empty($discountline)) {
            $requestData = array_merge($requestData, $discountline);
            $count++;
        }


        if (!$includesTax) {
            $taxLine = $this->getTaxLine($count, $payment);
            $requestData = array_merge($requestData, $taxLine);
            $count++;
        }

        return $requestData;
    }

    /**
     * @param \Magento\Payment\Model\Order\Invoice $invoice
     *
     * @return array
     */
    public function getInvoiceArticleData($invoice)
    {
        $includesTax = $this->_scopeConfig->getValue(static::TAX_CALCULATION_INCLUDES_TAX);

        // Set loop variables
        $articles = array();
        $count    = 1;

        foreach ($invoice->getAllItems() as $item) {
            if (empty($item) || $this->calculateProductPrice($item, $includesTax) == 0) {
                continue;
            }

            $article = $this->getArticleArrayLine(
                $count,
                (int) $item->getQty() . ' x ' . $item->getName(),
                $item->getProductId(),
                1,
                $this->calculateProductPrice($item, $includesTax),
                $this->getTaxCategory($item->getTaxClassId())
            );

            $articles = array_merge($articles, $article);

            // Capture calculates discount per order line
            if ($item->getDiscountAmount() > 0) {
                $count++;
                $article = $this->getArticleArrayLine(
                    $count,
                    'Korting op '. (int) $item->getQty() . ' x ' . $item->getName(),
                    $item->getProductId(),
                    1,
                    number_format(($item->getDiscountAmount()*-1), 2),
                    $this->getTaxCategory($item->getTaxClassId())
                );
                $articles = array_merge($articles, $article);
            }

            if ($count < self::AFTERPAY_MAX_ARTICLE_COUNT) {
                $count++;
                continue;
            }

            break;
        }

        $requestData = $articles;

        return $requestData;
    }

    /**
     * @param                                      $lastestKey
     * @param \Magento\Payment\Model\Order\Invoice $invoice
     *
     * @return array
     */
    public function getPartialRequestGrandTotal($lastestKey, $invoice)
    {

        $article = $this->getArticleArrayLine(
            $lastestKey,
            'Total',
            '0',
            1,
            number_format($invoice->getBaseGrandTotal(), 2),
            4
        );

        return $article;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $productItem
     * @param                                 $includesTax
     *
     * @return mixed
     */
    public function calculateProductPrice($productItem, $includesTax)
    {
        if ($includesTax) {
            $productPrice = $productItem->getRowTotalInclTax();
        } else {
            $productPrice = $productItem->getRowTotal();
        }

        return $productPrice;
    }

    /**
     * Get the service cost lines (buckfee)
     *
     * @param (int)                                                                              $latestKey
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     * @param $includesTax
     *
     * @return   array
     * @internal param $ (int) $latestKey
     */
    public function getServiceCostLine($latestKey, $payment, $includesTax)
    {
        /**
 * @var \Magento\Sales\Model\Order $order 
*/
        $order = $payment->getOrder();
        /**
 * @noinspection PhpUndefinedMethodInspection 
*/
        $buckarooFee = $order->getBuckarooFee();

        if ($includesTax) {
            /**
 * @noinspection PhpUndefinedMethodInspection 
*/
            $buckarooFeeLine = $order->getBaseBuckarooFeeInclTax();
        } else {
            /**
 * @noinspection PhpUndefinedMethodInspection 
*/
            $buckarooFeeLine = $order->getBaseBuckarooFee();
        }

        /**
 * @var \TIG\Buckaroo\Helper\PaymentFee $feeHelper 
*/
        $feeHelper = $this->objectManager->create('\TIG\Buckaroo\Helper\PaymentFee');

        $article = [];

        if (false !== $buckarooFee && (double)$buckarooFee > 0) {
            $storeId = (int) $order->getStoreId();

            $article = $this->getArticleArrayLine(
                $latestKey,
                'Servicekosten',
                1,
                1,
                round($buckarooFeeLine, 2),
                $this->getTaxCategory($feeHelper->getBuckarooFeeTaxClass($storeId))
            );
        }
        // Add aditional shippin costs.
        $shippingCost = [];

        if ($order->getShippingAmount() > 0) {
            $shippingCost = [
                [
                    '_'       => $order->getShippingAmount() + $order->getShippingTaxAmount(),
                    'Name'    => 'ShippingCosts',
                ]
            ];
        }

        $article = array_merge($article, $shippingCost);

        return $article;
    }

    /**
     * Get the discount cost lines
     *
     * @param (int)                                                                              $latestKey
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     *
     * @return array
     */
    public function getDiscountLine($latestKey, $payment)
    {
        /**
 * @var \Magento\Sales\Model\Order $order 
*/
        $order      = $payment->getOrder();

        $article = [];

        if ($order->getDiscountAmount() < 0) {
            $article = $this->getArticleArrayLine(
                $latestKey,
                'Korting',
                1,
                1,
                number_format($order->getDiscountAmount(), 2),
                4
            );
        }

        return $article;
    }

    /**
     * Get the tax line
     *
     * @param (int)                                                                              $latestKey
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     *
     * @return array
     */
    public function getTaxLine($latestKey, $payment)
    {
        /**
 * @var \Magento\Sales\Model\Order $order 
*/
        $order      = $payment->getOrder();

        $article = $this->getArticleArrayLine(
            $latestKey,
            'BTW',
            2,
            1,
            number_format($order->getTaxAmount(), 2),
            4
        );

        return $article;
    }

    /**
     * @param $latestKey
     * @param $articleDescription
     * @param $articleId
     * @param $articleQuantity
     * @param $articleUnitPrice
     * @param $articleVatCategory
     *
     * @return array
     */
    public function getArticleArrayLine(
        $latestKey,
        $articleDescription,
        $articleId,
        $articleQuantity,
        $articleUnitPrice,
        $articleVatCategory
    ) {
        $article = [
            [
                '_'       => $articleDescription,
                'Name'    => 'ArticleDescription',
                'GroupID' => $latestKey
            ],
            [
                '_'       => $articleId,
                'Name'    => 'ArticleId',
                'GroupID' => $latestKey
            ],
            [
                '_'       => $articleQuantity,
                'Name'    => 'ArticleQuantity',
                'GroupID' => $latestKey
            ],
            [
                '_'       => $articleUnitPrice,
                'Name'    => 'ArticleUnitPrice',
                'GroupID' => $latestKey
            ],
            [
                '_'       => $articleVatCategory,
                'Name'    => 'ArticleVatCategory',
                'GroupID' => $latestKey
            ]
        ];

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
        /**
 * @var \TIG\Buckaroo\Model\ConfigProvider\Method\Afterpay $afterPayConfig 
*/
        $afterPayConfig = $this->configProviderMethodFactory
            ->get(\TIG\Buckaroo\Model\Method\Afterpay::PAYMENT_METHOD_CODE);

        $highClasses   = explode(',', $afterPayConfig->getHighTaxClasses());
        $middleClasses = explode(',', $afterPayConfig->getMiddleTaxClasses());
        $lowClasses    = explode(',', $afterPayConfig->getLowTaxClasses());
        $zeroClasses   = explode(',', $afterPayConfig->getZeroTaxClasses());

        if (in_array($taxClassId, $highClasses)) {
            $taxCategory = 1;
        } elseif (in_array($taxClassId, $middleClasses)) {
            $taxCategory = 5;
        } elseif (in_array($taxClassId, $lowClasses)) {
            $taxCategory = 2;
        } elseif (in_array($taxClassId, $zeroClasses)) {
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
        /**
 * @var \Magento\Sales\Api\Data\OrderAddressInterface $billingAddress 
*/
        $billingAddress = $payment->getOrder()->getBillingAddress();
        $streetFormat   = $this->formatStreet($billingAddress->getStreet());

        $birthDayStamp = str_replace('/', '-', $payment->getAdditionalInformation('customer_DoB'));

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
                '_'    => $birthDayStamp,
                'Name' => 'BillingBirthDate',
            ],
            [
                '_'    => $streetFormat['street'],
                'Name' => 'BillingStreet',
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

        if (!empty($streetFormat['house_number'])) {
            $billingData[] = [
                '_'    => $streetFormat['house_number'],
                'Name' => 'BillingHouseNumber',
            ];
        }

        if (!empty($streetFormat['number_addition'])) {
            $billingData[] = [
                '_'    => $streetFormat['number_addition'],
                'Name' => 'BillingHouseNumberSuffix',
            ];
        }

        return $billingData;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     *
     * @return array
     */
    public function getRequestShippingData($payment)
    {
        /**
 * @var \Magento\Sales\Api\Data\OrderAddressInterface $shippingAddress 
*/
        $shippingAddress = $payment->getOrder()->getShippingAddress();
        $streetFormat    = $this->formatStreet($shippingAddress->getStreet());

        $birthDayStamp = str_replace('/', '-', $payment->getAdditionalInformation('customer_DoB'));

        $shippingData = [
            [
                '_'    => $shippingAddress->getFirstname(),
                'Name' => 'ShippingTitle',
            ],
            [
                '_'    => $payment->getAdditionalInformation('customer_gender'),
                'Name' => 'ShippingGender',
            ],
            [
                '_'    => strtoupper(substr($shippingAddress->getFirstname(), 0, 1)),
                'Name' => 'ShippingInitials',
            ],
            [
                '_'    => $shippingAddress->getLastName(),
                'Name' => 'ShippingLastName',
            ],
            [
                '_'    => $birthDayStamp,
                'Name' => 'ShippingBirthDate',
            ],
            [
                '_'    => $streetFormat['street'],
                'Name' => 'ShippingStreet',
            ],
            [
                '_'    => $shippingAddress->getPostcode(),
                'Name' => 'ShippingPostalCode',
            ],
            [
                '_'    => $shippingAddress->getCity(),
                'Name' => 'ShippingCity',
            ],
            [
                '_'    => $shippingAddress->getCountryId(),
                'Name' => 'ShippingCountryCode',
            ],
            [
                '_'    => $shippingAddress->getEmail(),
                'Name' => 'ShippingEmail',
            ],
            [
                '_'    => $shippingAddress->getTelephone(),
                'Name' => 'ShippingPhoneNumber',
            ],
            [
                '_'    => $shippingAddress->getCountryId(),
                'Name' => 'ShippingLanguage',
            ],
        ];

        if (!empty($streetFormat['house_number'])) {
            $shippingData[] = [
                '_'    => $streetFormat['house_number'],
                'Name' => 'ShippingHouseNumber',
            ];
        }

        if (!empty($streetFormat['number_addition'])) {
            $shippingData[] = [
                '_'    => $streetFormat['number_addition'],
                'Name' => 'ShippingHouseNumberSuffix',
            ];
        }

        return $shippingData;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     *
     * @return array
     */
    public function getRequestCustomerData($payment)
    {
        $accept = 'false';
        if ($payment->getAdditionalInformation('termsCondition')) {
            $accept = 'true';
        }

        $customerData = [
            [
                '_'    => $this->getRemoteAddress(),
                'Name' => 'CustomerIPAddress',
            ],
            [
                '_'    => $accept,
                'Name' => 'Accept',
            ]
        ];

        // Only required if afterpay paymentmethod is acceptgiro.
        if ($payment->getAdditionalInformation('customer_iban')) {
            $accountNumber = [
                [
                    '_'    => $payment->getAdditionalInformation('customer_iban'),
                    'Name' => 'CustomerAccountNumber',
                ]
            ];

            $customerData = array_merge($customerData, $accountNumber);
        }

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

        if (preg_match('#^(.*?)([0-9]+)(.*)#s', $street, $matches)) {
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
     * @param bool  $ipToLong
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
