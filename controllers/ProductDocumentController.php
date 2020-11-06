<?php

namespace app\controllers;

use app\models\ProductDocumentItems;
use app\modules\wms\models\WmsDocument;
use Yii;
use app\models\ProductDocument;
use app\models\ProductDocumentSearch;
use yii\helpers\VarDumper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ProductDocumentController implements the CRUD actions for ProductDocument model.
 */
class ProductDocumentController extends Controller
{
    public $slug;

    /**
     * @param $action
     * @return bool
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $slug = Yii::$app->request->get('slug');
            $flag = false;
            if (!empty($slug)) {
                if ( ProductDocument::hasDocTypeLabel($slug) ) {
                    $flag = true;
                    $this->slug = $slug;
                }
            }
            if (!$flag) {
                throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
            }
            return true;
        } else {
            return false;
        }
    }
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
     * Lists all ProductDocument models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductDocumentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render($this->slug.'/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ProductDocument model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render($this->slug.'/view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new ProductDocument model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ProductDocument();
        $modelItems = [new ProductDocumentItems()];

        $isTrue = ProductDocument::find()->orderBy(['id' => SORT_DESC])->one()?ProductDocument::find()->orderBy(['id' => SORT_DESC])->one():false;
        $listId = $isTrue?$isTrue['id']+1:1;
        $model->doc_number = "PO-".$listId.'/'.date('m.d.Y');
        $docType = ProductDocument::hasDocTypeLabel($this->slug);

        if ($model->load(Yii::$app->request->post())) {
            if($docType == ProductDocument::DOCUMENT_TYPE_INCOMING){
                $model->doc_type = $docType;
                $model->date = date('Y-m-d', strtotime($model->date));
                $data = Yii::$app->request->post();
                $transaction = Yii::$app->db->beginTransaction();
                $saved = false;
                try{
                    $saved = $model->save()?true:false;
                    if($saved){
                        $productItems = $data['ProductDocumentItems'];
                        if($productItems){
                            foreach ($productItems as $productItem) {
                                $items = new ProductDocumentItems();
                                $items->setAttributes([
                                    'product_id' => $productItem['product_id'],
                                    'incoming_price' => $productItem['incoming_price'],
                                    'quantity' => $productItem['quantity'],
                                    'product_doc_id' => $model->id
                                ]);
                                if($items->save()){
                                    $saved = true;
                                    unset($items);
                                }
                                else{
                                    $saved = false;
                                    break;
                                }
                            }
                        }
                    }

                    if($saved){
                        $transaction->commit();
                        Yii::$app->session->setFlash('success', 'Saqlandi');
                        return $this->redirect(['index', 'slug' => $this->slug]);
                    }
                    else{
                        $transaction->rollBack();
                        Yii::$app->session->setFlash('error', 'Saqlanmadi');
                        return $this->redirect(Yii::$app->request->referrer);
                    }
                }
                catch(\Exception $e){
                    Yii::info('error message '.$e->getMessage(), 'save');
                }

            }
            elseif($docType == ProductDocument::DOCUMENT_TYPE_SELLING){

            }

            return $this->redirect(['view', 'slug' => $this->slug, 'id' => $model->id]);
        }

        return $this->render($this->slug.'/create', [
            'model' => $model,
            'modelItems' => $modelItems
        ]);
    }

    /**
     * Updates an existing ProductDocument model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render($this->slug.'/update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing ProductDocument model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the ProductDocument model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ProductDocument the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ProductDocument::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
