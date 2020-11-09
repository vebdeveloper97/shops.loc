<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "reports".
 *
 * @property int $id
 * @property int $product_id
 * @property float $incoming_price
 * @property float $selling_price
 * @property float $profit
 * @property float $qty_difference
 * @property int|null $product_doc_id
 * @property int|null $status
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 *
 * @property ProductDocument $productDoc
 * @property Product $product
 */
class Reports extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'reports';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'incoming_price', 'selling_price', 'profit', 'qty_difference'], 'required'],
            [['product_id', 'product_doc_id', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['incoming_price', 'selling_price', 'profit', 'qty_difference'], 'number'],
            [['party_number'], 'string'],
            [['product_doc_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductDocument::className(), 'targetAttribute' => ['product_doc_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'product_id' => Yii::t('app', 'Product ID'),
            'incoming_price' => Yii::t('app', 'Incoming Price'),
            'selling_price' => Yii::t('app', 'Selling Price'),
            'profit' => Yii::t('app', 'Profit'),
            'qty_difference' => Yii::t('app', 'Qty Difference'),
            'product_doc_id' => Yii::t('app', 'Product Doc ID'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[ProductDoc]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductDoc()
    {
        return $this->hasOne(ProductDocument::className(), ['id' => 'product_doc_id']);
    }

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }
}
