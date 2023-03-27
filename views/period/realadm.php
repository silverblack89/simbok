<?php

use yii\helpers\Html;
// use yii\grid\GridView;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\Session;

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
    $this->params['breadcrumbs'][] = ['label' => 'Data POA Puskesmas '.$session['periodValue'], 'url' => ['period/list', 'period' => $session['periodValue']]];
}else{
    $this->params['breadcrumbs'][] = ['label' => 'POA '.$session['periodValue'], 'url' => ['period/create', 'p' => 'def']];   
}
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="period-real">
    <h1></h1>
    <p>
        <!-- <?php if($session['poaLabel'] == ' Awal' && Yii::$app->user->identity->username !== 'admin'){ ?>
            <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['exportxls'], ['title' => 'Export Excel', 'class' => 'btn btn-default']) ?>
        <?php }else{ ?>
            <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['exportxlsadm', 'unit_id' => $id], ['title' => 'Export Excel', 'class' => 'btn btn-default']) ?>
        <?php } ?>

        <?php if($session['poaLabel'] == ' Perubahan' && Yii::$app->user->identity->username !== 'admin'){ ?>
            <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['exportxlsubah'], ['title' => 'Export Excel', 'class' => 'btn btn-default']) ?>
        <?php } ?> -->

        <!-- <div class="row">
            <div class="col-sm-2">
                <?= Html::dropDownList('triwulan', null, ['1' => 'Triwulan 1', '2' => 'Triwulan 2', '3' => 'Triwulan 3', '4' => 'Triwulan 4'],
                [
                    'options'=>[$session['triwulan']=>['Selected'=>true]],
                    'prompt'=>'Pilih Tribulan',
                    'onchange'=>'
                        $.pjax.reload({
                            url: "'.Url::to(['detailpoa']).'?p="+$(this).val(),
                            container: "#real-all",
                            timeout: 1000,
                        });',
                    'class'=>'form-control']) 
                ?>
            </div>
        </div> -->
    </p>

    <p>
        <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['exportdetailpoa'], ['title' => 'Export Excel', 'class' => 'btn btn-default']) ?>
    </p>

    <?= GridView::widget([
        // 'beforeHeader' => [
        //     [
        //         'columns' => [
        //             ['content' => 'Komponen', 'options' => ['class' => 'text-center warning', 'style' => 'background:green; color:white']],
        //             ['content' => 'Kegiatan', 'options' => ['class' => 'text-center warning', 'style' => 'background:green; color:white']],
        //             ['content' => 'Bentuk Kegiatan', 'options' => ['class' => 'text-center warning', 'style' => 'background:green; color:white']],
        //             ['content' => 'Rekening', 'options' => ['class' => 'text-center warning', 'style' => 'background:green; color:white']],
        //             ['content' => 'Rincian', 'options' => ['class' => 'text-center warning', 'style' => 'background:green; color:white']],
        //             ['content' => 'POA', 'options' => ['class' => 'text-center warning', 'style' => 'background:green; color:white']],
        //             ['content' => 'Tw Lalu', 'options' => ['class' => 'text-center warning', 'style' => 'background:green; color:white']],
        //             ['content' => 'Realisasi', 'options' => ['class' => 'text-center warning', 'style' => 'background:green; color:white']],
        //             ['content' => '%', 'options' => ['class' => 'text-center warning', 'style' => 'background:green; color:white']],
        //             ['content' => 'Keuangan', 'options' => ['class' => 'text-center warning', 'style' => 'background:green; color:white']],
        //             ['content' => 'Kinerja', 'options' => ['class' => 'text-center warning', 'style' => 'background:green; color:white']],
        //         ],
        //         'options' => ['style' => 'kv header success'],
        //     ]
        // ],
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
                'attribute' => 'nama_pelayanan',
                'label' => 'Komponen',
                'group' => true,  // enable grouping
                'contentOptions' => ['style' => 'font-size:11px;'],
                // 'pageSummaryOptions' => ['colspan' => '6','append' => 'Total', 'style' => 'text-align:right'],            
                'groupFooter' => function ($model, $key, $index, $widget) { // Closure method
                    return [
                        // 'mergeColumns' => [[0,5]], // columns to merge in summary
                        'content' => [             // content to show in each summary cell
                            4 => 'Jumlah per Komponen',
                            5 => GridView::F_SUM,
                            6 => GridView::F_SUM,
                            7 => GridView::F_SUM,
                            // 9 => GridView::F_SUM,
                        ],
                        'contentFormats' => [      // content reformatting for each summary cell
                            5 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                            6 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                            7 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                            // 9 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        ],
                        'contentOptions' => [      // content html attributes for each summary cell
                            4 => ['style' => 'text-align:right'],
                            5 => ['style' => 'font-size:11px; text-align:right'],
                            6 => ['style' => 'font-size:11px; text-align:right'],
                            7 => ['style' => 'font-size:11px; text-align:right'],
                            // 9 => ['style' => 'font-size:11px; text-align:right'],
                        ],
                        // html attributes for group summary row
                        'options' => ['class' => 'info table-info','style' => 'font-weight:bold; text-align:right; font-size:11px;']
                    ];
                }
            ],
            [
                'attribute' => 'nama_kegiatan',
                'label' => 'Sub Komponen',
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
                            // 9 => GridView::F_SUM,
                        ],
                        'contentFormats' => [      // content reformatting for each summary cell
                            5 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                            6 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                            7 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                            // 9 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        ],
                        'contentOptions' => [      // content html attributes for each summary cell
                            4 => ['style' => 'text-align:right'],
                            5 => ['style' => 'font-size:11px; text-align:right'],
                            6 => ['style' => 'font-size:11px; text-align:right'],
                            7 => ['style' => 'font-size:11px; text-align:right'],
                            // 9 => ['style' => 'font-size:11px; text-align:right'],
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
                'contentOptions' => ['style' => 'font-size:11px;'],
                'pageSummary' => 'TOTAL',
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
                'enableSorting' => false,
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
                                // return Html::a('<span class="glyphicon glyphicon-check"></span> Input', array('realization/create', 'id' => $model['id'], 'poa' => $model['jumlah']), ['class'=>'btn btn-xs btn-success']);
                                return Html::button('<span class="glyphicon glyphicon-check"></span> Input', 
                                    ['value' => Url::to(['realization/create', 'id' => $model['id'], 'poa' => $model['jumlah'], 'prev' => $model['jml_real_lalu']]), 'title' => 'Input Realisasi', 'class' => 'showModalButton btn btn-xs btn-success']);
                            }else{
                                // return Html::a('<span class="glyphicon glyphicon-pencil"></span> Input', array('realization/update', 'id' => $model['realization_id'], 'poa' => $model['jumlah']), ['class'=>'btn btn-xs btn-warning']);
                                return Html::button('<span class="glyphicon glyphicon-pencil"></span> Ubah', 
                                    ['value' => Url::to(['realization/update', 'id' => $model['realization_id'], 'poa' => $model['jumlah'], 'prev' => $model['jml_real_lalu']]), 'title' => 'Ubah Realisasi', 'class' => 'showModalButton btn btn-xs btn-warning']);
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
                                // return Html::a('<span class="glyphicon glyphicon-stats"></span> Input', array('perfomance/create', 'id' => $model['activity_data_id'], 'target' => $model['target']), ['class'=>'btn btn-xs btn-info']);
                                return Html::button('<span class="glyphicon glyphicon-stats"></span> Input', 
                                    ['value' => Url::to(['perfomance/create', 'id' => $model['activity_data_id'], 'target' => $model['target']]), 'title' => 'Input Realisasi', 'class' => 'showModalButton btn btn-xs btn-info']);
                            }else{
                                // return Html::a('<span class="glyphicon glyphicon-pencil"></span> Input', array('perfomance/update', 'id' => $model['perfomance_id'], 'target' => $model['target']), ['class'=>'btn btn-xs btn-warning']);
                                return Html::button('<span class="glyphicon glyphicon-stats"></span> Ubah', 
                                    ['value' => Url::to(['perfomance/update', 'id' => $model['perfomance_id'], 'target' => $model['target']]), 'title' => 'Ubah Realisasi', 'class' => 'showModalButton btn btn-xs btn-warning']);
                            }
                        }
                    },
                ]
            ],
            // [
            //     'label' => 'I',
            //     'attribute' => 'tw1',
            //     'enableSorting' => false,
            //     'format' => 'raw',
            //     // 'visible' => $dis1,
            //     'contentOptions' => ['style' => 'width: 1%'],
            //     'value' => function ($model) {
            //         return Html::checkbox('tw1', $model['tw1'], ['id' => 'cb1','disabled' => false, 
            //         'onclick' => '
            //             $.ajax({
            //             type: "GET",
            //             url: "post-tw",
            //             data: { id: '.$model['id'].', tw: "1", val: $(this).prop("checked") },
            //             success: function(result) {
            //                 $.pjax.reload({container: "#real", timeout:false});
            //             },
            //             error: function(result) {
            //                 alert("Error");
            //             }});
            //         '
            //         ]);
            //     },
            // ],
            // [
            //     'label' => 'II',
            //     'attribute' => 'tw2',
            //     'enableSorting' => false,
            //     'format' => 'raw',
            //     // 'visible' => $dis2,
            //     'contentOptions' => ['style' => 'width: 1%'],
            //     'value' => function ($model) {
            //         return Html::checkbox('tw2', $model['tw2'], ['id' => 'cb2','disabled' => false, 
            //         'onclick' => '
            //             $.ajax({
            //             type: "GET",
            //             url: "post-tw",
            //             data: { id: '.$model['id'].', tw: "2", val: $(this).prop("checked") },
            //             success: function(result) {
            //                 $.pjax.reload({container: "#real", timeout:false});
            //             },
            //             error: function(result) {
            //                 alert("Error");
            //             }});
            //         '
            //         ]);
            //     },
            // ],
            // [
            //     'label' => 'III',
            //     'attribute' => 'tw3',
            //     'enableSorting' => false,
            //     'format' => 'raw',
            //     // 'visible' => $dis3,
            //     'contentOptions' => ['style' => 'width: 1%'],
            //     'value' => function ($model) {
            //         return Html::checkbox('tw3', $model['tw3'], ['id' => 'cb3','disabled' => false, 
            //         'onclick' => '
            //             $.ajax({
            //             type: "GET",
            //             url: "post-tw",
            //             data: { id: '.$model['id'].', tw: "3", val: $(this).prop("checked") },
            //             success: function(result) {
            //                 $.pjax.reload({container: "#real", timeout:false});
            //             },
            //             error: function(result) {
            //                 alert("Error");
            //             }});
            //         '
            //         ]);
            //     },
            // ],
            // [
            //     'label' => 'IV',
            //     'attribute' => 'tw4',
            //     'enableSorting' => false,
            //     'format' => 'raw',
            //     // 'visible' => $dis4,
            //     'contentOptions' => ['style' => 'width: 1%'],
            //     'value' => function ($model) {
            //         return Html::checkbox('tw4', $model['tw4'], ['id' => 'cb4','disabled' => false, 
            //         'onclick' => '
            //             $.ajax({
            //             type: "GET",
            //             url: "post-tw",
            //             data: { id: '.$model['id'].', tw: "4", val: $(this).prop("checked") },
            //             success: function(result) {
            //                 $.pjax.reload({container: "#real", timeout:false});
            //             },
            //             error: function(result) {
            //                 alert("Error");
            //             }});
            //         '
            //         ]);
            //     },
            // ],
        ],
    ]); ?>
</div>

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