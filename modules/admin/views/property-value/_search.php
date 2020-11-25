<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\PropertyValueSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="property-value-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'property_id') ?>

    <?= $form->field($model, 'post_id') ?>

    <?= $form->field($model, 'value_int') ?>

    <?= $form->field($model, 'value_float') ?>

    <?php // echo $form->field($model, 'value_string') ?>

    <?php // echo $form->field($model, 'value_bool') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
