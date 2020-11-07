<?php

namespace app\models;

use Yii;
use yii\helpers\VarDumper;

/**
 * This is the model class for table "product_document".
 *
 * @property int $id
 * @property string $doc_number
 * @property int $doc_type
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 *
 * @property ProductDocumentItems[] $productDocumentItems
 * @property ProductItemsBalance[] $productItemsBalances
 */
class ProductDocument extends BaseModel
{
    // yangi doc type yaratilsa, uni getDocTypeLabels() metodiga qo'shib qo'yish kerak
    const DOCUMENT_TYPE_INCOMING = 1;
    const DOCUMENT_TYPE_SELLING = 2;
    const DOCUMENT_TYPE_REPORT = 3;
    const DOCUMENT_TYPE_REPORT_INCOMING = 4;
    const DOCUMENT_TYPE_REPORT_SELLING = 5;

    /** Search fields*/
    public $start_date;
    public $end_date;
    public $product_id;
    public $party_number;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_document';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['doc_number', 'doc_type'], 'required'],
            [['doc_type', 'created_at', 'status', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['doc_number'], 'string', 'max' => 255],
            [['doc_number'], 'unique'],
            [['date'], 'date', 'skipOnEmpty' => false, 'format' => 'php:Y-m-d'],
            /** Search Fields */
            [['start_date', 'end_date'], 'date'],
            [['product_id', 'party_number'], 'integer']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'doc_number' => Yii::t('app', 'Doc Number'),
            'doc_type' => Yii::t('app', 'Doc Type'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'date' => Yii::t('app', 'Date'),
            'start_date' => Yii::t('app', 'Start Date'),
            'end_date' => Yii::t('app', 'End Date'),
            'party_number' => Yii::t('app', 'Party Number'),
            'product_id' => Yii::t('app', 'Product name'),
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if(empty($this->status)){
                $this->status = self::STATUS_ACTIVE;
            }
            if(!empty($this->date)){
                $this->date = date('Y-m-d', strtotime($this->date));
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
        return $this->hasMany(ProductDocumentItems::className(), ['product_doc_id' => 'id']);
    }

    /**
     * Gets query for [[ProductItemsBalances]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductItemsBalances()
    {
        return $this->hasMany(ProductItemsBalance::className(), ['product_doc_id' => 'id']);
    }

    /**
     * @param string $docTypeLabel
     * @return bool
     */
    public static function hasDocTypeLabel($docTypeLabel)
    {
        return array_search($docTypeLabel, self::getDocTypeTokens());
    }

    /**
     * @return array
     */
    public static function getDocTypeTokens()
    {
        return [
            self::DOCUMENT_TYPE_INCOMING => 'incoming',
            self::DOCUMENT_TYPE_SELLING => 'selling',
            self::DOCUMENT_TYPE_REPORT => 'report',
            self::DOCUMENT_TYPE_REPORT_INCOMING => 'report_incoming',
            self::DOCUMENT_TYPE_REPORT_SELLING => 'report_selling',
        ];
    }

}
