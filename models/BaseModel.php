<?php


namespace app\models;
use app\components\CustomBehaviors\CustomBehaviors;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class BaseModel extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_NO_ACTIVE = 2;
    const STATUS_SAVED = 3;
    const STATUS_DELETE = 4;

    public function behaviors()
    {
        return [
            [
                'class' => CustomBehaviors::className(),
                'updatedByAttribute' => 'updated_by',
            ],
            [
                'class' => TimestampBehavior::className(),
            ]
        ];
    }

    public function afterValidate()
    {
        if($this->hasErrors()){
            $res = [
                'status' => 'error',
                'table' => self::tableName() ?? '',
                'url' => \yii\helpers\Url::current([], true),
                'data' => $this->toArray(),
                'message' => $this->getErrors(),
            ];
            \Yii::error($res, 'save');
        }
    }

}