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

namespace TIG\Buckaroo\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
{
    /**
     * @var \Magento\Sales\Setup\SalesSetupFactory
     */
    protected $salesSetupFactory;

    /**
     * @var \Magento\Quote\Setup\QuoteSetupFactory
     */
    protected $quoteSetupFactory;

    /**
     * @param \Magento\Sales\Setup\SalesSetupFactory $salesSetupFactory
     * @param \Magento\Quote\Setup\QuoteSetupFactory $quoteSetupFactory
     */
    public function __construct(
        \Magento\Sales\Setup\SalesSetupFactory $salesSetupFactory,
        \Magento\Quote\Setup\QuoteSetupFactory $quoteSetupFactory
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '0.1.1', '<')
            && version_compare($context->getVersion(), '0.1.0', '>=')
        ) {
            /**
             * Install order statuses from config
             */
            $setup->getConnection()->insert(
                $setup->getTable('sales_order_status'),
                [
                    'status' => 'tig_buckaroo_pending_payment',
                    'label'  => __('TIG Buckaroo Pending Payment'),
                ]
            );

            $setup->getConnection()->insert(
                $setup->getTable('sales_order_status_state'),
                [
                    'status'     => 'tig_buckaroo_pending_payment',
                    'state'      => 'processing',
                    'is_default' =>  0,
                ]
            );
        }

        if (version_compare($context->getVersion(), '0.1.3', '<')) {
            $quoteInstaller = $this->quoteSetupFactory->create(['resourceName' => 'quote_setup', 'setup' => $setup]);
            $salesInstaller = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $setup]);

            $quoteInstaller->addAttribute(
                'quote',
                'buckaroo_fee',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
            );
            $quoteInstaller->addAttribute(
                'quote',
                'base_buckaroo_fee',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
            );

            $quoteInstaller->addAttribute(
                'quote_address',
                'buckaroo_fee',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
            );
            $quoteInstaller->addAttribute(
                'quote_address',
                'base_buckaroo_fee',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
            );

            $salesInstaller->addAttribute(
                'order',
                'buckaroo_fee',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
            );
            $salesInstaller->addAttribute(
                'order',
                'base_buckaroo_fee',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
            );
            $salesInstaller->addAttribute(
                'order',
                'buckaroo_fee_invoiced',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
            );
            $salesInstaller->addAttribute(
                'order',
                'base_buckaroo_fee_invoiced',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
            );
            $salesInstaller->addAttribute(
                'order',
                'buckaroo_fee_refunded',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
            );
            $salesInstaller->addAttribute(
                'order',
                'base_buckaroo_fee_refunded',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
            );

            $salesInstaller->addAttribute(
                'invoice',
                'base_buckaroo_fee',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
            );
            $salesInstaller->addAttribute(
                'invoice',
                'buckaroo_fee',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
            );

            $salesInstaller->addAttribute(
                'creditmemo',
                'base_buckaroo_fee',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
            );
            $salesInstaller->addAttribute(
                'creditmemo',
                'buckaroo_fee',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
            );
        }
    }
}
