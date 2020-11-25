<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PropertyValue */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="property-value-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'property_id')->textInput() ?>

    <?= $form->field($model, 'post_id')->textInput() ?>

    <?= $form->field($model, 'value_int')->textInput() ?>

    <?= $form->field($model, 'value_float')->textInput() ?>

    <?= $form->field($model, 'value_string')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'value_bool')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
