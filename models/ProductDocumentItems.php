<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "product_document_items".
 *
 * @property int $id
 * @property int $product_id
 * @property int $product_doc_id
 * @property float|null $incoming_price
 * @property float|null $quantity
 * @property int|null $status
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 *
 * @property ProductDocument $productDoc
 * @property Product $product
 * @property ProductItemsBalance[] $productItemsBalances
 */
class ProductDocumentItems extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_document_items';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'product_doc_id'], 'required'],
            [['product_id', 'product_doc_id', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['incoming_price', 'quantity'], 'number'],
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
            'product_doc_id' => Yii::t('app', 'Product Doc ID'),
            'incoming_price' => Yii::t('app', 'Incoming Price'),
            'quantity' => Yii::t('app', 'Quantity'),
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

    /**
     * Gets query for [[ProductItemsBalances]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductItemsBalances()
    {
        return $this->hasMany(ProductItemsBalance::className(), ['product_doc_items_id' => 'id']);
    }
}
