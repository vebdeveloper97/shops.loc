<?php

namespace app\controllers;

use app\models\BaseModel;
use app\models\Product;
use app\models\ProductDocumentItems;
use app\models\ProductItemsBalance;
use app\models\Reports;
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
                    $available = ProductItemsBalance::find()->where(['type' => 2])->groupBy('product_id')->orderBy(['id' => SORT_DESC])->asArray()->all();
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
                        $itemBalance->setAttributes([
                            'product_id' => $modelItem->product_id,
                            'product_doc_id' => $modelItem->product_doc_id,
                            'product_doc_items_id' => $modelItem->id,
                            'quantity' => $modelItem->quantity,
                            'amount' => $modelItem->quantity,
                            'type' => $type,
                            'party_number' => $modelItem->party_number
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
                    /** begin code
                     *  Maxsulotlar miqdorini ozgartirish
                     * */
                    foreach ($modelItems as $modelItem) {
                        $sellingCount = $modelItem['quantity'];

                        /** begin code
                         * Partiya raqamiga qarab tekshirish
                         * */

                        if(!empty($modelItem['party_number'])){
                            /** begin code
                             *  Maxsulotlar kirim qilinganmi yoqmi shuni tekshirish
                             * */
                            $products = ProductItemsBalance::find()
                                ->where([
                                    'party_number' => $modelItem['party_number'],
                                    'product_id' => $modelItem['product_id'],
                                    'type' => ProductDocument::DOCUMENT_TYPE_INCOMING,
                                    'status' => BaseModel::STATUS_ACTIVE
                                ])
                                ->all();
                            if(!empty($products)){
                                /** begin code
                                 *
                                 * */
                                $count = count($products);
                                foreach ($products as $key => $product) {
                                    /** begin code
                                     *  Skladagi kirim bolgan maxsulot miqdoriga tekshirib beradi
                                     * */

                                    /** begin code
                                     *  Report uchun
                                     * */
                                    $documentItems = ProductDocumentItems::findOne($product['product_doc_items_id']);
                                    $difference = $modelItem['quantity'];
                                    /** end code
                                     *  Report uchun
                                     * */

                                    if($modelItem['quantity'] >= $product['amount']){
                                        $modelItem['quantity'] = $product['amount'] - $modelItem['quantity'];
                                        if($key == $count-1){
                                            if($modelItem['quantity'] < 0){
                                                Yii::$app->session->setFlash('error', Yii::t('app', Yii::t('app', 'Error! The amount of product in stock is low')));
                                                $saved = false;
                                                break 2;
                                            }
                                            elseif($modelItem['quantity'] == 0){
                                                $is = ProductItemsBalance::updateAll(
                                                    [
                                                        'amount' => $modelItem['quantity'],
                                                        'status' => BaseModel::STATUS_SAVED
                                                    ],
                                                    [
                                                        'id' => $product['id'],
                                                    ]
                                                );
                                                if($is){
                                                    $saved = true;

                                                    /** begin code
                                                     *  Resport save
                                                     * */
                                                    $report = new Reports();
                                                    $report->setAttributes([
                                                        'product_id' => $product['product_id'],
                                                        'product_doc_id' => $model->id,
                                                        'incoming_price' => $documentItems->incoming_price,
                                                        'selling_price' => $modelItem['selling_price'],
                                                        'profit' => $modelItem['selling_price'] - $documentItems->incoming_price,
                                                        'qty_difference' => $product['amount'],
                                                        'party_number' => $documentItems->party_number,
                                                    ]);

                                                    $saved = $report->save() && $saved;
                                                    /** end code
                                                     *  Resport save
                                                     * */

                                                    break;
                                                }
                                                else{
                                                    $saved = false;
                                                    Yii::$app->session->setFlash('error', Yii::t('app', 'No Updated'));
                                                    break 2;
                                                }
                                            }
                                            else{
                                                $is = ProductItemsBalance::updateAll(
                                                    [
                                                        'amount' => $modelItem['quantity'],
                                                    ],
                                                    [
                                                        'id' => $product['id']
                                                    ]
                                                );
                                                if($is){
                                                    $saved = true;

                                                    /** begin code
                                                     *  Resport save
                                                     * */
                                                    $report = new Reports();
                                                    $report->setAttributes([
                                                        'product_id' => $product['product_id'],
                                                        'product_doc_id' => $model->id,
                                                        'incoming_price' => $documentItems->incoming_price,
                                                        'selling_price' => $modelItem['selling_price'],
                                                        'profit' => $modelItem['selling_price'] - $documentItems->incoming_price,
                                                        'qty_difference' => $difference,
                                                        'party_number' => $documentItems->party_number,
                                                    ]);

                                                    $saved = $report->save() && $saved;
                                                    /** end code
                                                     *  Resport save
                                                     * */

                                                    break;
                                                }
                                                else{
                                                    $saved = false;
                                                    Yii::$app->session->setFlash('error', Yii::t('app', 'No Updated'));
                                                    break 2;
                                                }
                                            }
                                        }
                                        elseif($modelItem['quantity'] < 0){

                                            /** begin code
                                             *  Resport save
                                             * */
                                            $report = new Reports();
                                            $report->setAttributes([
                                                'product_id' => $product['product_id'],
                                                'product_doc_id' => $model->id,
                                                'incoming_price' => $documentItems->incoming_price,
                                                'selling_price' => $modelItem['selling_price'],
                                                'profit' => $modelItem['selling_price'] - $documentItems->incoming_price,
                                                'qty_difference' => $product['amount'],
                                                'party_number' => $documentItems->party_number,
                                            ]);

                                            $saved = $report->save() && $saved;
                                            /** end code
                                             *  Resport save
                                             * */

                                            $is = ProductItemsBalance::updateAll(
                                                [
                                                    'amount' => 0,
                                                    'status' => BaseModel::STATUS_SAVED
                                                ],
                                                [
                                                    'id' => $product['id']
                                                ]
                                            );
                                            if($is){
                                                $modelItem['quantity'] = -1 * $modelItem['quantity'];
                                                $saved = true;
                                            }
                                            else{
                                                $saved = false;
                                                Yii::$app->session->setFlash('error', Yii::t('app', 'No Updated'));
                                                break 2;
                                            }
                                        }
                                        elseif($modelItem['quantity'] == 0){
                                            $is = ProductItemsBalance::updateAll(
                                                [
                                                    'amount' => $modelItem['quantity'],
                                                    'status' => BaseModel::STATUS_SAVED
                                                ],
                                                [
                                                    'id' => $product['id'],
                                                ]
                                            );
                                            if($is){
                                                $saved = true;

                                                /** begin code
                                                 *  Resport save
                                                 * */
                                                $report = new Reports();
                                                $report->setAttributes([
                                                    'product_id' => $product['product_id'],
                                                    'product_doc_id' => $model->id,
                                                    'incoming_price' => $documentItems->incoming_price,
                                                    'selling_price' => $modelItem['selling_price'],
                                                    'profit' => $modelItem['selling_price'] - $documentItems->incoming_price,
                                                    'qty_difference' => $difference,
                                                    'party_number' => $documentItems->party_number,
                                                ]);

                                                $saved = $report->save() && $saved;
                                                /** end code
                                                 *  Resport save
                                                 * */

                                                break;
                                            }
                                            else{
                                                $saved = false;
                                                Yii::$app->session->setFlash('error', Yii::t('app', 'No Updated'));
                                                break 2;
                                            }
                                        }
                                        else{
                                            $is = ProductItemsBalance::updateAll(
                                                [
                                                    'amount' => $modelItem['quantity'],
                                                ],
                                                [
                                                    'id' => $product['id']
                                                ]
                                            );
                                            if($is){
                                                $saved = true;

                                                /** begin code
                                                 *  Resport save
                                                 * */
                                                $report = new Reports();
                                                $report->setAttributes([
                                                    'product_id' => $product['product_id'],
                                                    'product_doc_id' => $model->id,
                                                    'incoming_price' => $documentItems->incoming_price,
                                                    'selling_price' => $modelItem['selling_price'],
                                                    'profit' => $modelItem['selling_price'] - $documentItems->incoming_price,
                                                    'qty_difference' => $difference,
                                                    'party_number' => $documentItems->party_number,
                                                ]);

                                                $saved = $report->save() && $saved;
                                                /** end code
                                                 *  Resport save
                                                 * */

                                                break;
                                            }
                                            else{
                                                $saved = false;
                                                Yii::$app->session->setFlash('error', Yii::t('app', 'No Updated'));
                                                break 2;
                                            }
                                        }
                                    }
                                    else{
                                        if($key == $count - 1){
                                            $modelItem['quantity'] = $product['amount'] - $modelItem['quantity'];
                                            if($modelItem['quantity'] < 0){
                                                Yii::$app->session->setFlash('error', Yii::t('app', Yii::t('app', 'Error! The amount of product in stock is low')));
                                                $saved = false;
                                                break 2;
                                            }
                                            elseif($modelItem['quantity'] == 0){
                                                $is = ProductItemsBalance::updateAll(
                                                    [
                                                        'amount' => $modelItem['quantity'],
                                                        'status' => BaseModel::STATUS_SAVED
                                                    ],
                                                    [
                                                        'id' => $product['id']
                                                    ]
                                                );
                                                if($is){
                                                    $saved = true;

                                                    /** begin code
                                                     *  Resport save
                                                     * */
                                                    $report = new Reports();
                                                    $report->setAttributes([
                                                        'product_id' => $product['product_id'],
                                                        'product_doc_id' => $model->id,
                                                        'incoming_price' => $documentItems->incoming_price,
                                                        'selling_price' => $modelItem['selling_price'],
                                                        'profit' => $modelItem['selling_price'] - $documentItems->incoming_price,
                                                        'qty_difference' => $difference,
                                                        'party_number' => $documentItems->party_number,
                                                    ]);

                                                    $saved = $report->save() && $saved;
                                                    /** end code
                                                     *  Resport save
                                                     * */

                                                    break;
                                                }
                                                else{
                                                    Yii::$app->session->setFlash('error', Yii::t('app', 'No updated'));
                                                    $saved = false;
                                                    break 2;
                                                }
                                            }
                                            else{
                                                $is = ProductItemsBalance::updateAll(
                                                    [
                                                        'amount' => $modelItem['quantity']
                                                    ],
                                                    [
                                                        'id' => $product['id']
                                                    ]
                                                );
                                                if($is){
                                                    $saved = true;

                                                    /** begin code
                                                     *  Resport save
                                                     * */
                                                    $report = new Reports();
                                                    $report->setAttributes([
                                                        'product_id' => $product['product_id'],
                                                        'product_doc_id' => $model->id,
                                                        'incoming_price' => $documentItems->incoming_price,
                                                        'selling_price' => $modelItem['selling_price'],
                                                        'profit' => $modelItem['selling_price'] - $documentItems->incoming_price,
                                                        'qty_difference' => $difference,
                                                        'party_number' => $documentItems->party_number,
                                                    ]);

                                                    $saved = $report->save() && $saved;
                                                    /** end code
                                                     *  Resport save
                                                     * */

                                                    break;
                                                }
                                            }
                                        }
                                        else{
                                            $modelItem['quantity'] = $product['amount'] - $modelItem['quantity'];
                                            if($modelItem['quantity'] < 0){

                                                /** begin code
                                                 *  Resport save
                                                 * */
                                                $report = new Reports();
                                                $report->setAttributes([
                                                    'product_id' => $product['product_id'],
                                                    'product_doc_id' => $model->id,
                                                    'incoming_price' => $documentItems->incoming_price,
                                                    'selling_price' => $modelItem['selling_price'],
                                                    'profit' => $modelItem['selling_price'] - $documentItems->incoming_price,
                                                    'qty_difference' => $product['amount'],
                                                    'party_number' => $documentItems->party_number,
                                                ]);

                                                $saved = $report->save();
                                                /** end code
                                                 *  Resport save
                                                 * */

                                                $is = ProductItemsBalance::updateAll(
                                                    [
                                                        'amount' => 0,
                                                        'status' => BaseModel::STATUS_SAVED
                                                    ],
                                                    [
                                                        'id' => $product['id']
                                                    ]
                                                );
                                                $modelItem = $modelItem['quantity'] * (-1);
                                                if($is){
                                                    $saved = true;
                                                    break;
                                                }
                                                else{
                                                    Yii::$app->session->setFlash('error', Yii::t('app', 'No updated'));
                                                    $saved = false;
                                                    break;
                                                }
                                            }
                                            elseif($modelItem['quantity'] == 0){
                                                $is = ProductItemsBalance::updateAll(
                                                    [
                                                        'amount' => $modelItem['quantity'],
                                                        'status' => BaseModel::STATUS_SAVED
                                                    ],
                                                    [
                                                        'id' => $product['id']
                                                    ]
                                                );
                                                if($is){
                                                    $saved = true;

                                                    /** begin code
                                                     *  Resport save
                                                     * */
                                                    $report = new Reports();
                                                    $report->setAttributes([
                                                        'product_id' => $product['product_id'],
                                                        'product_doc_id' => $model->id,
                                                        'incoming_price' => $documentItems->incoming_price,
                                                        'selling_price' => $modelItem['selling_price'],
                                                        'profit' => $modelItem['selling_price'] - $documentItems->incoming_price,
                                                        'qty_difference' => $difference,
                                                        'party_number' => $documentItems->party_number,
                                                    ]);

                                                    $saved = $report->save() && $saved;
                                                    /** end code
                                                     *  Resport save
                                                     * */

                                                    break;
                                                }
                                            }
                                            else{
                                                $is = ProductItemsBalance::updateAll(
                                                    [
                                                        'amount' => $modelItem['quantity'],
                                                    ],
                                                    [
                                                        'id' => $product['id']
                                                    ]
                                                );
                                                if($is){
                                                    $saved = true;

                                                    /** begin code
                                                     *  Resport save
                                                     * */
                                                    $report = new Reports();
                                                    $report->setAttributes([
                                                        'product_id' => $product['product_id'],
                                                        'product_doc_id' => $model->id,
                                                        'incoming_price' => $documentItems->incoming_price,
                                                        'selling_price' => $modelItem['selling_price'],
                                                        'profit' => $modelItem['selling_price'] - $documentItems->incoming_price,
                                                        'qty_difference' => $difference,
                                                        'party_number' => $documentItems->party_number,
                                                    ]);

                                                    $saved = $report->save() && $saved;
                                                    /** end code
                                                     *  Resport save
                                                     * */

                                                    break;
                                                }
                                            }
                                        }
                                    }
                                    /** end code
                                     *  Skladagi kirim bolgan maxsulot miqdoriga tekshirib beradi
                                     * */
                                }
                                /** end code
                                 *
                                 * */
                            }
                            else{
                                Yii::$app->session->setFlash('error', Yii::t('app', 'Error! No such product was found in the warehouse'));
                                $saved = false;
                                break;
                            }
                            /** end code
                             *  Maxsulotlar kirim qilinganmi yoqmi shuni tekshirish
                             * */
                        }
                        else{
                            /** begin code
                             *  Maxsulotlar kirim qilinganmi yoqmi shuni tekshirish
                             * */
                            $products = ProductItemsBalance::find()
                                ->where([
                                    'product_id' => $modelItem['product_id'],
                                    'type' => ProductDocument::DOCUMENT_TYPE_INCOMING,
                                    'status' => BaseModel::STATUS_ACTIVE
                                ])
                                ->all();
                            if(!empty($products)){
                                /** begin code
                                 *
                                 * */
                                $count = count($products);
                                foreach ($products as $key => $product) {
                                    /** begin code
                                     *  Skladagi kirim bolgan maxsulot miqdoriga tekshirib beradi
                                     * */

                                    /** begin code
                                     *  Report uchun
                                     * */
                                    $documentItems = ProductDocumentItems::findOne($product['product_doc_items_id']);
                                    $difference = $modelItem['quantity'];
                                    /** end code
                                     *  Report uchun
                                     * */

                                    if($modelItem['quantity'] >= $product['amount']){
                                        $modelItem['quantity'] = $product['amount'] - $modelItem['quantity'];
                                        if($key == $count-1){
                                            if($modelItem['quantity'] < 0){
                                                Yii::$app->session->setFlash('error', Yii::t('app', Yii::t('app', 'Error! The amount of product in stock is low')));
                                                $saved = false;
                                                break 2;
                                            }
                                            elseif($modelItem['quantity'] == 0){

                                                /** begin code
                                                 *  Resport save
                                                 * */
                                                $report = new Reports();
                                                $report->setAttributes([
                                                    'product_id' => $product['product_id'],
                                                    'product_doc_id' => $model->id,
                                                    'incoming_price' => $documentItems->incoming_price,
                                                    'selling_price' => $modelItem['selling_price'],
                                                    'profit' => $modelItem['selling_price'] - $documentItems->incoming_price,
                                                    'qty_difference' => $product['amount'],
                                                    'party_number' => $documentItems->party_number,
                                                ]);

                                                $saved = $report->save() && $saved;
                                                /** end code
                                                 *  Resport save
                                                 * */

                                                $is = ProductItemsBalance::updateAll(
                                                    [
                                                        'amount' => $modelItem['quantity'],
                                                        'status' => BaseModel::STATUS_SAVED
                                                    ],
                                                    [
                                                        'id' => $product['id'],
                                                    ]
                                                );
                                                if($is){
                                                    $saved = true;
                                                    break;
                                                }
                                                else{
                                                    $saved = false;
                                                    Yii::$app->session->setFlash('error', Yii::t('app', 'No Updated'));
                                                    break 2;
                                                }
                                            }
                                            else{
                                                $is = ProductItemsBalance::updateAll(
                                                    [
                                                        'amount' => $modelItem['quantity'],
                                                    ],
                                                    [
                                                        'id' => $product['id']
                                                    ]
                                                );
                                                if($is){
                                                    $saved = true;

                                                    /** begin code
                                                     *  Resport save
                                                     * */
                                                    $report = new Reports();
                                                    $report->setAttributes([
                                                        'product_id' => $product['product_id'],
                                                        'product_doc_id' => $model->id,
                                                        'incoming_price' => $documentItems->incoming_price,
                                                        'selling_price' => $modelItem['selling_price'],
                                                        'profit' => $modelItem['selling_price'] - $documentItems->incoming_price,
                                                        'qty_difference' => $difference,
                                                        'party_number' => $documentItems->party_number,
                                                    ]);

                                                    $saved = $report->save() && $saved;
                                                    /** end code
                                                     *  Resport save
                                                     * */

                                                    break;
                                                }
                                                else{
                                                    $saved = false;
                                                    Yii::$app->session->setFlash('error', Yii::t('app', 'No Updated'));
                                                    break 2;
                                                }
                                            }
                                        }
                                        elseif($modelItem['quantity'] < 0){

                                            /** begin code
                                             *  Resport save
                                             * */
                                            $report = new Reports();
                                            $report->setAttributes([
                                                'product_id' => $product['product_id'],
                                                'product_doc_id' => $model->id,
                                                'incoming_price' => $documentItems->incoming_price,
                                                'selling_price' => $modelItem['selling_price'],
                                                'profit' => $modelItem['selling_price'] - $documentItems->incoming_price,
                                                'qty_difference' => $product['amount'],
                                                'party_number' => $documentItems->party_number,
                                            ]);

                                            $saved = $report->save() && $saved;
                                            /** end code
                                             *  Resport save
                                             * */

                                            $is = ProductItemsBalance::updateAll(
                                                [
                                                    'amount' => 0,
                                                    'status' => BaseModel::STATUS_SAVED
                                                ],
                                                [
                                                    'id' => $product['id']
                                                ]
                                            );
                                            if($is){
                                                $modelItem['quantity'] = -1 * $modelItem['quantity'];
                                                $saved = true;
                                            }
                                            else{
                                                $saved = false;
                                                Yii::$app->session->setFlash('error', Yii::t('app', 'No Updated'));
                                                break 2;
                                            }
                                        }
                                        elseif($modelItem['quantity'] == 0){

                                            /** begin code
                                             *  Resport save
                                             * */
                                            $report = new Reports();
                                            $report->setAttributes([
                                                'product_id' => $product['product_id'],
                                                'product_doc_id' => $model->id,
                                                'incoming_price' => $documentItems->incoming_price,
                                                'selling_price' => $modelItem['selling_price'],
                                                'profit' => $modelItem['selling_price'] - $documentItems->incoming_price,
                                                'qty_difference' => $product['amount'],
                                                'party_number' => $documentItems->party_number,
                                            ]);

                                            $saved = $report->save() && $saved;
                                            /** end code
                                             *  Resport save
                                             * */

                                            $is = ProductItemsBalance::updateAll(
                                                [
                                                    'amount' => $modelItem['quantity'],
                                                    'status' => BaseModel::STATUS_SAVED
                                                ],
                                                [
                                                    'id' => $product['id'],
                                                ]
                                            );
                                            if($is){
                                                $saved = true;
                                                break;
                                            }
                                            else{
                                                $saved = false;
                                                Yii::$app->session->setFlash('error', Yii::t('app', 'No Updated'));
                                                break 2;
                                            }
                                        }
                                        else{
                                            $is = ProductItemsBalance::updateAll(
                                                [
                                                    'amount' => $modelItem['quantity'],
                                                ],
                                                [
                                                    'id' => $product['id']
                                                ]
                                            );
                                            if($is){
                                                $saved = true;

                                                /** begin code
                                                 *  Resport save
                                                 * */
                                                $report = new Reports();
                                                $report->setAttributes([
                                                    'product_id' => $product['product_id'],
                                                    'product_doc_id' => $model->id,
                                                    'incoming_price' => $documentItems->incoming_price,
                                                    'selling_price' => $modelItem['selling_price'],
                                                    'profit' => $modelItem['selling_price'] - $documentItems->incoming_price,
                                                    'qty_difference' => $difference,
                                                    'party_number' => $documentItems->party_number,
                                                ]);

                                                $saved = $report->save() && $saved;
                                                /** end code
                                                 *  Resport save
                                                 * */

                                                break;
                                            }
                                            else{
                                                $saved = false;
                                                Yii::$app->session->setFlash('error', Yii::t('app', 'No Updated'));
                                                break 2;
                                            }
                                        }
                                    }
                                    else{
                                        if($key == $count - 1){
                                            $modelItem['quantity'] = $product['amount'] - $modelItem['quantity'];
                                            if($modelItem['quantity'] < 0){
                                                Yii::$app->session->setFlash('error', Yii::t('app', Yii::t('app', 'Error! The amount of product in stock is low')));
                                                $saved = false;
                                                break 2;
                                            }
                                            elseif($modelItem['quantity'] == 0){

                                                /** begin code
                                                 *  Resport save
                                                 * */
                                                $report = new Reports();
                                                $report->setAttributes([
                                                    'product_id' => $product['product_id'],
                                                    'product_doc_id' => $model->id,
                                                    'incoming_price' => $documentItems->incoming_price,
                                                    'selling_price' => $modelItem['selling_price'],
                                                    'profit' => $modelItem['selling_price'] - $documentItems->incoming_price,
                                                    'qty_difference' => $product['amount'],
                                                ]);

                                                $saved = $report->save() && $saved;
                                                /** end code
                                                 *  Resport save
                                                 * */

                                                $is = ProductItemsBalance::updateAll(
                                                    [
                                                        'amount' => $modelItem['quantity'],
                                                        'status' => BaseModel::STATUS_SAVED
                                                    ],
                                                    [
                                                        'id' => $product['id']
                                                    ]
                                                );
                                                if($is){
                                                    $saved = true;
                                                    break;
                                                }
                                                else{
                                                    Yii::$app->session->setFlash('error', Yii::t('app', 'No updated'));
                                                    $saved = false;
                                                    break 2;
                                                }
                                            }
                                            else{
                                                $is = ProductItemsBalance::updateAll(
                                                    [
                                                        'amount' => $modelItem['quantity']
                                                    ],
                                                    [
                                                        'id' => $product['id']
                                                    ]
                                                );
                                                if($is){
                                                    $saved = true;

                                                    /** begin code
                                                     *  Resport save
                                                     * */
                                                    $report = new Reports();
                                                    $report->setAttributes([
                                                        'product_id' => $product['product_id'],
                                                        'product_doc_id' => $model->id,
                                                        'incoming_price' => $documentItems->incoming_price,
                                                        'selling_price' => $modelItem['selling_price'],
                                                        'profit' => $modelItem['selling_price'] - $documentItems->incoming_price,
                                                        'qty_difference' => $difference,
                                                    ]);

                                                    $saved = $report->save() && $saved;
                                                    /** end code
                                                     *  Resport save
                                                     * */

                                                    break;
                                                }
                                            }
                                        }
                                        else{
                                            $modelItem['quantity'] = $product['amount'] - $modelItem['quantity'];
                                            if($modelItem['quantity'] < 0){

                                                /** begin code
                                                 *  Resport save
                                                 * */
                                                $report = new Reports();
                                                $report->setAttributes([
                                                    'product_id' => $product['product_id'],
                                                    'product_doc_id' => $model->id,
                                                    'incoming_price' => $documentItems->incoming_price,
                                                    'selling_price' => $modelItem['selling_price'],
                                                    'profit' => $modelItem['selling_price'] - $documentItems->incoming_price,
                                                    'qty_difference' => $product['amount'],
                                                ]);

                                                $saved = $report->save() && $saved;
                                                /** end code
                                                 *  Resport save
                                                 * */

                                                $is = ProductItemsBalance::updateAll(
                                                    [
                                                        'amount' => -1 * $modelItem['quantity'],
                                                        'status' => BaseModel::STATUS_SAVED
                                                    ],
                                                    [
                                                        'id' => $product['id']
                                                    ]
                                                );
                                                $modelItem = $modelItem['quantity'] * (-1);
                                                if($is){
                                                    $saved = true;
                                                    break;
                                                }
                                                else{
                                                    Yii::$app->session->setFlash('error', Yii::t('app', 'No updated'));
                                                    $saved = false;
                                                    break;
                                                }
                                            }
                                            elseif($modelItem['quantity'] == 0){

                                                /** begin code
                                                 *  Resport save
                                                 * */
                                                $report = new Reports();
                                                $report->setAttributes([
                                                    'product_id' => $product['product_id'],
                                                    'product_doc_id' => $model->id,
                                                    'incoming_price' => $documentItems->incoming_price,
                                                    'selling_price' => $modelItem['selling_price'],
                                                    'profit' => $modelItem['selling_price'] - $documentItems->incoming_price,
                                                    'qty_difference' => $product['amount'],
                                                    'party_number' => $documentItems->party_number,
                                                ]);

                                                $saved = $report->save() && $saved;
                                                /** end code
                                                 *  Resport save
                                                 * */

                                                $is = ProductItemsBalance::updateAll(
                                                    [
                                                        'amount' => $modelItem['quantity'],
                                                        'status' => BaseModel::STATUS_SAVED
                                                    ],
                                                    [
                                                        'id' => $product['id']
                                                    ]
                                                );
                                                if($is){
                                                    $saved = true;
                                                    break;
                                                }
                                            }
                                            else{

                                                /** begin code
                                                 *  Resport save
                                                 * */
                                                $report = new Reports();
                                                $report->setAttributes([
                                                    'product_id' => $product['product_id'],
                                                    'product_doc_id' => $model->id,
                                                    'incoming_price' => $documentItems->incoming_price,
                                                    'selling_price' => $modelItem['selling_price'],
                                                    'profit' => $modelItem['selling_price'] - $documentItems->incoming_price,
                                                    'qty_difference' => $difference,
                                                ]);

                                                $saved = $report->save() && $saved;
                                                /** end code
                                                 *  Resport save
                                                 * */

                                                $is = ProductItemsBalance::updateAll(
                                                    [
                                                        'amount' => $modelItem['quantity'],
                                                    ],
                                                    [
                                                        'id' => $product['id']
                                                    ]
                                                );
                                                if($is){
                                                    $saved = true;
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                    /** end code
                                     *  Skladagi kirim bolgan maxsulot miqdoriga tekshirib beradi
                                     * */
                                }
                                /** end code
                                 *
                                 * */
                            }
                            else{
                                Yii::$app->session->setFlash('error', Yii::t('app', 'Error! No such product was found in the warehouse'));
                                $saved = false;
                                break;
                            }
                            /** end code
                             *  Maxsulotlar kirim qilinganmi yoqmi shuni tekshirish
                             * */
                        }

                        /** end code
                         * Partiya raqamiga qarab tekshirish
                         * */

                        /** begin code
                         *  Sotilgan maxsulotni miqdorini saqlash
                         * */

                        $itemBalances = new ProductItemsBalance();
                        $itemBalances->setAttributes([
                            'product_id' => $modelItem['product_id'],
                            'product_doc_id' => $modelItem['product_doc_id'],
                            'product_doc_items_id' => $modelItem['id'],
                            'quantity' => $sellingCount,
                            'amount' => -1 * $sellingCount,
                            'type' => $type,
                            'party_number' => $modelItem['party_number']?$modelItem['party_number']:''
                        ]);

                        if($itemBalances->save() && $saved){
                            $saved = true;
                            unset($itemBalances);
                        }
                        else{
                            $saved = true;
                            Yii::$app->session->setFlash('error', Yii::t('app', 'No Saved'));
                            break;
                        }
                        /** end code
                         *  Sotilgan maxsulotni miqdorini saqlash
                         * */

                    }
                    /** end code
                     *  Maxsulotlar miqdorini
                     * */

                }

                $model->status = BaseModel::STATUS_SAVED;
                $saved = $model->save() && $saved;

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
            if($docType == ProductDocument::DOCUMENT_TYPE_INCOMING){
                $model->doc_type = $docType;
                $model->date = date('Y-m-d', strtotime($model->date));
                $data = Yii::$app->request->post();
                $transaction = Yii::$app->db->beginTransaction();
                $saved = false;
                try{
                    $saved = $model->save();
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
            elseif($docType == ProductDocument::DOCUMENT_TYPE_SELLING){
                $model->doc_type = $docType;
                $model->date = date('Y-m-d', strtotime($model->date));
                $data = Yii::$app->request->post();
                $transaction = Yii::$app->db->beginTransaction();
                $saved = false;
                try{
                    $totalPrice = 0;
                    $saved = $model->save();
                    if($saved){
                        /** begin code
                         *  maxsulotlarni hujjat asosida sotish
                         * */
                        $productItems = $data['ProductDocumentItems'];
                        if($productItems){
                            foreach ($productItems as $productItem) {
                                $items = new ProductDocumentItems();
                                $items->setAttributes([
                                    'product_id' => $productItem['product_id'],
                                    'selling_price' => $productItem['selling_price'],
                                    'quantity' => $productItem['quantity'],
                                    'product_doc_id' => $model->id,
                                    'party_number' => $productItem['party_number'],
                                ]);
                                $oldItems = ProductDocumentItems::find()
                                    ->where(['product_id' => $productItem['product_id']])
                                    ->orderBy(['id' => SORT_DESC])
                                    ->one();
                                if($items->save()){

                                    /** begin code
                                     *  Sotishda maxsulotni foydasini yozib ketish
                                     */
                                    if($oldItems){
                                        $totalPrice = (($items->selling_price - $oldItems['incoming_price']) * $items->quantity) + $totalPrice;
                                    }
                                    /** end code
                                     *  Sotishda maxsulotni foydasini yozib ketish
                                     */

                                    $saved = true;
                                    unset($items);
                                }
                                else{
                                    $saved = false;
                                    break;
                                }
                            }
                        }
                        /** end code
                         *  maxsulotlarni hujjat asosida sotish
                         * */
                    }

                    /** begin code
                     *  Hujjatni saqlash
                     * */
                    $model->precent_price = $totalPrice;
                    $saved = $model->save() && $saved;
                    /** end code
                     *  Hujjatni saqlash
                     * */

                    /** begin code
                     *  Transactionni ishga tushirish
                     * */
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
                    /** end code
                     *  Transactionni ishga tushirish
                     * */
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
        $model->date = date('d.m.Y', strtotime($model->date));

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
                                    'incoming_price' => $productItem['incoming_price']?$productItem['incoming_price']:'',
                                    'selling_price' => $productItem['selling_price']?$productItem['selling_price']:'',
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
