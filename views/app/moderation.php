<?php

use yii\widgets\LinkPager;

\app\assets\AppModerationAsset::register($this);

/* @var $this yii\web\View */
/* @var $models \app\models\Post[] */
/* @var $cat_id int */
/* @var $pages \yii\data\Pagination */
/* @var $totalCount int */
$this->title = 'Модерация - soldr';
?>
<div class="breadcrumbs">
    <div class="breadcrumbs-container">
        <ul class="breadcrumbs-list">
            <li class="breadcrumbs-list-item">
                <a href="/app/"><span>Главная</span></a>
            </li>
            <li class="breadcrumbs-list-item">
                <a href="/post/moderation"><span>Модерация</span></a>
            </li>
        </ul>
    </div>
</div>
<div class="content">
    <div class="content-main">
        <div class="content-main-nav">
            <div class="title">
                <h1 class="title-name">
                    Модерация
                    <span class="title-count">
                            <?= $totalCount ?>
                        </span>
                </h1>
            </div>
        </div>
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
            $('html').css('overflow-y', 'hidden');
            <?php if($userIOS): ?>
            hideScroll();
            <?php endif; ?>
        });
        $('.lose-focus-filters').click(function () {
            $('.content-side').removeClass('content-side-visible');
            $('.lose-focus-filters').css('display', 'none');
            $('html').css('overflow-y', 'scroll');
            <?php if($userIOS): ?>
            showScroll();
            <?php endif; ?>
        });
    });
</script>