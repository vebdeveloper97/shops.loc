<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\ProductDocument */
/* @var $modelItems \app\models\ProductDocumentItems */

$this->title = Yii::t('app', 'Update Product Document: {name}', [
    'name' => $model->id,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Product Documents'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id, 'slug' => $this->context->slug]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="product-document-update">

    <?= $this->render('_form', [
        'model' => $model,
        'modelItems' => $modelItems
    ]) ?>

</div>
