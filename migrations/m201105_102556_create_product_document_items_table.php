<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%product_document_items}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%product}}`
 * - `{{%product_document}}`
 */
class m201105_102556_create_product_document_items_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%product_document_items}}', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->notNull(),
            'product_doc_id' => $this->integer()->notNull(),
            'incoming_price' => $this->decimal(20,3),
            'quantity' => $this->decimal(20,3),
            'status' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);

        // creates index for column `product_id`
        $this->createIndex(
            '{{%idx-product_document_items-product_id}}',
            '{{%product_document_items}}',
            'product_id'
        );

        // add foreign key for table `{{%product}}`
        $this->addForeignKey(
            '{{%fk-product_document_items-product_id}}',
            '{{%product_document_items}}',
            'product_id',
            '{{%product}}',
            'id'
        );

        // creates index for column `product_doc_id`
        $this->createIndex(
            '{{%idx-product_document_items-product_doc_id}}',
            '{{%product_document_items}}',
            'product_doc_id'
        );

        // add foreign key for table `{{%product_document}}`
        $this->addForeignKey(
            '{{%fk-product_document_items-product_doc_id}}',
            '{{%product_document_items}}',
            'product_doc_id',
            '{{%product_document}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%product}}`
        $this->dropForeignKey(
            '{{%fk-product_document_items-product_id}}',
            '{{%product_document_items}}'
        );

        // drops index for column `product_id`
        $this->dropIndex(
            '{{%idx-product_document_items-product_id}}',
            '{{%product_document_items}}'
        );

        // drops foreign key for table `{{%product_document}}`
        $this->dropForeignKey(
            '{{%fk-product_document_items-product_doc_id}}',
            '{{%product_document_items}}'
        );

        // drops index for column `product_doc_id`
        $this->dropIndex(
            '{{%idx-product_document_items-product_doc_id}}',
            '{{%product_document_items}}'
        );

        $this->dropTable('{{%product_document_items}}');
    }
}
