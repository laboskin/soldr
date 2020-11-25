<?php

namespace app\models;

use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "city".
 *
 * @property int $id
 * @property int $region_id
 * @property string $name_ru
 * @property string $name_en
 * @property float $lat
 * @property float $lon
 * @property string $timezone
 * @property string $okato
 */
class City extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'city';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'region_id', 'name_ru', 'name_en', 'lat', 'lon', 'okato', 'timezone'], 'required'],
            [['id', 'region_id'], 'integer'],
            [['lat', 'lon'], 'number'],
            [['name_ru', 'name_en', 'timezone'], 'string', 'max' => 128],
            [['okato'], 'string', 'max' => 20],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'region_id' => 'Region ID',
            'name_ru' => 'Name Ru',
            'name_en' => 'Name En',
            'lat' => 'Lat',
            'lon' => 'Lon',
            'timezone' => 'Timezone',
            'okato' => 'Okato',
        ];
    }
    public static function sortedArray(){
        $cities = ArrayHelper::toArray(self::find()->all(), ['app\models\City' => ['id', 'region_id', 'name_ru']]);
        ArrayHelper::multisort($cities, ['name_ru'], [SORT_ASC]);
        return $cities;
    }

    public static function arrayForTypeahead(){
        $sortedCities = self::sortedArray();
        $regions = ArrayHelper::toArray(Region::find()->all(), ['app\models\Region' => ['id', 'name_ru']]);
        $regionsById = [];
        for($i = 0; $i < count($regions); $i++)
            $regionsById[$regions[$i]['id']] = $regions[$i]['name_ru'];
        $citiesWithRegion = [];
        for($i = 0; $i < count($sortedCities); $i++)
            $citiesWithRegion[$i] = $sortedCities[$i]['name_ru'].', '.$regionsById[$sortedCities[$i]['region_id']];
        return $citiesWithRegion;
    }
    public static function widgetIndexToCityId($widgetIndex){
        return self::sortedArray()[$widgetIndex]['id'];
    }
    public static function defaultCity(){
        $geo = new \jisoft\sypexgeo\Sypexgeo();
        $geo->get();
        $city = self::findOne($geo->city['id']);
        if ($city)
            return $city;
        else
            return self::findOne(524901);

    }
}
