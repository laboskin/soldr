<?php


namespace app\models;


use Yii;
use yii\base\Model;

class UserPasswordChangeForm extends Model
{
    public $oldPassword;
    public $newPassword;
    public $newPasswordRepeat;

    public function rules()
    {
        return [
            [['oldPassword', 'newPassword', 'newPasswordRepeat'], 'required'],
            [['oldPassword', 'newPassword', 'newPasswordRepeat'], 'string', 'min' => 8, 'max'=>255],
            [['oldPassword'], 'validateOldPassword'],
            ['newPasswordRepeat', 'compare', 'compareAttribute'=>'newPassword'],

        ];
    }

    public function attributeLabels()
    {
        return [
            'oldPassword' => 'Текущий пароль',
            'newPassword' => 'Новый пароль',
            'newPasswordRepeat' => 'Повторите пароль'
        ];
    }

    public function validateOldPassword($attribute, $params){
        $user = $this->getUser();
        if (!$user || !Yii::$app->getSecurity()->validatePassword($this->oldPassword, $user->password)){
            $this->addError($attribute, 'Пароль указан неверно');
        }
    }



    public function changePassword(){
        $user = $this->getUser();
        $user->setPassword($this->newPassword);
        return $user->save();

    }
    public function getUser(){
        return User::findIdentity(Yii::$app->user->id);
    }

}