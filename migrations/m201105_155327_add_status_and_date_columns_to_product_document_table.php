<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%product_document}}`.
 */
class m201105_155327_add_status_and_date_columns_to_product_document_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%product_document}}', 'status', $this->integer());
        $this->addColumn('{{%product_document}}', 'date', $this->date());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%product_document}}', 'status');
        $this->dropColumn('{{%product_document}}', 'date');
    }
}
