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

namespace TIG\buckaroo\Block\Adminhtml\Order\Creditmemo\Create;

class BankFields extends \Magento\Backend\Block\Template
{

    /**
     * Xpath parts. Used in conjunction with the payment method code to find if any refund extra fields are used.
     */
    const XPATH_PAYMENT             = 'payment/';
    const XPATH_EXTRA_FIELDS        = '/refund_extra_fields';
    const XPATH_EXTRA_FIELDS_LABELS = '/refund_extra_fields_labels';

    protected $orderPaymentBlock    = 'order_payment';

    /**
     * @param \Magento\Backend\Block\Template\Context              $context
     * @param \TIG\Buckaroo\Gateway\Http\TransactionBuilderFactory $transactionBuilderFactory
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \TIG\Buckaroo\Gateway\Http\TransactionBuilderFactory $transactionBuilderFactory = null
    ) {
        $this->transactionBuilder = $transactionBuilderFactory;
        return parent::__construct($context);
    }

    /**
     * Get the payment method and dynamically find which extra fields (if any) need to be shown.
     *
     * @return array
     */
    public function getExtraFields()
    {
        $extraFields = array();
        $paymentMethod = $this->getPaymentMethod();

        $xpathFields = self::XPATH_PAYMENT . $paymentMethod . self::XPATH_EXTRA_FIELDS;
        $xpathLabels = self::XPATH_PAYMENT . $paymentMethod . self::XPATH_EXTRA_FIELDS_LABELS;

        /** If no payment method is found, return the empty array. */
        if(!$paymentMethod) {
            return $extraFields;
        }

        /**
         * get both the field codes and labels. These are used for the Buckaroo request (codes)
         * and human readability (labels)
         */
        $fields = $this->_scopeConfig->getValue($xpathFields);
        $fields = explode(',', $fields);

        $labels = $this->_scopeConfig->getValue($xpathLabels);
        $labels = explode(',', $labels);

        /** Parse the code and label in the same array, to keep the data paired. */
        foreach($fields as $key => $field) {
            $extraFields[$labels[$key]] = $field;
        }

        return $extraFields;
    }

    /**
     * Returns the Payment Method name. If something goes wrong, this will return false.
     *
     * @return string | false (when not found)
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPaymentMethod()
    {
        $paymentMethod = false;

        $layout = $this->getLayout();
        $paymentBlock = $layout->getBlock($this->orderPaymentBlock);

        if($paymentBlock) {
            $paymentMethod = $paymentBlock->getPayment()->getMethod();
        }

        return $paymentMethod;
    }

}