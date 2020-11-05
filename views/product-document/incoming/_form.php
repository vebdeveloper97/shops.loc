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
                            'addon' => [
                                'append' => [
                                    'content' => KHtml::button(KHtml::icon('plus'), [
                                        'class' => 'showModalButton3 btn btn-success btn-sm toquv_raw_materials_id',
                                        'style' => 'width:15px; padding:2px; font-size: 8px',
                                        'title' => Yii::t('app', 'Create'),
                                        'value' => Url::to(['product/create']),
                                        'data-toggle' => "modal",
                                        'data-form-id' => 'product_form',
                                        'data-input-name' => 'productdocumentitems-0-product_id'
                                    ]),
                                    'asButton' => true
                                ]
                            ],
                            'pluginOptions' => [
                                'debug' => true,
                                'width' => '300px',
                                'escapeMarkup' => new JsExpression(
                                    "function (markup) { 
                                                return markup;
                                            }"
                                ),
                                'templateResult' => new JsExpression(
                                    "function(data) {
                                                   return data.text;
                                             }"
                                ),
                                'templateSelection' => new JsExpression(
                                    "function (data) { return data.text; }"
                                ),
                                'allowClear' => true,
                            ]
                        ],
                        'title' => Yii::t('app', 'Material'),
                    ],
                    [
                        'name' => 'incoming_price',
                        'title' => Yii::t('app', 'Incoming Price'),
                        'options' => [
                            'style' => 'width: 300px',
                        ],
                    ],
                    [
                        'name' => 'quantity',
                        'title' => Yii::t('app', 'Quantity'),
                        'options' => [
                            'style' => 'width: 300px',
                            'options' => [
                                'required' => true,
                                'class' => 'quantity'
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
$js =<<< JS
let formEl;
let url;
let formId;
let inputId;
const modalForm = $('#add_new_item_modal');

$(document).on('click', '.showModalButton3', function(){
    formId = $(this).data('formId');
    inputId = $(this).data('inputName');
    url = $(this).attr('value');
    if (modalForm.data('bs.modal').isShown) {
        modalForm.find('#modalContent')
                .load($(this).attr('value'));
        //dynamiclly set the header for the modal via title tag
        document.getElementById('modalHeader').innerHTML = '<h4>' + $(this).attr('title') + '</h4>';
    } else {
        //if modal isn't open; open it and load content
        modalForm.modal('show')
                .find('#modalContent')
                .load($(this).attr('value'), function(responseTxt, statusTxt, jqXHR){
            if(statusTxt === "success"){
                formProcess();
            }
            if(statusTxt === "error"){
                alert("Error: " + jqXHR.status + " " + jqXHR.statusText);
            }
        });
         //dynamiclly set the header for the modal via title tag
        document.getElementById('modalHeader').innerHTML = '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' 
        +'<h4>' + $(this).attr('title') + '</h4>';
    }
});

function formProcess() {
    formEl = document.getElementById(formId);
    $('#'+formId).on('beforeSubmit', function () {
        const yiiForm = $(this);
        $.ajax({
                type: yiiForm.attr('method'),
                url: yiiForm.attr('action'),
                data: yiiForm.serializeArray()
                })
                .done(function(data) {
                    if(data.success) {
                        const response = data;
                        PNotify.defaults.styling = "bootstrap4";
                        PNotify.defaults.delay = 2000;
                        PNotify.alert({text:"Success",type:'success'});
                        modalForm.modal('hide');    
                        let newOption = new Option(response.title, response.selected_id, true, true);
                        $('#'+inputId).append(newOption).trigger('change');
                    
                    } else if (data.validation) {
                        // server validation failed
                        yiiForm.yiiActiveForm('updateMessages', data.validation, true); // renders validation messages at appropriate places
                        PNotify.defaults.styling = "bootstrap4";
                        PNotify.defaults.delay = 3000;
                        PNotify.alert({text:'Error',type:'error'});
                    } else {
                        // incorrect server response
                    }
                })
                .fail(function () {
                    // request failed
                });
        
            return false; // prevent default form submission
    });
}
JS;

$this->registerJs($js);