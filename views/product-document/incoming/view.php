<?php

use app\components\TabularInput\CustomTabularInput;
use app\models\Product;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\DetailView;
use kartik\helpers\Html as KHtml;

/* @var $this yii\web\View */
/* @var $model app\models\ProductDocument */
/* @var $modelItems \app\models\ProductDocumentItems */

$this->title = $model->doc_number;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Product Documents'), 'url' => ['index', 'slug' => $this->context->slug]];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="product-document-view">

    <p>
        <?php if($model->status == \app\models\BaseModel::STATUS_ACTIVE): ?>
            <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id, 'slug' => $this->context->slug], ['class' => 'btn btn-primary']) ?>
            <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id, 'slug' => $this->context->slug], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                    'method' => 'post',
                ],
            ]) ?>
            <?=Html::a(Yii::t('app', 'Save And Finish'), ['save-and-finish', 'slug' => $this->context->slug, 'id' => $model->id], ['class' => 'btn btn-success'])?>
        <?php endif; ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'doc_number',
            'doc_type',
            [
                'attribute' => 'created_at',
                'value' => function($model){
                    return dateFormatter($model->created_at);
                }
            ],
            [
                'attribute' => 'updated_at',
                'value' => function($model){
                    return dateFormatter($model->updated_at);
                }
            ],
            'created_by',
            'updated_by',
        ],
    ]) ?>

    <div class="row">
        <div class="col-sm-12">
            <?=CustomTabularInput::widget([
                'id' => 'material_inputs',
                'models' => $modelItems,
                'addButtonOptions' => [
                    'class' => 'hide',
                ],
                'removeButtonOptions' => [
                    'class' => 'hide',
                ],
                'columns' => [
                    [
                        'name'  => 'product_id',
                        'type' => Select2::className(),
                        'options' => [
                            'data' => Product::getArrayHelp(),
                            'size' => Select2::SIZE_TINY,
                            'disabled' => true,
                            'options' => [
                                'placeholder' => Yii::t('app', 'Products'),
                                'class' => 'product_names',
                                'style' => 'width: 300px',
                            ],
                        ],
                        'title' => Yii::t('app', 'Products'),
                    ],
                    [
                        'name' => 'incoming_price',
                        'title' => Yii::t('app', 'Incoming Price'),
                        'options' => [
                            'style' => 'width: 250px',
                            'readonly' => true,
                        ],
                    ],
                    [
                        'name' => 'quantity',
                        'title' => Yii::t('app', 'Quantity'),
                        'options' => [
                            'style' => 'width: 200px',
                            'readonly' => true,
                            'options' => [
                                'required' => true,
                                'class' => 'quantity'
                            ]
                        ],
                    ],
                    [
                        'name' => 'party_number',
                        'title' => Yii::t('app', 'Party Number'),
                        'options' => [
                            'style' => 'width: 100px',
                            'readonly' => true,
                            'options' => [
                                'class' => 'party_number'
                            ]
                        ],
                    ],
                ]
            ])?>
        </div>
    </div>

</div>
