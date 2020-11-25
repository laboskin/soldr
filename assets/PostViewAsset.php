<?php

namespace app\assets;

use yii\web\AssetBundle;

class PostViewAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/postView.css',
        'css/fotorama.css',
    ];
    public $js = [
        'js/fotorama.js'
    ];
    public $depends = [

    ];
}
