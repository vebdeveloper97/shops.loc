<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%product_items_balance}}`.
 */
class m201106_050401_add_type_column_to_product_items_balance_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%product_items_balance}}', 'type', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%product_items_balance}}', 'type');
    }
}
