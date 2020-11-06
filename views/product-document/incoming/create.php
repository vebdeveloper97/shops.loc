<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\ProductDocument */
/* @var $modelItems \app\models\ProductDocumentItems */

$this->title = Yii::t('app', 'Create Product Document');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Product Documents'), 'url' => ['index', 'slug' => $this->context->slug]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-document-create">

    <?= $this->render('_form', [
        'model' => $model,
        'modelItems' => $modelItems,
    ]) ?>

</div>
