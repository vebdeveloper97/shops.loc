<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%reports}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%product}}`
 * - `{{%product_document}}`
 */
class m201109_100625_create_reports_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%reports}}', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->notNull(),
            'incoming_price' => $this->decimal(20,3)->notNull(),
            'selling_price' => $this->decimal(20,3)->notNull(),
            'profit' => $this->decimal(20,3)->notNull(),
            'qty_difference' => $this->decimal(20,3)->notNull(),
            'product_doc_id' => $this->integer(),
            'status' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);

        // creates index for column `product_id`
        $this->createIndex(
            '{{%idx-reports-product_id}}',
            '{{%reports}}',
            'product_id'
        );

        // add foreign key for table `{{%product}}`
        $this->addForeignKey(
            '{{%fk-reports-product_id}}',
            '{{%reports}}',
            'product_id',
            '{{%product}}',
            'id',
            'CASCADE'
        );

        // creates index for column `product_doc_id`
        $this->createIndex(
            '{{%idx-reports-product_doc_id}}',
            '{{%reports}}',
            'product_doc_id'
        );

        // add foreign key for table `{{%product_document}}`
        $this->addForeignKey(
            '{{%fk-reports-product_doc_id}}',
            '{{%reports}}',
            'product_doc_id',
            '{{%product_document}}',
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
            '{{%fk-reports-product_id}}',
            '{{%reports}}'
        );

        // drops index for column `product_id`
        $this->dropIndex(
            '{{%idx-reports-product_id}}',
            '{{%reports}}'
        );

        // drops foreign key for table `{{%product_document}}`
        $this->dropForeignKey(
            '{{%fk-reports-product_doc_id}}',
            '{{%reports}}'
        );

        // drops index for column `product_doc_id`
        $this->dropIndex(
            '{{%idx-reports-product_doc_id}}',
            '{{%reports}}'
        );

        $this->dropTable('{{%reports}}');
    }
}
