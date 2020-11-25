<?php


namespace app\assets;


use yii\web\AssetBundle;

class UserPostAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/userPost.css',
        'css/jQuery.Brazzers-Carousel.css',
    ];
    public $js = [
        'js/jQuery.Brazzers-Carousel.js',
    ];
    public $depends = [

    ];
}
