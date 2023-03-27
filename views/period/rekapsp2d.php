<?php

use yii\helpers\Html;
// use yii\grid\GridView;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\web\Session;
use yii\widgets\Pjax;

$session = Yii::$app->session;
$this->title = 'Rekap SP2D';
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
        <div class="col-sm-4">
                <?= Html::dropDownList('triwulan', null, [ 21 => 'TRIWULAN I', 22 => 'TRIWULAN II', 23 => 'TRIWULAN III', 24 => 'TRIWULAN IV', 0 => 'TAHUNAN', 
                1 => 'JANUARI',
                2 => 'FEBRUARI',
                3 => 'MARET',
                4 => 'APRIL',
                5 => 'MEI',
                6 => 'JUNI',
                7 => 'JULI',
                8 => 'AGUSTUS',
                9 => 'SEPTEMBER',
                10 => 'OKTOBER',
                11 => 'NOVEMBER',
                12 => 'DESEMBER'
            ] ,
            [
                // 'prompt'=>'Pilih Periode',
                'options'=>[$session['tw']=>['Selected'=>true]],
                // 'style' => 'width:150px; margin-right:5px !important;', 
                'onchange'=>'
                    $.pjax.reload({
                        url: "'.Url::toRoute(['period/rekap-sp2d']).'?id="+$(this).val(),
                        container: "#pjax-gridview",
                        timeout: 1000,
                    });',
                'class'=>'form-control']) 
            ?>
        </div>
        <div class="col-sm-8">
            <?php if(Yii::$app->user->identity->username == 'admin'){ //$session['poaLabel'] == ' Awal' && ?> 
                <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['export-rekap-sp2d'], ['title' => 'Export Excel', 'class' => 'btn btn-default']) ?>
            <?php }else{ ?>
                <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['export-rekap-sp2d'], ['title' => 'Export Excel', 'class' => 'btn btn-default']) ?>
            <?php } ?>

            <?php if($session['poaLabel'] == ' Perubahan' && Yii::$app->user->identity->username == 'admin'){ ?>
                <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['export-rekap-sp2d'], ['title' => 'Export Excel', 'class' => 'btn btn-default']) ?>
            <?php } ?>
        </div>
    </div>
</p>

<?php Pjax::begin(['id' => 'pjax-gridview']) ?>
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
    'panel' => ['type' => 'primary', 'heading' => 'Rekap SP2D ' .$session['periodValue']],
    'toggleDataContainer' => ['class' => 'btn-group mr-2'],
    'toolbar' => false,
    'columns' => [
        [
            'attribute' => 'puskesmas',
            'label' => 'Puskesmas',
            'group' => true,
            'contentOptions' => ['style' => 'font-size:11px;'],
            'pageSummaryOptions' => ['colspan' => '5', 'append' => 'Total', 'style' => 'text-align:right'],
            'groupFooter' => function ($model, $key, $index, $widget) { // Closure method
                return [
                    'mergeColumns' => [[0,4]], // columns to merge in summary
                    'content' => [             // content to show in each summary cell
                        1 => 'Jumlah per Puskesmas',
                        5 => GridView::F_SUM,
                    ],
                    'contentFormats' => [      // content reformatting for each summary cell
                        5 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                    ],
                    'contentOptions' => [      // content html attributes for each summary cell
                        1 => ['style' => 'text-align:right'],
                        5 => ['style' => 'font-size:11px; text-align:right'],
                    ],
                    // html attributes for group summary row
                    'options' => ['class' => 'info table-info','style' => 'font-weight:bold; text-align:right; font-size:11px;']
                ];
            }
        ],
        [
            'attribute' => 'tanggal',
            'label' => 'Tanggal',
            'contentOptions' => ['style' => 'font-size:11px;width:7%']
        ],
        [
            'attribute' => 'no_sp2d',
            'label' => 'No SP2D',
            'contentOptions' => ['style' => 'font-size:11px;']
        ],
        [
            'attribute' => 'jenis_spm',
            'label' => 'Jenis SPM ',
            'contentOptions' => ['style' => 'font-size:11px;']
        ],
        [
            'attribute' => 'uraian',
            'label' => 'Uraian ',
            'contentOptions' => ['style' => 'font-size:11px;']
        ],
        [
            'label' => 'Jumlah',
            'attribute' =>'jumlah',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => true,
            // 'pageSummaryFunc' => GridView::F_SUM
        ],
    ],
]); ?>
<?php Pjax::end() ?>
