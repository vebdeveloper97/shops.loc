<?php

namespace app\models;

use Yii;

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
class ProductDocument extends \yii\db\ActiveRecord
{
    // yangi doc type yaratilsa, uni getDocTypeLabels() metodiga qo'shib qo'yish kerak
    const DOCUMENT_TYPE_INCOMING = 1;
    const DOCUMENT_TYPE_SELLING = 2;
    const DOCUMENT_TYPE_REPORT = 3;
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
            [['date'], 'date'],
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
        ];
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
    public static function hasDocTypeLabel(string $docTypeLabel): bool
    {
        return in_array($docTypeLabel, self::getDocTypeTokens());
    }

    /**
     * @return array
     */
    public static function getDocTypeTokens(): array
    {
        return [
            self::DOCUMENT_TYPE_INCOMING => 'incoming',
            self::DOCUMENT_TYPE_SELLING => 'selling',
            self::DOCUMENT_TYPE_REPORT => 'report',
        ];
    }
}
