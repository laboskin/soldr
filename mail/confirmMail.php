<?php
/**
 * @var $user \app\models\User
 */
use yii\helpers\Html;
echo 'Здравствуйте, '.Html::encode($user->name).'. ';
echo '<br>';
echo Html::a('Для подтверждения E-Mail перейдите по этой ссылке.',
    Yii::$app->urlManager->createAbsoluteUrl(
        [
            '/user/confirm-mail',
            'key' => $user->restore_key
        ]
    ));