<?php

namespace app\controllers;

use app\models\BaseModel;
use app\models\Product;
use app\models\ProductDocumentItems;
use app\models\ProductItemsBalance;
use app\modules\wms\models\WmsDocument;
use Yii;
use app\models\ProductDocument;
use app\models\ProductDocumentSearch;
use yii\bootstrap\Html;
use yii\data\ActiveDataProvider;
use yii\helpers\VarDumper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use function Symfony\Component\String\s;

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
//            VarDumper::dump($slug,10,true);die;
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
        $type = ProductDocument::hasDocTypeLabel($this->slug);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$type);

        if($type == ProductDocument::DOCUMENT_TYPE_REPORT_INCOMING){
            $model = new ProductDocument();
            if($model->load(Yii::$app->request->post())){
                $data = Yii::$app->request->post();
                $data = $searchModel->searchReport($data,ProductDocument::DOCUMENT_TYPE_INCOMING);

                if($data){
                    return $this->render($this->slug.'/index', [
                        'searchModel' => $searchModel,
                        'dataProvider' => $dataProvider,
                        'model' => $model,
                        'sqldataprovider' => $data
                    ]);
                }
            }
        }
        elseif($type == ProductDocument::DOCUMENT_TYPE_REPORT_SELLING){
            $model = new ProductDocument();
            if($model->load(Yii::$app->request->post())){
                $data = Yii::$app->request->post();
                $available = null;
                if(empty($data['product_id'])){
                    $all = true;
                    $available = ProductItemsBalance::find()->where(['type' => 2])->groupBy(['product_id'])->orderBy(['id' => SORT_DESC])->asArray()->all();
                }
                else $all = false;

                $data = $searchModel->searchReport($data,ProductDocument::DOCUMENT_TYPE_SELLING);
                if($data){
                    return $this->render($this->slug.'/index', [
                        'searchModel' => $searchModel,
                        'dataProvider' => $dataProvider,
                        'model' => $model,
                        'sqldataprovider' => $data,
                        'all' => $all,
                        'available' => $available
                    ]);
                }
            }
        }

        return $this->render($this->slug.'/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model
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
        $model = $this->findModel($id,ProductDocument::hasDocTypeLabel($this->slug));
        $modelItems = $model->productDocumentItems?$model->productDocumentItems:[new ProductDocumentItems()];
        return $this->render($this->slug.'/view', [
            'model' => $model,
            'modelItems' => $modelItems
        ]);
    }


    /**
     * Save And Finish
     * @param integer $id
     * return mixed
     * */
    public function actionSaveAndFinish()
    {
        $id = Yii::$app->request->get('id');
        $type = ProductDocument::hasDocTypeLabel($this->slug);
        $model = ProductDocument::findOne(['id' => $id, 'doc_type' => $type]);
        $modelItems = $model->productDocumentItems?$model->productDocumentItems:false;
        if($type == ProductDocument::DOCUMENT_TYPE_INCOMING){
            $transaction = Yii::$app->db->beginTransaction();
            $saved = false;
            try{
                if($model && $modelItems){
                    foreach ($modelItems as $modelItem) {
                        $itemBalance = new ProductItemsBalance();
                        $balance = ProductItemsBalance::find()
                            ->where(['product_id' => $modelItem->product_id])
                            ->orderBy(['id' => SORT_DESC])
                            ->one();
                        $isBool = $balance?true:false;
                        if($isBool){
                            $itemBalance->setAttributes([
                                'product_id' => $modelItem->product_id,
                                'product_doc_id' => $modelItem->product_doc_id,
                                'product_doc_items_id' => $modelItem->id,
                                'quantity' => $modelItem->quantity,
                                'amount' => $modelItem->quantity + $balance->amount,
                                'type' => $type
                            ]);
                            if($itemBalance->save()){
                                $saved = true;
                                unset($itemBalance);
                            }
                            else{
                                $saved = false;
                                break;
                            }
                        }
                        else{
                            $itemBalance->setAttributes([
                                'product_id' => $modelItem->product_id,
                                'product_doc_id' => $modelItem->product_doc_id,
                                'product_doc_items_id' => $modelItem->id,
                                'quantity' => $modelItem->quantity,
                                'amount' => $modelItem->quantity,
                                'type' => $type
                            ]);
                            if($itemBalance->save()){
                                $saved = true;
                                unset($itemBalance);
                            }
                            else{
                                $saved = false;
                                break;
                            }
                        }
                    }
                }

                if($saved){
                    $model->status = BaseModel::STATUS_SAVED;
                    $saved = $model->save()?true:false;
                }

                if($saved){
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'Save and finish completed');
                }
                else{
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('error', 'Save and finish failed');
                }

                return $this->redirect(['index', 'slug' => $this->slug]);
            }
            catch(\Exception $e){
                Yii::info('error message '.$e->getMessage(),'save');
            }
        }
        elseif($type == ProductDocument::DOCUMENT_TYPE_SELLING){
            $transaction = Yii::$app->db->beginTransaction();
            $saved = false;
            try{
                if($model && $modelItems){
                    foreach ($modelItems as $modelItem) {
                        $itemBalance = new ProductItemsBalance();
                        $balance = ProductItemsBalance::find()
                            ->where(['product_id' => $modelItem->product_id])
                            ->orderBy(['id' => SORT_DESC])
                            ->one();
                        $isBool = $balance?true:false;
                        if($isBool){
                            if($balance->amount >= $modelItem->quantity){
                                $itemBalance->setAttributes([
                                    'product_id' => $modelItem->product_id,
                                    'product_doc_id' => $modelItem->product_doc_id,
                                    'product_doc_items_id' => $modelItem->id,
                                    'quantity' => $modelItem->quantity,
                                    'amount' => $balance->amount - $modelItem->quantity,
                                    'type' => $type
                                ]);

                                $newColumn = new ProductItemsBalance();
                                $newColumn->setAttributes([
                                    'product_id' => $modelItem->product_id,
                                    'product_doc_id' => $modelItem->product_doc_id,
                                    'product_doc_items_id' => $modelItem->id,
                                    'quantity' => $modelItem->quantity,
                                    'amount' => -1 * $modelItem->quantity,
                                    'type' => $type
                                ]);
                                if($newColumn->save() && $itemBalance->save()){
                                    $saved = true;
                                    unset($itemBalance);
                                    unset($newColumn);
                                }
                                else{
                                    $saved = false;
                                    break;
                                }
                            }
                            else{
                                Yii::$app->session->setFlash('error', Yii::t('app', 'Large quantities of products were introduced.'));
                                $saved = false;
                                break;
                            }
                        }
                        else{
                            Yii::$app->session->setFlash('error', Yii::t('app', 'No product found'));
                        }
                    }
                }

                if($saved){
                    $model->status = BaseModel::STATUS_SAVED;
                    $saved = $model->save()?true:false;
                    if(!$saved){
                        Yii::$app->session->setFlash('error', Yii::t('app', 'Save and finish failed'));
                    }
                }

                if($saved){
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'Save and finish completed');
                }
                else{
                    $transaction->rollBack();
                }

                return $this->redirect(['index', 'slug' => $this->slug]);
            }
            catch(\Exception $e){
                Yii::info('error message '.$e->getMessage(),'save');
            }
        }
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
            if($docType == ProductDocument::DOCUMENT_TYPE_INCOMING || $docType == ProductDocument::DOCUMENT_TYPE_SELLING){
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
                                    'product_doc_id' => $model->id,
                                    'party_number' => $productItem['party_number'],
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
                        return $this->redirect(['view', 'id'=>$model->id, 'slug' => $this->slug]);
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

            return $this->redirect(Yii::$app->request->referrer);
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
        $docType = ProductDocument::hasDocTypeLabel($this->slug);
        $model = $this->findModel($id,$docType);
        $modelItems = $model->productDocumentItems?$model->productDocumentItems:[new ProductDocumentItems()];

        if($model->load(Yii::$app->request->post())) {
            if($docType == ProductDocument::DOCUMENT_TYPE_INCOMING || $docType == ProductDocument::DOCUMENT_TYPE_SELLING){
                $transaction = Yii::$app->db->beginTransaction();
                $saved = false;
                try{
                    $data = Yii::$app->request->post();
                    if(!empty($data['ProductDocumentItems'])){
                        foreach ($modelItems as $modelItem) {
                            $modelItem->delete();
                        }
                    }
                    $model->date = date('Y-m-d', strtotime($model->date));
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
                                    'product_doc_id' => $model->id,
                                    'party_number' => $productItem['party_number'],
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
                        return $this->redirect(['view', 'id'=>$model->id, 'slug' => $this->slug]);
                    }
                    else{
                        $transaction->rollBack();
                        Yii::$app->session->setFlash('error', 'Saqlanmadi');
                        return $this->redirect(Yii::$app->request->referrer);
                    }
                }
                catch(\Exception $e){
                    Yii::info('error message '.$e->getMessage(),'save');
                }
            }
            elseif($docType == ProductDocument::DOCUMENT_TYPE_SELLING){

            }
        }

        return $this->render($this->slug.'/update', [
            'model' => $model,
            'modelItems' => $modelItems
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
        $model = $this->findModel($id,ProductDocument::hasDocTypeLabel($this->slug));
        $model->status = BaseModel::STATUS_DELETE;
        $saved = $model->save()?true:false;
        if($saved)
            return $this->redirect(['index', 'slug' => $this->slug]);
        else
            return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionGetPartyNumber()
    {
        if(Yii::$app->request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            $id = Yii::$app->request->get('name');
            $product = Product::findOne($id);
            if(!empty($product)){
                $response['status'] = true;
                $response['part_number'] = $product->partiy_number;
            }
            else{
                $response['status'] = false;
            }
            return $response;
        }
    }

    /**
     * Finds the ProductDocument model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ProductDocument the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id,$type)
    {
        if (($model = ProductDocument::findOne(['id' => $id, 'doc_type' => $type])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
