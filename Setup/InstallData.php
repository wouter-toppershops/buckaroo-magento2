<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace TIG\Buckaroo\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class InstallData
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @codeCoverageIgnore
 */
class InstallData implements \Magento\Framework\Setup\InstallDataInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
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
