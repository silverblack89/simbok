<?php

use yii\helpers\Html;
// use yii\grid\GridView;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\web\Session;
use yii\bootstrap\Progress;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProgramSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$session = Yii::$app->session;
// $this->title = 'Data POA';
// $this->params['breadcrumbs'][] = ['label' => $session['periodValue']. $session['poaLabel'], 'url' => ['period/create', 'p' => 'def']];
// $this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->identity->unit_id == 'DINKES'){
    $this->title = 'Data POA';
    $this->params['breadcrumbs'][] = ['label' => $session['periodValue']. $session['poaLabel'], 'url' => ['period/list', 'period' => $session['periodValue']]];
    $this->params['breadcrumbs'][] = $this->title;
}else{
    $this->title = 'Data POA';
    $this->params['breadcrumbs'][] = ['label' => $session['periodValue']. $session['poaLabel'], 'url' => ['period/create', 'p' => 'def']];
    $this->params['breadcrumbs'][] = $this->title;
}


if($session['poaLabel'] == ' Awal'){
    echo Progress::widget([
        'bars' => [
            ['percent' => $session['prosentase'], 'label' => $session['prosentase'].'% dari Pagu BOK (RP. ' .number_format($session['pagu'], 0, ',', '.'). ')', 'options' => ['class' => $session['barColor']]],
        ],
        'options' => ['class' => $session['barStatus']]
    ]);
}

if($session['poaLabel'] == ' Perubahan'){
    echo Progress::widget([
        'bars' => [
            ['percent' => $session['prosentase'], 'label' => $session['prosentase'].'% dari Pagu Perubahan (RP. ' .number_format($session['pagu_ubah'], 0, ',', '.'). ')', 'options' => ['class' => $session['barColor']]],
        ],
        'options' => ['class' => $session['barStatus']]
    ]);
}

?>

<h1></h1>
<p>
    <?php if($session['poaLabel'] == ' Awal'){ ?>
        <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['exportxls'], ['title' => 'Export Excel', 'class' => 'btn btn-default']) ?>
    <?php } ?>

    <?php if($session['poaLabel'] == ' Perubahan'){ ?>
        <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['exportxlsubah'], ['title' => 'Export Excel', 'class' => 'btn btn-default']) ?>
    <?php } ?>
</p>
<div style="overflow-x:auto;">
<?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        // 'options' => ['style' => 'font-size:11px;'],
        'id' => 'GridView',
        'showPageSummary' => true,
        'pjax' => true,
        'striped' => true,
        'hover' => false,
        'panel' => ['type' => 'primary', 'heading' => 'Data POA ' .$session['poaLabel']. ' ' .$namaUnit],
        'toggleDataContainer' => ['class' => 'btn-group mr-2'],
        'toolbar' => false,
        'beforeHeader' => [
            [
                'columns' => [
                    ['content' => 'Keterangan', 'options' => ['colspan' => 4, 'class' => 'text-center default']],

                    // ['content' => '', 'options' => ['rowspan' => 1, 'class' => 'text-center default']],
                    // ['content' => '', 'options' => ['rowspan' => 1, 'class' => 'text-center default']],
                    // ['content' => '', 'options' => ['rowspan' => 1, 'class' => 'text-center default']],
                    // ['content' => '', 'options' => ['rowspan' => 1, 'class' => 'text-center default']],
                    // ['content' => '', 'options' => ['rowspan' => 1, 'class' => 'text-center default']],
                    // ['content' => '', 'options' => ['rowspan' => 1, 'class' => 'text-center default']],
                    
                    // ['content' => 'Volume 1', 'options' => ['colspan' => 2, 'class' => 'text-center default']],
                    // ['content' => 'Satuan 1', 'options' => ['colspan' => 2, 'class' => 'text-center default']],
                    // ['content' => 'Volume 2', 'options' => ['colspan' => 2, 'class' => 'text-center default']],
                    // ['content' => 'Satuan 2', 'options' => ['colspan' => 2, 'class' => 'text-center default']],
                    // ['content' => 'Biaya', 'options' => ['colspan' => 2, 'class' => 'text-center default']],
                    // ['content' => 'Jumlah', 'options' => ['colspan' => 2, 'class' => 'text-center default']],

                    // ['content' => 'Awal', 'options' => ['colspan' => 6, 'class' => 'text-center default']],
                    ['content' => 'Rincian', 'options' => ['colspan' => 6, 'class' => 'text-center default']],
                    

                ],
            ]
        ],
        'columns' => [
            // ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'nama_pelayanan',
                'label' => 'Pelayanan',
                'group' => true,  // enable grouping
                'contentOptions' => ['style' => 'font-size:11px;'],
                'groupFooter' => function ($model, $key, $index, $widget) { // Closure method
                    return [
                        // 'mergeColumns' => [[0,10], [12,16]], // columns to merge in summary
                        'mergeColumns' => [[0,8]], // columns to merge in summary
                        'content' => [             // content to show in each summary cell
                            1 => 'Jumlah',
                            13 => 'Jumlah',
                            9 => GridView::F_SUM,
                            // 17 => GridView::F_SUM,
                        ],
                        'contentFormats' => [      // content reformatting for each summary cell
                            9 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                            // 17 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        ],
                        'contentOptions' => [      // content html attributes for each summary cell
                            1 => ['style' => 'text-align:right'],
                            9 => ['style' => 'font-size:11px; text-align:right'],
                            // 17 => ['style' => 'font-size:11px; text-align:right'],
                        ],
                        // html attributes for group summary row
                        'options' => ['class' => 'info table-info','style' => 'font-weight:bold; text-align:right; font-size:11px;']
                    ];
                }
            ],
            [
                'headerOptions'=>['style'=>'text-align: center;'],
                'attribute' => 'nama_kegiatan',
                'label' => 'Nama Kegiatan',
                'group' => true,  // enable grouping
                'contentOptions' => ['style' => 'font-size:11px;']
            ],
            [
                'headerOptions'=>['style'=>'text-align: center;'],
                'attribute' => 'bentuk_kegiatan',
                'label' => 'Bentuk Kegitan',
                // 'group' => true,  // enable grouping
                'contentOptions' => ['style' => 'font-size:11px;']
            ],
            // [
            //     'headerOptions'=>['style'=>'text-align: center;'],
            //     'attribute' => 'sasaran',
            //     'label' => 'Sasaran',
            //     'contentOptions' => ['style' => 'font-size:11px;']
            // ],
            // 'target',
            // [
            //     'headerOptions'=>['style'=>'text-align: center;'],
            //     'attribute' => 'lokasi',
            //     'label' => 'Lokasi',
            //     'contentOptions' => ['style' => 'font-size:11px;']
            // ],
            // 'pelaksana',
            [
                'headerOptions'=>['style'=>'text-align: center;'],
                'attribute' => 'nama_rekening',
                'label' => 'Rekening',
                'contentOptions' => ['style' => 'font-size:11px;']
            ],

            // [
            //     'headerOptions'=>['style'=>'text-align: center;'],
            //     'label' => 'Volume 1',
            //     'attribute' =>'vol_1_awal',
            //     'enableSorting' => false,
            //     'contentOptions' => ['class' => 'col-lg-1 text-right'],
            //     'format'=>['decimal',0],
            //     'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            // ],
            // [
            //     'headerOptions'=>['style'=>'text-align: center;'],
            //     'attribute' => 'satuan_1_awal',
            //     'label' => 'Sat 1',
            //     'contentOptions' => ['style' => 'width: 5%'],
            //     'contentOptions' => ['style' => 'font-size:11px;']
            // ],
            // [
            //     'headerOptions'=>['style'=>'text-align: center;'],
            //     'label' => 'Volume 2',
            //     'attribute' =>'vol_2_awal',
            //     'enableSorting' => false,
            //     'contentOptions' => ['class' => 'col-lg-1 text-right'],
            //     'format'=>['decimal',0],
            //     'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            // ],
            // [
            //     'headerOptions'=>['style'=>'text-align: center;'],
            //     'attribute' => 'satuan_2_awal',
            //     'label' => 'Satuan 2',
            //     'contentOptions' => ['style' => 'width: 5%'],
            //     'contentOptions' => ['style' => 'font-size:11px;']
            // ],
            // [
            //     'headerOptions'=>['style'=>'text-align: center;'],
            //     'label' => 'Unit Cost',
            //     'attribute' =>'unit_cost_awal',
            //     'enableSorting' => false,
            //     'contentOptions' => ['class' => 'col-lg-1 text-right'],
            //     'format'=>['decimal',0],
            //     'pageSummary' => 'Total',
            //     'pageSummaryOptions' => ['class' => 'text-right'],
            //     'contentOptions' => ['style' => 'font-size:11px; text-align:right']
            // ],
            // [
            //     'headerOptions'=>['style'=>'text-align: center;'],
            //     'label' => 'Jumlah',
            //     'attribute' =>'jumlah_awal',
            //     'enableSorting' => false,
            //     'contentOptions' => ['class' => 'col-lg-1 text-right'],
            //     'format'=>['decimal',0],
            //     'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            //     'pageSummary' => true,
            //     'pageSummaryFunc' => GridView::F_SUM
            // ],

            [
                'headerOptions'=>['style'=>'text-align: center;'],
                'label' => 'Volume 1',
                'attribute' =>'vol_1',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right'],
                'format'=>['decimal',0],
                'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            ],
            [
                'headerOptions'=>['style'=>'text-align: center;'],
                'attribute' => 'satuan_1',
                'label' => 'Satuan',
                'contentOptions' => ['style' => 'width: 5%'],
                'contentOptions' => ['style' => 'font-size:11px;']
            ],
            [
                'headerOptions'=>['style'=>'text-align: center;'],
                'label' => 'Volume 2',
                'attribute' =>'vol_2',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right'],
                'format'=>['decimal',0],
                'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            ],
            [
                'headerOptions'=>['style'=>'text-align: center;'],
                'attribute' => 'satuan_2',
                'label' => 'Satuan',
                'contentOptions' => ['style' => 'width: 5%'],
                'contentOptions' => ['style' => 'font-size:11px;']
            ],
            [
                'headerOptions'=>['style'=>'text-align: center;'],
                'label' => 'Unit Cost',
                'attribute' =>'unit_cost',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right'],
                'format'=>['decimal',0],
                'pageSummary' => 'Total',
                'pageSummaryOptions' => ['class' => 'text-right'],
                'contentOptions' => ['style' => 'font-size:11px; text-align:right']
            ],
            [
                'headerOptions'=>['style'=>'text-align: center;'],
                'label' => 'Jumlah',
                'attribute' =>'jumlah',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right'],
                'format'=>['decimal',0],
                'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
                'pageSummary' => true,
                'pageSummaryFunc' => GridView::F_SUM
            ],

            // ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>

<?php
    // You only need add this,
    // $this->registerJs('
    //     var gridview_id = ""; // specific gridview
    //     var columns = [1,2,3]; // index column that will grouping, start 1
 
    //     /*
    //     DON\'T EDIT HERE
 
    //     http://www.hafidmukhlasin.com
 
    //     */
    //     var column_data = [];
    //         column_start = [];
    //         rowspan = [];
 
    //     for (var i = 0; i < columns.length; i++) {
    //         column = columns[i];
    //         column_data[column] = "";
    //         column_start[column] = null;
    //         rowspan[column] = 1;
    //     }
 
    //     var row = 1;
    //     $(gridview_id+" table > tbody  > tr").each(function() {
    //         var col = 1;
    //         $(this).find("td").each(function(){
    //             for (var i = 0; i < columns.length; i++) {
    //                 if(col==columns[i]){
    //                     if(column_data[columns[i]] == $(this).html()){
    //                         $(this).remove();
    //                         rowspan[columns[i]]++;
    //                         $(column_start[columns[i]]).attr("rowspan",rowspan[columns[i]]);
    //                     }
    //                     else{
    //                         column_data[columns[i]] = $(this).html();
    //                         rowspan[columns[i]] = 1;
    //                         column_start[columns[i]] = $(this);
    //                     }
    //                 }
    //             }
    //             col++;
    //         })
    //         row++;
    //     });
    // ');
?>