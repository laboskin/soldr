<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\PropertyOption */
\app\assets\AdminAsset::register($this);
$this->title = 'Create Property Option';
$this->params['breadcrumbs'][] = ['label' => 'Property Options', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="property-option-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
