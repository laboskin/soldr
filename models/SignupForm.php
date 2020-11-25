<?php


namespace app\models;


use yii\base\Model;
use Yii;

class SignupForm extends Model
{
  public $email;
  public $password;
  public $phone;
  public $name;

  public function rules()
  {
    return [
      [['phone'], 'filter', 'filter'=> function($value) { return str_replace(['+7', '-', ' '], '' , $value); }],
      [['email', 'password', 'phone', 'name'], 'required'],
      [['email'], 'unique', 'targetClass'=>'app\models\User'],
      [['phone'], 'unique', 'targetClass'=>'app\models\User'],
      [['email'], 'string', 'min' => 5, 'max'=>50],
      [['password'], 'string', 'min' => 8, 'max'=>255],
      [['phone'], 'string', 'min' => 10, 'max'=>10],
      [['name'], 'string', 'min' => 2, 'max'=>50],
    ];
  }
  public function signup(){
    $user = new User();
    $user->email = $this->email;
    $user->password = Yii::$app->getSecurity()->generatePasswordHash($this->password);
    $user->phone = $this->phone;
    $user->name = $this->name;
    $user->status = User::STATUS_ACTIVE;
    $user->generateRestoreKey();
    if($user->save()){
      return Yii::$app->user->login($user, 3600*24*30);
    }
  }

}