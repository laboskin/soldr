<?php


namespace app\models;


use yii\base\Model;

class CitySelectForm extends Model
{

    public $widgetIndex;

    public function rules()
    {
        return [
            [['widgetIndex'], 'required'],

        ];
    }
}