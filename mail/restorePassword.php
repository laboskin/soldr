<?php
/**
 * @var $user \app\models\User
 */
use yii\helpers\Html;
echo 'Здравствуйте, '.Html::encode($user->name).'. ';
echo '<br>';
echo Html::a('Для смены пароля перейдите по этой ссылке.',
    Yii::$app->urlManager->createAbsoluteUrl(
        [
            '/user/reset-password',
            'key' => $user->restore_key
        ]
    ));