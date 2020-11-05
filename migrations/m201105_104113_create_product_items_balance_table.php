<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%product_items_balance}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%product}}`
 * - `{{%product_document}}`
 * - `{{%product_document_items}}`
 */
class m201105_104113_create_product_items_balance_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%product_items_balance}}', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->notNull(),
            'product_doc_id' => $this->integer()->notNull(),
            'product_doc_items_id' => $this->integer()->notNull(),
            'quantity' => $this->decimal(20,3),
            'amount' => $this->decimal(20,3),
            'status' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        // creates index for column `product_id`
        $this->createIndex(
            '{{%idx-product_items_balance-product_id}}',
            '{{%product_items_balance}}',
            'product_id'
        );

        // add foreign key for table `{{%product}}`
        $this->addForeignKey(
            '{{%fk-product_items_balance-product_id}}',
            '{{%product_items_balance}}',
            'product_id',
            '{{%product}}',
            'id',
            'CASCADE'
        );

        // creates index for column `product_doc_id`
        $this->createIndex(
            '{{%idx-product_items_balance-product_doc_id}}',
            '{{%product_items_balance}}',
            'product_doc_id'
        );

        // add foreign key for table `{{%product_document}}`
        $this->addForeignKey(
            '{{%fk-product_items_balance-product_doc_id}}',
            '{{%product_items_balance}}',
            'product_doc_id',
            '{{%product_document}}',
            'id',
            'CASCADE'
        );

        // creates index for column `product_doc_items_id`
        $this->createIndex(
            '{{%idx-product_items_balance-product_doc_items_id}}',
            '{{%product_items_balance}}',
            'product_doc_items_id'
        );

        // add foreign key for table `{{%product_document_items}}`
        $this->addForeignKey(
            '{{%fk-product_items_balance-product_doc_items_id}}',
            '{{%product_items_balance}}',
            'product_doc_items_id',
            '{{%product_document_items}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%product}}`
        $this->dropForeignKey(
            '{{%fk-product_items_balance-product_id}}',
            '{{%product_items_balance}}'
        );

        // drops index for column `product_id`
        $this->dropIndex(
            '{{%idx-product_items_balance-product_id}}',
            '{{%product_items_balance}}'
        );

        // drops foreign key for table `{{%product_document}}`
        $this->dropForeignKey(
            '{{%fk-product_items_balance-product_doc_id}}',
            '{{%product_items_balance}}'
        );

        // drops index for column `product_doc_id`
        $this->dropIndex(
            '{{%idx-product_items_balance-product_doc_id}}',
            '{{%product_items_balance}}'
        );

        // drops foreign key for table `{{%product_document_items}}`
        $this->dropForeignKey(
            '{{%fk-product_items_balance-product_doc_items_id}}',
            '{{%product_items_balance}}'
        );

        // drops index for column `product_doc_items_id`
        $this->dropIndex(
            '{{%idx-product_items_balance-product_doc_items_id}}',
            '{{%product_items_balance}}'
        );

        $this->dropTable('{{%product_items_balance}}');
    }
}
