<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProductDocumentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Product Documents');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-document-index">
    <p>
        <?= Html::a(Yii::t('app', 'Create Product Document'), ['create', 'slug' => $this->context->slug], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'doc_number',
            'doc_type',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete}',
                'buttons' => [
                    'view' => function($url,$model,$key) {
                        return Html::a('<i class="glyphicon glyphicon-eye-open"></i>', [$url, 'slug' => $this->context->slug]);
                    },
                    'update' => function($url){
                        return Html::a('<i class="glyphicon glyphicon-pencil"></i>', [$url, 'slug' => $this->context->slug]);
                    },
                    'delete' => function($url){
                        return Html::a('<i class="glyphicon glyphicon-trash"></i>', [$url, 'slug' => $this->context->slug], [
                            'title' => Yii::t('app', 'Delete'),
                            'data-confirm' => Yii::t('yii', 'Are you sure you want to delete?'),
                            'data-method' => 'post', 'data-pjax' => '0',
                        ]);
                    },
                ],
                'urlCreator' => function ($action, $model, $key, $index) {
                    if ($action === 'view') {
                        $url = $action.'?id='. $model->id;
                        return $url;
                    }

                    if ($action === 'update') {
                        $url =  $action.'?id='.$model->id;
                        return $url;
                    }
                    if ($action === 'delete') {
                        $url = $action.'?id='. $model->id;
                        return $url;
                    }

                  },
                'visibleButtons' => [
                    'update' => function($model) {
                        return
                            $model->status < $model::STATUS_SAVED && $model->status !== 2;
                    },
                    'delete' => function($model) {
                        return $model->status < $model::STATUS_SAVED && $model->status !== 2;
                    },
                ],
            ],
        ],
    ]); ?>


</div>
