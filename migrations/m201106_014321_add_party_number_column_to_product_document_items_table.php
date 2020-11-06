<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%product_document_items}}`.
 */
class m201106_014321_add_party_number_column_to_product_document_items_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%product_document_items}}', 'party_number', $this->char(50));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%product_document_items}}', 'party_number');
    }
}
