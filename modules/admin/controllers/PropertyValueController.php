<?php

namespace app\modules\admin\controllers;

use Yii;
use app\models\PropertyValue;
use app\modules\admin\models\PropertyValueSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PropertyValueController implements the CRUD actions for PropertyValue model.
 */
class PropertyValueController extends Controller
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
     * Lists all PropertyValue models.
     * @return mixed
     */
    public function actionIndex()
    {

        if(!\Yii::$app->user->isGuest and \app\models\User::findOne(Yii::$app->user->id)->status == \app\models\User::STATUS_ADMIN)
        {
            $searchModel = new PropertyValueSearch();
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
     * Displays a single PropertyValue model.
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
     * Creates a new PropertyValue model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        if(!\Yii::$app->user->isGuest and \app\models\User::findOne(Yii::$app->user->id)->status == \app\models\User::STATUS_ADMIN)
        {
            $model = new PropertyValue();

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
     * Updates an existing PropertyValue model.
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
     * Deletes an existing PropertyValue model.
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
     * Finds the PropertyValue model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PropertyValue the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if(!\Yii::$app->user->isGuest and \app\models\User::findOne(Yii::$app->user->id)->status == \app\models\User::STATUS_ADMIN)
        {
            if (($model = PropertyValue::findOne($id)) !== null) {
                return $model;
            }

            throw new NotFoundHttpException('The requested page does not exist.');
        }
        else
            throw new NotFoundHttpException('The requested page does not exist.');


    }
}
