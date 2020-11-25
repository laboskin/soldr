<?php

namespace app\models;

/**
 * This is the model class for table "property_option".
 *
 * @property int $id
 * @property int $property_id
 * @property int $sort
 * @property string|null $value_string
 * @property string|null $depend_string
 */
class PropertyOption extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'property_option';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sort'], 'default', 'value' => function(){
                return PropertyOption::find()->where(['property_id'=>$this->property_id])->count();
            }],
            [['property_id', 'sort'], 'required'],
            [['property_id', 'sort',], 'integer'],
            [['value_string', 'depend_string'], 'string', 'max' => 150],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'property_id' => 'Property ID',
            'sort_filter' => 'Order ID',
            'value_string' => 'Value String',
            'depend_string' => 'Depend String',
        ];
    }

    public function getProperty()
    {
        return $this->hasOne(Property::className(), ['id' => 'property_id']);
    }


}
