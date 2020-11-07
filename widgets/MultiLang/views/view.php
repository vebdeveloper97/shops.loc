<?php
namespace app\widgets\MultiLang;
use yii\helpers\Html;
use Yii;
?>


    <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
        <span class="uppercase"><?=Html::img('/img/flags/'.Yii::$app->language.'.png', ['width'=>'20']) ?><?= '&nbsp;'.strtoupper(Yii::$app->language); ?></span>
            <span class="caret"></span>
    </a>
    <ul class="dropdown-menu">
        <li class="item-lang">
        <li class="item-lang">
            <?= Html::a(Html::img('/img/flags/tr.png', ['width'=>'20']). ' TR', array_merge(
                \Yii::$app->request->get(),
                ['language' => 'tr']
            )); ?>
        </li>
        <li>
            <?= Html::a(Html::img('/img/flags/uz.png', ['width'=>'20']). ' UZ', array_merge(
                \Yii::$app->request->get(),
                ['language' => 'uz']
            )); ?>
        </li>
        <li class="item-lang">
            <?= Html::a(Html::img('/img/flags/ru.png', ['width'=>'20']). ' RU', array_merge(
                \Yii::$app->request->get(),
                ['language' => 'ru']
            )); ?>
        </li>
    </ul>
