<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%product_document}}`.
 */
class m201105_100929_create_product_document_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%product_document}}', [
            'id' => $this->primaryKey(),
            'doc_number' => $this->char(255)->notNull()->unique(),
            'doc_type' => $this->integer()->notNull(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%product_document}}');
    }
}
