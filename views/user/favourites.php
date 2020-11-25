<?php


/* @var $this yii\web\View */
/* @var $models \app\models\Post[] */
/* @var $cat_id int */
/* @var $pages \yii\data\Pagination */
/* @var $totalCount int */
$this->title = 'Избранное - soldr';

use yii\widgets\LinkPager;

\app\assets\UserFavouritesAsset::register($this);

?>
<div class="breadcrumbs">
    <div class="breadcrumbs-container">
        <ul class="breadcrumbs-list">
            <li class="breadcrumbs-list-item">
                <a href="/app/"><span>Главная</span></a>
            </li>
            <li class="breadcrumbs-list-item">
                <a href="/user/favourites"><span>Избранное</span></a>
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
            <li class="content-side-nav-item content-side-nav-item-selected">
                <a href="/user/favourites" class="content-side-nav-item-link">
                    <span>Избранное</span>
                </a>
            </li>
            <li class="content-side-nav-item">
                <a href="/user/post-active" class="content-side-nav-item-link">
                    <span>Мои объявления</span>
                </a>
            </li>
            <li class="content-side-nav-item">
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
                    Избранное
                </h1>
            </div>
        </div>
        <?php if (count($models)>0): ?>
        <div class="items-grid">
            <? foreach ($models as $model): ?>
                <a href="<?='/post/view?id='.$model['id']?>" class="item"><div>
                        <div class="item-image-price">
                            <div class="item-image-carousel item-image-carousel-off">
                                <?php
                                foreach ($model->smallPhotos as $photoUrl)
                                    echo '<img src="'.$photoUrl.'" alt="">';
                                ?>
                                <div class="item-price"><?= $model['price']?></div>
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
        <?php endif; ?>
        <?php if (count($models)==0): ?>
            <div class="post-empty">
                <p>Пока здесь пусто.</p>
                <p>Чтобы добавить объявление в избранное, нажмите кнопку с сердечком :)</p>
            </div>
        <?php endif; ?>
    </div>
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
    });
</script>