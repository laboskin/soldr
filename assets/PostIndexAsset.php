<?php

namespace app\assets;

use yii\web\AssetBundle;

class PostIndexAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/postIndex.css',
        'css/jQuery.Brazzers-Carousel.css',
    ];
    public $js = [
        'js/jQuery.Brazzers-Carousel.js',
    ];
    public $depends = [

    ];
}
