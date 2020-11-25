<?php
\app\assets\AdminAsset::register($this);

?>

<div class="admin-default-index">
    <h1>Панель администратора</h1>
    <h3>Будьте внимательны.</h3>
    <div class="admin-panel-buttons">
        <?= \yii\bootstrap\Html::a('Category', '/admin/category', ['class' => 'btn btn-success'])?>
        <?= \yii\bootstrap\Html::a('City', '/admin/city', ['class' => 'btn btn-success'])?>
        <?= \yii\bootstrap\Html::a('Favorite', '/admin/favorite', ['class' => 'btn btn-success'])?>
        <?= \yii\bootstrap\Html::a('Image', '/admin/image', ['class' => 'btn btn-success'])?>
        <?= \yii\bootstrap\Html::a('Post', '/admin/post', ['class' => 'btn btn-success'])?>
        <?= \yii\bootstrap\Html::a('Property', '/admin/property', ['class' => 'btn btn-success'])?>
        <?= \yii\bootstrap\Html::a('Property Option', '/admin/property-option', ['class' => 'btn btn-success'])?>
        <?= \yii\bootstrap\Html::a('Property Value', '/admin/property-value', ['class' => 'btn btn-success'])?>
        <?= \yii\bootstrap\Html::a('Region', '/admin/region', ['class' => 'btn btn-success'])?>
        <?= \yii\bootstrap\Html::a('User', '/admin/user', ['class' => 'btn btn-success'])?>
    </div>


</div>
