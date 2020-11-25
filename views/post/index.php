<?php

use app\models\Category;
use yii\helpers\Html;
use yii\widgets\LinkPager;
use app\assets\PostIndexAsset;
use yii\helpers\Url;

PostIndexAsset::register($this);

/* @var $this yii\web\View */
/* @var $models \app\models\Post[] */
/* @var $cat_id int */
/* @var $pages \yii\data\Pagination */
/* @var $totalCount int */
$category = $cat_id?Category::findOne(['id'=>$cat_id]):null;
if ($cat_id)
        $this->title = $category['name'].': '.count($models).' объявлений - soldr';
else
    $this->title = 'Все объявления - soldr';
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
            <?php if($category): ?>
            <?php if($category->parent_id): ?>
                <li class="breadcrumbs-list-item">
                    <a href="/post/index?cat_id=<?= $category->parent_id ?>"><span><?=  $category->getParent()->name?></span></a>
                </li>
            <?php endif; ?>
            <li class="breadcrumbs-list-item">
                <a href="/post/index?cat_id=<?= $category->id ?>"><span><?=  $category->name?></span></a>
            </li>
            <?php endif; ?>

        </ul>
    </div>
</div>
<div class="show-filters-button">Показать фильтры</div>
    <div class="content">
        <div class="content-side">
            <?php
                    if (!$category)
                    {
                        $previousHref = '/app/';
                        $previousName = 'Главная';
                    }
                    elseif (!$category->parent_id)
                    {
                        $previousHref = '/post/';
                        $previousName = 'Все объявления';
                    }
                    else
                    {
                        $previousHref = '/post/index?cat_id='.$category->parent_id;
                        $previousName = $category->getParent()->name;
                    }
                ?>
                <a href="<?= $previousHref ?>" class="content-side-previous">
                    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                         viewBox="0 0 492 492" xml:space="preserve">
            <path d="M198.608,246.104L382.664,62.04c5.068-5.056,7.856-11.816,7.856-19.024c0-7.212-2.788-13.968-7.856-19.032l-16.128-16.12
                C361.476,2.792,354.712,0,347.504,0s-13.964,2.792-19.028,7.864L109.328,227.008c-5.084,5.08-7.868,11.868-7.848,19.084
                c-0.02,7.248,2.76,14.028,7.848,19.112l218.944,218.932c5.064,5.072,11.82,7.864,19.032,7.864c7.208,0,13.964-2.792,19.032-7.864
                l16.124-16.12c10.492-10.492,10.492-27.572,0-38.06L198.608,246.104z"/></svg>
                    <?= $previousName ?>
                </a>
            <ul class="content-side-nav">
                <?php
                if (!$category)
                    $navcategories = Category::find()->where(['parent_id'=>0])->all();
                elseif($category->parent_id)
                    $navcategories = $category->getParent()->getChildren();
                else
                    $navcategories = $category->getChildren(); ?>
                <?php foreach ($navcategories as $navcategory): ?>
                    <li class="content-side-nav-item <?= ($navcategory->id == $cat_id)?'content-side-nav-item-selected':''?>">
                        <a href="/post/index?cat_id=<?= $navcategory->id ?><?= (Yii::$app->request->get('q') and $navcategory->id != $cat_id)?'&q='.Yii::$app->request->get('q'):''?>" class="content-side-nav-item-link">
                            <span><?= $navcategory->name?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php
            if (Yii::$app->request->get('cat_id'))
                $properties = $category->getPropertiesForFilter();
            else
                $properties = [];
            $values = Yii::$app->request->isPjax?Yii::$app->request->post():Yii::$app->request->get();
            $queryParamsFilters = Yii::$app->request->queryParams;
            unset($queryParamsFilters['pmin'], $queryParamsFilters['pmax']);
            foreach ($properties as $property)
            {
                switch($property->filter_type)
                {
                    case \app\models\Property::FILTER_TYPE_SELECT:
                    case \app\models\Property::FILTER_TYPE_SELECT_MULTIPLE:
                    case \app\models\Property::FILTER_TYPE_CHECKBOX:
                        unset($queryParamsFilters["f$property->id"]);
                        break;
                    case \app\models\Property::FILTER_TYPE_RANGE:
                        unset($queryParamsFilters["f$property->id"."min"], $queryParamsFilters["f$property->id"."max"]);
                        break;
                }
            }

            $myPjaxParams = "";
            foreach ($properties as $property)
            {
                switch($property->filter_type)
                {
                    case \app\models\Property::FILTER_TYPE_SELECT:
                    case \app\models\Property::FILTER_TYPE_SELECT_MULTIPLE:
                    case \app\models\Property::FILTER_TYPE_CHECKBOX:
                        $myPjaxParams .= 'f'.$property->id.': $("#f'.$property->id.'").val(), ';
                        break;
                    case \app\models\Property::FILTER_TYPE_RANGE:
                        $myPjaxParams .= 'f'.$property->id.'min: $("#f'.$property->id.'min").val(), ';
                        $myPjaxParams .= 'f'.$property->id.'max: $("#f'.$property->id.'max").val(), ';
                        break;
                }
                if ($property->depend_id){
                    $myPjaxParams .= "f$property->id".'param: $("#f'.$property->id.'param").val(), ';
                    $myPjaxParams .= "f$property->id".'paramValue: $("#f'.$property->id.'").val(), ';
                }
            }

            ?>
            <script>
                function myPjaxFunction()
                {
                    $.pjax({
                        type       : 'POST',
                        container  : '#myPjaxContainer',
                        data       : {<?= $myPjaxParams ?>},
                        push       : false,
                        scrollTo : false
                    })
                }

            </script>
            <?= Html::beginForm(Yii::$app->controller->action->id.'?'.http_build_query($queryParamsFilters), 'get', [
                'class' => 'filters-form',
            ]); ?>
            <div class="filter-block">
                <div class="filter-block-title">Цена</div>
                <?php
                echo \yii\widgets\MaskedInput::widget([
                    'name' => "pmin",
                    'value' => Yii::$app->request->get("pmin"),
                    'options' => [
                        'class' => 'filter-input-text filter-double-text',
                        'placeholder' => 'от ',
                        'onfocus' => '$(this).inputmask({ prefix: "" });
                                  $(this).inputmask({ suffix: "" });  ',
                        'onblur' => 'if ($(this).val() ==="") $(this).val(undefined);
                                         $(this).inputmask({ prefix: "от " });
                                         $(this).inputmask({ suffix: " руб." });',
                    ],
                    'clientOptions' => [
                        'alias' => 'integer',
                        'rightAlign' => false,
                        'groupSize'=>3,
                        'groupSeparator' => ' ',
                        'autoGroup' => true,
                        'prefix' => 'от ',
                        'suffix' => ' руб.',
                        'removeMaskOnSubmit' => true,
                    ]
                ]);
                echo \yii\widgets\MaskedInput::widget([
                    'name' => "pmax",
                    'value' => Yii::$app->request->get("pmax"),
                    'options' => [
                        'class' => 'filter-input-text filter-double-text',
                        'placeholder' => 'до',
                        'onfocus' => '$(this).inputmask({ prefix: "" });
                                  $(this).inputmask({ suffix: "" }); ',
                        'onblur' => 'if ($(this).val() ==="") $(this).val(undefined);
                                         $(this).inputmask({ prefix: "до " });
                                         $(this).inputmask({ suffix: " руб." });',

                    ],
                    'clientOptions' => [
                        'alias' => 'integer',
                        'rightAlign' => false,
                        'groupSize'=>3,
                        'groupSeparator' => ' ',
                        'autoGroup' => true,
                        'prefix' => 'до ',
                        'suffix' => ' руб.',
                        'removeMaskOnSubmit' => true,
                    ]
                ]); ?>
            </div>
            <?php
            \yii\widgets\Pjax::begin([
                'id' => 'myPjaxContainer',
                'enablePushState' => false,
                'enableReplaceState' => false,
                'options' => [
                ]
            ]);
            foreach ($properties as $property)
            {
                if ($property->filter_type == \app\models\Property::FILTER_TYPE_SELECT and $property->depend_id)
                {
                    echo Html::hiddenInput("f$property->id".'param', $property->id, ['id' => 'f'.$property->id.'param']);
                    echo Html::hiddenInput("f$property->id".'paramValue', $values['f'.$property->id], ['id' => 'f'.$property->id.'paramValue']);
                }
            }
            foreach ($properties as $property)
            {
                $isChild = $property->isChild();
                $isParent = $property->isParent();

                if (!$isChild or ($property->parent_string == $values['f'.$property->parent_id]))
                {
                    echo '<div class="filter-block">';
                    echo '<div class="filter-block-title">'.$property->name.'</div>';
                    switch($property->filter_type)
                    {
                        case \app\models\Property::FILTER_TYPE_SELECT:
                            if (!$property->depend_id)
                                echo Html::dropDownList("f$property->id", $values['f'.$property->id], $property->optionsSelectArray, [
                                    'prompt' => 'Все',
                                    'id' => 'f'.$property->id,
                                    'class' => 'form-control',
                                    'onchange' => $isParent?'myPjaxFunction();':'',
                                    'options'=>[
                                    ]]);
                            else
                                echo \kartik\depdrop\DepDrop::widget([
                                    'options'=>[
                                        'class' => 'form-control',
                                        'onchange' => $isParent?'myPjaxFunction();':'',
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
                            break;
                        case \app\models\Property::FILTER_TYPE_SELECT_MULTIPLE:
                            echo Html::checkboxList("f$property->id", $values['f'.$property->id], $property->optionsSelectArray, [
                                'class' => 'checkbox-list',
                                'item' => function($index, $label, $name, $checked, $value) {
                                    $checkedLabel = $checked ? 'checked' : '';
                                    $inputId = str_replace(['[', ']'], ['', ''], $name) . '_' . $index;

                                    return "<div><input type='checkbox' class='checkbox' name=$name value='".Html::encode($value)."' id=$inputId $checkedLabel>"
                                        . "<label class='checkbox-label' for=$inputId>".Html::encode($label)."</label></div>";
                                },

                            ]);
                            break;
                        case \app\models\Property::FILTER_TYPE_CHECKBOX:
                            echo Html::checkbox("f$property->id", $values['f'.$property->id], [
                                'options'=>[
                                ]]);
                            break;
                        case \app\models\Property::FILTER_TYPE_RANGE:
                            switch($property->value_type)
                            {
                                case \app\models\Property::VALUE_TYPE_INT:
                                    echo \yii\widgets\MaskedInput::widget([
                                        'name' => "f$property->id"."min",
                                        'value' => $values['f'.$property->id.'min'],
                                        'options' => [
                                            'class' => 'filter-input-text filter-double-text',
                                            'id' => "f$property->id"."min",
                                            'placeholder' => 'от ',
                                            'onfocus' => '$(this).inputmask({ prefix: "" });',
                                            'onblur' => 'if ($(this).val() ==="") $(this).val(undefined);
                                         $(this).inputmask({ prefix: "от " });',
                                        ],
                                        'clientOptions' => [
                                            'alias' => 'integer',
                                            'rightAlign' => false,
                                            'prefix' => 'от ',
                                            'removeMaskOnSubmit' => true,
                                        ]
                                    ]);
                                    echo \yii\widgets\MaskedInput::widget([
                                        'name' => "f$property->id"."max",
                                        'value' => $values['f'.$property->id.'max'],
                                        'options' => [
                                            'class' => 'filter-input-text filter-double-text',
                                            'id' => "f$property->id"."max",
                                            'placeholder' => 'до',
                                            'onfocus' => '$(this).inputmask({ prefix: "" });',
                                            'onblur' => 'if ($(this).val() ==="") $(this).val(undefined);
                                         $(this).inputmask({ prefix: "до " });',


                                        ],
                                        'clientOptions' => [
                                            'alias' => 'integer',
                                            'rightAlign' => false,
                                            'prefix' => 'до ',
                                            'removeMaskOnSubmit' => true,
                                        ]
                                    ]);
                                    break;
                                case \app\models\Property::VALUE_TYPE_FLOAT:
                                    echo \yii\widgets\MaskedInput::widget([
                                        'name' => "f$property->id"."min",
                                        'value' => $values['f'.$property->id.'min'],
                                        'options' => [
                                            'class' => 'filter-input-text filter-double-text',
                                            'id' => "f$property->id"."min",
                                            'placeholder' => 'от ',
                                            'onfocus' => '$(this).inputmask({ prefix: "" });',
                                            'onblur' => 'if ($(this).val() ==="") $(this).val(undefined);
                                         $(this).inputmask({ prefix: "от " });',
                                        ],
                                        'clientOptions' => [
                                            'alias' => 'decimal',
                                            'rightAlign' => false,
                                            'prefix' => 'от ',
                                            'radixPoint' => '.',
                                            'removeMaskOnSubmit' => true,
                                        ]
                                    ]);
                                    echo \yii\widgets\MaskedInput::widget([
                                        'name' => "f$property->id"."max",
                                        'value' => $values['f'.$property->id.'max'],
                                        'options' => [
                                            'class' => 'filter-input-text filter-double-text',
                                            'id' => "f$property->id"."max",
                                            'placeholder' => 'до',
                                            'onfocus' => '$(this).inputmask({ prefix: "" });',
                                            'onblur' => 'if ($(this).val() ==="") $(this).val(undefined);
                                         $(this).inputmask({ prefix: "до " });',
                                        ],
                                        'clientOptions' => [
                                            'alias' => 'decimal',
                                            'rightAlign' => false,
                                            'prefix' => 'до ',
                                            'radixPoint' => '.',
                                            'removeMaskOnSubmit' => true,
                                        ]
                                    ]);
                                    break;
                            }
                    }
                    echo '</div>';
                }
            }
            \yii\widgets\Pjax::end();
            ?>
            <?= Html::submitButton('Применить', ['class' => 'filters-apply-button']) ?>
            <?= Html::endForm(); ?>
        </div>
        <div class="content-main">
            <div class="content-main-nav">
                <div class="title">
                    <h1 class="title-name">
                        <?= $cat_id?$category->name:'Все объявления'; ?>
                        <span class="title-count">
                            <?= $totalCount ?>
                        </span>
                    </h1>
                </div>
                <div class="content-sort">
                    <span>Сортировать: </span>
                    <?php
                    $queryParamsSort = Yii::$app->request->queryParams;
                    unset($queryParamsSort['sort']);
                    ?>
                    <?= Html::beginForm(Yii::$app->controller->action->id.'?'.http_build_query($queryParamsSort), 'get', [
                    ]); ?>
                    <?= Html::radioList('sort', Yii::$app->request->get('sort')?Yii::$app->request->get('sort'):0, [0 => 'По дате', 1 => 'Дешевле', 2 => 'Дороже'], [
                        'class'=> 'sort-radiolist',
                        'onchange' => '$(this).closest("form").submit();',
                    ]); ?>
                    <?= Html::endForm(); ?>
                </div>
            </div>
            <?php if (count($models) == 0): ?>
            <div class="empty-block">
                <h4>Ничего не найдено в области поиска</h4>
                <p>Выберите другую область поиска.</p>
                <p>Задайте запрос по-другому или установите более мягкие ограничения.</p>
            </div>
            <? endif; ?>
            <div class="items-grid">
                <? foreach ($models as $model): ?>
                    <a href="<?='/post/view?id='.$model['id']?>" class="item"><div>
                            <div class="item-image-price">
                                <div class="item-image-carousel item-image-carousel-off">
                                    <?php
                                    foreach ($model->smallPhotos as $photoUrl)
                                        echo '<img src="'.$photoUrl.'" alt="">';
                                    ?>
                                    <div class="item-price"><?= number_format($model->price, 0, '', ' ').' руб.'?></div>
                                </div>
                            </div>
                            <div class="item-headline"><?= $model['name']?></div>
                            <div class="item-date"><?= $model->timeAgoString()?></div>
                        </div></a>
                <? endforeach; ?>

                <div class="hidden-item"></div><div class="hidden-item"></div>
                <div class="hidden-item"></div><div class="hidden-item"></div>
            </div>
            <?= LinkPager::widget(['pagination' => $pages]); ?>
        </div>
        <div class="lose-focus-filters"></div>
    </div>


<script>
            var userMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|BB|PlayBook|IEMobile|Windows Phone|Kindle|Silk|Opera Mini/i.test(navigator.userAgent);
            $(document).ready(function() {
                $(".item-image-carousel").brazzersCarousel();
                $(".item-image-carousel").removeClass('brazzers-daddy');
                $(".item-image-carousel").addClass('item-image-carousel-off');
                $(".item-image-carousel").find('img').hide();
                $(".item-image-carousel").find('img:first-of-type').show();
                $(".item-image-carousel").find('.tmb-wrap-table div').removeClass('active');
                $(".item-image-carousel").find('.tmb-wrap-table div:first-of-type').addClass('active');

                $('.sort-radiolist label:has(input[checked])').addClass('sort-radiolist-label-active');

                if(!userMobile)
                {
                    $(".item").mouseover(
                        function(){
                            $(this).find('.item-image-carousel').addClass('brazzers-daddy');
                            $(this).find('.item-image-carousel').removeClass('item-image-carousel-off');
                        }
                    );

                    $(".item").mouseleave(
                        function(){
                            $(this).find('.item-image-carousel').removeClass('brazzers-daddy');
                            $(this).find('.item-image-carousel').addClass('item-image-carousel-off');
                            $(this).find('img').hide();
                            $(this).find('img:first-of-type').show();
                            $(this).find('.tmb-wrap-table div').removeClass('active');
                            $(this).find('.tmb-wrap-table div:first-of-type').addClass('active');
                        }
                    );
                }
                $('.show-filters-button').click(function(){
                    $('.content-side').addClass('content-side-visible');
                    $('.lose-focus-filters').css('display', 'block');
                    scrollLock.disablePageScroll(document.querySelector('.content-side'));
                });
                $('.lose-focus-filters').click(function () {
                    $('.content-side').removeClass('content-side-visible');
                    $('.lose-focus-filters').css('display', 'none');
                    scrollLock.enablePageScroll(document.querySelector('.content-side'));

                });
                $('.toggle-sort').click(function(){
                    $('.content-sort').toggleClass('content-sort-visible');
                });
            });
        </script>