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

if(Yii::$app->user->identity->username == 'admin'){
    $tmp = '';
    $visible = false;
    $unit = $id;
}else{
    $tmp = '{rak}';
    $visible = true;
    $unit = Yii::$app->user->identity->unit_id;
}

$this->title = $namaUnit;
if (Yii::$app->user->identity->unit_id == 'DINKES'){
    $this->params['breadcrumbs'][] = ['label' => 'Data POA Puskesmas '.$session['periodValue'], 'url' => ['period/list', 'period' => $session['periodValue']]];
}else{
    if (Yii::$app->user->identity->group_id == 'SEK'){
        $this->params['breadcrumbs'][] = ['label' => 'POA '.$session['periodValue'], 'url' => ['period/list', 'period' => $session['periodValue']]]; 
    }else{
        $this->params['breadcrumbs'][] = ['label' => 'POA '.$session['periodValue'], 'url' => ['period/create', 'p' => 'def']]; 
    }  
}
$this->params['breadcrumbs'][] = $this->title;
?>

<h1></h1>
<p>
    <?php if($session['poaLabel'] == ' Awal' && Yii::$app->user->identity->username !== 'admin'){ ?>
        <?php if (Yii::$app->user->identity->group_id == 'SEK'){ ?>
            <!-- <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['exportxlsadm', 'unit_id' => $id], ['title' => 'Export Excel', 'class' => 'btn btn-default']) ?> -->
        <?php }else{ ?>
            <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['exportxls'], ['title' => 'Export Excel', 'class' => 'btn btn-default']) ?>
            <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export Desk', ['exportxlsdesk'], ['title' => 'Export Excel Desk', 'class' => 'btn btn-success']) ?>
        <?php } ?>
    <?php }else{ ?>
        <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['exportxlsadm', 'unit_id' => $id], ['title' => 'Export Excel', 'class' => 'btn btn-default']) ?>
        <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export Desk', ['exportxlsdesk'], ['title' => 'Export Excel Desk', 'class' => 'btn btn-success']) ?>
    <?php } ?>

    <?php if($session['poaLabel'] == ' Perubahan' && Yii::$app->user->identity->username !== 'admin'){ ?>
        <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['exportxlsubah'], ['title' => 'Export Excel', 'class' => 'btn btn-default']) ?>
    <?php } ?>

    <?php if(Yii::$app->user->identity->username !== 'admins'){ ?>
        <?= Html::dropDownList('option', null, ['0' => 'Sembunyikan RAK', '1' => 'Tampilkan RAK'],
            [
                'id' => 'rak',
                'options'=>[$session['rak']=>['Selected'=>true]],
                'style' => 'width: 180px !important;', 
                'onchange'=>'
                    $.pjax.reload({
                        container: "#detail",
                        timeout: false,
                    });',
                'class'=>'form-control pull-right'
            ]);
        ?>  
        <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export RAK', ['exportxlsrak'], ['title' => 'Export Excel', 'class' => 'btn btn-info']) ?>
        <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export RAK per Rekening', ['exportxlsrakrek', 'id' => $unit], ['title' => 'Export Excel', 'class' => 'btn btn-warning']) ?>
    <?php } ?>
</p>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    // 'filterModel' => $searchModel,
    // 'options' => ['style' => 'font-size:11px;'],
    'id' => 'GridView',
    'showPageSummary' => true,
    'pageSummaryRowOptions' => ['class' => 'kv-page-summary success', 'style' => 'text-align:right'],
    'pjax' => true,
    'pjaxSettings' =>[
        'neverTimeout'=>true,
        'options'=>[
            'id'=>'detail',
        ]
    ],  
    'striped' => true,
    'hover' => false,
    'panel' => ['type' => 'primary', 'heading' => 'Data POA ' .$session['poaLabel']],
    'toggleDataContainer' => ['class' => 'btn-group mr-2'],
    'toolbar' => false,
    'columns' => [
        // ['class' => 'yii\grid\SerialColumn'],
        [
            'attribute' => 'nama_pelayanan',
            'label' => 'Komponen',
            'group' => true,  // enable grouping
            'contentOptions' => ['style' => 'font-size:11px;'],
            'pageSummaryOptions' => ['colspan' => '10', 'append' => 'Total', 'style' => 'text-align:right'],
            'groupFooter' => function ($model, $key, $index, $widget) { // Closure method
                return [
                    'mergeColumns' => [[0,9]], // columns to merge in summary
                    'content' => [             // content to show in each summary cell
                        1 => 'Jumlah per Komponen',
                        10 => GridView::F_SUM,
                        11 => GridView::F_SUM,
                        12 => GridView::F_SUM,
                        13 => GridView::F_SUM,
                        14 => GridView::F_SUM,
                        15 => GridView::F_SUM,
                        16 => GridView::F_SUM,
                        17 => GridView::F_SUM,
                        18 => GridView::F_SUM,
                        19 => GridView::F_SUM,
                        20 => GridView::F_SUM,
                        21 => GridView::F_SUM,
                        22 => GridView::F_SUM,
                    ],
                    'contentFormats' => [      // content reformatting for each summary cell
                        10 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        11 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        12 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        13 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        14 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        15 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        16 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        17 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        18 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        19 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        20 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        21 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        22 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                    ],
                    'contentOptions' => [      // content html attributes for each summary cell
                        1 => ['style' => 'text-align:right'],
                        10 => ['style' => 'font-size:11px; text-align:right'],
                        11 => ['style' => 'font-size:11px; text-align:right'],
                        12 => ['style' => 'font-size:11px; text-align:right'],
                        13 => ['style' => 'font-size:11px; text-align:right'],
                        14 => ['style' => 'font-size:11px; text-align:right'],
                        15 => ['style' => 'font-size:11px; text-align:right'],
                        16 => ['style' => 'font-size:11px; text-align:right'],
                        17 => ['style' => 'font-size:11px; text-align:right'],
                        18 => ['style' => 'font-size:11px; text-align:right'],
                        19 => ['style' => 'font-size:11px; text-align:right'],
                        20 => ['style' => 'font-size:11px; text-align:right'],
                        21 => ['style' => 'font-size:11px; text-align:right'],
                        22 => ['style' => 'font-size:11px; text-align:right'],
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
            // 'pageSummaryOptions' => ['colspan' => '10', 'append' => 'Total', 'style' => 'text-align:right'],
            'groupFooter' => function ($model, $key, $index, $widget) { // Closure method
                return [
                    'mergeColumns' => [[1,9]], // columns to merge in summary
                    'content' => [             // content to show in each summary cell
                        2 => 'Jumlah per Kegiatan',
                        10 => GridView::F_SUM,
                        11 => GridView::F_SUM,
                        12 => GridView::F_SUM,
                        13 => GridView::F_SUM,
                        14 => GridView::F_SUM,
                        15 => GridView::F_SUM,
                        16 => GridView::F_SUM,
                        17 => GridView::F_SUM,
                        18 => GridView::F_SUM,
                        19 => GridView::F_SUM,
                        20 => GridView::F_SUM,
                        21 => GridView::F_SUM,
                        22 => GridView::F_SUM,
                    ],
                    'contentFormats' => [      // content reformatting for each summary cell
                        10 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        11 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        12 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        13 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        14 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        15 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        16 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        17 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        18 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        19 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        20 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        21 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        22 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                    ],
                    'contentOptions' => [      // content html attributes for each summary cell
                        2 => ['style' => 'text-align:right'],
                        10 => ['style' => 'font-size:11px; text-align:right'],
                        11 => ['style' => 'font-size:11px; text-align:right'],
                        12 => ['style' => 'font-size:11px; text-align:right'],
                        13 => ['style' => 'font-size:11px; text-align:right'],
                        14 => ['style' => 'font-size:11px; text-align:right'],
                        15 => ['style' => 'font-size:11px; text-align:right'],
                        16 => ['style' => 'font-size:11px; text-align:right'],
                        17 => ['style' => 'font-size:11px; text-align:right'],
                        18 => ['style' => 'font-size:11px; text-align:right'],
                        19 => ['style' => 'font-size:11px; text-align:right'],
                        20 => ['style' => 'font-size:11px; text-align:right'],
                        21 => ['style' => 'font-size:11px; text-align:right'],
                        22 => ['style' => 'font-size:11px; text-align:right'],
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
        // [
        //     'attribute' => 'sasaran',
        //     'label' => 'Sasaran',
        //     'contentOptions' => ['style' => 'font-size:11px;']
        // ],
        // // 'target',
        // [
        //     'attribute' => 'lokasi',
        //     'label' => 'Lokasi',
        //     'contentOptions' => ['style' => 'font-size:11px;']
        // ],
        // 'pelaksana',
        [
            'attribute' => 'nama_rekening',
            'label' => 'Rekening',
            // 'value' => function($model){
            //     if(empty($model['rincian'])){
            //         return $model['nama_rekening'];
            //     }else{
            //         return $model['nama_rekening'].' ('.$model['rincian'].')';
            //     }
            // },
            'contentOptions' => ['style' => 'font-size:11px;']
        ],
        [
            'attribute' => 'rincian',
            'label' => 'Rincian ',
            'contentOptions' => ['style' => 'font-size:11px;']
        ],
        [
            'label' => 'Vol 1',
            'attribute' =>'vol_1',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
        ],
        // [
        //     'attribute' => 'satuan_1',
        //     'label' => 'Sat 1',
        //     'contentOptions' => ['style' => 'width: 5%'],
        //     'contentOptions' => ['style' => 'font-size:11px;']
        // ],
        [
            'label' => 'Vol 2',
            'attribute' =>'vol_2',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
        ],
        // [
        //     'attribute' => 'satuan_2',
        //     'label' => 'Sat 2',
        //     'contentOptions' => ['style' => 'width: 5%'],
        //     'contentOptions' => ['style' => 'font-size:11px;']
        // ],
        [
            'label' => 'Vol 3',
            'attribute' =>'vol_3',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            // 'format'=>['decimal',0],
            'value' => function($model){
                if (empty($model['vol_3'])){
                    return "";
                }else{
                    return $model['vol_3'];
                }
            },
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
        ],
        // [
        //     'attribute' => 'satuan_3',
        //     'label' => 'Sat 3',
        //     'contentOptions' => ['style' => 'width: 5%'],
        //     'contentOptions' => ['style' => 'font-size:11px;']
        // ],
        [
            'label' => 'Vol 4',
            'attribute' => 'vol_4',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            // 'format'=> ['decimal',0],
            'value' => function($model){
                if (empty($model['vol_4'])){
                    return "";
                }else{
                    return $model['vol_4'];
                }
            },
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
        ],
        // [
        //     'attribute' => 'satuan_4',
        //     'label' => 'Sat 4',
        //     'contentOptions' => ['style' => 'width: 5%'],
        //     'contentOptions' => ['style' => 'font-size:11px;']
        // ],
        [
            'label' => 'Biaya',
            'attribute' =>'unit_cost',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'pageSummary' => 'Total',
            'pageSummaryOptions' => ['class' => 'text-right'],
            'contentOptions' => ['style' => 'font-size:11px; text-align:right']
        ],
        [
            'label' => 'Jumlah',
            'attribute' =>'jumlah',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM
        ],

        //RAK
        [
            'label' => 'Jan',
            'attribute' =>'jan_val',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'visible' => $rak,
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => $rak,
            'pageSummaryFunc' => GridView::F_SUM
        ],
        [
            'label' => 'Feb',
            'attribute' =>'feb_val',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'visible' => $rak,
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => $rak,
            'pageSummaryFunc' => GridView::F_SUM
        ],
        [
            'label' => 'Mar',
            'attribute' =>'mar_val',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'visible' => $rak,
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => $rak,
            'pageSummaryFunc' => GridView::F_SUM
        ],
        [
            'label' => 'Apr',
            'attribute' =>'apr_val',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'visible' => $rak,
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => $rak,
            'pageSummaryFunc' => GridView::F_SUM
        ],
        [
            'label' => 'Mei',
            'attribute' =>'mei_val',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'visible' => $rak,
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => $rak,
            'pageSummaryFunc' => GridView::F_SUM
        ],
        [
            'label' => 'Jun',
            'attribute' =>'jun_val',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'visible' => $rak,
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => $rak,
            'pageSummaryFunc' => GridView::F_SUM
        ],
        [
            'label' => 'Jul',
            'attribute' =>'jul_val',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'visible' => $rak,
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => $rak,
            'pageSummaryFunc' => GridView::F_SUM
        ],
        [
            'label' => 'Agu',
            'attribute' =>'agu_val',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'visible' => $rak,
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => $rak,
            'pageSummaryFunc' => GridView::F_SUM
        ],
        [
            'label' => 'Sep',
            'attribute' =>'sep_val',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'visible' => $rak,
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => $rak,
            'pageSummaryFunc' => GridView::F_SUM
        ],
        [
            'label' => 'Okt',
            'attribute' =>'okt_val',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'visible' => $rak,
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => $rak,
            'pageSummaryFunc' => GridView::F_SUM
        ],
        [
            'label' => 'Nov',
            'attribute' =>'nov_val',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'visible' => $rak,
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => $rak,
            'pageSummaryFunc' => GridView::F_SUM
        ],
        [
            'label' => 'Des',
            'attribute' =>'des_val',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'visible' => $rak,
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => $rak,
            'pageSummaryFunc' => GridView::F_SUM
        ],
        ['class' => 'yii\grid\ActionColumn',
            'contentOptions' => ['style' => 'width: 5%;text-align:center'],
            'template' => $tmp,
            'visible' => $visible,
            'buttons' => [
                'rak' => function ($url, $model) {
                    // return Html::a('<span class="glyphicon glyphicon-pencil"></span> RAK', array('activitydetail/rak', 'id'=>$model['id']), ['class'=>'btn btn-xs btn-info custom_button']);
                    return Html::button('<span class="glyphicon glyphicon-pencil"></span> RAK', ['value' => Url::to(['activitydetail/rak', 'id' => $model['id']]), 'title' => 'Entri RAK', 'class' => 'showModalButton btn btn-xs btn-info custom_button']);
                },
            ]
        ],
    ],
    ]); 
?>

<?php 
    $this->registerJs('
        $("#rak").on("change", function (e) {
            createCookie("rak",document.getElementById("rak").value, "1");
            baseUrl = window.origin;
            if("'.Yii::$app->user->identity->group_id.'" == "PKM"){
                var link = baseUrl+"'.Url::to(['datapoa', 'p' => 'def']).'";
            }else{
                var link = baseUrl+"'.Url::to(['datapoaadm', 'p' => 'def', 'id' => $id]).'";
            }
            $.get(link);

            // Function to create the cookie 
            function createCookie(name, value, days) { 
                var expires; 
                
                if (days) { 
                    var date = new Date(); 
                    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000)); 
                    expires = "; expires=" + date.toGMTString(); 
                } 
                else { 
                    expires = ""; 
                } 
                
                document.cookie = escape(name) + "=" +  
                    escape(value) + expires + "; path=/"; 
            } 
        });
    ');

    Modal::begin([
            // 'header'=>'<h4>Detail Kegiatan</h4>', 
            'id'=>'modal',
            'size'=>'modal-lg',
            'clientOptions' => ['backdrop' => 'static', 'keyboard' => FALSE],
            // 'footer' => ''
        ]);
    echo "<div id='modalContent'></div>";
    Modal::end();
?>