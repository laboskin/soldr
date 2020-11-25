<?php

namespace app\assets;

use yii\web\AssetBundle;

class AppModerationAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/appModeration.css',
        'css/jQuery.Brazzers-Carousel.css',
    ];
    public $js = [
        'js/jQuery.Brazzers-Carousel.js',
    ];
    public $depends = [

    ];
}
