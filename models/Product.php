<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "product".
 *
 * @property int $id
 * @property string $name
 * @property string|null $partiy_number
 * @property int|null $status
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 *
 * @property ProductDocumentItems[] $productDocumentItems
 * @property ProductItemsBalance[] $productItemsBalances
 */
class Product extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['name'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
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
     * Gets query for [[ProductDocumentItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductDocumentItems()
    {
        return $this->hasMany(ProductDocumentItems::className(), ['product_id' => 'id']);
    }

    /**
     * Gets query for [[ProductItemsBalances]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductItemsBalances()
    {
        return $this->hasMany(ProductItemsBalance::className(), ['product_id' => 'id']);
    }

    public static function getArrayHelp(){
        return ArrayHelper::map(self::find()->all(), 'id', 'name');
    }
}
