<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\ProductDocument */

$this->title = Yii::t('app', 'Create Product Document');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Product Documents'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-document-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
