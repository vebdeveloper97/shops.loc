<?php


namespace app\components\CustomBehaviors;
use Yii;
use yii\base\InvalidCallException;
use yii\db\BaseActiveRecord;
use yii\behaviors\AttributeBehavior;


class CustomBehaviors extends AttributeBehavior
{
    public $createdByAttribute = 'created_by';
    public $updatedByAttribute = 'created_by';

    public $value;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if (empty($this->attributes)) {
            $this->attributes = [
                BaseActiveRecord::EVENT_BEFORE_INSERT => $this->createdByAttribute,
                BaseActiveRecord::EVENT_BEFORE_UPDATE => $this->updatedByAttribute,
            ];
        }
    }

    /**
     * @param \yii\base\Event $event
     * @return int|mixed|string
     */

    protected function getValue($event)
    {
        if ($this->value === null) {
            return Yii::$app->user->id;
        }

        return parent::getValue($event);
    }

    public function touch($attribute)
    {
        /* @var $owner BaseActiveRecord */
        $owner = $this->owner;
        if ($owner->getIsNewRecord()) {
            throw new InvalidCallException('Updating the timestamp is not possible on a new record.');
        }
        $owner->updateAttributes(array_fill_keys((array) $attribute, $this->getValue(null)));
    }
}