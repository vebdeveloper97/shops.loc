<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%product_items_balance}}`.
 */
class m201108_093527_add_party_number_column_to_product_items_balance_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%product_items_balance}}', 'party_number', $this->char(50));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%product_items_balance}}', 'party_number');
    }
}
