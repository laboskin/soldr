<?php

namespace app\models;

use Yii;
use yii\helpers\Url;

/**
 * This is the model class for table "image".
 *
 * @property int $id
 * @property int $post_id
 * @property string $name
 * @property int $sort
 */
class Image extends \yii\db\ActiveRecord
{
    public $attachment;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'image';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['post_id', 'name'], 'required'],
            [['post_id', 'sort'], 'integer'],
            [['sort'], 'default', 'value'=>function(){
                return Image::find()->where(['post_id'=>$this->post_id])->count();
                }],
            [['name'], 'string', 'max' => 255],
            [['attachment'], 'image'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'post_id' => 'Post ID',
            'name' => 'Name',
            'sort' => 'Sort'
        ];
    }

    public function getImageUrl(){
        if ($this->name and file_exists(Yii::getAlias('@images').'/'.$this->name))
            return Url::home(true).'web/uploads/images/'.$this->name;
        else
            return self::getNoImageUrl();
    }

    public function getSmallImageUrl(){
        if ($this->name and file_exists(Yii::getAlias('@images').'/small/'.$this->name))
            return Url::home(true).'web/uploads/images/small/'.$this->name;
        else
            return self::getNoSmallImageUrl();
    }

    public static function getNoImageUrl(){

            return Url::home(true).'web/uploads/images/'.'nophoto.svg';
    }

    public static function getNoSmallImageUrl(){

        return Url::home(true).'web/uploads/images/small/'.'nophoto.svg';
    }



    public function getPost(){
        return $this->hasOne(Post::classname(), ['id' => 'post_id']);
    }

    public function beforeDelete()
    {
        Image::updateAllCounters(['sort'=>-1], ['and', ['post_id' => $this->post_id], ['>', 'sort', $this->sort]]);

        if (file_exists(Yii::getAlias('@images').'/'.$this->name))
        unlink(Yii::getAlias('@images').'/'.$this->name);
        if (file_exists(Yii::getAlias('@images').'/small/'.$this->name))
            unlink(Yii::getAlias('@images').'/small/'.$this->name);
        return parent::beforeDelete(); // TODO: Change the autogenerated stub
    }

    public function getFilename(){
        //return Url::to('@web/web/uploads/').$this->name;
        return Url::to('@images').$this->name;
    }
}
