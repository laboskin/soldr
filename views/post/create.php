<?php

use kartik\file\FileInput;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use app\models\Category;
use yii\helpers\Url;
use yii\widgets\MaskedInput;

\app\assets\PostCreateAsset::register($this);

$this->title = 'Новое объявление - soldr';
/* @var $this yii\web\View */
/* @var $modelPost app\models\Post */
/* @var $modelPropertyValues app\models\PropertyValue[] */

?>
<div class="breadcrumbs">
    <div class="breadcrumbs-container">
        <ul class="breadcrumbs-list">
            <li class="breadcrumbs-list-item">
                <a href="/app/"><span>Главная</span></a>
            </li>
            <li class="breadcrumbs-list-item">
                <a href="/post/"><span>Объявления</span></a>
            </li>
            <li class="breadcrumbs-list-item">
                <a href="/post/create"><span>Новое объявление</span></a>
            </li>
        </ul>
    </div>
</div>

<div class="content">
    <div class="content-side">
        <a href="/app/" class="content-side-previous">
            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                 viewBox="0 0 492 492" xml:space="preserve">
            <path d="M198.608,246.104L382.664,62.04c5.068-5.056,7.856-11.816,7.856-19.024c0-7.212-2.788-13.968-7.856-19.032l-16.128-16.12
                C361.476,2.792,354.712,0,347.504,0s-13.964,2.792-19.028,7.864L109.328,227.008c-5.084,5.08-7.868,11.868-7.848,19.084
                c-0.02,7.248,2.76,14.028,7.848,19.112l218.944,218.932c5.064,5.072,11.82,7.864,19.032,7.864c7.208,0,13.964-2.792,19.032-7.864
                l16.124-16.12c10.492-10.492,10.492-27.572,0-38.06L198.608,246.104z"/></svg>
            Главная
        </a>
        <ul class="content-side-nav">
            <li class="content-side-nav-item">
                <a href="/user/favourites" class="content-side-nav-item-link">
                    <span>Избранное</span>
                </a>
            </li>
            <li class="content-side-nav-item">
                <a href="/user/post-active" class="content-side-nav-item-link">
                    <span>Мои объявления</span>
                </a>
            </li>
            <li class="content-side-nav-item content-side-nav-item-selected">
                <a href="/post/create" class="content-side-nav-item-link">
                    <span>Подать объявление</span>
                </a>
            </li>
            <li class="content-side-nav-item">
                <a href="/user/settings" class="content-side-nav-item-link">
                    <span>Настройки</span>
                </a>
            </li>
        </ul>
    </div>
    <div class="content-main">
        <div class="content-main-nav">
            <div class="title">
                <h1 class="title-name">
                    Новое объявление
                </h1>
            </div>
        </div>
        <div class="post-create">
                <?php $form = ActiveForm::begin([
                    'options' => ['enctype'=>'multipart/form-data'],
                ]); ?>

                <?= Html::hiddenInput('post_id', $modelPost->id); ?>

                <?= $form->field($modelPost, 'category_id')->dropDownList(Category::multiArray(),[
                    'prompt' => 'Выберите категорию',
                    'id' => 'categorySelect',
                    'onchange' => '$.pjax({
                type       : \'POST\',
                url        : \'\',
                container  : \'#pjaxProperties\',
                data       : {categoryId: $(this).val(), post_id:'.$modelPost->id.'},
                push       : false,
                replace    : false,
                timeout    : 10000,
                "scrollTo" : false
            })',
                ]); ?>

                <?php \yii\widgets\Pjax::begin([
                    'id' => 'pjaxProperties',
                    'enablePushState'=> false,
                    'enableReplaceState' => false,
                    'options' => [
                    ]

                ]) ?>

                <?php
                $values = Yii::$app->request->post();
                $categoryId = Yii::$app->request->isPjax?Yii::$app->request->post('categoryId'):Yii::$app->request->post('Post')['category_id'];
                if ($categoryId)
                {
                    $properties = Category::findOne(['id'=>$categoryId])->getPropertiesForCreate();
                    $myPjaxParams = "";
                    foreach ($properties as $property)
                        $myPjaxParams .= 'f'.$property->id.': $("#f'.$property->id.'").val(), ';
                    ?>
                    <script>
                        function myPjaxFunction()
                        {
                            $.pjax({
                                type       : 'POST',
                                container  : '#pjaxProperties',
                                data       : {categoryId: $('#categorySelect').val(), post_id: <?= $modelPost->id ?>,
                                    <?= $myPjaxParams ?>},
                                push       : false,
                                scrollTo : false
                            })
                        }
                    </script>
                    <?php
                    foreach ($properties as $property)
                    {

                        $isChild = $property->isChild();
                        $isParent = $property->isParent();
                        if (!$isChild or ($property->parent_string == $values['f'.$property->parent_id]))
                        {
                            if ($property->value_type != \app\models\Property::VALUE_TYPE_BOOL)
                            {
                                $errorCLass = (Yii::$app->request->isPost and !Yii::$app->request->isPjax and !Yii::$app->request->post('f'.$property->id))?' has-error':'';
                                echo '<div class="form-group required'.$errorCLass.'">';
                                echo Html::label($property->name, 'f'.$property->id, ['class'=>'control-label']);
                                if ($property->value_type != \app\models\Property::VALUE_TYPE_CHECKLIST)
                                {
                                    switch($property->input_type)
                                    {
                                        case \app\models\Property::INPUT_TYPE_TEXT:
                                            switch ($property->value_type)
                                            {
                                                case \app\models\Property::VALUE_TYPE_INT:
                                                    echo \yii\widgets\MaskedInput::widget([
                                                        'name' => 'f'.$property->id,
                                                        'value' => $values['f'.$property->id],
                                                        'options' => [
                                                            'class' => 'form-control',
                                                            'id' => 'f'.$property->id,
                                                            'required' => 'required',
                                                            'onblur' => 'requiredBlur($(this))',
                                                        ],
                                                        'clientOptions' => [
                                                            'alias' => 'integer',
                                                            'rightAlign' => false,
                                                            'removeMaskOnSubmit' => true,
                                                        ]
                                                    ]);
                                                    break;
                                                case \app\models\Property::VALUE_TYPE_FLOAT:
                                                    echo \yii\widgets\MaskedInput::widget([
                                                        'name' => 'f'.$property->id,
                                                        'value' => $values['f'.$property->id],
                                                        'options' => [
                                                            'class' => 'form-control',
                                                            'id' => 'f'.$property->id,
                                                            'required' => 'required',
                                                            'onblur' => 'requiredBlur($(this))',
                                                        ],
                                                        'clientOptions' => [
                                                            'alias' => 'decimal',
                                                            'rightAlign' => false,
                                                            'removeMaskOnSubmit' => true,
                                                        ]
                                                    ]);
                                                    break;
                                                case \app\models\Property::VALUE_TYPE_STRING:
                                                    echo Html::textInput('f'.$property->id, $values['f'.$property->id], [
                                                        'id' => 'f'.$property->id,
                                                        'class' => 'form-control',
                                                        'required' => 'required',
                                                        'onblur' => 'requiredBlur($(this))',

                                                    ]);
                                                    break;
                                            }
                                            break;
                                        case \app\models\Property::INPUT_TYPE_SELECT:
                                            if (!$property->depend_id)
                                                echo Html::dropDownList("f$property->id", $values['f'.$property->id], $property->optionsSelectArray, [
                                                    'prompt' => 'Все',
                                                    'id' => 'f'.$property->id,
                                                    'class' => 'form-control',
                                                    'onchange' => $isParent?'myPjaxFunction();':'',
                                                    'required' => 'required',
                                                    'onblur' => 'requiredBlur($(this))',
                                                    'options'=>[
                                                    ]]);
                                            else
                                            {
                                                echo Html::hiddenInput("f$property->id".'param', $property->id, ['id' => 'f'.$property->id.'param']);
                                                echo Html::hiddenInput("f$property->id".'paramValue', $values['f'.$property->id], ['id' => 'f'.$property->id.'paramValue']);
                                                echo \kartik\depdrop\DepDrop::widget([
                                                    'options'=>[
                                                        'class' => 'form-control',
                                                        'onchange' => $isParent?'myPjaxFunction();':'',
                                                        'required' => 'required',
                                                        'onblur' => 'requiredBlur($(this))',
                                                    ],
                                                    'id' => 'f'.$property->id,
                                                    'name' => "f$property->id",
                                                    'pluginOptions'=>[
                                                        'initialize' => true,
                                                        'depends'=>['f'.$property->depend_id],
                                                        'placeholder'=>'Все',
                                                        'url'=>Url::to(['/post/depend-input']),
                                                        'params' => ['f'.$property->id.'param', 'f'.$property->id.'paramValue'],
                                                    ]
                                                ]);
                                            }

                                            break;
                                    }
                                }
                                else
                                {
                                    echo Html::hiddenInput('f'.$property->id, 1, []);
                                    echo '<div class="checkbox-list">';
                                    foreach ($properties as $boolProperty)
                                    {
                                        if ($boolProperty->depend_id == $property->id)
                                        {
                                            echo '<div>';
                                            echo Html::checkbox('f'.$boolProperty->id, $values['f'.$boolProperty->id], ['id'=>'f'.$boolProperty->id, 'class'=>'checkbox']);
                                            echo '<label class="checkbox-label" for="'.'f'.$boolProperty->id.'">'.$boolProperty->name.'</label>';
                                            echo '</div>';
                                        }
                                    }
                                    echo '</div>';
                                }


                                echo '<p class="help-block help-block-error">Необходимо заполнить «'.$property->name.'».</p>';
                                echo '</div>';

                            }
                        }
                    }
                }
                ?>

                <?php \yii\widgets\Pjax::end() ?>
                <?= $form->field($modelPost, 'name')->textInput(['maxlength' => true]) ?>

                <?= $form->field($modelPost, 'price')->widget(MaskedInput::classname(), [
                    'name' => 'price',
                    'value' => $values['f'.$property->id],
                    'options' => [
                        'id' => 'price',
                        'onchange' => '$(this).removeMask();',
                    ],
                    'clientOptions' => [
                        'alias' => 'integer',
                        'rightAlign' => false,
                        'removeMaskOnSubmit' => true,
                    ]
                ]); ?>

                <?= $form->field($modelPost, 'description')->textarea(['rows' => 6]) ?>

                <?php \yii\widgets\Pjax::begin([
                    'id' => 'pjaxPhotoUploader',
                    'enablePushState'=> false,
                    'enableReplaceState' => false,
                    'options' => [
                    ]

                ]) ?>

                <div class="form-group">
                    <?= Html::label('Фото', '', ['class'=>'control-label']); ?>
                    <?= FileInput::widget([
                        'name' => 'Image[attachment]',
                        'options' => [
                            'multiple'=>true,
                            'accept'=>'image/*'
                        ],
                        'pluginOptions'=> [

                            'deleteUrl' => Url::toRoute(['/post/delete-image']),
                            'uploadUrl' => Url::toRoute(['/post/save-image']),
                            'uploadExtraData' => [
                                'Image[post_id]' => $modelPost->id,
                            ],
                            'uploadAsync'=> true,
                            'initialPreview'=> $modelPost->imagesLinks,
                            'initialPreviewAsData'=>true,
                            'overwriteInitial'=>false,
                            'initialPreviewConfig'=>$modelPost->imagesLinksData,
                            'showUpload'=>false,
                            'showRemove'=>false,
                            'showCaption' => false,
                            'showBrowse' => false,
                            'showClose'=>false,
                            'browseOnZoneClick' => true,
                            'fileActionSettings' => [
                                'showZoom' => false,
                            ],

                        ],
                        'pluginEvents' => [
                            'filesorted' => new \yii\web\JsExpression('function(event, params){
                      $.post("'.Url::toRoute(["/post/sort-image","id"=>$modelPost->id]).'",{sort: params});
                        }'),
                            'filebatchselected' => new \yii\web\JsExpression('function(event, files){
                      $(this).fileinput("upload");
                        }'),
                            'filebatchuploadcomplete' => new \yii\web\JsExpression('function(event, files){
                      $.pjax({
                            type       : \'POST\',
                            url        : \'\',
                            container  : \'#pjaxPhotoUploader\',
                            data       : {post_id:'.$modelPost->id.'},
                            push       : false,
                            replace    : false,
                            timeout    : 10000,
                            "scrollTo" : false
                        })
                                    }'),
                        ],
                    ]) ?>
                </div>
                <?php \yii\widgets\Pjax::end() ?>
                <?= Html::submitButton('Опубликовать', ['class' => 'applyButton']) ?>
                <?php ActiveForm::end(); ?>
        </div>

    </div>
</div>

<script>
    function requiredBlur(elem){
        if (!elem.val())
            elem.closest(".form-group").addClass("has-error");
        else
            elem.closest(".form-group").removeClass("has-error");
    }
    $(document).ready(function(){

    })
</script>
