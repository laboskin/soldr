<?php

namespace app\models;

/**
 * This is the model class for table "property".
 *
 * @property int $id
 * @property string $name
 * @property int $category_id
 * @property int $value_type
 * @property int $sort_filter
 * @property int $sort_create
 * @property int $sort_view
 * @property int $filter_type
 * @property int $input_type
 * @property int|null $parent_id
 * @property string|null $parent_string
 * @property int|null $depend_id

 */
class Property extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'property';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sort_filter'], 'default', 'value' => function(){
                return Property::find()->where(['category_id'=>$this->category_id])->count();
            }],
            [['sort_create'], 'default', 'value' => function(){
                return Property::find()->where(['category_id'=>$this->category_id])->count();
            }],
            [['sort_view'], 'default', 'value' => function(){
                return Property::find()->where(['category_id'=>$this->category_id])->count();
            }],
            [['name', 'category_id', 'value_type', 'sort_filter', 'sort_create', 'sort_view', 'filter_type', 'input_type'], 'required'],
            [['category_id', 'value_type', 'sort_filter', 'sort_create', 'sort_view', 'filter_type', 'input_type', 'parent_id', 'depend_id'], 'integer'],
            [['parent_id', 'depend_id'], 'exist', 'targetClass'=>self::className(), 'targetAttribute'=>'id'],
            [['value_type' ], 'in', 'range'=>[self::VALUE_TYPE_INT, self::VALUE_TYPE_FLOAT, self::VALUE_TYPE_STRING, self::VALUE_TYPE_BOOL, self::VALUE_TYPE_CHECKLIST]],
            [['filter_type' ], 'in', 'range'=>[self::FILTER_TYPE_NONE, self::FILTER_TYPE_SELECT, self::FILTER_TYPE_SELECT_MULTIPLE, self::FILTER_TYPE_CHECKBOX, self::FILTER_TYPE_RANGE]],
            [['input_type' ], 'in', 'range'=>[self::INPUT_TYPE_TEXT, self::INPUT_TYPE_SELECT, self::INPUT_TYPE_CHECKBOX]],
            [['name'], 'string', 'max' => 50],
            [['parent_string'], 'string', 'max' => 50],
            [['category_id'], 'exist', 'targetClass'=>Category::className(), 'targetAttribute'=>'id']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'category_id' => 'Категория',
            'value_type' => 'Тип значения',
            'sort_filter' => 'Порядок в фильтрах',
            'sort_create' => 'Порядок при создании',
            'sort_view' => 'Порядок при просмотре',
            'filter_type' => 'Тип фильтра',
            'input_type' => 'Тип input',
            'parent_id' => 'Родительское свойство',
            'parent_string' => 'Значение родительского свойства (строка)',
            'depend_id' => 'depend id'
        ];
    }

    const VALUE_TYPE_INT = 0;
    const VALUE_TYPE_FLOAT = 1;
    const VALUE_TYPE_STRING = 2;
    const VALUE_TYPE_BOOL = 3;
    const VALUE_TYPE_CHECKLIST = 4;

    const FILTER_TYPE_NONE = 0;
    const FILTER_TYPE_SELECT = 1;
    const FILTER_TYPE_SELECT_MULTIPLE = 2;
    const FILTER_TYPE_CHECKBOX = 3;
    const FILTER_TYPE_RANGE = 4;

    const INPUT_TYPE_TEXT = 0;
    const INPUT_TYPE_SELECT = 1;
    const INPUT_TYPE_CHECKBOX = 2;

    public function isParent()
    {
        return (count(self::find()->where(['parent_id' => $this->id])->all())>0);
    }

    public function getChildren()
    {
        return self::find()->where(['parent_id' => $this->id])->all();
    }

    public function getParentProperty()
    {
        if ($this->parent_id)
            return self::findOne($this->parent_id);
        else
            return null;
    }

    public function isChild()
    {
        return ($this->parent_id != null);
    }
    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }

    public function getPropertyValues()
    {
        return $this->hasMany(PropertyValue::className(), ['property_id' => 'id']);
    }

    public function getPropertyOptions()
    {
        return $this->hasMany(PropertyOption::className(), ['property_id' => 'id'])->orderBy('sort');
    }

    public function getValueTypeSelectArray()
    {
        return [self::VALUE_TYPE_INT => 'Целое число', self::VALUE_TYPE_FLOAT => 'Вещественное число', self::VALUE_TYPE_STRING => 'Строка', self::VALUE_TYPE_BOOL => 'Булевый тип', self::VALUE_TYPE_CHECKLIST => 'Checklist'];
    }

    public function getFilterTypeSelectArray()
    {
        return [self::FILTER_TYPE_NONE => 'Не подлежит фильтрации', self::FILTER_TYPE_SELECT => 'Select', self::FILTER_TYPE_SELECT_MULTIPLE => 'Multiple select', self::FILTER_TYPE_CHECKBOX => 'Checkbox', self::FILTER_TYPE_RANGE => 'Range',];
    }

    public function getInputTypeSelectArray()
    {
        return [self::INPUT_TYPE_TEXT => 'Текстовый', self::INPUT_TYPE_SELECT => 'Select', self::INPUT_TYPE_CHECKBOX => 'Checkbox'];
    }


    public function getValueColumnName()
    {
        $attributeName = 'value_';
        switch ($this->value_type) {

            case self::VALUE_TYPE_INT:
                $attributeName .= 'int';
                break;
            case self::VALUE_TYPE_FLOAT:
                $attributeName .= 'float';
                break;
            case self::VALUE_TYPE_STRING:
                $attributeName .= 'string';
                break;
            case self::VALUE_TYPE_BOOL:
                $attributeName .= 'bool';
                break;
            case self::VALUE_TYPE_CHECKLIST:
                $attributeName.='bool';
        }
        return $attributeName;
    }

    public function getOptionsSelectArray()
    {
        if ($this->depend_id != null or count($this->getPropertyOptions()->all()) == 0)
            return null;
        else
        {
            $result = [];
            foreach($this->propertyOptions as $option)
                $result[$option->getAttribute($this->valueColumnName)] = $option->getAttribute($this->valueColumnName);
            return $result;
        }
    }

    public static function getDependOptionsSelectArray($depend_value, $property_id, $forAjax = true)
    {
        $result = [];
        $property = self::findOne($property_id);
        $options = PropertyOption::find()
            ->where(['property_id'=>$property_id])
            ->andWhere(['depend_string' => $depend_value])
            ->all();
        if ($forAjax)
            foreach ($options as $option)
                $result[] = ['id' => $option->getAttribute($property->valueColumnName), 'name' => $option->getAttribute($property->valueColumnName)];
        else
            foreach ($options as $option)
                $result[$option->getAttribute($property->valueColumnName)] = $option->getAttribute($property->valueColumnName);

        return $result;
    }

    public function getDependProperty()
    {
        if ($this->depend_id)
            return self::findOne($this->depend_id);
        else
            return null;
    }

    public static function propertySelectArray()
    {
        $result = [];
        foreach (self::find()->all() as $property)
            $result[$property->id] = $property->name;
        return $result;
    }

    public function beforeDelete()
    {
        $propertyValues = $this->propertyValues;
        foreach($propertyValues as $propertyValue)
            $propertyValue->delete;

        $propertyOptions = $this->propertyOptions;
        foreach($propertyOptions as $propertyOption)
            $propertyOption->delete;

        return parent::beforeDelete(); // TODO: Change the autogenerated stub
    }
}
