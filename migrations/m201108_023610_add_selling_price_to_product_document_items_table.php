<?php

use yii\db\Migration;

/**
 * Class m201108_023610_add_selling_price_to_product_document_items_table
 */
class m201108_023610_add_selling_price_to_product_document_items_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%product_document_items}}', 'selling_price', $this->decimal(20,3));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%product_document_items}}', 'selling_price');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m201108_023610_add_selling_price_to_product_document_items_table cannot be reverted.\n";

        return false;
    }
    */
}
