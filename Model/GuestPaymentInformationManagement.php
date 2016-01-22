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
 * @copyright   Copyright (c) 2015 TIG B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */

namespace TIG\Buckaroo\Model;

use Magento\Checkout\Model\GuestPaymentInformationManagement as MagentoGuestPaymentInformationManagement;
use TIG\Buckaroo\Api\GuestPaymentInformationManagementInterface;

// @codingStandardsIgnoreStart
class GuestPaymentInformationManagement extends MagentoGuestPaymentInformationManagement implements GuestPaymentInformationManagementInterface
// @codingStandardsIgnoreEnd
{

    protected $registry = null;
    protected $logger = null;

    /**
     * @param \Magento\Quote\Api\GuestBillingAddressManagementInterface   $billingAddressManagement
     * @param \Magento\Quote\Api\GuestPaymentMethodManagementInterface    $paymentMethodManagement
     * @param \Magento\Quote\Api\GuestCartManagementInterface             $cartManagement
     * @param \Magento\Checkout\Api\PaymentInformationManagementInterface $paymentInformationManagement
     * @param \Magento\Quote\Model\QuoteIdMaskFactory                     $quoteIdMaskFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface                  $cartRepository
     * @param \Magento\Framework\Registry                                 $registry
     * @param \Psr\Log\LoggerInterface                                    $logger
     *
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Quote\Api\GuestBillingAddressManagementInterface $billingAddressManagement,
        \Magento\Quote\Api\GuestPaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Quote\Api\GuestCartManagementInterface $cartManagement,
        \Magento\Checkout\Api\PaymentInformationManagementInterface $paymentInformationManagement,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Framework\Registry $registry,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct(
            $billingAddressManagement,
            $paymentMethodManagement,
            $cartManagement,
            $paymentInformationManagement,
            $quoteIdMaskFactory,
            $cartRepository
        );
        $this->registry = $registry;
        $this->logger = $logger;
    }

    /**
     * Set payment information and place order for a specified cart.
     *
     * @param int $cartId
     * @param string $email
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return string
     */
    public function buckarooSavePaymentInformationAndPlaceOrder(
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        $this->savePaymentInformationAndPlaceOrder($cartId, $email, $paymentMethod, $billingAddress);

        $this->logger->debug('-[RESULT]----------------------------------------');
        $this->logger->debug(print_r($this->registry->registry('buckaroo_response'), true));
        $this->logger->debug('-------------------------------------------------');

        $response = [];
        if ($this->registry && $this->registry->registry('buckaroo_response')) {
            $response = $this->registry->registry('buckaroo_response')[0];
        }
        return json_encode($response);
    }
}
