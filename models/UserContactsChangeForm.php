<?php


namespace app\models;


use Yii;
use yii\base\Model;

class UserContactsChangeForm extends Model
{
    public $email;
    public $name;
    public $phone;


    public function rules()
    {
        return [
            [['phone'], 'string', 'min' => 10, 'max'=>10],
            [['name'], 'string', 'min' => 2, 'max'=>50],
            [['phone'], 'unique', 'targetClass'=>'app\models\User', 'filter'=>['!=', 'phone', $this->getUser()->phone]],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Имя',
            'phone' => 'Телефон'
        ];
    }

    public function changeContacts()
    {
        $user = $this->getUser();
        $user->name = $this->name;
        $user->phone = $this->phone;
        return $user->save();
    }

    public function getUser(){
        return User::findIdentity(Yii::$app->user->id);
    }

}