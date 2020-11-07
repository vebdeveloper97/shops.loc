<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $model \app\models\ProductDocument */
/* @var $sqldataprovider  */
/* @var $all */

?>
<div class="row">
    <?php $form = \yii\bootstrap\ActiveForm::begin(); ?>
    <div class="col-sm-3">
        <?=$form->field($model, 'start_date')->widget(\kartik\date\DatePicker::class, [
            'pluginOptions' => [
                'format' => 'dd.mm.yyyy',
                'todayHighlight' => true,
                'autoclose'=>true,
            ]
        ]); ?>
    </div>
    <div class="col-sm-3">
        <?=$form->field($model, 'end_date')->widget(\kartik\date\DatePicker::class, [
            'pluginOptions' => [
                'format' => 'dd.mm.yyyy',
                'todayHighlight' => true,
                'autoclose'=>true,
            ]
        ]); ?>
    </div>
    <div class="col-sm-3">
        <?=$form->field($model, 'party_number'); ?>
    </div>
    <div class="col-sm-3">
        <?=$form->field($model, 'product_id')->widget(\kartik\select2\Select2::class, [
            'data' => \app\models\Product::getArrayHelp(),
            'pluginOptions' => [
                'allowClear' => true
            ],
            'options' => ['placeholder' => 'All Product'],
        ]); ?>
    </div>
    <div class="col-sm-6">
        <?=Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-success btn-xs'])?>
    </div></div>
    <?php \yii\bootstrap\ActiveForm::end(); ?>
    <?php
    $totalCount = 0;
    $totalAmount = 0;
    ?>
    <?php $i = 1; if(isset($sqldataprovider)): ?>
        <div class="row">
            <div class="col-sm-11" style="margin-left: 15px;">
                <div class="panel panel-default">
                    <!-- Default panel contents -->
                    <div class="panel-heading"><?=Yii::t('app', 'Report')?></div>
                    <table class="table">
                        <thead class="">
                        <th>N</th>
                        <th><?=Yii::t('app', 'Product name')?></th>
                        <th><?=Yii::t('app', 'Quantity')?></th>
                        <th><?=Yii::t('app', 'Amount')?></th>
                        <th><?=Yii::t('app', 'Start Date')?></th>
                        <th><?=Yii::t('app', 'Party Number')?></th>
                        <th><?=Yii::t('app', 'Document Number')?></th>
                        <td></td>
                        </thead>
                        <tbody>
                        <?php if($sqldataprovider->getModels()): ?>
                            <?php foreach($sqldataprovider->getModels() as $key => $val): ?>
                                <tr>
                                    <td>
                                        <?=$i;?>
                                    </td>
                                    <td>
                                        <strong><?=$val['name'];?></strong>
                                    </td>
                                    <td>
                                        <?=$val['quantity']?>
                                    </td>
                                    <td>
                                        <?=$val['amount']?>
                                    </td>
                                    <td>
                                        <?=$val['date']?>
                                    </td>
                                    <td>
                                        <?=$val['pnumber']?>
                                    </td>
                                    <td>
                                        <?=$val['doc_number']?>
                                    </td>
                                    <td>
                                        <?php
                                            $lastCount = count($sqldataprovider->getModels()) - 1;
                                            if($val['amount'] < 0){
                                                echo Yii::t('app', 'Sold');
                                                $totalCount = $totalCount + $val['quantity'];
                                            }
                                            elseif($lastCount == $key){
                                                echo Yii::t('app', 'Available');
                                                $totalAmount = $val['amount'];
                                            }
                                        ?>
                                    </td>
                                </tr>
                                <?php $i++; endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8"><h3><?=Yii::t('app', 'Not Found')?></h3></td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                        <tfoot>
                        <tr style="margin: 10px;">
                            <td colspan="4">
                                <strong><?=Yii::t('app', 'Sold')?> <span style="background: orange; padding: 5px;"><?=$totalCount?></span></strong>
                            </td>
                            <td colspan="4">
                                <strong><?=Yii::t('app', 'Available')?> <span style="background: orange; padding: 5px;"><?=$totalAmount?></span></strong>
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
