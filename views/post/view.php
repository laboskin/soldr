<?php

use app\models\City;
use laboskin\favorite\widgets\FavoriteButton;
use yii\bootstrap\Modal;
use yii\helpers\Html;

\app\assets\PostViewAsset::register($this);

/* @var $this yii\web\View */
/* @var $model app\models\Post */
/* @var $propertyValues app\models\PropertyValue[] */

$this->title = $model->name.' - soldr';
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
                    <a href="/post/index?cat_id=<?= $model->category->parent_id ?>"><span><?=  $model->category->parent->name?></span></a>
                </li>
            <li class="breadcrumbs-list-item">
                <a href="/post/index?cat_id=<?= $model->category->id ?>"><span><?=  $model->category->name?></span></a>
            </li>
        </ul>
    </div>
</div>

<div class="content">
    <div class="content-side">
            <a href="<?= Yii::$app->request->referrer ?:'/post/index?cat_id='.$model->category_id ?>" class="content-side-previous">
                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                     viewBox="0 0 492 492" xml:space="preserve">
            <path d="M198.608,246.104L382.664,62.04c5.068-5.056,7.856-11.816,7.856-19.024c0-7.212-2.788-13.968-7.856-19.032l-16.128-16.12
                C361.476,2.792,354.712,0,347.504,0s-13.964,2.792-19.028,7.864L109.328,227.008c-5.084,5.08-7.868,11.868-7.848,19.084
                c-0.02,7.248,2.76,14.028,7.848,19.112l218.944,218.932c5.064,5.072,11.82,7.864,19.032,7.864c7.208,0,13.964-2.792,19.032-7.864
                l16.124-16.12c10.492-10.492,10.492-27.572,0-38.06L198.608,246.104z"/></svg>
                <?=  Yii::$app->request->referrer?'Назад к поиску':$model->category->name; ?>
            </a>
        <ul class="content-side-nav">
            <?php $navcategories = \app\models\Category::findOne(['id'=>$model->category_id])->getParent()->getChildren() ?>
            <?php foreach ($navcategories as $navcategory): ?>
                <li class="content-side-nav-item <?= ($navcategory->id == $model->category_id)?'content-side-nav-item-selected':''?>">
                    <a href="/post/index?cat_id=<?= $navcategory->id ?>" class="content-side-nav-item-link">
                        <span><?= $navcategory->name?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="content-main">
        <?php if(in_array(\app\models\User::findOne(Yii::$app->user->getId())->status, [\app\models\User::STATUS_MODERATOR, \app\models\User::STATUS_ADMIN])): ?>
            <div class="admin-actions">
                <div class="admin-label">
                    Статус объявления:
                    <span><?php
                        switch($model->status){
                            case \app\models\Post::STATUS_POSTED:
                                echo 'На проверке';
                                break;
                            case \app\models\Post::STATUS_ACTIVE:
                                echo 'Опубликовано';
                                break;
                            case \app\models\Post::STATUS_DECLINED:
                                echo 'Отклонено';
                                break;
                            case \app\models\Post::STATUS_CLOSED:
                                echo 'Завершено';
                                break;
                            case \app\models\Post::STATUS_BAN:
                                echo 'Заблокировано';
                                break;



                        }?></span>
                </div>
                <div class="admin-buttons">
                    <?php if(in_array($model->status, [\app\models\Post::STATUS_POSTED, \app\models\Post::STATUS_DECLINED, \app\models\Post::STATUS_BAN])): ?>
                        <a href="/app/admin-approve?id=<?=$model->id?>">
                            Опубликовать
                        </a>
                    <?php if($model->status != \app\models\Post::STATUS_DECLINED): ?>
                        <a href="/app/admin-decline?id=<?=$model->id?>">
                            Отклонить
                        </a>
                    <?php endif; ?>
                    <?php endif; ?>
                    <?php if($model->status == \app\models\Post::STATUS_ACTIVE): ?>
                        <a href="/app/admin-date-update?id=<?=$model->id?>">
                            Поднять
                        </a>

                        <a href="/app/admin-decline?id=<?=$model->id?>">
                            Отклонить
                        </a>
                        <a href="/app/admin-close?id=<?=$model->id?>">
                            Закрыть
                        </a>
                    <?php endif; ?>
                    <?php if($model->status != \app\models\Post::STATUS_POSTED): ?>
                    <a href="/app/admin-check-again?id=<?=$model->id?>">
                        На проверку
                    </a>
                    <?php endif; ?>
                    <a href="/app/admin-ban?id=<?=$model->id?>">
                        Заблокировать
                    </a>
                </div>

        </div>
        <?php endif; ?>

        <?php if(Yii::$app->user->id == $model->user_id): ?>
            <?php
                switch ($model->status){
                    case \app\models\Post::STATUS_POSTED:
                        $statusClass = 'posted';
                        $statusTitle = 'Сейчас объявление проверяется модераторами.';
                        $statusText = 'Как правило, это занимает несколько минут. Но иногда нам нужно больше времени на проверку.';
                        break;
                    case \app\models\Post::STATUS_ACTIVE:
                        $statusClass = 'active';
                        if($model->date < \app\models\Post::expiredDate())
                        {
                            $statusClass = 'expired';
                            $statusTitle = 'У вашего объявления истёк срок публикации.';
                            $statusText = '';
                        }
                        $statusTitle = 'Ваше объявление опубликовано.';
                        $statusText = '';
                        break;
                    case \app\models\Post::STATUS_DECLINED:
                        $statusClass = 'declined';
                        $statusTitle = 'Ваше объявление не прошло модерацию.';
                        $statusText = 'Ознакомьтесь с правилами подачи объявлений и проверьте введённые вами данные. Вы можете отредактировать объявление, и оно попадёт на повторную проверку.';
                        break;
                    case \app\models\Post::STATUS_CLOSED:
                        $statusClass = 'closed';
                        $statusTitle = 'Вы закрыли это объявление';
                        $statusText = '';
                        break;
                    case \app\models\Post::STATUS_BAN:
                        $statusClass = 'ban';
                        $statusTitle = 'Ваше объявление заблокировано за грубое нарушение правил.';
                        $statusText = 'Ознакомьтесь с правилами подачи объявлений и проверьте ввёденные вами данные. Не допускайте ошибок при следующей подаче объявления.';
                        break;
                }
            ?>
        <div class="author-actions <?= $statusClass ?>">
            <div class="author-actions-title"><?= $statusTitle?></div>
            <p class="author-actions-text"><?= $statusText?></p>
            <div class="author-buttons">
                <?php if($statusClass == 'active'): ?>
                    <?= Html::a('Поднять', ['date-update', 'id' => $model->id], ['class' => 'author-button']) ?>
                    <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'author-button']) ?>
                    <?= Html::a('Снять с публикации', ['close', 'id' => $model->id], ['class' => 'author-button']) ?>
                <?php endif; ?>
                <?php if($statusClass == 'posted'): ?>
                    <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'author-button']) ?>
                    <?= Html::a('Снять с публикации', ['close', 'id' => $model->id], ['class' => 'author-button']) ?>
                <?php endif; ?>
                <?php if($statusClass == 'closed'): ?>
                    <?= Html::a('Активировать', ['activate', 'id' => $model->id], ['class' => 'author-button']) ?>
                    <?= Html::a('Удалить', ['delete', 'id' => $model->id], ['class' => 'author-button']) ?>
                <?php endif; ?>
                <?php if($statusClass == 'declined'): ?>
                    <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'author-button']) ?>
                    <?= Html::a('Удалить', ['delete', 'id' => $model->id], ['class' => 'author-button']) ?>
                <?php endif; ?>
                <?php if($statusClass == 'expired'): ?>
                    <?= Html::a('Активировать и поднять', ['date-update', 'id' => $model->id], ['class' => 'author-button']) ?>
                    <?= Html::a('Удалить', ['delete', 'id' => $model->id], ['class' => 'author-button']) ?>
                <?php endif; ?>
                <?php if($statusClass == 'ban'): ?>
                    <?= Html::a('Удалить', ['delete', 'id' => $model->id], ['class' => 'author-button']) ?>
                <?php endif; ?>
            </div>

        </div>
        <? endif; ?>
        <div class="content-main-nav">
            <div class="title">
                <h1 class="title-name">
                    <?= $model->name; ?>
                </h1>
            </div>
            <div class="price">
                <h1>
                    <?= number_format($model->price, 0, '', ' ').' ₽'?>
                </h1>
            </div>
        </div>

        <div class="post-view">
            <div class="post-actions">
                <label class="post-contact post-actions-button" for="contactButton">
                    <span>Показать телефон</span>
                </label>

                <?= ($model->user_id != Yii::$app->user->id)?FavoriteButton::widget([
                    'model' => $model, // модель для добавления
                    'anchorActive' => '<span>В избранном</span>', // свой текст активной кнопки
                    'anchorUnactive' => '<span>Добавить в избранное</span>', // свой текст неактивной кнопки
                    //'htmlTag' => 'button', // тэг
                    'cssClass' => 'post-favourite post-actions-button', // свой класс
                    //'cssClassInList' => 'custom_class' // свой класс для добавленного объекта
                ]):'';
                ?>
                <div class="post-status">
                    <?= '<span>'.$model->timeExactString().'</span>' ?>
                    <svg x="0px" y="0px" viewBox="0 0 512 512" xml:space="preserve" width="14" height="14">
       <path d="M256,0C153.755,0,70.573,83.182,70.573,185.426c0,126.888,165.939,313.167,173.004,321.035
    c6.636,7.391,18.222,7.378,24.846,0c7.065-7.868,173.004-194.147,173.004-321.035C441.425,83.182,358.244,0,256,0z M256,278.719
    c-51.442,0-93.292-41.851-93.292-93.293S204.559,92.134,256,92.134s93.291,41.851,93.291,93.293S307.441,278.719,256,278.719z"/></svg>
                    <span><?= City::findOne($model->city_id)->name_ru ?></span>
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="18" height="12">
                        <defs><path id="a" d="M1.24 9.67a9.6 9.6 0 0 1 17.52 0c.1.21.1.45 0 .66a9.6 9.6 0 0 1-17.52 0 .8.8 0 0
                    1 0-.66zM10 14a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm0-1.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/></defs>
                        <use transform="translate(-1 -4)" xlink:href="#a"/></svg>
                    <?= $model->views ?>
                </div>


            </div>
            <div class="view-block">
                <div id="fotorama"
                     class="fotorama"
                     data-keyboard="true"
                     data-nav="thumbs"
                     data-width="100%"
                     data-maxwidth="900"
                     data-ratio="16/9"
                     data-allowfullscreen=true
                >
                    <?php
                    foreach ($model->Photos as $photoUrl)
                        echo '<img src="'.$photoUrl.'" alt="">';
                    ?>
                </div>
            </div>
            <?php
            $mainPropertyValues = $propertyValues;
            $boolValues = [];
            $checklistNames=[];
            foreach ($mainPropertyValues as $index=>$item)
            {
                $property = $item->property;
                if ($property->value_type == \app\models\Property::VALUE_TYPE_CHECKLIST)
                {
                    $checklistNames[$property->id]=$property->name;
                    unset($mainPropertyValues[$index]);
                }
                elseif($property->value_type == \app\models\Property::VALUE_TYPE_BOOL)
                {
                    $boolValues[$property->depend_id][]=$property->name;
                    unset($mainPropertyValues[$index]);
                }
            }
            ?>
            <?php if($mainPropertyValues): ?>
            <?php $even = 1; $length = count($mainPropertyValues) ?>
            <div class="view-block view-properties">
                <?php foreach ($mainPropertyValues as $propertyValue): ?>
                    <div class="view-property" style="order: <?= ($even<=intdiv($length,2))?($even*2):($even*2-$length+1); ?>">
                        <div class="view-property-name">
                            <span>
                                <?php
                                $property = $propertyValue->property;
                                switch($property->value_type){
                                    case \app\models\Property::VALUE_TYPE_INT:
                                    case \app\models\Property::VALUE_TYPE_FLOAT:
                                    case \app\models\Property::VALUE_TYPE_STRING:
                                        echo $property->name;
                                        break;
                                    default:
                                }
                                ?>
                            </span>
                        </div>
                        <div class="view-property-value">
                            <span>
                                <?php
                                switch($property->value_type){
                                    case \app\models\Property::VALUE_TYPE_INT:
                                    case \app\models\Property::VALUE_TYPE_FLOAT:
                                    case \app\models\Property::VALUE_TYPE_STRING:
                                        echo $propertyValue->getAttribute($property->valueColumnName);
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                <?php $even++; ?>
                <?php endforeach;?>

            </div>
            <?php endif; ?>
            <?php if($boolValues): ?>
                <?php $even = 1; $length = count($boolValues) ?>
                <div class="view-block view-properties">
                    <?php foreach ($boolValues as $index=>$propertyValue): ?>
                        <div class="view-property" style="order: <?= ($even<=intdiv($length,2))?($even*2):($even*2-$length+1); ?>">
                            <div class="view-property-name">
                            <span>
                                <?= $checklistNames[$index] ?>
                            </span>
                            </div>
                            <div class="view-property-value">
                                <?php foreach ($boolValues[$index] as $checkboxValue): ?>
                                <span><?=$checkboxValue?></span><br>
                                <?php endforeach;?>
                            </div>
                        </div>
                        <?php $even++; ?>
                    <?php endforeach;?>

                </div>
            <?php endif; ?>
            <div class="view-block">
                <?= nl2br($model->description ); ?>
            </div>


            <?php Modal::begin([
                    'header' => '<div class="modal-author">
                                <div class="modal-author-icon"><svg viewBox="-42 0 512 512.001" xmlns="http://www.w3.org/2000/svg">
                                    <path d="m210.351562 246.632812c33.882813 0 63.21875-12.152343 87.195313-36.128906 23.96875-23.972656 36.125-53.304687 36.125-87.191406
                                0-33.875-12.152344-63.210938-36.128906-87.191406-23.976563-23.96875-53.3125-36.121094-87.191407-36.121094-33.886718 0-63.21875 12.152344-87.191406
                                36.125s-36.128906 53.308594-36.128906 87.1875c0 33.886719 12.15625 63.222656 36.128906 87.195312 23.980469 23.96875 53.316406 36.125 87.191406
                                36.125zm-65.972656-189.292968c18.394532-18.394532 39.972656-27.335938 65.972656-27.335938 25.996094 0 47.578126 8.941406 65.976563 27.335938
                                18.394531 18.398437 27.339844 39.980468 27.339844 65.972656 0 26-8.945313 47.578125-27.339844 65.976562-18.398437 18.398438-39.980469
                                27.339844-65.976563 27.339844-25.992187 0-47.570312-8.945312-65.972656-27.339844-18.398437-18.394531-27.34375-39.976562-27.34375-65.976562
                                0-25.992188 8.945313-47.574219 27.34375-65.972656zm0 0"/>
                                    <path d="m426.128906 393.703125c-.691406-9.976563-2.089844-20.859375-4.148437-32.351563-2.078125-11.578124-4.753907-22.523437-7.957031-32.527343-3.3125-10.339844-7.808594-20.550781-13.375-30.335938-5.769532-10.15625-12.550782-19-20.160157-26.277343-7.957031-7.613282-17.699219-13.734376-28.964843-18.199219-11.226563-4.441407-23.667969-6.691407-36.976563-6.691407-5.226563
                                0-10.28125 2.144532-20.042969 8.5-6.007812 3.917969-13.035156 8.449219-20.878906 13.460938-6.707031 4.273438-15.792969 8.277344-27.015625
                                11.902344-10.949219 3.542968-22.066406 5.339844-33.042969 5.339844-10.96875 0-22.085937-1.796876-33.042968-5.339844-11.210938-3.621094-20.300782-7.625-26.996094-11.898438-7.769532-4.964844-14.800782-9.496094-20.898438-13.46875-9.753906-6.355468-14.808594-8.5-20.035156-8.5-13.3125
                                0-25.75 2.253906-36.972656 6.699219-11.257813 4.457031-21.003906 10.578125-28.96875 18.199219-7.609375 7.28125-14.390625 16.121094-20.15625
                                26.273437-5.558594 9.785157-10.058594 19.992188-13.371094 30.339844-3.199219 10.003906-5.875 20.945313-7.953125 32.523437-2.0625 11.476563-3.457031
                                22.363282-4.148437 32.363282-.679688 9.777344-1.023438 19.953125-1.023438 30.234375 0 26.726562 8.496094 48.363281 25.25 64.320312 16.546875 15.746094
                                38.4375 23.730469 65.066406 23.730469h246.53125c26.621094 0 48.511719-7.984375 65.0625-23.730469 16.757813-15.945312 25.253906-37.589843
                                25.253906-64.324219-.003906-10.316406-.351562-20.492187-1.035156-30.242187zm-44.90625 72.828125c-10.933594 10.40625-25.449218 15.464844-44.378906
                                15.464844h-246.527344c-18.933594 0-33.449218-5.058594-44.378906-15.460938-10.722656-10.207031-15.933594-24.140625-15.933594-42.585937
                                0-9.59375.316406-19.066407.949219-28.160157.617187-8.921874 1.878906-18.722656 3.75-29.136718 1.847656-10.285156 4.199219-19.9375
                                6.996094-28.675782 2.683593-8.378906 6.34375-16.675781 10.882812-24.667968 4.332031-7.617188 9.316407-14.152344 14.816407-19.417969
                                5.144531-4.925781 11.628906-8.957031 19.269531-11.980469 7.066406-2.796875 15.007812-4.328125 23.628906-4.558594 1.050781.558594 2.921875
                                1.625 5.953125 3.601563 6.167969 4.019531 13.277344 8.605469 21.136719 13.625 8.859375 5.648437 20.273437 10.75 33.910156 15.152344 13.941406
                                4.507812 28.160156 6.796875 42.273437 6.796875 14.113282 0 28.335938-2.289063 42.269532-6.792969 13.648437-4.410156 25.058594-9.507813
                                33.929687-15.164063 8.042969-5.140624 14.953125-9.59375 21.121094-13.617187 3.03125-1.972656 4.902344-3.042969 5.953125-3.601563 8.625.230469
                                16.566406 1.761719 23.636719 4.558594 7.636719 3.023438 14.121093 7.058594 19.265625 11.980469 5.5 5.261719 10.484375 11.796875 14.816406
                                19.421875 4.542969 7.988281 8.207031 16.289062 10.886719 24.660156 2.800781 8.75 5.15625 18.398438 7 28.675782 1.867187 10.433593 3.132812
                                20.238281 3.75 29.144531v.007812c.636719 9.058594.957031 18.527344.960937 28.148438-.003906 18.449219-5.214844 32.378906-15.9375 42.582031zm0 0"/></svg></div>
                                <div class="modal-author-text">'.\app\models\User::findOne($model->user_id)->name.'</div></div>',
                    'toggleButton' => [
                    'label' => '',
                    'tag' => 'button',
                    'class' => '',
                    'id' => 'contactButton',
                    'style'=>'display:none;'
                ],
            ]); ?>
            <div class="modal-phone">
                <?php
                $phoneNumber = \app\models\User::findOne($model->user_id)->phone;
                echo '+7 '.substr($phoneNumber, 0, 3).' '.substr($phoneNumber, 3, 3).'-'.substr($phoneNumber, 6, 2).
                    '-'.substr($phoneNumber, 8, 2);
                ?>
            </div>
            <div class="modal-advice">Скажите продавцу, что нашли это объявление на <span>soldr</span></div>
            <?php Modal::end(); ?>
        </div>
    </div>
</div>


<script>
    $(document).ready(function() {
       $('.view-block').last().addClass('last');
    });
</script>
