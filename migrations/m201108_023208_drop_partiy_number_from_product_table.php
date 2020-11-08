<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%partiy_number_from_product}}`.
 */
class m201108_023208_drop_partiy_number_from_product_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('product', 'partiy_number');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('product', 'partiy_number', $this->char(50));
    }
}
