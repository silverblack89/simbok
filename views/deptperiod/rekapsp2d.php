<?php

use yii\helpers\Html;
// use yii\grid\GridView;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\web\Session;
use yii\widgets\Pjax;

$session = Yii::$app->session;
$this->title = 'Rekap SP2D';
$this->params['breadcrumbs'][] = ['label' => 'Data POA Dinas '.$session['periodValue'], 'url' => ['deptperiod/list', 'period' => $session['deptPeriodValue']]];
$this->params['breadcrumbs'][] = $this->title;
?>

<h1></h1>
<p>
    <?= Html::dropDownList('triwulan', null, [ 1 => 'TRIWULAN I', 2 => 'TRIWULAN II', 3 => 'TRIWULAN III', 4 => 'TRIWULAN IV' ] ,
    [
        // 'prompt'=>'Pilih Periode',
        'options'=>[$session['tw']=>['Selected'=>true]],
        'style' => 'width:150px; margin-right:5px !important;', 
        'onchange'=>'
            $.pjax.reload({
                url: "'.Url::toRoute(['deptperiod/rekap-sp2d']).'?id="+$(this).val(),
                container: "#pjax-gridview",
                timeout: 1000,
            });',
        'class'=>'form-control pull-left']) 
    ?>

    <div class="row">
        <div class="col-sm-1">
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
    'panel' => ['type' => 'primary', 'heading' => 'Rekap SP2D ' .$session['deptPeriodValue']],
    'toggleDataContainer' => ['class' => 'btn-group mr-2'],
    'toolbar' => false,
    'columns' => [
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
