<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Property */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="property-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'category_id')->textInput() ?>

    <?= $form->field($model, 'value_type')->textInput() ?>

    <?= $form->field($model, 'sort_filter')->textInput() ?>

    <?= $form->field($model, 'sort_create')->textInput() ?>

    <?= $form->field($model, 'sort_view')->textInput() ?>

    <?= $form->field($model, 'filter_type')->textInput() ?>

    <?= $form->field($model, 'input_type')->textInput() ?>

    <?= $form->field($model, 'parent_id')->textInput() ?>

    <?= $form->field($model, 'parent_string')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'depend_id')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
