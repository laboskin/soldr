<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\PropertyValue */
\app\assets\AdminAsset::register($this);
$this->title = 'Create Property Value';
$this->params['breadcrumbs'][] = ['label' => 'Property Values', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="property-value-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
