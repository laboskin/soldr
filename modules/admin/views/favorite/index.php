<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\models\FavoriteSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
\app\assets\AdminAsset::register($this);
$this->title = 'Favorites';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="favorite-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Favorite', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'user_id',
            'model',
            'item_id',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
