<?php

use yii\helpers\Html;
// use yii\grid\GridView;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\Session;
use yii\helpers\ArrayHelper;
use app\models\Sumberdana;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProgramSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$session = Yii::$app->session;
$this->title = $namaUnit;
if (Yii::$app->user->identity->group_id == 'ADM'){
    $visible = false;
    if($triwulan == 1){
        $tmp = '{cb1}';
        $dis1 = 0;
        $dis2 = 1;
        $dis3 = 1;
        $dis4 = 1;
    }elseif($triwulan == 2){
        $tmp = '{cb2}';
        $dis1 = 1;
        $dis2 = 0;
        $dis3 = 1;
        $dis4 = 1;
    }elseif($triwulan == 3){
        $tmp = '{cb3}';
        $dis1 = 1;
        $dis2 = 1;
        $dis3 = 0;
        $dis4 = 1;
    }else{
        $tmp = '{cb4}';
        $dis1 = 1;
        $dis2 = 1;
        $dis3 = 1;
        $dis4 = 0;
    }
}else{
    if($session['sp2d'] > 0){
        if($stsreal == 'Kunci'){
            $tmp = '{button}';
            $visible = true;
        }else{
            $tmp = '';
            $visible = false;
        }
    }else{
        $tmp = '';
    }
}

if (Yii::$app->user->identity->unit_id == 'DINKES'){
    $this->params['breadcrumbs'][] = ['label' => 'Data POA '.$session['deptPeriodValue'], 'url' => ['deptperiod/list', 'period' => $session['deptPeriodValue']]];
}else{
    $this->params['breadcrumbs'][] = ['label' => 'POA '.$session['deptPeriodValue'], 'url' => ['deptperiod/create', 'p' => 'def']];   
}
$this->params['breadcrumbs'][] = $this->title;
?>

<h1></h1>
<div class="row">
    <div class="col-md-2">
        <?= Html::dropDownList('sumberdana', null, ArrayHelper::map(Sumberdana::find()->all(),'id','nama'),
        [
            'options'=>[$session['sumberdana']=>['Selected'=>true]],
            'prompt'=>'Pilih Sumber Dana',
            'onchange'=>'
                $.pjax.reload({
                    url: "'.Url::to(['deptperiod/detailpoaadm', 'p' => $triwulan, 'unit_id' => $id]).'&sd="+$(this).val(),
                    container: "#real",
                    timeout: 1000,
                });',
            'class'=>'form-control']) 
        ?>
    </div>
    <div class="col-md-2">
        <!-- <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['exportdetailpoa'], ['title' => 'Export Excel', 'class' => 'btn btn-default']) ?> -->
    </div>
</div>

<div style="margin-top:10px">

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    // 'filterModel' => $searchModel,
    // 'options' => ['style' => 'font-size:11px;'],
    'id' => 'GridView',
    'showPageSummary' => true,
    'pageSummaryRowOptions' => ['class' => 'kv-page-summary default', 'style' => 'text-align:right'],
    'pjax' => true,
    'pjaxSettings' =>[
        'neverTimeout'=>true,
        'options'=>[
            'id'=>'real',
        ]
    ],  
    'striped' => false,
    'hover' => false,
    'panel' => ['type' => 'primary', 'heading' => 'Data POA Realisasi Triwulan ' .$session['triwulan']],
    'toggleDataContainer' => ['class' => 'btn-group mr-2'],
    'toolbar' => false,
    'columns' => [
        // ['class' => 'yii\grid\SerialColumn'],
        [
            'attribute' => 'nama_kegiatan',
            'label' => 'Komponen',
            'group' => true,  // enable grouping
            'contentOptions' => ['style' => 'font-size:11px;'],
            // 'pageSummaryOptions' => ['colspan' => '6', 'append' => 'Total', 'style' => 'text-align:right'],
            'groupFooter' => function ($model, $key, $index, $widget) { // Closure method
                return [
                    // 'mergeColumns' => [[0,5]], // columns to merge in summary
                    'content' => [             // content to show in each summary cell
                        4 => 'Jumlah per Komponen',
                        5 => GridView::F_SUM,
                        6 => GridView::F_SUM,
                        7 => GridView::F_SUM,
                    ],
                    'contentFormats' => [      // content reformatting for each summary cell
                        5 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        6 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        7 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                    ],
                    'contentOptions' => [      // content html attributes for each summary cell
                        4 => ['style' => 'text-align:right'],
                        5 => ['style' => 'font-size:11px; text-align:right'],
                        6 => ['style' => 'font-size:11px; text-align:right'],
                        7 => ['style' => 'font-size:11px; text-align:right'],
                    ],
                    // html attributes for group summary row
                    'options' => ['class' => 'info table-info','style' => 'font-weight:bold; text-align:right; font-size:11px;']
                ];
            }
        ],
        [
            'attribute' => 'nama_sub_kegiatan',
            'label' => 'Kegiatan',
            'group' => true,  // enable grouping
            'subGroupOf' => 0,// supplier column index is the parent group,
            'contentOptions' => ['style' => 'font-size:11px;'],
            // 'pageSummaryOptions' => ['colspan' => '5', 'append' => 'Total', 'style' => 'text-align:right'],
            'groupFooter' => function ($model, $key, $index, $widget) { // Closure method
                return [
                    // 'mergeColumns' => [[1,5]], // columns to merge in summary
                    'content' => [             // content to show in each summary cell
                        4 => 'Jumlah per Kegiatan',
                        5 => GridView::F_SUM,
                        6 => GridView::F_SUM,
                        7 => GridView::F_SUM,
                    ],
                    'contentFormats' => [      // content reformatting for each summary cell
                        5 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        6 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        7 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                    ],
                    'contentOptions' => [      // content html attributes for each summary cell
                        4 => ['style' => 'text-align:right'],
                        5 => ['style' => 'font-size:11px; text-align:right'],
                        6 => ['style' => 'font-size:11px; text-align:right'],
                        7 => ['style' => 'font-size:11px; text-align:right'],
                    ],
                    // html attributes for group summary row
                    'options' => ['class' => 'info table-info','style' => 'font-weight:bold; text-align:right; font-size:11px;']
                ];
            }
        ],
        [
            'attribute' => 'bentuk_kegiatan',
            'label' => 'Bentuk Kegiatan',
            // 'group' => true,  // enable grouping
            'contentOptions' => ['style' => 'font-size:11px;']
        ],
        
        [
            'attribute' => 'nama_rekening',
            'label' => 'Rekening',
            'contentOptions' => ['style' => 'font-size:11px;']
        ],
        [
            'attribute' => 'rincian',
            'label' => 'Rincian ',
            'contentOptions' => ['style' => 'font-size:11px;']
        ],
        [
            'label' => 'POA',
            'attribute' =>'jumlah',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM
        ],

        [
            'label' => $twprev,
            'attribute' => 'jml_real_lalu',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM
        ],

        [
            'label' => 'Realisasi',
            'attribute' => 'jml_real',
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM
        ],

        [
            'label' => '%',
            'attribute' => 'persen',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            // 'pageSummary' => true,
            // 'pageSummaryFunc' => GridView::F_SUM
        ],

        ['class' => 'yii\grid\ActionColumn',
            'header'=>'Keuangan',
            'contentOptions' => ['style' => 'width:5%'],
            'template' => $tmp,
            'visible' => $visible,
            'buttons' => [
                'button' => function ($url, $model, $session) {
                    $session = Yii::$app->session;
                    if($session['sp2dStatus']){
                        if(empty($model['realization_id'])){
                            // return Html::a('<span class="glyphicon glyphicon-check"></span> Input', array('deptrealization/create', 'id' => $model['id'], 'poa' => $model['jumlah']), ['class'=>'btn btn-xs btn-success']);
                            return Html::button('<span class="glyphicon glyphicon-check"></span> Input', 
                                ['value' => Url::to(['deptrealization/create', 'id' => $model['id'], 'poa' => $model['jumlah'], 'prev' => $model['jml_real_lalu']]), 'title' => 'Input Realisasi', 'class' => 'showModalButton btn btn-xs btn-success']);
                        }else{
                            // return Html::a('<span class="glyphicon glyphicon-pencil"></span> Input', array('deptrealization/update', 'id' => $model['realization_id'], 'poa' => $model['jumlah']), ['class'=>'btn btn-xs btn-warning']);
                            return Html::button('<span class="glyphicon glyphicon-pencil"></span> Ubah', 
                                ['value' => Url::to(['deptrealization/update', 'id' => $model['realization_id'], 'poa' => $model['jumlah'], 'prev' => $model['jml_real_lalu']]), 'title' => 'Ubah Realisasi', 'class' => 'showModalButton btn btn-xs btn-warning']);
                        }

                    }
                },
            ]
        ],

        ['class' => 'yii\grid\ActionColumn',
            'header'=>'Kinerja',
            'contentOptions' => ['style' => 'width:5%;text-align:center'],
            'template' => $tmp,
            'visible' => $visible,
            'buttons' => [
                'button' => function ($url, $model, $session) {
                    $session = Yii::$app->session;
                    if($model['bentuk_kegiatan'] !== $session['act']){
                        $session->open();
                        $session['act'] = $model['bentuk_kegiatan'];
                        if(empty($model['perfomance_id'])){
                            // return Html::a('<span class="glyphicon glyphicon-stats"></span> Input', array('deptperfomance/create', 'id' => $model['dept_sub_activity_data_id'], 'target' => $model['target_hasil']), ['class'=>'btn btn-xs btn-info']);
                            return Html::button('<span class="glyphicon glyphicon-stats"></span> Input', 
                                ['value' => Url::to(['deptperfomance/create', 'id' => $model['dept_sub_activity_data_id'], 'target' => $model['target_hasil']]), 'title' => 'Input Realisasi', 'class' => 'showModalButton btn btn-xs btn-info']);
                        }else{
                            // return Html::a('<span class="glyphicon glyphicon-pencil"></span> Input', array('deptperfomance/update', 'id' => $model['perfomance_id'], 'target' => $model['target_hasil']), ['class'=>'btn btn-xs btn-warning']);
                            return Html::button('<span class="glyphicon glyphicon-stats"></span> Ubah', 
                                ['value' => Url::to(['deptperfomance/update', 'id' => $model['perfomance_id'], 'target' => $model['target_hasil']]), 'title' => 'Ubah Realisasi', 'class' => 'showModalButton btn btn-xs btn-warning']);
                        }
                    }
                },
            ]
        ],
        [
            'attribute' => 'sumber_dana',
            'contentOptions' => ['style' => 'font-size:11px; text-align:center'],
        ],
        [
            'label' => 'I',
            'attribute' => 't1',
            'enableSorting' => false,
            'format' => 'raw',
            // 'visible' => $dis1,
            'contentOptions' => ['style' => 'width: 1%'],
            'value' => function ($model) {
                return Html::checkbox('t1', $model['t1'], ['id' => 'cb1','disabled' => false, 
                'onclick' => '
                    $.ajax({
                    type: "GET",
                    url: "post-tw",
                    data: { id: '.$model['id'].', tw: "1", val: $(this).prop("checked") },
                    success: function(result) {
                        $.pjax.reload({container: "#real", timeout:false});
                    },
                    error: function(result) {
                        alert("Error");
                    }});
                '
                ]);
            },
        ],
        [
            'label' => 'II',
            'attribute' => 't2',
            'enableSorting' => false,
            'format' => 'raw',
            // 'visible' => $dis2,
            'contentOptions' => ['style' => 'width: 1%'],
            'value' => function ($model) {
                return Html::checkbox('t2', $model['t2'], ['id' => 'cb2','disabled' => false, 
                'onclick' => '
                    $.ajax({
                    type: "GET",
                    url: "post-tw",
                    data: { id: '.$model['id'].', tw: "2", val: $(this).prop("checked") },
                    success: function(result) {
                        $.pjax.reload({container: "#real", timeout:false});
                    },
                    error: function(result) {
                        alert("Error");
                    }});
                '
                ]);
            },
        ],
        [
            'label' => 'III',
            'attribute' => 't3',
            'enableSorting' => false,
            'format' => 'raw',
            // 'visible' => $dis3,
            'contentOptions' => ['style' => 'width: 1%'],
            'value' => function ($model) {
                return Html::checkbox('t3', $model['t3'], ['id' => 'cb3','disabled' => false, 
                'onclick' => '
                    $.ajax({
                    type: "GET",
                    url: "post-tw",
                    data: { id: '.$model['id'].', tw: "3", val: $(this).prop("checked") },
                    success: function(result) {
                        $.pjax.reload({container: "#real", timeout:false});
                    },
                    error: function(result) {
                        alert("Error");
                    }});
                '
                ]);
            },
        ],
        [
            'label' => 'IV',
            'attribute' => 't4',
            'enableSorting' => false,
            'format' => 'raw',
            // 'visible' => $dis4,
            'contentOptions' => ['style' => 'width: 1%'],
            'value' => function ($model) {
                return Html::checkbox('t4', $model['t4'], ['id' => 'cb4','disabled' => false, 
                'onclick' => '
                    $.ajax({
                    type: "GET",
                    url: "post-tw",
                    data: { id: '.$model['id'].', tw: "4", val: $(this).prop("checked") },
                    success: function(result) {
                        $.pjax.reload({container: "#real", timeout:false});
                    },
                    error: function(result) {
                        alert("Error");
                    }});
                '
                ]);
            },
        ],
    ],
]); ?>

<?php 
    Modal::begin([
            // 'header'=>'<h4>Detail Kegiatan</h4>', 
            'id'=>'modal',
            'size'=>'modal-sm',
            'clientOptions' => ['backdrop' => 'static', 'keyboard' => FALSE],
            // 'footer' => ''
        ]);
    echo "<div id='modalContent'></div>";
    Modal::end();
?>