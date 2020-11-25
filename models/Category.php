<?php

namespace app\models;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "category".
 *
 * @property int $id
 * @property string $name
 * @property int $parent_id
 */
class Category extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'parent_id'], 'required'],
            [['parent_id'], 'integer'],
            [['name'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'parent_id' => 'Parent ID',
        ];
    }

    public function getPosts(){
        return $this->hasMany(Post::classname(), ['category_id' => 'id']);
    }

    public function getPropertys()
    {
        return $this->hasMany(Property::className(), ['category_id' => 'id']);
    }

    public function getPropertiesForFilter()
    {
        return $this->hasMany(Property::className(), ['category_id' => 'id'])->andWhere(['!=', 'filter_type', Property::FILTER_TYPE_NONE])->orderBy('sort_filter')->all();
    }

    public function getPropertiesForCreate()
    {
        return $this->hasMany(Property::className(), ['category_id' => 'id'])->orderBy('sort_create')->all();
    }

    public function getPropertiesForView()
    {
        return $this->hasMany(Property::className(), ['category_id' => 'id'])->orderBy('sort_create')->all();
    }
    public function getParent()
    {
        return self::findOne(['id'=>$this->parent_id]);
    }
    public function getChildren()
    {
        return self::find()->where(['parent_id'=>$this->id])->all();
    }


    public static function multiArray(){
        $category = ArrayHelper::toArray(self::find()->all(), ['app\models\Category' => ['id', 'name', 'parent_id']]);
        $items = [];
        for($i = 0; $i < count($category); $i++){
            if ($category[$i]['parent_id']!=0){
                $parent_name = $category[$category[$i]['parent_id']-1]['name'];
                $items[$parent_name][$i+1] = $category[$i]['name'];
            }
        }
        return $items;

    }
}
