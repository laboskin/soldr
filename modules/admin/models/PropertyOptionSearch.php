<?php

namespace app\modules\admin\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\PropertyOption;

/**
 * PropertyOptionSearch represents the model behind the search form of `app\models\PropertyOption`.
 */
class PropertyOptionSearch extends PropertyOption
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'property_id', 'sort'], 'integer'],
            [['value_string', 'depend_string'], 'safe'],
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
        $query = PropertyOption::find();

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
            'sort' => $this->sort,
        ]);

        $query->andFilterWhere(['like', 'value_string', $this->value_string])
            ->andFilterWhere(['like', 'depend_string', $this->depend_string]);

        return $dataProvider;
    }
}
