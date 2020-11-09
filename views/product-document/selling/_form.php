<?php

use kartik\select2\Select2;
use yii\helpers\Html;
use kartik\helpers\Html as KHtml;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use app\components\TabularInput\CustomTabularInput;
use yii\helpers\Url;
use app\models\Product;


/* @var $this yii\web\View */
/* @var $model app\models\ProductDocument */
/* @var $form yii\widgets\ActiveForm */
/* @var $modelItems \app\models\ProductDocumentItems */
?>

    <div class="product-document-form">

        <?php $form = ActiveForm::begin(); ?>

        <div class="row">
            <div class="col-sm-6">
                <?= $form->field($model, 'doc_number')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-sm-6">
                <?= $form->field($model, 'date')->widget(\kartik\date\DatePicker::class, [
                    'options' => [
                        'value' => $model->isNewRecord?date('d.m.yy'):$model->date,
                    ],
                    'pluginOptions' => [
                        'autoclose'=>true,
                        'format' => 'dd.mm.yyyy'
                    ]
                ]); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <?=CustomTabularInput::widget([
                    'id' => 'material_inputs',
                    'models' => $modelItems,
                    'addButtonOptions' => [
                        'class' => 'btn-success btn',
                    ],
                    'removeButtonOptions' => [
                        'class' => 'btn-danger btn',
                    ],
                    'columns' => [
                        [
                            'name'  => 'product_id',
                            'type' => Select2::className(),
                            'options' => [
                                'data' => Product::getArrayHelp(),
                                'size' => Select2::SIZE_TINY,
                                'options' => [
                                    'placeholder' => Yii::t('app', 'Products'),
                                    'class' => 'product_names',
                                    'required' => true,
                                ],
                                'pluginOptions' => [
                                    'debug' => true,
                                    'width' => '300px',
                                    'allowClear' => true,
                                ]
                            ],
                            'title' => Yii::t('app', 'Products'),
                        ],
                        [
                            'name' => 'selling_price',
                            'title' => Yii::t('app', 'Selling Price').'<span style="color: orangered"> $</span>',
                            'options' => [
                                'style' => 'width: 250px',
                            ],
                        ],
                        [
                            'name' => 'quantity',
                            'title' => Yii::t('app', 'Quantity'),
                            'options' => [
                                'style' => 'width: 200px',
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
                                'options' => [
                                    'required' => true,
                                    'class' => 'party_number'
                                ]
                            ],
                        ],
                    ]
                ])?>
            </div>
        </div>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
<?php

yii\bootstrap\Modal::begin([
    'headerOptions' => ['id' => 'modalHeader'],
    'options' => [
        'tabindex' => false,
    ],
    'size' => 'modal-lg',
    'id' => 'add_new_item_modal',
]);
echo "<div id='modalContent'></div>";
yii\bootstrap\Modal::end();
$partyAjax = Url::to(['product-document/get-party-number', 'slug' => $this->context->slug]);
$js =<<< JS
$('body').delegate('select.product_names', 'change', function(event){
    let name = $(this).val();
    let obj = event.target;
    if(name){
        $.ajax({
            data: {name: name},
            type: 'GET',
            url: "$partyAjax",
            success: function (result){
                if(result.status){
                    $(obj).parents('tr').find('.list-cell__party_number input').val(result.part_number);
                    $(obj).parents('tr').find('.list-cell__party_number input').attr('readonly', true);
                    
                }
                else{
                    alert('Malumot kelmadi');
                }
            }
        });
    }
    else{
        $(obj).parents('tr').find('.list-cell__party_number input').val('').attr('readonly', false).trigger('change');
    }
});
JS;



$this->registerJs($js);
