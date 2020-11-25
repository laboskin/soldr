<?php


namespace app\controllers;

use app\models\City;
use app\models\CitySelectForm;
use app\models\Post;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use Yii;
use app\models\SignupForm;
use app\models\LoginForm;
use yii\web\Response;
use app\models\User;
use yii\widgets\ActiveForm;

class AppController extends Controller
{

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

  public function actionIndex(){
    $loginModel = new LoginForm();
    $signupModel = new SignupForm();

    $loginStatus = User::LOGIN_STATUS_OK;
    if (Yii::$app->request->isAjax && $signupModel->load(Yii::$app->request->post())) {
      Yii::$app->response->format = Response::FORMAT_JSON;
      return ActiveForm::validate($signupModel);
    }
    if (!Yii::$app->request->isAjax and $signupModel->load(Yii::$app->request->post())) {
      if ($signupModel->validate() && $signupModel->signup()) {
        return $this->goHome();
      }
    }
    if (!Yii::$app->request->isAjax and $loginModel->load(Yii::$app->request->post())) {
      if ($loginModel->validate()) {
        $user = $loginModel->getUser();
        if ($user->status == User::STATUS_UNACTIVE)
          $loginStatus = User::LOGIN_STATUS_NEED_CONFIRM;
        else
        {
          Yii::$app->user->login($user, $loginModel->rememberMe ? 3600*24*30 : 0);
          return $this->goHome();
        }
      }
    }
    if(!Yii::$app->request->cookies->has('city')) {
      $city_id = 524901;
    }
    else
      $city_id = Yii::$app->request->cookies->getValue('city');
    if (Yii::$app->request->cookies->has('cat_rec')){
      $models = Post::find()
        ->where([
          'city_id'=> $city_id,
          'status'=>Post::STATUS_ACTIVE,
        ])
        ->andWhere (['>', 'date', Post::expiredDate()])
        ->andWhere(['in', 'category_id', json_decode(\Yii::$app->request->cookies->getValue('cat_rec'), true)])
        ->limit(21)
        ->all();
      shuffle($models);
    }
    else
      $models = [];
    if (count($models)<48)
    {
      $ids = ArrayHelper::getColumn($models, 'id');
      $models_all = Post::find()
        ->where([
          'city_id'=> $city_id,
          'status'=>Post::STATUS_ACTIVE,
        ])
        ->andWhere (['>', 'date', Post::expiredDate()])
        ->andWhere(['not in', 'id', $ids])
        ->limit(48-count($models))
        ->all();
      shuffle($models_all);
      foreach ($models_all as $model)
        $models[]=$model;
    }
    return $this->render('index', [
      'loginModel' => $loginModel,
      'signupModel' => $signupModel,
      'models' => $models,
      'loginStatus' => $loginStatus
    ]);

  }

    public function actionChangeCity(){
        $citySelectForm = new CitySelectForm();
        if($citySelectForm->load(Yii::$app->request->post()))
        {
            $cityId = City::widgetIndexToCityId($citySelectForm['widgetIndex']);
            if (City::findOne($cityId) != null)
                Yii::$app->response->cookies->add(new yii\web\Cookie(['name' => 'city', 'value' => $cityId]));
        }
        return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
    }

    public function actionModeration(){
        if(in_array(\app\models\User::findOne(Yii::$app->user->getId())->status, [\app\models\User::STATUS_MODERATOR, \app\models\User::STATUS_ADMIN]))
        {
            $query = Post::find()
                ->where([
                    'status'=>Post::STATUS_POSTED,
                ])
                ->andWhere (['>', 'date', Post::expiredDate()])
                ->orderBy(['date'=>SORT_ASC]);
            $countQuery = clone $query;
            $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize'=>12]);
            $models = $query->offset($pages->offset)
                ->limit($pages->limit)
                ->all();
            return $this->render('moderation', [
                'models' => $models,
                'pages' => $pages,
                'totalCount'=> $countQuery->count(),
            ]);

        }
        else
            $this->goHome();
    }
    public function actionAdminApprove(){
        if(in_array(\app\models\User::findOne(Yii::$app->user->getId())->status, [\app\models\User::STATUS_MODERATOR, \app\models\User::STATUS_ADMIN]))
        {
            if(Yii::$app->request->get('id'))
            {
                $post = Post::findOne(Yii::$app->request->get('id'));
                $post->status = Post::STATUS_ACTIVE;
                $post->save();
            }
        }
        return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
    }
    public function actionAdminCheckAgain(){
        if(in_array(\app\models\User::findOne(Yii::$app->user->getId())->status, [\app\models\User::STATUS_MODERATOR, \app\models\User::STATUS_ADMIN]))
        {
            if(Yii::$app->request->get('id'))
            {
                $post = Post::findOne(Yii::$app->request->get('id'));
                $post->status = Post::STATUS_POSTED;
                $post->save();
            }
        }
        return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
    }
    public function actionAdminDecline(){
        if(in_array(\app\models\User::findOne(Yii::$app->user->getId())->status, [\app\models\User::STATUS_MODERATOR, \app\models\User::STATUS_ADMIN]))
        {
            if(Yii::$app->request->get('id'))
            {
                $post = Post::findOne(Yii::$app->request->get('id'));
                $post->status = Post::STATUS_DECLINED;
                $post->save();
            }
        }
        return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
    }
    public function actionAdminDateUpdate(){
        if(in_array(\app\models\User::findOne(Yii::$app->user->getId())->status, [\app\models\User::STATUS_MODERATOR, \app\models\User::STATUS_ADMIN]))
        {
            if(Yii::$app->request->get('id'))
            {
                $post = Post::findOne(Yii::$app->request->get('id'));
                $post->date = time();
                $post->save();
            }
        }
        return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
    }
    public function actionAdminBan(){
        if(in_array(\app\models\User::findOne(Yii::$app->user->getId())->status, [\app\models\User::STATUS_MODERATOR, \app\models\User::STATUS_ADMIN]))
        {
            if(Yii::$app->request->get('id'))
            {
                $post = Post::findOne(Yii::$app->request->get('id'));
                $post->status = Post::STATUS_BAN;
                $post->save();
            }
        }
        return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
    }
    public function actionAdminClose(){
        if(in_array(\app\models\User::findOne(Yii::$app->user->getId())->status, [\app\models\User::STATUS_MODERATOR, \app\models\User::STATUS_ADMIN]))
        {
            if(Yii::$app->request->get('id'))
            {
                $post = Post::findOne(Yii::$app->request->get('id'));
                $post->status = Post::STATUS_CLOSED;
                $post->save();
            }
        }
        return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
    }


}