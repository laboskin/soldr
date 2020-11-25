<?php


namespace app\assets;


use yii\web\AssetBundle;

class AppErrorAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/appError.css',
    ];
    public $js = [
    ];
    public $depends = [

    ];
}
