<?php

namespace app\modules\admin\controllers;

use Yii;
use app\models\User;
use app\modules\admin\models\UserSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        if(!\Yii::$app->user->isGuest and \app\models\User::findOne(Yii::$app->user->id)->status == \app\models\User::STATUS_ADMIN)
        {
            $searchModel = new UserSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
        else
            throw new NotFoundHttpException('The requested page does not exist.');


    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        if(!\Yii::$app->user->isGuest and \app\models\User::findOne(Yii::$app->user->id)->status == \app\models\User::STATUS_ADMIN)
        {
            return $this->render('view', [
                'model' => $this->findModel($id),
            ]);
        }
        else
            throw new NotFoundHttpException('The requested page does not exist.');

    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        if(!\Yii::$app->user->isGuest and \app\models\User::findOne(Yii::$app->user->id)->status == \app\models\User::STATUS_ADMIN)
        {
            $model = new User();

            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }

            return $this->render('create', [
                'model' => $model,
            ]);
        }
        else
            throw new NotFoundHttpException('The requested page does not exist.');


    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        if(!\Yii::$app->user->isGuest and \app\models\User::findOne(Yii::$app->user->id)->status == \app\models\User::STATUS_ADMIN)
        {

            $model = $this->findModel($id);

            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }

            return $this->render('update', [
                'model' => $model,
            ]);
        }
        else
            throw new NotFoundHttpException('The requested page does not exist.');

    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        if(!\Yii::$app->user->isGuest and \app\models\User::findOne(Yii::$app->user->id)->status == \app\models\User::STATUS_ADMIN)
        {
            $this->findModel($id)->delete();

            return $this->redirect(['index']);
        }
        else
            throw new NotFoundHttpException('The requested page does not exist.');


    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if(!\Yii::$app->user->isGuest and \app\models\User::findOne(Yii::$app->user->id)->status == \app\models\User::STATUS_ADMIN)
        {
            if (($model = User::findOne($id)) !== null) {
                return $model;
            }

            throw new NotFoundHttpException('The requested page does not exist.');
        }
        else
            throw new NotFoundHttpException('The requested page does not exist.');


    }
}
