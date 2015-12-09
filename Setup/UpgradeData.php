<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace TIG\Buckaroo\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '0.1.1', '<')) {
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
    }
}
