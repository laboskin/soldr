<?php

namespace app\controllers;

use app\models\Category;
use app\models\Image;
use app\models\Property;
use app\models\PropertyValue;
use Yii;
use app\models\Post;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\UploadedFile;

class PostController extends Controller
{


    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['create', 'update'],
                'rules' => [
                    [
                        'actions' => ['create', 'update'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $cat_id = Yii::$app->request->get('cat_id');
        $query = Post::find()
            ->where([
                'city_id'=>Yii::$app->request->cookies->getValue('city'),
                'status'=>Post::STATUS_ACTIVE,
            ])
            ->andWhere (['>', 'date', Post::expiredDate()]);
        if ($cat_id)
        {
            if (!Yii::$app->request->cookies->has('cat_rec'))
            {
                $cat_rec_array = [];
                $cat_rec_array[]=$cat_id;
                \Yii::$app->response->cookies->add(new \yii\web\Cookie([
                    'name'   => 'cat_rec',
                    'value'  => json_encode($cat_rec_array),
                    'expire' => time() + 30*24*60*60,
                ]));
            }
            else
            {
                $cat_rec_array = json_decode(\Yii::$app->request->cookies->getValue('cat_rec'), true);
                foreach ($cat_rec_array as $index => $item)
                    if ($item==$cat_id)
                        unset($cat_rec_array[$index]);
                $cat_rec_array[]=$cat_id;
                $cat_rec_array = array_values($cat_rec_array);
                while (count($cat_rec_array)>5){
                    unset($cat_rec_array[0]);
                    $cat_rec_array = array_values($cat_rec_array);
                }
                \Yii::$app->response->cookies->add(new \yii\web\Cookie([
                    'name'   => 'cat_rec',
                    'value'  => json_encode($cat_rec_array),
                    'expire' => time() + 30*24*60*60,
                ]));
            }
            $category = Category::findOne(['id'=>$cat_id]);
            if($category->parent_id)
                $query->andWhere(['category_id'=>$cat_id]);
            else
            {
                $category_ids = [];
                foreach ($category->getChildren() as $child)
                    $category_ids[]=$child->id;
                    $query->andWhere(['in', 'category_id', $category_ids]);
            }
        }

        if (Yii::$app->request->get('pmin'))
            $query->andWhere(['>=', 'price', Yii::$app->request->get('pmin')]);
        if (Yii::$app->request->get('pmax'))
            $query->andWhere(['<=', 'price', Yii::$app->request->get('pmax')]);
        if (Yii::$app->request->get('q'))
            $query->andWhere(['like', 'name', Yii::$app->request->get('q')]);
        if ($cat_id)
            foreach (Category::findOne($cat_id)->getPropertiesForFilter() as $property)
            {
                switch ($property->filter_type)
                {
                    case Property::FILTER_TYPE_SELECT:
                        if (Yii::$app->request->get("f$property->id"))
                        {
                            $propertyValuesQuery = PropertyValue::find()->where(['property_id' => $property->id])
                                ->andWhere([$property->valueColumnName => Yii::$app->request->get("f$property->id")]);
                            $idsArray = ArrayHelper::getColumn($propertyValuesQuery->all(), 'post_id');
                            $query->andWhere(['in', 'id', $idsArray]);
                        }
                        break;
                    case Property::FILTER_TYPE_SELECT_MULTIPLE:
                        if (Yii::$app->request->get("f$property->id"))
                        {
                            $propertyValuesQuery = PropertyValue::find()->where(['property_id' => $property->id])
                                ->andWhere(['in', $property->valueColumnName, Yii::$app->request->get("f$property->id")]);
                            $idsArray = ArrayHelper::getColumn($propertyValuesQuery->all(), 'post_id');
                            $query->andWhere(['in', 'id', $idsArray]);
                        }
                        break;
                    case Property::FILTER_TYPE_CHECKBOX:
                        if (Yii::$app->request->get("f$property->id"))
                        {
                            $propertyValuesQuery = PropertyValue::find()->where(['property_id' => $property->id])
                                ->andWhere([$property->valueColumnName => 1]);
                            $idsArray = ArrayHelper::getColumn($propertyValuesQuery, 'post_id');
                            $query->andWhere(['in', 'id', $idsArray]);
                            break;
                        }

                    case Property::FILTER_TYPE_RANGE:
                        $filterUsed = false;
                        $propertyValuesQuery = PropertyValue::find()->where(['property_id' => $property->id]);
                        if (Yii::$app->request->get("f$property->id"."min"))
                        {
                            $propertyValuesQuery->andWhere(['>=', $property->valueColumnName, Yii::$app->request->get("f$property->id"."min")]);
                            $filterUsed = true;
                        }
                        if (Yii::$app->request->get("f$property->id"."max"))
                        {
                            $propertyValuesQuery->andWhere(['<=', $property->valueColumnName, Yii::$app->request->get("f$property->id"."max")]);
                            $filterUsed = true;
                        }
                        if ($filterUsed)
                        {
                            $idsArray = ArrayHelper::getColumn($propertyValuesQuery->all(), 'post_id');
                            $query->andWhere(['in', 'id', $idsArray]);
                        }
                        break;
                }
            }
        switch(Yii::$app->request->get('sort'))
        {
            case 1:
                $query->orderBy(['price'=>SORT_ASC]);
                break;
            case 2:
                $query->orderBy(['price'=>SORT_DESC]);
                break;
            case 0:
            default:
                $query->orderBy(['date'=>SORT_DESC]);
                break;
        }
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize'=>12]);
        $models = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->all();
        return $this->render('index', [
            'models' => $models,
            'pages' => $pages,
            'cat_id' => $cat_id,
            'totalCount'=> $countQuery->count(),
        ]);
    }
    public function actionView($id)
    {
        $model = $this->findModel($id);
        if (($model->date > Post::expiredDate() and $model->status == Post::STATUS_ACTIVE) or $model->user_id == Yii::$app->user->id)
        {
            $model->increaseViews();
            $propertyValues = $model->getPropertyValues();
            return $this->render('view', [
                'model' => $model,
                'propertyValues' => $propertyValues
            ]);
        }
        return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
    }
    public function actionCreate()
    {
        if (!Yii::$app->request->isPjax and !Yii::$app->request->isPost and !Yii::$app->request->isAjax)
        {
            foreach (Post::find()
                         ->where(['status' => Post::STATUS_UNFINISHED, 'user_id' => Yii::$app->user->id])
                         ->andWhere(['<', 'original_date', time()-60*60*24])
                         ->all() as $deleteModel)
                $deleteModel->delete();
            $modelPost = new Post();
            $modelPost->original_date = time();
            $modelPost->user_id = Yii::$app->user->id;
            $modelPost->city_id = Yii::$app->request->cookies->getValue('city');
            $modelPost->status = Post::STATUS_UNFINISHED;
            $modelPost->save(false);
        }
        else
            $modelPost = $this->findModel(Yii::$app->request->post('post_id'));
        $modelPropertyValues = [];

        if (!Yii::$app->request->isPjax and Yii::$app->request->isPost)
        {
            $modelPost->original_date = time();
            $modelPost->date = $modelPost->original_date;
            $modelPost->status = Post::STATUS_POSTED;

            if ($modelPost->load(Yii::$app->request->post()) and $modelPost->validate())
            {
                $validated = true;
                $allProperties = Category::findOne(['id'=>$modelPost->category_id])->getPropertiesForCreate();
                $propertyValues = [];
                foreach ($allProperties as $property)
                {
                    if ($validated)
                    {
                        if (($property->value_type == Property::VALUE_TYPE_BOOL and Yii::$app->request->post('f'.$property->id)==null)
                            or ($property->parent_id !=0 and Yii::$app->request->post('f'.$property->parent_id)!=$property->parent_string))
                            continue;
                        elseif (Yii::$app->request->post('f'.$property->id))
                        {
                            $propertyValue = new PropertyValue();
                            $propertyValue->setAttribute($property->getValueColumnName(), Yii::$app->request->post('f'.$property->id));
                            $propertyValue->property_id = $property->id;
                            $propertyValue->post_id = $modelPost->id;
                            if ($propertyValue->validate())
                                $propertyValues[] = $propertyValue;
                            else
                                $validated = false;
                        }
                        else
                            $validated = false;
                    }
                }
                $success = false;
                if ($validated)
                {
                    $success = true;
                    foreach ($propertyValues as $propertyValue)
                        $success *= $propertyValue->save(false);
                    $success *= $modelPost->save(false);
                }
                if ($success)
                    return $this->redirect(['view', 'id' => $modelPost->id]);
            }
        }
        return $this->render('create', ['modelPost'=>$modelPost, 'modelPropertyValues'=>$modelPropertyValues]);
    }

    public function actionUpdate($id)
    {
        $modelPost = $this->findModel($id);
        if(Yii::$app->user->id == $modelPost->user_id)
        {
            $modelPropertyValues = $modelPost->propertyValues;
            $modelValues = null;
            if (Yii::$app->request->isGet)
            {
                foreach ($modelPropertyValues as $propertyValue)
                {
                    $property = $propertyValue->property;
                    $modelValues['f'.$propertyValue->property_id] = $propertyValue->getAttribute($property->valueColumnName);
                    if ($property->input_type == Property::INPUT_TYPE_SELECT and $property->depend_id)
                        $modelValues['f'.$propertyValue->property_id.'param'];
                }

            }
            if (!Yii::$app->request->isPjax and Yii::$app->request->isPost) {
                $modelPost->status = Post::STATUS_POSTED;

                if ($modelPost->load(Yii::$app->request->post()) and $modelPost->validate())
                {
                    $validated = true;
                    $allProperties = Category::findOne(['id'=>$modelPost->category_id])->getPropertiesForCreate();
                    $propertyValues = [];
                    foreach ($allProperties as $property)
                    {
                        if ($validated)
                        {
                            if (($property->value_type == Property::VALUE_TYPE_BOOL and Yii::$app->request->post('f'.$property->id)==null)
                                or ($property->parent_id !=0 and Yii::$app->request->post('f'.$property->parent_id)!=$property->parent_string))
                                continue;
                            elseif (Yii::$app->request->post('f'.$property->id))
                            {
                                $propertyValue = new PropertyValue();
                                $propertyValue->setAttribute($property->getValueColumnName(), Yii::$app->request->post('f'.$property->id));
                                $propertyValue->property_id = $property->id;
                                $propertyValue->post_id = $modelPost->id;
                                if ($propertyValue->validate())
                                    $propertyValues[] = $propertyValue;
                                else
                                    $validated = false;
                            }
                            else
                                $validated = false;
                        }
                    }
                    $success = false;
                    if ($validated)
                    {
                        foreach ($modelPropertyValues as $propertyValue)
                            $propertyValue->delete();
                    }
                        $success = true;
                        foreach ($propertyValues as $propertyValue)
                            $success *= $propertyValue->save(false);
                        $success *= $modelPost->save(false);
                    }
                    if ($success)
                        return $this->redirect(['view', 'id' => $modelPost->id]);
            }
            return $this->render('update', [
                'modelPost' => $modelPost,
                'modelPropertyValues' => $modelPropertyValues,
                'modelValues' => $modelValues
            ]);
        }
        else return $this->goHome();
    }
    public function actionClose($id)
    {
        $model = $this->findModel($id);
        if(Yii::$app->user->id == $model->user_id)
            $model->close();
        return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
    }
    public function actionActivate($id)
    {
        $model = $this->findModel($id);
        if(Yii::$app->user->id == $model->user_id)
        {
            $model->status=Post::STATUS_POSTED;
            $model->save();
        }
        return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
    }
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if(Yii::$app->user->id == $model->user_id)
            $model->delete();
        else
            return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);

        return $this->redirect(['index']);
    }
    public function actionDateUpdate($id){
        $model = $this->findModel($id);
        if(Yii::$app->user->id == $model->user_id)
        {
            $model->date = time();
            $model->save();
        }
        return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
    }
    public function actionDependInput()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        $selected = "";
        if (Yii::$app->request->post('depdrop_parents') and Yii::$app->request->post('depdrop_params')) {
            $depend_value = Yii::$app->request->post('depdrop_parents')[0];
            $property_id = Yii::$app->request->post('depdrop_params')[0];
            if ($property_id != null and $depend_value != null)
            {
                $out = Property::getDependOptionsSelectArray($depend_value, $property_id);
                if (Yii::$app->request->post('depdrop_params')[1])
                    $selected = Yii::$app->request->post('depdrop_params')[1];
                return ['output'=>$out, 'selected'=>$selected];
            }
        }
        return ['output'=>'', 'selected'=>''];
    }
    public function actionSaveImage()
    {
        $this->enableCsrfValidation = false;
        if(Yii::$app->request->isPost)
        {
            $post = Yii::$app->request->post();
            $dir = Yii::getAlias('@images').'/';
            if (!file_exists($dir))
                FileHelper::createDirectory($dir);
            $result_link = Url::home(true).'web/uploads/images/';
            $file = UploadedFile::getInstanceByName('Image[attachment][0]');
            $model = new Image();
            $model->name = strtotime('now').'_'.Yii::$app->getSecurity()->generateRandomString(6).'.'.$file->extension;
            $model->load($post);
            $model->validate();
            if ($model->hasErrors())
                $result = ['error' => $model->getFirstError('file')];
            else
            {
                if ($file->saveAs($dir.$model->name))
                {
                    $img = Yii::$app->image->load($dir.$model->name);
                    $img->background('#fff', 0)->resize(800, NULL, Yii\image\drivers\Image::PRECISE)
                        ->save($dir.'small/'.$model->name);
                    $result = ['filelink' => $result_link.$model->name, 'filename'=>$model->name];
                }
                else
                    $result = ['error' => 'error'];
                $model->save();
            }
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $result;
        }
        else
            throw new BadRequestHttpException('Only POST is allowed');
    }
    public function actionDeleteImage()
    {
        if ($model = Image::findOne(Yii::$app->request->post('key')) and $model->delete())
            return true;
        else
            throw new NotFoundHttpException('The requested page does not exist.');

    }
    public function actionSortImage($id)
    {
        if(Yii::$app->request->isAjax){
            $post = Yii::$app->request->post('sort');
            if($post['oldIndex'] > $post['newIndex']){
                $param = ['and',['>=','sort',$post['newIndex']],['<','sort',$post['oldIndex']]];
                $counter = 1;
            }else{
                $param = ['and',['<=','sort',$post['newIndex']],['>','sort',$post['oldIndex']]];
                $counter = -1;
            }
            Image::updateAllCounters(['sort' => $counter], [
                'and',['post_id'=>$id],$param
            ]);
            Image::updateAll(['sort' => $post['newIndex']], [
                'id' => $post['stack'][$post['newIndex']]['key']
            ]);
            return true;
        }
        throw new MethodNotAllowedHttpException();
    }

    protected function findModel($id)
    {
        if (($model = Post::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}
