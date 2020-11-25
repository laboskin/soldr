<?php

namespace app\modules\admin\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Property;

/**
 * PropertySearch represents the model behind the search form of `app\models\Property`.
 */
class PropertySearch extends Property
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'category_id', 'value_type', 'sort_filter', 'sort_create', 'sort_view', 'filter_type', 'input_type', 'parent_id', 'depend_id'], 'integer'],
            [['name', 'parent_string'], 'safe'],
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
        $query = Property::find();

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
            'category_id' => $this->category_id,
            'value_type' => $this->value_type,
            'sort_filter' => $this->sort_filter,
            'sort_create' => $this->sort_create,
            'sort_view' => $this->sort_view,
            'filter_type' => $this->filter_type,
            'input_type' => $this->input_type,
            'parent_id' => $this->parent_id,
            'depend_id' => $this->depend_id,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'parent_string', $this->parent_string]);

        return $dataProvider;
    }
}
