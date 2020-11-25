<?php

namespace app\modules\admin\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\PropertyValue;

/**
 * PropertyValueSearch represents the model behind the search form of `app\models\PropertyValue`.
 */
class PropertyValueSearch extends PropertyValue
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'property_id', 'post_id', 'value_int', 'value_bool'], 'integer'],
            [['value_float'], 'number'],
            [['value_string'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = PropertyValue::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'property_id' => $this->property_id,
            'post_id' => $this->post_id,
            'value_int' => $this->value_int,
            'value_float' => $this->value_float,
            'value_bool' => $this->value_bool,
        ]);

        $query->andFilterWhere(['like', 'value_string', $this->value_string]);

        return $dataProvider;
    }
}
