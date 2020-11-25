<?php


namespace app\models;


use yii\base\Model;
use Yii;

class LoginForm extends Model
{
    public $email;
    public $password;
    public $rememberMe = true;

    public function rules()
{
    return [
        [['email', 'password'], 'required'],
        [['email'], 'string', 'min' => 5, 'max'=>50],
        [['email'], 'email'],
        [['password'], 'string', 'min' => 8, 'max'=>255],
        [['password'], 'validatePassword'],
        ['rememberMe', 'boolean'],

    ];
}
    public function attributeLabels()
    {
        return [
            'email' => 'Логин',
            'password' => 'Пароль',
            'rememberMe' => 'Запомнить меня'
        ];
    }
public function validatePassword($attribute, $params){
    $user = User::findOne(['email'=>$this->email]);
    //  var_dump(!$user || Yii::$app->getSecurity()->validatePassword($this->password, $user->password));die;;
    if (!$user || !Yii::$app->getSecurity()->validatePassword($this->password, $user->password)){
        $this->addError($attribute, 'Логин или пароль введены неверно');
    }
}
public function getUser(){
    return User::findOne(['email'=>$this->email]);
}
}