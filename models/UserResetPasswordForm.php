<?php


namespace app\models;


use yii\base\InvalidArgumentException;
use yii\base\Model;

class UserResetPasswordForm extends Model
{
    public $password;
    public $passwordRepeat;
    private $user;
    public function rules()
    {
        return [
            [['password', 'passwordRepeat'], 'required'],
            [['password', 'passwordRepeat'], 'string', 'min' => 8, 'max'=>255],
            ['passwordRepeat', 'compare', 'compareAttribute'=>'password'],
        ];
    }
    public function attributeLabels()
    {
        return [
            'password' => 'Новый пароль',
            'passwordRepeat' => 'Повторите пароль'
        ];
    }
    public function __construct($key, $config = [])
    {
        if(empty($key) || !is_string($key))
            throw new InvalidArgumentException('Ключ не может быть пустым.');
        $this->user = User::findByRestoreKey($key);
        if(!$this->user)
            throw new InvalidArgumentException('Не верный ключ.');
        parent::__construct($config);
    }

    public function resetPassword()
    {
        /* @var $user User */
        $user = $this->user;
        $user->setPassword($this->password);
        $user->removeRestoreKey();
        return $user->save();
    }


}