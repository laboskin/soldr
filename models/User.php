<?php

namespace app\models;

use laboskin\favorite\models\Favorite;
use Yii;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $email
 * @property string $password
 * @property string $phone
 * @property string $name
 * @property int $status
 * @property string $restore_key
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['email', 'password', 'phone', 'name'], 'required'],
            [['status'], 'integer'],
            [['status'], 'in', 'range'=>[self::STATUS_UNACTIVE, self::STATUS_ACTIVE, self::STATUS_MODERATOR, self::STATUS_ADMIN]],
            [['restore_key'], 'string', 'max' => 255],
            ['restore_key', 'unique'],
            [['password'], 'string', 'min' => 8, 'max'=>255],
            [['phone'], 'string', 'min' => 10, 'max'=>10],
            [['name'], 'string', 'min' => 2, 'max'=>50],
            [['email'], 'string', 'min' => 5, 'max'=>50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'email' => 'Email',
            'password' => 'Password',
            'phone' => 'Phone',
            'name' => 'Name',
            'auth_key' => 'Auth Key',
            'status' => 'status',
            'restore_key' => 'Restore Key',
        ];
    }

    const STATUS_UNACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_MODERATOR = 2;
    const STATUS_ADMIN = 3;

    const LOGIN_STATUS_OK = 0;
    const LOGIN_STATUS_NEED_CONFIRM = 1;
    const LOGIN_STATUS_MAIL_SENT = 2;


    public function getPosts()
    {
        return $this->hasMany(Post::classname(), ['user_id' => 'id']);
    }



    public static function findIdentity($id)
    {
        return self::findOne($id);
    }

    /**
     * Finds an identity by the given token.
     * @param mixed $token the token to be looked for
     * @param mixed $type the type of the token. The value of this parameter depends on the implementation.
     * For example, [[\yii\filters\auth\HttpBearerAuth]] will set this parameter to be `yii\filters\auth\HttpBearerAuth`.
     * @return IdentityInterface|null the identity object that matches the given token.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {

    }

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|int an ID that uniquely identifies a user identity.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns a key that can be used to check the validity of a given identity ID.
     *
     * The key should be unique for each individual user, and should be persistent
     * so that it can be used to check the validity of the user identity.
     *
     * The space of such keys should be big enough to defeat potential identity attacks.
     *
     * This is required if [[User::enableAutoLogin]] is enabled. The returned key will be stored on the
     * client side as a cookie and will be used to authenticate user even if PHP session has been expired.
     *
     * Make sure to invalidate earlier issued authKeys when you implement force user logout, password change and
     * other scenarios, that require forceful access revocation for old sessions.
     *
     * @return string a key that is used to check the validity of a given identity ID.
     * @see validateAuthKey()
     */
    public function getAuthKey()
    {

    }

    public function validateAuthKey($authKey)
    {

    }

    public static function findByRestoreKey($key)
    {
        if (!static::isRestoreKeyExpire($key))
        {
            return null;
        }
        return static::findOne([
            'restore_key' => $key,
        ]);
    }

    public function generateRestoreKey()
    {
        $this->restore_key = Yii::$app->security->generateRandomString() . '_' . time();
    }

    public function removeRestoreKey()
    {
        $this->restore_key = null;
    }

    public static function isRestoreKeyExpire($key)
    {
        if (empty($key)) {
            return false;
        }
        $expire = Yii::$app->params['restoreKeyExpire'];
        $parts = explode('_', $key);
        $timestamp = (int)end($parts);
        return $timestamp + $expire >= time();
    }

    public function setPassword($newPassword){
        $this->password = Yii::$app->getSecurity()->generatePasswordHash($newPassword);
    }

    public function getFavorites(){
        return $this->hasMany(Favorite::classname(), ['user_id' => 'id']);
    }
}
