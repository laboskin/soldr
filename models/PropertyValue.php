<?php

namespace app\models;

/**
 * This is the model class for table "property_value".
 *
 * @property int $id
 * @property int $property_id
 * @property int $post_id
 * @property int|null $value_int
 * @property float|null $value_float
 * @property string|null $value_string
 * @property int|null $value_bool
 */
class PropertyValue extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'property_value';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['property_id', 'post_id'], 'required'],
            [['property_id', 'post_id', 'value_int'], 'integer'],
            [['value_float'], 'number'],
            [['value_bool'], 'boolean'],
            [['value_string'], 'string', 'max' => 150],
            [['post_id'], 'exist', 'targetClass'=>Post::className(), 'targetAttribute'=>'id'],
            [['property_id'], 'exist', 'targetClass'=>Property::className(), 'targetAttribute'=>'id'],
            [['value_int'], 'required', 'when'=>function($model){
            return $model->property->value_type == Property::VALUE_TYPE_INT;
            }],
            [['value_float'], 'required', 'when'=>function($model){
                return $model->property->value_type == Property::VALUE_TYPE_FLOAT;
            }],
            [['value_string'], 'required', 'when'=>function($model){
                return $model->property->value_type == Property::VALUE_TYPE_STRING;
            }],
            [['value_bool'], 'required', 'when'=>function($model){
                return $model->property->value_type == Property::VALUE_TYPE_BOOL;
            }],
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
            'post_id' => 'Post ID',
            'value_int' => 'Value Int',
            'value_float' => 'Value Float',
            'value_string' => 'Value String',
        ];
    }

    public function getProperty()
    {
        return $this->hasOne(Property::className(), ['id' => 'property_id']);
    }

    public function getPost()
    {
        return $this->hasOne(Post::className(), ['id' => 'post_id']);
    }

    public function getName()
    {
        return $this->property->name;
    }



    //public function getPropertyOptions()
    //{
    //    return $this->hasMany(PropertyOption::className(), ['property_id' => 'id']);
    //}
}
