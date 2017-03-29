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

abstract class AbstractMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    const BUCKAROO_ORIGINAL_TRANSACTION_KEY_KEY = 'buckaroo_original_transaction_key';

    /**
     * The regex used to validate the entered BIC number
     */
    const BIC_NUMBER_REGEX = '^([a-zA-Z]){4}([a-zA-Z]){2}([0-9a-zA-Z]){2}([0-9a-zA-Z]{3})?$^';

    /**
     * @var string
     */
    public $buckarooPaymentMethodCode;

    /**
     * @var \TIG\Buckaroo\Gateway\GatewayInterface
     */
    protected $gateway;

    /**
     * @var \TIG\Buckaroo\Gateway\Http\TransactionBuilderFactory
     */
    protected $transactionBuilderFactory;

    /**
     * @var \TIG\Buckaroo\Model\ValidatorFactory
     */
    protected $validatorFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $messageManager;

    /**
     * @var \TIG\Buckaroo\Helper\Data
     */
    public $helper;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    public $priceHelper;

    /**
     * @var \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface
     */
    public $payment;

    /**
     * @var \TIG\Buckaroo\Model\ConfigProvider\Factory
     */
    public $configProviderFactory;

    /**
     * @var \TIG\Buckaroo\Model\ConfigProvider\Method\Factory
     */
    public $configProviderMethodFactory;

    /**
     * @var \TIG\Buckaroo\Model\RefundFieldsFactory
     */
    public $refundFieldsFactory;

    /**
     * @var bool
     */
    public $closeOrderTransaction       = true;

    /**
     * @var bool
     */
    public $closeAuthorizeTransaction   = true;

    /**
     * @var bool
     */
    public $closeCaptureTransaction     = true;

    /**
     * @var bool
     */
    public $closeRefundTransaction      = true;

    /**
     * @var bool
     */
    public $closeCancelTransaction      = true;

    /**
     * @var bool|string
     */
    public $orderPlaceRedirectUrl       = true;

    /**
     * @var bool
     */
    public $usesRedirect            = true;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    // @codingStandardsIgnoreStart
    /**
     * @var string
     */
    protected $_infoBlockType = 'TIG\Buckaroo\Block\Info';
    // @codingStandardsIgnoreEnd

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Developer\Helper\Data
     */
    protected $developmentHelper;

    /**
     * @param \Magento\Framework\ObjectManagerInterface               $objectManager
     * @param \Magento\Framework\Model\Context                        $context
     * @param \Magento\Framework\Registry                             $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory       $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory            $customAttributeFactory
     * @param \Magento\Payment\Helper\Data                            $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface      $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger                    $logger
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb           $resourceCollection
     * @param \TIG\Buckaroo\Gateway\GatewayInterface                  $gateway
     * @param \TIG\Buckaroo\Gateway\Http\TransactionBuilderFactory    $transactionBuilderFactory
     * @param \TIG\Buckaroo\Model\ValidatorFactory                    $validatorFactory
     * @param \Magento\Framework\Message\ManagerInterface             $messageManager
     * @param \TIG\Buckaroo\Helper\Data                               $helper
     * @param \Magento\Framework\App\RequestInterface                 $request
     * @param \TIG\Buckaroo\Model\RefundFieldsFactory                 $refundFieldsFactory
     * @param \TIG\Buckaroo\Model\ConfigProvider\Factory              $configProviderFactory
     * @param \TIG\Buckaroo\Model\ConfigProvider\Method\Factory       $configProviderMethodFactory
     * @param \Magento\Framework\Pricing\Helper\Data                  $priceHelper
     * @param \Magento\Developer\Helper\Data                          $developmentHelper
     * @param array                                                   $data
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \TIG\Buckaroo\Gateway\GatewayInterface $gateway = null,
        \TIG\Buckaroo\Gateway\Http\TransactionBuilderFactory $transactionBuilderFactory = null,
        \TIG\Buckaroo\Model\ValidatorFactory $validatorFactory = null,
        \Magento\Framework\Message\ManagerInterface $messageManager = null,
        \TIG\Buckaroo\Helper\Data $helper = null,
        \Magento\Framework\App\RequestInterface $request = null,
        \TIG\Buckaroo\Model\RefundFieldsFactory $refundFieldsFactory = null,
        \TIG\Buckaroo\Model\ConfigProvider\Factory $configProviderFactory = null,
        \TIG\Buckaroo\Model\ConfigProvider\Method\Factory $configProviderMethodFactory = null,
        \Magento\Framework\Pricing\Helper\Data $priceHelper = null,
        \Magento\Developer\Helper\Data $developmentHelper = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        /**
         * @todo : Remove usage of objectManager, better to use DI.
         */
        $this->objectManager                = $objectManager;
        $this->gateway                      = $gateway;
        $this->transactionBuilderFactory    = $transactionBuilderFactory;
        $this->validatorFactory             = $validatorFactory;
        $this->messageManager               = $messageManager;
        $this->helper                       = $helper;
        $this->request                      = $request;
        $this->refundFieldsFactory          = $refundFieldsFactory;
        $this->configProviderFactory        = $configProviderFactory;
        $this->configProviderMethodFactory  = $configProviderMethodFactory;
        $this->priceHelper                  = $priceHelper;
        $this->developmentHelper            = $developmentHelper;

        $this->gateway->setMode(
            $this->helper->getMode($this->buckarooPaymentMethodCode)
        );
    }

    /**
     * @return bool
     */
    public function canRefund()
    {
        if (!parent::canRefund()) {
            return false;
        }

        /**
         * @var \TIG\Buckaroo\Model\ConfigProvider\Refund $refundConfig
         */
        $refundConfig = $this->configProviderFactory->get('refund');

        if ($refundConfig->getEnabled()) {
            return true;
        }

        return false;
    }

    /**
     * @param \Magento\Framework\DataObject $data
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        if ($data instanceof \Magento\Framework\DataObject) {
            /**
             * @noinspection PhpUndefinedMethodInspection
             */
            $this->getInfoInstance()->setAdditionalInformation(
                'buckaroo_skip_validation',
                $data->getBuckarooSkipValidation()
            );
        }
        return $this;
    }

    /**
     * Older version expect different formatted $data
     *
     * @param  \Magento\Framework\DataObject $data
     * @todo   Think of a nicer way to implement the version differences in one codebase
     * @return array
     */
    public function assignDataConvertAllVersionsArray(\Magento\Framework\DataObject $data)
    {
        if (!is_array($data)) {
            $data->convertToArray();
        }

        $magentoVersion = str_replace('.', '', $this->getMagentoVersion());

        // Below 2.0.5 versions do not consists the key additional_data.
        if ($magentoVersion < '205') {
            $data['additional_data'] = $data;
        }

        return $data;
    }

    /**
     * Determine Magento 2 Version used for changing assignData output data on differences
     * between 2.0.4 and 2.0.7+
     *
     * @return string
     */
    public function getMagentoVersion()
    {
        $productMetadata = $this->objectManager->get('Magento\Framework\App\ProductMetadataInterface');
        return $productMetadata->getVersion();
    }

    /**
     * Check whether payment method can be used
     *
     * @param  \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if (null == $quote) {
            return false;
        }
        /**
         * @var \TIG\Buckaroo\Model\ConfigProvider\Account $accountConfig
         */
        $accountConfig = $this->configProviderFactory->get('account');
        if ($accountConfig->getActive() == 0) {
            return false;
        }

        $areaCode = $this->objectManager->get('Magento\Framework\App\State')->getAreaCode();
        if ('adminhtml' === $areaCode
            && $this->getConfigData('available_in_backend') !== null
            && $this->getConfigData('available_in_backend') == 0
        ) {
            return false;
        }

        if (!$this->isAvailableBasedOnIp($accountConfig, $quote)) {
            return false;
        }

        if (!$this->isAvailableBasedOnAmount($quote)) {
            return false;
        }

        if (!$this->isAvailableBasedOnCurrency($quote)) {
            return false;
        }

        return parent::isAvailable($quote);
    }

    /**
     * Check if this payment method is limited by IP.
     *
     * @param \TIG\Buckaroo\Model\ConfigProvider\Account $accountConfig
     * @param \Magento\Quote\Api\Data\CartInterface      $quote
     *
     * @return bool
     */
    protected function isAvailableBasedOnIp(
        \TIG\Buckaroo\Model\ConfigProvider\Account $accountConfig,
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        $methodValue = $this->getConfigData('limit_by_ip');
        if ($accountConfig->getLimitByIp() == 1 || $methodValue == 1) {
            $storeId = $quote ? $quote->getStoreId() : null;
            $isAllowed = $this->developmentHelper->isDevAllowed($storeId);

            if (!$isAllowed) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the grand total exceeds the maximum allowed total.
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     *
     * @return bool
     */
    protected function isAvailableBasedOnAmount(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        $storeId = $quote->getStoreId();
        $maximum = $this->getConfigData('max_amount', $storeId);
        $minimum = $this->getConfigData('min_amount', $storeId);

        /**
         * @var \Magento\Quote\Model\Quote $quote
         */
        $total = $quote->getGrandTotal();

        if ($maximum !== null && $total > $maximum) {
            return false;
        }

        if ($minimum !== null && $total < $minimum) {
            return false;
        }

        return true;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     *
     * @return bool
     */
    protected function isAvailableBasedOnCurrency(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        $allowedCurrenciesRaw = $this->getConfigData('allowed_currencies');
        $allowedCurrencies = explode(',', $allowedCurrenciesRaw);

        $currentCurrency = $quote->getCurrency()->getQuoteCurrencyCode();

        return $allowedCurrenciesRaw === null || in_array($currentCurrency, $allowedCurrencies);
    }

    /**
     * Retrieve information from payment configuration
     *
     * @param string                                     $field
     * @param int|string|null|\Magento\Store\Model\Store $storeId
     *
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        if ('order_place_redirect_url' === $field) {
            return $this->getOrderPlaceRedirectUrl();
        }
        return parent::getConfigData($field, $storeId);
    }

    /**
     * @return string
     * @throws \TIG\Buckaroo\Exception
     */
    public function getTitle()
    {
        $title = $this->getConfigData('title');

        if (!$this->configProviderMethodFactory->has($this->buckarooPaymentMethodCode)) {
            return $title;
        }

        $paymentFee = trim($this->configProviderMethodFactory->get($this->buckarooPaymentMethodCode)->getPaymentFee());
        if (!$paymentFee || (float) $paymentFee < 0.01) {
            return $title;
        }

        if (strpos($paymentFee, '%') === false) {
            $title .= ' + ' . $this->priceHelper->currency(number_format($paymentFee, 2), true, false);
        } else {
            $title .= ' + ' . $paymentFee;
        }

        return $title;
    }

    /**
     * @return bool|string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return $this->orderPlaceRedirectUrl;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     * @param float                                                                              $amount
     *
     * @return $this
     *
     * @throws \TIG\Buckaroo\Exception|\LogicException|\InvalidArgumentException
     */
    public function order(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$payment instanceof \Magento\Sales\Api\Data\OrderPaymentInterface
            || !$payment instanceof \Magento\Payment\Model\InfoInterface
        ) {
            throw new \InvalidArgumentException(
                'Buckaroo requires the payment to be an instance of "\Magento\Sales\Api\Data\OrderPaymentInterface"' .
                ' and "\Magento\Payment\Model\InfoInterface".'
            );
        }

        parent::order($payment, $amount);

        $this->payment = $payment;

        $transactionBuilder = $this->getOrderTransactionBuilder($payment);

        if (!$transactionBuilder) {
            throw new \LogicException(
                'Order action is not implemented for this payment method.'
            );
        } elseif ($transactionBuilder === true) {
            return $this;
        }

        $transaction = $transactionBuilder->build();

        $response = $this->orderTransaction($transaction);

        $this->saveTransactionData($response[0], $payment, $this->closeOrderTransaction, true);

        // SET REGISTRY BUCKAROO REDIRECT
        $this->_registry->register('buckaroo_response', $response);

        $this->afterOrder($payment, $response);

        return $this;
    }

    /**
     * Should be overwritten by the respective payment method class when it has a specific failure message.
     *
     * @param $transactionResponse
     *
     * @return string
     */
    protected function getFailureMessageFromMethod($transactionResponse)
    {
        return '';
    }

    /**
     * @param $response
     *
     * @return string
     */
    protected function getFailureMessage($response)
    {
        $message = 'Unfortunately the payment was unsuccessful. Please try again or choose a different payment method.';

        if (!isset($response[0]) || empty($response[0])) {
            return $message;
        }

        $transactionResponse = $response[0];
        $responseCode = $transactionResponse->Status->Code->Code;
        $billingCountry = $this->payment->getOrder()->getBillingAddress()->getCountryId();

        if ($billingCountry == 'NL' && $responseCode == 490) {
            $methodMessage = $this->getFailureMessageFromMethod($transactionResponse);
            $message = strlen($methodMessage) > 0 ? $methodMessage : $message;
        }

        return $message;
    }

    /**
     * @param \TIG\Buckaroo\Gateway\Http\Transaction $transaction
     *
     * @return array
     * @throws \TIG\Buckaroo\Exception
     */
    public function orderTransaction(\TIG\Buckaroo\Gateway\Http\Transaction $transaction)
    {
        $response = $this->gateway->authorize($transaction);

        if (!$this->validatorFactory->get('transaction_response')->validate($response)) {
            throw new \TIG\Buckaroo\Exception(
                new \Magento\Framework\Phrase(
                    'The transaction response could not be verified.'
                )
            );
        }

        if (!$this->validatorFactory->get('transaction_response_status')->validate($response)) {
            $failureMessage = $this->getFailureMessage($response);

            throw new \TIG\Buckaroo\Exception(
                new \Magento\Framework\Phrase($failureMessage)
            );
        }

        return $response;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     * @param float                                                                              $amount
     *
     * @return $this
     *
     * @throws \TIG\Buckaroo\Exception|\LogicException|\InvalidArgumentException
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$payment instanceof \Magento\Sales\Api\Data\OrderPaymentInterface
            || !$payment instanceof \Magento\Payment\Model\InfoInterface
        ) {
            throw new \InvalidArgumentException(
                'Buckaroo requires the payment to be an instance of "\Magento\Sales\Api\Data\OrderPaymentInterface"' .
                ' and "\Magento\Payment\Model\InfoInterface".'
            );
        }

        parent::authorize($payment, $amount);

        $this->payment = $payment;

        $transactionBuilder = $this->getAuthorizeTransactionBuilder($payment);

        if (!$transactionBuilder) {
            throw new \LogicException(
                'Authorize action is not implemented for this payment method.'
            );
        } elseif ($transactionBuilder === true) {
            return $this;
        }

        $transaction = $transactionBuilder->build();

        $response = $this->authorizeTransaction($transaction);

        $this->saveTransactionData($response[0], $payment, $this->closeAuthorizeTransaction, true);

        // SET REGISTRY BUCKAROO REDIRECT
        $this->_registry->register('buckaroo_response', $response);

        /**
         * Fix for Magento setting the order to suspected fraud when the order currency doe snot match with the
         * payment's currency.
         */
        $configProvider = $this->configProviderMethodFactory->get($this->buckarooPaymentMethodCode);
        $allowedCurrencies = $configProvider->getAllowedCurrencies();

        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        if (!$payment->getCurrencyCode() || !in_array($payment->getCurrencyCode(), $allowedCurrencies)) {
            /**
             * @noinspection PhpUndefinedMethodInspection
             */
            $this->payment->setIsFraudDetected(false);
        }

        $this->afterAuthorize($payment, $response);

        return $this;
    }

    /**
     * @param \TIG\Buckaroo\Gateway\Http\Transaction $transaction
     *
     * @return array
     * @throws \TIG\Buckaroo\Exception
     */
    public function authorizeTransaction(\TIG\Buckaroo\Gateway\Http\Transaction $transaction)
    {
        $response = $this->gateway->authorize($transaction);

        if (!$this->validatorFactory->get('transaction_response')->validate($response)) {
            throw new \TIG\Buckaroo\Exception(
                new \Magento\Framework\Phrase(
                    'The transaction response could not be verified.'
                )
            );
        }

        if (!$this->validatorFactory->get('transaction_response_status')->validate($response)) {
            $failureMessage = $this->getFailureMessage($response);

            throw new \TIG\Buckaroo\Exception(
                new \Magento\Framework\Phrase($failureMessage)
            );
        }

        return $response;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     * @param float                                                                              $amount
     *
     * @return $this
     *
     * @throws \TIG\Buckaroo\Exception|\LogicException|\InvalidArgumentException
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$payment instanceof \Magento\Sales\Api\Data\OrderPaymentInterface
            || !$payment instanceof \Magento\Payment\Model\InfoInterface
        ) {
            throw new \InvalidArgumentException(
                'Buckaroo requires the payment to be an instance of "\Magento\Sales\Api\Data\OrderPaymentInterface"' .
                ' and "\Magento\Payment\Model\InfoInterface".'
            );
        }

        parent::capture($payment, $amount);

        $this->payment = $payment;

        $transactionBuilder = $this->getCaptureTransactionBuilder($payment);

        if (!$transactionBuilder) {
            throw new \LogicException(
                'Capture action is not implemented for this payment method.'
            );
        } elseif ($transactionBuilder === true) {
            return $this;
        }

        $transaction = $transactionBuilder->build();

        $response = $this->captureTransaction($transaction);

        $this->saveTransactionData($response[0], $payment, $this->closeCaptureTransaction, true);

        // SET REGISTRY BUCKAROO REDIRECT
        $this->_registry->register('buckaroo_response', $response);

        $this->afterCapture($payment, $response);

        return $this;
    }

    /**
     * @param \TIG\Buckaroo\Gateway\Http\Transaction $transaction
     *
     * @return array|\StdClass
     * @throws \TIG\Buckaroo\Exception
     */
    public function captureTransaction(\TIG\Buckaroo\Gateway\Http\Transaction $transaction)
    {
        $response = $this->gateway->capture($transaction);

        if (!$this->validatorFactory->get('transaction_response')->validate($response)) {
            throw new \TIG\Buckaroo\Exception(
                new \Magento\Framework\Phrase(
                    'The transaction response could not be verified.'
                )
            );
        }

        if (!$this->validatorFactory->get('transaction_response_status')->validate($response)) {
            throw new \TIG\Buckaroo\Exception(
                new \Magento\Framework\Phrase(
                    'Unfortunately the payment was unsuccessful. Please try again or choose a different payment method.'
                )
            );
        }

        return $response;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     * @param float                                                                              $amount
     *
     * @return $this
     *
     * @throws \TIG\Buckaroo\Exception|\LogicException|\InvalidArgumentException
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$payment instanceof \Magento\Sales\Api\Data\OrderPaymentInterface
            || !$payment instanceof \Magento\Payment\Model\InfoInterface
        ) {
            throw new \InvalidArgumentException(
                'Buckaroo requires the payment to be an instance of "\Magento\Sales\Api\Data\OrderPaymentInterface"' .
                ' and "\Magento\Payment\Model\InfoInterface".'
            );
        }

        parent::refund($payment, $amount);

        $this->payment = $payment;

        $transactionBuilder = $this->getRefundTransactionBuilder($payment);

        if (!$transactionBuilder) {
            throw new \LogicException(
                'Refund action is not implemented for this payment method.'
            );
        } elseif ($transactionBuilder === true) {
            return $this;
        }
        $transactionBuilder->setAmount($amount);

        $transaction = $transactionBuilder->build();

        $response = $this->refundTransaction($transaction);

        $this->saveTransactionData($response[0], $payment, $this->closeRefundTransaction, false);

        $this->afterRefund($payment, $response);

        return $this;
    }

    /**
     * @param \TIG\Buckaroo\Gateway\Http\Transaction $transaction
     *
     * @return array|\StdClass
     * @throws \TIG\Buckaroo\Exception
     */
    public function refundTransaction(\TIG\Buckaroo\Gateway\Http\Transaction $transaction)
    {
        $response = $this->gateway->refund($transaction);

        if (!$this->validatorFactory->get('transaction_response')->validate($response)) {
            throw new \TIG\Buckaroo\Exception(
                new \Magento\Framework\Phrase(
                    'The transaction response could not be verified.'
                )
            );
        }

        if (!$this->validatorFactory->get('transaction_response_status')->validate($response)) {
            throw new \TIG\Buckaroo\Exception(
                new \Magento\Framework\Phrase(
                    'Unfortunately the payment was unsuccessful. Please try again or choose a different payment method.'
                )
            );
        }

        return $response;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     *
     * @return $this
     *
     * @throws \TIG\Buckaroo\Exception|\LogicException|\InvalidArgumentException
     */
    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        parent::cancel($payment);
        return $this->void($payment);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     *
     * @return $this
     *
     * @throws \TIG\Buckaroo\Exception|\LogicException|\InvalidArgumentException
     */
    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        if (!$payment instanceof \Magento\Sales\Api\Data\OrderPaymentInterface
            || !$payment instanceof \Magento\Payment\Model\InfoInterface
        ) {
            throw new \InvalidArgumentException(
                'Buckaroo requires the payment to be an instance of "\Magento\Sales\Api\Data\OrderPaymentInterface"' .
                ' and "\Magento\Payment\Model\InfoInterface".'
            );
        }

        $this->_canVoid = true;
        parent::void($payment);

        $this->payment = $payment;

        // Do not cancel authorize when accept authorize is failed.
        // buckaroo_failed_authorize is set in Push.php
        if ($this->payment->getAdditionalInformation('buckaroo_failed_authorize') == 1) {
            return $this;
        }
        

        $transactionBuilder = $this->getVoidTransactionBuilder($payment);

        if (!$transactionBuilder) {
            throw new \LogicException(
                'Void action is not implemented for this payment method.'
            );
        } elseif ($transactionBuilder === true) {
            return $this;
        }

        $transaction = $transactionBuilder->build();

        $response = $this->voidTransaction($transaction);

        $this->saveTransactionData($response[0], $payment, $this->closeCancelTransaction, true);

        // SET REGISTRY BUCKAROO REDIRECT
        $this->_registry->register('buckaroo_response', $response);

        $this->afterVoid($payment, $response);

        return $this;
    }

    /**
     * @param \TIG\Buckaroo\Gateway\Http\Transaction $transaction
     *
     * @return array|\StdClass
     * @throws \TIG\Buckaroo\Exception
     */
    public function voidTransaction(\TIG\Buckaroo\Gateway\Http\Transaction $transaction)
    {
        $response = $this->gateway->void($transaction);

        if (!$this->validatorFactory->get('transaction_response')->validate($response)) {
            throw new \TIG\Buckaroo\Exception(
                new \Magento\Framework\Phrase(
                    'The transaction response could not be verified.'
                )
            );
        }

        if (!$this->validatorFactory->get('transaction_response_status')->validate($response)) {
            throw new \TIG\Buckaroo\Exception(
                new \Magento\Framework\Phrase(
                    'Unfortunately the payment authorization could not be voided. Please try again.'
                )
            );
        }

        return $response;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     * @param array|\StdCLass                                                                    $response
     *
     * @return $this
     */
    protected function afterOrder($payment, $response)
    {
        return $this->dispatchAfterEvent('tig_buckaroo_method_order_after', $payment, $response);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     * @param array|\StdCLass                                                                    $response
     *
     * @return $this
     */
    protected function afterAuthorize($payment, $response)
    {
        return $this->dispatchAfterEvent('tig_buckaroo_method_authorize_after', $payment, $response);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     * @param array|\StdCLass                                                                    $response
     *
     * @return $this
     */
    protected function afterCapture($payment, $response)
    {
        return $this->dispatchAfterEvent('tig_buckaroo_method_capture_after', $payment, $response);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     * @param array|\StdCLass                                                                    $response
     *
     * @return $this
     */
    protected function afterRefund($payment, $response)
    {
        return $this->dispatchAfterEvent('tig_buckaroo_method_refund_after', $payment, $response);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     * @param array|\StdCLass                                                                    $response
     *
     * @return $this
     */
    protected function afterCancel($payment, $response)
    {
        return $this->afterVoid($payment, $response);
    }
    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     * @param array|\StdCLass                                                                    $response
     *
     * @return $this
     */
    protected function afterVoid($payment, $response)
    {
        return $this->dispatchAfterEvent('tig_buckaroo_method_void_after', $payment, $response);
    }

    /**
     * @param $name
     * @param $payment
     * @param $response
     *
     * @return $this
     */
    protected function dispatchAfterEvent($name, $payment, $response)
    {
        $this->_eventManager->dispatch(
            $name,
            [
                'payment' => $payment,
                'response' => $response,
            ]
        );

        return $this;
    }

    /**
     * @param \StdClass                                                                          $response
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     * @param                                                                                    $close
     * @param bool                                                                               $saveId
     *
     * @return \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface
     */
    public function saveTransactionData(
        \StdClass $response,
        \Magento\Payment\Model\InfoInterface $payment,
        $close,
        $saveId = false
    ) {
        if (!empty($response->Key)) {
            $transactionKey = $response->Key;
            /**
             * @noinspection PhpUndefinedMethodInspection
             */
            $payment->setIsTransactionClosed($close);

            /**
             * Recursively convert object to array.
             */
            $arrayResponse = json_decode(json_encode($response), true);

            /**
             * Save the transaction's response as additional info for the transaction.
             */
            $rawInfo = $this->getTransactionAdditionalInfo($arrayResponse);

            /**
             * @noinspection PhpUndefinedMethodInspection
             */
            $payment->setTransactionAdditionalInfo(
                \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS,
                $rawInfo
            );

            /**
             * @noinspection PhpUndefinedMethodInspection
             */
            $payment->setTransactionId($transactionKey);

            /**
             * Save the payment's transaction key.
             */
            if ($saveId) {
                $payment->setAdditionalInformation(self::BUCKAROO_ORIGINAL_TRANSACTION_KEY_KEY, $transactionKey);
            }
        }

        return $payment;
    }

    /**
     * @param array $array
     *
     * @return array
     */
    public function getTransactionAdditionalInfo(array $array)
    {
        return $this->helper->getTransactionAdditionalInfo($array);
    }

    /**
     * @param string $paymentMethodCode
     *
     * @return array
     */
    public function addExtraFields($paymentMethodCode)
    {
        $requestParams = $this->request->getParams();
        $creditMemoParams = $requestParams['creditmemo'];

        $extraFields = $this->refundFieldsFactory->get($paymentMethodCode);

        $services = [];

        /**
         * If extra fields are found, attach these as 'RequestParameter' to the services.
         */
        if (!empty($extraFields)) {
            foreach ($extraFields as $extraField) {
                $code = $extraField['code'];
                $services['RequestParameter'][] = [
                    '_' => "$creditMemoParams[$code]",
                    'Name' => $code,
                ];
            }
        }

        return $services;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     *
     * @return \TIG\Buckaroo\Gateway\Http\TransactionBuilderInterface|bool
     */
    abstract public function getOrderTransactionBuilder($payment);

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     *
     * @return \TIG\Buckaroo\Gateway\Http\TransactionBuilderInterface|bool
     */
    abstract public function getAuthorizeTransactionBuilder($payment);

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     *
     * @return \TIG\Buckaroo\Gateway\Http\TransactionBuilderInterface|bool
     */
    abstract public function getCaptureTransactionBuilder($payment);

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     *
     * @return \TIG\Buckaroo\Gateway\Http\TransactionBuilderInterface|bool
     */
    abstract public function getRefundTransactionBuilder($payment);

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|\Magento\Payment\Model\InfoInterface $payment
     *
     * @return \TIG\Buckaroo\Gateway\Http\TransactionBuilderInterface|bool
     */
    abstract public function getVoidTransactionBuilder($payment);
}
