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

class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(
        \Magento\Framework\Setup\SchemaSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '0.1.2', '<')) {
            $this->addBuckarooFeeTable($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    protected function addBuckarooFeeTable(\Magento\Framework\Setup\SchemaSetupInterface $setup)
    {
        $installer = $setup;

        if (!$installer->tableExists('tig_buckaroo_quote_fee')) {
            $table = $installer->getConnection()
                               ->newTable($installer->getTable('tig_buckaroo_quote_fee'));
            $table->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true,
                ],
                'Entity ID'
            );

            $table->addColumn(
                'address_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                [
                    'unsigned' => true,
                ],
                'Address Id'
            );

            $table->addColumn(
                'buckaroo_fee',
                \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                '12,4',
                [
                    'default'  => '0.0000'
                ],
                'Buckaroo Fee'
            );

            $table->addColumn(
                'buckaroo_base_fee',
                \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                '12,4',
                [
                    'default'  => '0.0000'
                ],
                'Buckaroo Base Fee'
            );

            $table->addIndex(
                $installer->getIdxName(
                    $installer->getTable('tig_buckaroo_quote_fee'),
                    ['address_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['address_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            );

            $table->addForeignKey(
                $installer->getFkName(
                    $installer->getTable('tig_buckaroo_quote_fee'),
                    'address_id',
                    $installer->getTable('quote_address'),
                    'address_id'
                ),
                'address_id',
                $installer->getTable('quote_address'),
                'address_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );

            $table->setComment('TIG Buckaroo Quote Fee');

            $installer->getConnection()->createTable($table);
        }
    }
}
