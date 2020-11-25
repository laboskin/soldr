<?php


namespace app\assets;


use yii\web\AssetBundle;

class UserFavouritesAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/userFavourites.css',
        'css/jQuery.Brazzers-Carousel.css',
    ];
    public $js = [
        'js/jQuery.Brazzers-Carousel.js',
    ];
    public $depends = [

    ];
}
