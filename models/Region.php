<?php

namespace app\models;

/**
 * This is the model class for table "region".
 *
 * @property int $id
 * @property string $name_ru
 */
class Region extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'region';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'name_ru'], 'required'],
            [['id'], 'integer'],
            [['name_ru'], 'string', 'max' => 128],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name_ru' => 'Name Ru',
        ];
    }
}
