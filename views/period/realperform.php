<?php

use yii\helpers\Html;
// use yii\grid\GridView;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\web\Session;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProgramSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$session = Yii::$app->session;
$this->title = $namaUnit;
if (Yii::$app->user->identity->unit_id == 'DINKES'){
    $this->params['breadcrumbs'][] = ['label' => 'Data POA Puskesmas '.$session['periodValue'], 'url' => ['period/list', 'period' => $session['periodValue']]];
}else{
    $this->params['breadcrumbs'][] = ['label' => 'POA '.$session['periodValue'], 'url' => ['period/create', 'p' => 'def']];   
}
$this->params['breadcrumbs'][] = $this->title;
?>

<h1></h1>
<p>
    <div class="row">
        <div class="col-sm-2">
        <?= Html::dropDownList('triwulan', null, ['1' => 'Triwulan 1', '2' => 'Triwulan 2', '3' => 'Triwulan 3', '4' => 'Triwulan 4'],
            [
                'options'=>[$session['triwulan']=>['Selected'=>true]],
                // 'prompt'=>'Pilih Tribulan',
                'onchange'=>'
                    $.pjax.reload({
                        url: "'.Url::to(['realperform', 'id' => $id]).'&tw="+$(this).val(),
                        container: "#real-all",
                        timeout: 1000,
                    });',
                'class'=>'form-control']) 
            ?>
        </div>

        <div class="col-sm-2">
            <?php if($session['poaLabel'] == ' Awal' && Yii::$app->user->identity->username !== 'admin'){ ?>
                <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['exportxls'], ['title' => 'Export Excel', 'class' => 'btn btn-default']) ?>
            <?php }else{ ?>
                <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['exportrealperform', 'unit_id' => $id], ['title' => 'Export Excel', 'class' => 'btn btn-default']) ?>
            <?php } ?>

            <?php if($session['poaLabel'] == ' Perubahan' && Yii::$app->user->identity->username !== 'admin'){ ?>
                <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['exportrealperform'], ['title' => 'Export Excel', 'class' => 'btn btn-default']) ?>
            <?php } ?>
        </div>
    </div>
</p>

<?php Pjax::begin(['id' => 'real-all']) ?>
<?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        // 'options' => ['style' => 'font-size:11px;'],
        'id' => 'GridView',
        // 'showPageSummary' => true,
        // 'pageSummaryRowOptions' => ['class' => 'kv-page-summary success', 'style' => 'text-align:right'],
        'pjax' => true,
        'striped' => true,
        'hover' => false,
        'panel' => ['type' => 'primary', 'heading' => 'Data Realisasi Kinerja'],
        'toggleDataContainer' => ['class' => 'btn-group mr-2'],
        'toolbar' => false,
        'columns' => [
            // ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'nama_program',
                'label' => 'Rincian Menu',
                'group' => true,  // enable grouping
                'contentOptions' => ['style' => 'font-size:11px;'],
                // 'pageSummaryOptions' => ['colspan' => '3', 'append' => 'Total', 'style' => 'text-align:right'],
                // 'groupFooter' => function ($model, $key, $index, $widget) { // Closure method
                //     return [
                //         'mergeColumns' => [[0,2]], // columns to merge in summary
                //         'content' => [             // content to show in each summary cell
                //             1 => 'Jumlah per Komponen',
                //             3 => GridView::F_SUM,
                //             5 => GridView::F_SUM,
                //         ],
                //         'contentFormats' => [      // content reformatting for each summary cell
                //             3 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                //             5 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                //         ],
                //         'contentOptions' => [      // content html attributes for each summary cell
                //             1 => ['style' => 'text-align:right'],
                //             3 => ['style' => 'font-size:11px; text-align:right'],
                //             5 => ['style' => 'font-size:11px; text-align:right'],
                //         ],
                //         // html attributes for group summary row
                //         'options' => ['class' => 'info table-info','style' => 'font-weight:bold; text-align:right; font-size:11px;']
                //     ];
                // }
            ],
            [
                'attribute' => 'nama_pelayanan',
                'label' => 'Komponen',
                'group' => true,  // enable grouping
                'subGroupOf' => 0,// supplier column index is the parent group,
                'contentOptions' => ['style' => 'font-size:11px;'],
                // 'pageSummaryOptions' => ['colspan' => '10', 'append' => 'Total', 'style' => 'text-align:right'],
                // 'groupFooter' => function ($model, $key, $index, $widget) { // Closure method
                //     return [
                //         'mergeColumns' => [[1,2]], // columns to merge in summary
                //         'content' => [             // content to show in each summary cell
                //             2 => 'Jumlah per Kegiatan',
                //             3 => GridView::F_SUM,
                //             5 => GridView::F_SUM,
                //         ],
                //         'contentFormats' => [      // content reformatting for each summary cell
                //             3 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                //             5 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                //         ],
                //         'contentOptions' => [      // content html attributes for each summary cell
                //             2 => ['style' => 'text-align:right'],
                //             3 => ['style' => 'font-size:11px; text-align:right'],
                //             5 => ['style' => 'font-size:11px; text-align:right'],
                //         ],
                //         // html attributes for group summary row
                //         'options' => ['class' => 'info table-info','style' => 'font-weight:bold; text-align:right; font-size:11px;']
                //     ];
                // }
            ],
            [
                'attribute' => 'nama_kegiatan',
                'label' => 'Kegiatan',
                'group' => true,  // enable grouping
                'contentOptions' => ['style' => 'font-size:11px;']
            ],
            [
                'attribute' => 'bentuk_kegiatan',
                'label' => 'Bentuk Kegiatan',
                // 'group' => true,  // enable grouping
                'contentOptions' => ['style' => 'font-size:11px;']
            ],
            [
                'label' => 'Target Awal',
                'attribute' =>'target_awal',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right'],
                'format'=>['decimal',0],
                'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            ],
            [
                'attribute' => 'satuan_awal',
                'label' => 'Satuan ',
                'contentOptions' => ['style' => 'font-size:11px;']
            ],
            [
                'label' => 'Realisasi',
                'attribute' =>'target_real',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right'],
                'format'=>['decimal',0],
                'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            ],
            [
                'label' => '%',
                'attribute' =>'prosentase',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right'],
                'format'=>['decimal',0],
                'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            ],
        ],
    ]); ?>
<?php Pjax::end() ?>