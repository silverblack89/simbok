<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $searchModel app\models\PeriodSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Data Pagu';
$this->params['breadcrumbs'][] = 'Pagu';
?>
<div class="period-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <div class="row">
            <div class="col-md-2">
                <?= Html::dropDownList('tahun', null, [ date('Y')-1 => date('Y')-1, date('Y') => date('Y'), date('Y')+1 => date('Y')+1, ] ,
                [
                    // 'prompt'=>'Pilih Periode',
                    'options'=>[$session['periodValue']=>['Selected'=>true]],
                    'onchange'=>'
                        $.pjax.reload({
                            url: "'.Url::to(['index']).'?tahun="+$(this).val(),
                            container: "#pjax-gridview",
                            timeout: 1000,
                        });',
                    'class'=>'form-control pull-right']) 
                ?>
            </div>
        </div>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    
    <?php Pjax::begin(['id' => 'pjax-gridview']) ?>
    <div style="overflow-x:auto;">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'id',
            // 'unit_id',
            [
                'label' => 'Kode',
                'attribute' =>'unit_id',
                'enableSorting' => false,
            ],
            ['attribute' => 'unit_id',
                'label' => 'Nama Puskesmas',
                'value' => 'unit.puskesmas',
                'enableSorting' => false,
                // 'contentOptions' => ['style' => 'width: 14%']
            ],
            // 'tahun',
            // 'pagu',
            [
                'label' => 'Pagu Awal',
                'attribute' =>'pagu',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right'], //, 'style' => 'width: 4%'
                'format'=>['decimal',0]
            ],

            [
                'label' => 'Pagu Pergeseran',
                'attribute' =>'pagu_geser',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right'], //, 'style' => 'width: 4%'
                'format'=>['decimal',0]
            ],

            [
                'label' => 'Pagu Perubahan',
                'attribute' =>'pagu_ubah',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right'], //, 'style' => 'width: 4%'
                'format'=>['decimal',0]
            ],

            // ['class' => 'yii\grid\ActionColumn'],
            ['class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['style' => 'width: 8%'],
                'template' => '{update}',
                'buttons' => [
                    'update' => function ($url, $model) {
                        // return Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['period/update', 'id' => $model->id]); 
                        return Html::button('<span class="glyphicon glyphicon-pencil"></span> Ubah', 
                        ['value' => Url::to(['/period/update', 'id' => $model->id]), 'title' => 'Ubah', 'class' => 'showModalButton']);
                    },
                ]
            ],
        ],
    ]); ?>
    </div>
    <?php Pjax::end() ?>

    <?php 
        Modal::begin([
                'header'=>'<h4>Pagu Puskesmas</h4>',
                'id'=>'modal',
                'size'=>'modal-sm',
                'clientOptions' => ['backdrop' => 'static', 'keyboard' => FALSE],
                // 'footer' => ''
            ]);
        echo "<div id='modalContent'></div>";
        Modal::end();
    ?>

</div>

<?php
$js=<<< JS
$(".alert").animate({opacity: 1.0}, 3000).fadeOut("slow");
JS;
$this->registerJs($js, yii\web\View::POS_READY);
?>
