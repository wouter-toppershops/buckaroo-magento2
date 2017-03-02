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

namespace TIG\Buckaroo\Model\Service\Plugin\PaypalSellersProtection;

class Push
{
    /**#@+
     * PayPal Seller's Protection eligibility types.
     */
    const ELIGIBILITY_INELIGIBLE                = 'Ineligible';
    const ELIGIBILITY_TYPE_ELIGIBLE             = 'Eligible';
    const ELIGIBILITY_TYPE_ITEM_NOT_RECEIVED    = 'ItemNotReceivedEligible';
    const ELIGIBILITY_TYPE_UNAUTHORIZED_PAYMENT = 'UnauthorizedPaymentEligible';
    const ELIGIBILITY_TYPE_NONE                 = 'None';
    /**#@-*/

    // @codingStandardsIgnoreStart
    /**#@+
     * Xpaths to the paypal seller protection custom order statuses.
     */
    const XPATH_PAYPAL_SELLER_PROTECTION_STATUS_ELIGIBLE             = 'payment/tig_buckaroo_paypal/sellers_protection_eligible';
    const XPATH_PAYPAL_SELLER_PROTECTION_STATUS_ITEM_NOT_RECEIVED    = 'payment/tig_buckaroo_paypal/sellers_protection_itemnotreceived_eligible';
    const XPATH_PAYPAL_SELLER_PROTECTION_STATUS_UNAUTHORIZED_PAYMENT = 'payment/tig_buckaroo_paypal/sellers_protection_unauthorizedpayment_eligible';
    const XPATH_PAYPAL_SELLER_PROTECTION_STATUS_NONE                 = 'payment/tig_buckaroo_paypal/sellers_protection_ineligible';
    /**#@-*/
    // @codingStandardsIgnoreEnd

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param \TIG\Buckaroo\Model\Push $push
     * @param boolean                  $result
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function afterProcessSucceededPush(
        \TIG\Buckaroo\Model\Push $push,
        $result
    ) {
        if (empty($push->postData['brq_service_paypal_protectioneligibility'])
            || empty($push->postData['brq_service_paypal_protectioneligibilitytype'])
        ) {
            return $result;
        }

        $eligibility = $push->postData['brq_service_paypal_protectioneligibility'];
        if ($eligibility == self::ELIGIBILITY_INELIGIBLE) {
            $eligibilityType = self::ELIGIBILITY_TYPE_NONE;
        } else {
            $eligibilityType = $push->postData['brq_service_paypal_protectioneligibilitytype'];
        }

        $order = $push->order;
        switch ($eligibilityType) {
            case self::ELIGIBILITY_TYPE_ELIGIBLE:
                $comment = __(
                    "Merchant is protected by PayPal Seller Protection Policy for both Unauthorized Payment and Item" .
                    " Not Received."
                );

                $status = $this->scopeConfig->getValue(
                    self::XPATH_PAYPAL_SELLER_PROTECTION_STATUS_ELIGIBLE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
                break;
            case self::ELIGIBILITY_TYPE_ITEM_NOT_RECEIVED:
                $comment = __("Merchant is protected by Paypal Seller Protection Policy for Item Not Received.");

                $status = $this->scopeConfig->getValue(
                    self::XPATH_PAYPAL_SELLER_PROTECTION_STATUS_ITEM_NOT_RECEIVED,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
                break;
            case self::ELIGIBILITY_TYPE_UNAUTHORIZED_PAYMENT:
                $comment = __("Merchant is protected by Paypal Seller Protection Policy for Unauthorized Payment.");

                $status = $this->scopeConfig->getValue(
                    self::XPATH_PAYPAL_SELLER_PROTECTION_STATUS_UNAUTHORIZED_PAYMENT,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
                break;
            case self::ELIGIBILITY_TYPE_NONE:
                $comment = __("Merchant is not protected under the Seller Protection Policy.");

                $status = $this->scopeConfig->getValue(
                    self::XPATH_PAYPAL_SELLER_PROTECTION_STATUS_NONE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
                break;
            default:
                throw new \InvalidArgumentException("Invalid eligibility type.");
        }
        $order->addStatusHistoryComment($comment, $status ? $status : false);

        return $result;
    }
}
