<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%reports}}`.
 */
class m201109_114251_add_party_number_column_to_reports_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%reports}}', 'party_number', $this->char(50));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%reports}}', 'party_number');
    }
}
