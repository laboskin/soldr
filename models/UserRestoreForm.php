<?php


namespace app\models;


use Yii;
use yii\base\Model;

class UserRestoreForm extends Model
{
    public $email;

    public function rules(){
        return[
            ['email', 'filter', 'filter'=>'trim'],
            ['email', 'required'],
            ['email', 'exist', 'targetClass'=>User::className(), 'message'=>'Пользователь с указанным e-mail не найден'],
        ];
    }

    public function sendMail(){
        /* @var $user User */
        $user = User::findOne(['email'=>$this->email]);
        if ($user){
            $user->generateRestoreKey();
            if($user->save()){
                return Yii::$app->mailer->compose('restorePassword', ['user'=>$user])
                    ->setFrom([Yii::$app->params['senderEmail']=>Yii::$app->name.'(отправлено роботом'])
                    ->setTo($this->email)
                    ->setSubject('Сброс пароля')
                    ->send();
            }
        }
        return false;

    }
}