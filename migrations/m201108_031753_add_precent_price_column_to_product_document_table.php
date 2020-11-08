<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%product_document}}`.
 */
class m201108_031753_add_precent_price_column_to_product_document_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%product_document}}', 'precent_price', $this->decimal(20,3));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%product_document}}', 'precent_price');
    }
}
