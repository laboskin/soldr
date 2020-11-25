<?php

namespace app\models;

use laboskin\favorite\models\Favorite;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "post".
 *
 * @property int $id
 * @property int $user_id
 * @property int $category_id
 * @property string $name
 * @property int $price
 * @property string $description
 * @property string $date
 * @property int $original_date
 * @property int $city_id
 * @property int $views
 * @property int $status
 */
class Post extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'post';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['price'], 'filter', 'filter'=> 'intval'],
            [['user_id', 'category_id', 'name', 'price', 'description', 'city_id', 'date', 'original_date', 'status'], 'required'],
            [['user_id', 'category_id', 'price', 'city_id', 'views', 'date', 'original_date', 'status'], 'integer'],
            [['description'], 'string', 'max' => 4000],
            [['name'], 'string', 'max' => 30],
            [['price'], 'integer', 'max' => 100000000],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Номер объявления',
            'user_id' => 'Пользователь',
            'category_id' => 'Категория',
            'name' => 'Заголовок объявления',
            'price' => 'Цена',
            'description' => 'Описание',
            'date' => 'Дата',
            'original_date' => 'Дата подачи',
            'city_id' => 'Город',
            'views' => 'Просмотры',
            'status' => 'Статус'
        ];
    }

    const STATUS_POSTED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DECLINED = 2;
    const STATUS_BAN = 3;
    const STATUS_CLOSED = 4;
    const STATUS_UNFINISHED = 404;

    const EXPIRING_DAYS = 1095;


    public function getCategory(){
        return $this->hasOne(Category::classname(), ['id' => 'category_id']);
    }

    public function getUser(){
        return $this->hasOne(User::classname(), ['id' => 'user_id']);
    }

    public function getImages(){
        return $this->hasMany(Image::classname(), ['post_id' => 'id'])->orderBy('sort');
    }

    public function getFavorites(){
        return $this->hasMany(Favorite::classname(), ['item_id' => 'id']);
    }

    public function getImagesLinks()
    {
        return ArrayHelper::getColumn($this->images, 'imageUrl');
    }

    public function getImagesLinksData()
    {
        return ArrayHelper::toArray($this->images, [
            Image::classname() => [
                'caption' => 'name',
                'key' => 'id',
                ]
            ]);
    }

    public function getPhotos()
    {
        $photos = ArrayHelper::getColumn($this->images, 'imageUrl');
        if (count($photos)>0)
            return $photos;
        else
            return [Image::getNoImageUrl()];
    }

    public function getSmallPhotos()
    {
        $photos = ArrayHelper::getColumn($this->images, 'smallImageUrl');
        if (count($photos)>0)
            return $photos;
        else
            return [Image::getNoSmallImageUrl()];
    }

    public function getPropertyValues()
    {
        return $this->hasMany(PropertyValue::className(), ['post_id' => 'id'])->all();
    }

    public function increaseViews(){
        $this->views += 1;
        $this->save();
    }

    public function close(){
        $this->status=Post::STATUS_CLOSED;
        $this->save();
    }

    public function timeAgoString($originalDate = false)
    {

        $city = Yii::$app->request->cookies->has('city')?City::findOne(Yii::$app->request->cookies->getValue('city')):City::defaultCity();
        $timezone = new \DateTimeZone($city->timezone);
        $currentTime = new \DateTime();
        $currentTime->setTimezone($timezone);
        $modelTime = new \DateTime();
        $modelTime->setTimezone($timezone);
        if (!$originalDate) $modelTime->setTimestamp($this->date);
        else $modelTime->setTimestamp($this->original_date);
        $postAge = $currentTime->diff($modelTime);
        $result = '';
        if ($postAge->days == 0) {
            if ($postAge->h == 0) {
                if ($postAge->i == 0) {
                    $result = 'Несколько секунд';
                } else {
                    $result = strval($postAge->i);
                    if (intdiv($postAge->i, 10) != 1) {
                        if ($postAge->i % 10 == 1) $result .= ' минуту';
                        elseif ($postAge->i % 10 >= 2 and $postAge->i % 10 <= 4) $result .= ' минуты';
                        else $result .= ' минут';
                    } else $result .= ' минут';
                }
            } else {
                $result = strval($postAge->h);
                if ($postAge->h == 1 or $postAge->h == 21) $result .= ' час';
                elseif (($postAge->h >= 2 and $postAge->h <= 4) or ($postAge->h >= 22 and $postAge->h <= 23)) $result .= ' часа';
                else $result .= ' часов';
            }
        } elseif ($postAge->days <= 6) {
            $result = $postAge->days;
            if ($postAge->days == 1) $result .= ' день';
            else if ($postAge->days >= 2 and $postAge->days <= 4) $result .= ' дня';
            else $result .= ' дней';
        } elseif (intdiv($postAge->days, 7) <= 3) {
            $result = strval(intdiv($postAge->days, 7));
            if (intdiv($postAge->days, 7) == 1) $result .= ' неделю';
            else $result .= ' недели';
        } else {
            $result = $modelTime->format('j ');
            $dateTranslate = [
                'January'=>'января',
                'February'=>'февраля',
                'March'=>'марта',
                'April'=>'апреля',
                'May'=>'мая',
                'June'=>'июня',
                'July'=>'июля',
                'August'=>'августа',
                'September'=>'сентября',
                'October'=>'октября',
                'November'=>'ноября',
                'December'=>'декабря',
            ];
            $result .= $dateTranslate[$modelTime->format('F')];
            if ($modelTime->format('Y') != $currentTime->format('Y'))
                $result .= ' '.$modelTime->format('Y');
            else {
                $result .= ' ';
                $result .= $modelTime->format('H:i');
            }
            return $result;
        }
        $result .= ' назад';
        return $result;
    }

    public function timeExactString($originalDate = false)
    {
        $city = Yii::$app->request->cookies->has('city')?City::findOne(Yii::$app->request->cookies->getValue('city')):City::defaultCity();
        $timezone = new \DateTimeZone($city->timezone);
        $modelTime = new \DateTime();
        $modelTime->setTimezone($timezone);
        if (!$originalDate) $modelTime->setTimestamp($this->date);
        else $modelTime->setTimestamp($this->original_date);
        $today = new \DateTime('today');
        $today->setTimezone($timezone);
        $yesterday = new \DateTime('yesterday');
        $yesterday->setTimezone($timezone);
        $result = '';
        if ($modelTime->format('Y-m-d')==$today->format('Y-m-d')) $result = 'Сегодня';
        elseif ($modelTime->format('Y-m-d')==$yesterday->format('Y-m-d')) $result = 'Вчера';
        else
        {
            $result = $modelTime->format('j ');
            $dateTranslate = [
                'January'=>'января',
                'February'=>'февраля',
                'March'=>'марта',
                'April'=>'апреля',
                'May'=>'мая',
                'June'=>'июня',
                'July'=>'июля',
                'August'=>'августа',
                'September'=>'сентября',
                'October'=>'октября',
                'November'=>'ноября',
                'December'=>'декабря',
            ];
            $result .= $dateTranslate[$modelTime->format('F')];
            if ($modelTime->format('Y')!=$today->format('Y'))
                $result .= ' '.$modelTime->format('Y');

        }
        $result .= ' в ';
        $result .= $modelTime->format('H:i');
        return $result;
    }
    public static function expiredDate()
    {
        $expiredDate = new \DateTime('-'.strval(Post::EXPIRING_DAYS).' days');
        return $expiredDate->getTimestamp();
    }
    public function beforeDelete()
    {
        $propertyValues = $this->propertyValues;
        foreach($propertyValues as $propertyValue)
            $propertyValue->delete();
        $images = $this->images;
        foreach($images as $image)
        {
            $image->delete();
        }

        return parent::beforeDelete(); // TODO: Change the autogenerated stub
    }

}
