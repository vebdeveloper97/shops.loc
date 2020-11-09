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
    <div class="col-sm-4">
        <?=$form->field($model, 'start_date')->widget(\kartik\date\DatePicker::class, [
            'pluginOptions' => [
                'format' => 'dd.mm.yyyy',
                'todayHighlight' => true,
                'autoclose'=>true,
            ]
        ]); ?>
    </div>
    <div class="col-sm-4">
        <?=$form->field($model, 'end_date')->widget(\kartik\date\DatePicker::class, [
            'pluginOptions' => [
                'format' => 'dd.mm.yyyy',
                'todayHighlight' => true,
                'autoclose'=>true,
            ]
        ]); ?>
    </div>
    <div class="col-sm-4">
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
    $incomingPrice = 0;
    $sellingPrice = 0;
    $profit = 0;
    $count = 0;
    ?>
    <?php $i = 1; if(isset($sqldataprovider)): ?>
        <div class="row">
            <div class="col-sm-11" style="margin-left: 15px;">
                <div class="panel panel-default">
                    <!-- Default panel contents -->
                    <div class="panel-heading"><?=Yii::t('app', 'Report')?></div>
                    <table class="table">
                        <thead style="background: #3c8dbc">
                        <th>N</th>
                        <th><?=Yii::t('app', 'Product name')?></th>
                        <th><?=Yii::t('app', 'Incoming price')?></th>
                        <th><?=Yii::t('app', 'Selling price')?></th>
                        <th><?=Yii::t('app', 'Profit')?></th>
                        <th><?=Yii::t('app', 'Quantity Difference')?></th>
                        <th><?=Yii::t('app', 'date')?></th>
                        <td></td>
                        </thead>
                        <tbody>
                        <?php if($sqldataprovider->getModels()): ?>
                            <?php foreach($sqldataprovider->getModels() as $key => $val): ?>
                            <?php
                                $incomingPrice = $incomingPrice + $val['incoming_price'];
                                $sellingPrice = $sellingPrice + $val['selling_price'];
                                $profit = $profit + $val['profit'];
                                $count = $count + $val['qty_difference'];
                            ?>
                                <tr>
                                    <td>
                                        <?=$i;?>
                                    </td>
                                    <td>
                                        <strong style="color: black"><?=$val['name'];?></strong>
                                    </td>
                                    <td>
                                        <?=$val['incoming_price']?>
                                    </td>
                                    <td>
                                        <?=$val['selling_price']?>
                                    </td>
                                    <td>
                                        <?=$val['profit']?>
                                    </td>
                                    <td>
                                        <?=$val['qty_difference']?>
                                    </td>
                                    <td>
                                        <?=$val['date']?>
                                    </td>
                                    <td>
                                    </td>
                                </tr>
                                <?php $i++; endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8"><h3><?=Yii::t('app', 'Not Found')?></h3></td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                        <tfoot style="background: #3c8dbc">
                        <tr style="margin: 10px;">
                            <td colspan="2">
                                <strong><?=Yii::t('app', 'All')?></strong>
                            </td>
                            <td>
                                <strong><?=$incomingPrice?></strong>
                            </td>
                            <td>
                                <strong><?=$sellingPrice?></strong>
                            </td>
                            <td>
                                <strong>Foyda: <?php echo $profit * $count;?></strong>
                            </td>
                            <td>
                                <strong><?=$count?> ta</strong>
                            </td>
                            <td>
                                <strong>
                                    <?php
                                    ?>
                                </strong>
                            </td>
                            <td></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php
    $this->registerCss("
        strong{
            color: white
        }
    ");
