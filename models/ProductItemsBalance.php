<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "product_items_balance".
 *
 * @property int $id
 * @property int $product_id
 * @property int $product_doc_id
 * @property int $product_doc_items_id
 * @property float|null $quantity
 * @property float|null $amount
 * @property int|null $status
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $created_by
 *
 * @property ProductDocument $productDoc
 * @property ProductDocumentItems $productDocItems
 * @property Product $product
 */
class ProductItemsBalance extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_items_balance';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'product_doc_id', 'product_doc_items_id'], 'required'],
            [['product_id', 'type', 'product_doc_id', 'product_doc_items_id', 'status', 'created_at', 'updated_at', 'created_by'], 'integer'],
            [['quantity', 'amount'], 'number'],
            [['party_number'], 'string'],
            [['product_doc_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductDocument::className(), 'targetAttribute' => ['product_doc_id' => 'id']],
            [['product_doc_items_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductDocumentItems::className(), 'targetAttribute' => ['product_doc_items_id' => 'id']],
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
            'product_doc_items_id' => Yii::t('app', 'Product Doc Items ID'),
            'quantity' => Yii::t('app', 'Quantity'),
            'amount' => Yii::t('app', 'Amount'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'created_by' => Yii::t('app', 'Created By'),
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if(empty($this->status)){
                $this->status = self::STATUS_ACTIVE;
            }
            return true;
        } else {
            return false;
        }
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
     * Gets query for [[ProductDocItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductDocItems()
    {
        return $this->hasOne(ProductDocumentItems::className(), ['id' => 'product_doc_items_id']);
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
