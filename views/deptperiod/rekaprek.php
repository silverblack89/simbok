<?php

use yii\helpers\Html;
// use yii\grid\GridView;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\web\Session;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProgramSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$session = Yii::$app->session;
$this->title = 'Rekap per Rekening';
$this->params['breadcrumbs'][] = ['label' => 'Data POA '.$session['deptPeriodValue'], 'url' => ['deptperiod/list', 'period' => $session['deptPeriodValue']]];
$this->params['breadcrumbs'][] = $this->title;
?>

<h1></h1>
<p>
    <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['exportxlsrek'], ['title' => 'Export Excel', 'class' => 'btn btn-default']) ?>
</p>
<div style="overflow-x:auto;">
<?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        // 'options' => ['style' => 'font-size:11px;'],
        'id' => 'GridView',
        'showPageSummary' => true,
        'pageSummaryRowOptions' => ['class' => 'kv-page-summary success', 'style' => 'text-align:right'],
        'pjax' => true,
        'striped' => true,
        'hover' => false,
        'panel' => ['type' => 'primary', 'heading' => 'Rekap Rekening ' .$session['poaLabel']],
        'toggleDataContainer' => ['class' => 'btn-group mr-2'],
        'toolbar' => false,
        'columns' => [
            // ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'nama_program',
                'label' => 'Rincian Menu Kegiatan',
                'group' => true,  // enable grouping
                'contentOptions' => ['style' => 'font-size:11px;'],
                'pageSummaryOptions' => ['colspan' => '7', 'append' => 'Total', 'style' => 'text-align:right'],
                'groupFooter' => function ($model, $key, $index, $widget) { // Closure method
                    return [
                        'mergeColumns' => [[0,6]], // columns to merge in summary
                        'content' => [             // content to show in each summary cell
                            1 => 'Jumlah',
                            7 => GridView::F_SUM,
                        ],
                        'contentFormats' => [      // content reformatting for each summary cell
                            7 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        ],
                        'contentOptions' => [      // content html attributes for each summary cell
                            1 => ['style' => 'text-align:right'],
                            7 => ['style' => 'font-size:11px; text-align:right'],
                        ],
                        // html attributes for group summary row
                        'options' => ['class' => 'info table-info','style' => 'font-weight:bold; text-align:right; font-size:11px;']
                    ];
                }
            ],
            [
                'attribute' => 'nama_sub_kegiatan',
                'label' => 'Komponen',
                'group' => true,  // enable grouping
                'contentOptions' => ['style' => 'font-size:11px;']
            ],
            [
                'attribute' => 'nama_rekening',
                'label' => 'Rekening Pembiayaan',
                // 'group' => true,  // enable grouping
                'contentOptions' => ['style' => 'font-size:11px;']
            ],
            [
                'label' => 'Vol 1',
                'attribute' =>'jml_vol_1',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right'],
                'format'=>['decimal',0],
                'contentOptions' => ['style' => 'font-size:11px; text-align:right']
            ],
            [
                'label' => 'Vol 2',
                'attribute' =>'jml_vol_2',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right'],
                'format'=>['decimal',0],
                'contentOptions' => ['style' => 'font-size:11px; text-align:right']
            ],
            [
                'label' => 'Vol 3',
                'attribute' =>'jml_vol_3',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right'],
                'format'=>['decimal',0],
                'contentOptions' => ['style' => 'font-size:11px; text-align:right']
            ],
            [
                'label' => 'Vol 4',
                'attribute' =>'jml_vol_4',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right'],
                'format'=>['decimal',0],
                'contentOptions' => ['style' => 'font-size:11px; text-align:right']
            ],
            [
                'label' => 'Sub Total',
                'attribute' =>'sub_total',
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