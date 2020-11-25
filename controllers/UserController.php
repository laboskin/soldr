<?php

namespace app\controllers;

use app\models\Post;
use app\models\UserContactsChangeForm;
use app\models\UserPasswordChangeForm;
use app\models\UserResetPasswordForm;
use app\models\UserRestoreForm;
use Yii;
use app\models\User;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii2mod\query\ArrayQuery;

class UserController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => [
                    'logout',
                    'restore',
                    'reset-password',
                    'settings',
                    'post-active',
                    'post-old'],
                'rules' => [
                    [
                        'actions' => ['restore', 'reset-password'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'settings', 'post-active', 'post-old'],
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
                'denyCallback' => function($rule, $action)
                {
                    if (in_array($action->id, $this->behaviors()['access']['rules'][0]['actions']))
                        $this->goHome();
                    else
                        Yii::$app->user->loginRequired();
                }
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }
    public function actionLogout(){
        Yii::$app->user->logout();
        return $this->goHome();
    }

    public function actionSettings(){
        $userPasswordChange = new UserPasswordChangeForm();
        $userContactsChange = new UserContactsChangeForm();

        if($userContactsChange->load(Yii::$app->request->post())){
            if ($userContactsChange->validate())
                if ($userContactsChange->changeContacts())
                    return $this->refresh();
        }

        if($userPasswordChange->load(Yii::$app->request->post())){
            if ($userPasswordChange->validate())
                if ($userPasswordChange->changePassword())
                    return $this->refresh();
        }
        return $this->render('settings', ['userPasswordChange'=>$userPasswordChange, 'userContactsChange'=>$userContactsChange ]);
    }


    public function actionRestore(){
        $model = new UserRestoreForm();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if($model->sendMail()):
                    Yii::$app->getSession()->setFlash('warning', 'Проверьте емайл.');
                    return $this->goHome();
                else:
                    Yii::$app->getSession()->setFlash('error', 'Нельзя сбросить пароль.');
                endif;
            }
        }

        return $this->render('restore', ['model'=>$model]);
    }

    public function actionConfirmMail($key)
    {
        if(empty($key) || !is_string($key))
            throw new InvalidArgumentException('Ключ не может быть пустым.');
        $user = User::findByRestoreKey($key);
        if(!$user)
            throw new InvalidArgumentException('Не верный ключ.');
        $user->status=User::STATUS_ACTIVE;
        $user->removeRestoreKey();
        $user->save();
        Yii::$app->user->login($user, 3600*24*30);
        return $this->goHome();

    }

    public function actionResetPassword($key)
    {
        try {
            $model = new UserResetPasswordForm($key);
        }
        catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate() && $model->resetPassword()) {
                Yii::$app->getSession()->setFlash('warning', 'Пароль изменен.');
                return $this->redirect(['/app/']);
            }
        }
        return $this->render('reset-password', [
            'model' => $model,
        ]);
    }

    public function actionFavourites()
    {
        $query = new ArrayQuery();
        $query->from(Yii::$app->favorite->getUserWishList());
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize'=>6]);
        $models = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->all();
        return $this->render('favourites', [
            'models' => $models,
            'pages' => $pages,
            'totalCount'=> $countQuery->count()
        ]);
    }

    public function actionPostActive()
    {
        $query = Post::find()
            ->where([
                'user_id'=>Yii::$app->user->id,
                ])
            ->andWhere(['and', ['>', 'date', Post::expiredDate()],['in', 'status', [Post::STATUS_POSTED, Post::STATUS_ACTIVE]]])
            ;
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize'=>20]);
        $models = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->all();
        return $this->render('post-active', [
            'models' => $models,
            'pages' => $pages,
            'totalCount'=> $countQuery->count()
        ]);
    }
    public function actionPostOld(){
        $query = Post::find()
            ->where([
                'user_id'=>Yii::$app->user->id
            ])
            ->andWhere(['or', ['and', ['status'=>Post::STATUS_ACTIVE], ['<', 'date', Post::expiredDate()]], ['in', 'status', [Post::STATUS_CLOSED, Post::STATUS_DECLINED, Post::STATUS_BAN]]])
        ;
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize'=>20]);
        $models = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->all();
        return $this->render('post-old', [
            'models' => $models,
            'pages' => $pages,
            'totalCount'=> $countQuery->count()
        ]);
    }

    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
