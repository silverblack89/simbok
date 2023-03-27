<?php

use yii\helpers\Html;
// use yii\grid\GridView;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\web\Session;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use app\models\Bok;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProgramSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$session = Yii::$app->session;
$this->title = 'Rekap Realisasi per Upaya';
$this->params['breadcrumbs'][] = ['label' => 'Data POA '.$session['deptPeriodValue'], 'url' => ['deptperiod/list', 'period' => $session['deptPeriodValue']]];
$this->params['breadcrumbs'][] = $this->title;
?>

<h1></h1>
<p>
    <div class="row">
        <div class="col-sm-2">
            <?= Html::dropDownList('triwulan', null, [ 1 => 'TRIWULAN I', 2 => 'TRIWULAN II', 3 => 'TRIWULAN III', 4 => 'TRIWULAN IV' ] ,
            [
                'id' => 'tw',
                // 'prompt'=>'Pilih Periode',
                'options'=>[$session['tw']=>['Selected'=>true]],
                // 'style' => 'width:150px; margin-right:5px !important;', 
                // 'onchange'=>'
                //     $.pjax.reload({
                //         url: "'.Url::toRoute(['deptperiod/rekap-real']).'?id="+$(this).val(),
                //         container: "#pjax-gridview",
                //         timeout: 1000,
                //     });',
                'class'=>'form-control']) 
            ?>
        </div>
        <div class="col-sm-4">
            <?= Html::dropDownList('bok', null, ArrayHelper::map(Bok::find()->where(['<>', 'id', 2])->all(),'id','keterangan'),
            [
                'id' => 'bok',
                'options'=>[$session['bok']=>['Selected'=>true]],
                // 'prompt'=>'Pilih Menu Kegiatan',
                // 'style' => 'width:300px; font-weight:bold !important;', 
                // 'onchange'=>'
                //     $.pjax.reload({
                //         url: "'.Url::to(['deptperiod/rekap-real', 'id' => 1]).'&bok="+$(this).val(),
                //         container: "#pjax-gridview,
                //         timeout: 1000,
                //     });
                //     ',
                'class'=>'form-control']) 
            ?>
        </div>
        <div class="col-sm-1">
            <?= Html::a('<span class="glyphicon glyphicon-filter"></span> Filter', ['rekap-real', 'cond' => 'fltr'], ['class' => 'btn btn-primary', 'id' => 'filter']) ?>
        </div>
        <div class="col-sm-5">
            <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['export-rekap-real'], ['title' => 'Export Excel', 'class' => 'btn btn-default pull-right']) ?>
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
    'panel' => ['type' => 'primary', 'heading' => 'Rekap Realisasi ' .$session['poaLabel']],
    'toggleDataContainer' => ['class' => 'btn-group mr-2'],
    'toolbar' => false,
    'columns' => [
        // ['class' => 'yii\grid\SerialColumn'],
        [
            'attribute' => 'nama_program',
            'label' => 'Rinci Menu Kegiatan',
            'group' => true,  // enable grouping
            'contentOptions' => ['style' => 'font-size:11px;'],
            'pageSummaryOptions' => ['colspan' => '17', 'append' => 'Total', 'style' => 'text-align:right'],
            'groupFooter' => function ($model, $key, $index, $widget) { // Closure method
                return [
                    'mergeColumns' => [[0,16]], // columns to merge in summary
                    'content' => [             // content to show in each summary cell
                        1 => 'Jumlah',
                        17 => GridView::F_SUM,
                    ],
                    'contentFormats' => [      // content reformatting for each summary cell
                        17 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                    ],
                    'contentOptions' => [      // content html attributes for each summary cell
                        1 => ['style' => 'text-align:right'],
                        17 => ['style' => 'font-size:11px; text-align:right'],
                    ],
                    // html attributes for group summary row
                    'options' => ['class' => 'info table-info','style' => 'font-weight:bold; text-align:right; font-size:11px;']
                ];
            }
        ],
        [
            'attribute' => 'nama_kegiatan',
            'label' => 'Komponen',
            // 'group' => true,  // enable grouping
            'contentOptions' => ['style' => 'font-size:11px;']
        ],
        [
            'label' => 'Umpeg',
            'attribute' =>'umpeg',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM
        ],
        [
            'label' => 'Farmamin',
            'attribute' =>'farmamin',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM
        ],
        [
            'label' => 'Kesga',
            'attribute' =>'kesga',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM
        ],
        [
            'label' => 'Kesling',
            'attribute' =>'kesling',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM
        ],
        [
            'label' => 'Yankesruj',
            'attribute' =>'yankesruj',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM
        ],
        [
            'label' => 'Yankestrad',
            'attribute' =>'yankestrad',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM
        ],
        [
            'label' => 'Keuset',
            'attribute' =>'keuset',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM
        ],
        [
            'label' => 'Yankesprim',
            'attribute' =>'yankesprim',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM
        ],
        [
            'label' => 'P2PM',
            'attribute' =>'p2pm',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM
        ],
        [
            'label' => 'P2PTM',
            'attribute' =>'p2ptm',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM
        ],
        [
            'label' => 'Perbekes',
            'attribute' =>'perbekes',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM
        ],
        [
            'label' => 'Promkes',
            'attribute' =>'promkes',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM
        ],
        [
            'label' => 'Renlap',
            'attribute' =>'renlap',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM
        ],
        [
            'label' => 'Sdmk',
            'attribute' =>'sdmk',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM
        ],
        [
            'label' => 'Surveilans',
            'attribute' =>'surveilans',
            'enableSorting' => false,
            'contentOptions' => ['class' => 'col-lg-1 text-right'],
            'format'=>['decimal',0],
            'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM
        ],
        [
            'label' => 'Total',
            'attribute' =>'total',
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
<?php Pjax::end() ?>

<?php
    $js=<<< JS
    $("#filter").on("click", function (e) {
        createCookie("tw",document.getElementById("tw").value, "1");
        createCookie("bok",document.getElementById("bok").value, "1");
        baseUrl = window.origin;
        var link = baseUrl+"'.Url::to(['rekap-real']).'";
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
JS;
$this->registerJs($js, yii\web\View::POS_READY);
$this->registerJs($js, yii\web\View::POS_HEAD);
?>