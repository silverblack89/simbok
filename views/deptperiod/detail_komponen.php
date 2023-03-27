<?php

use yii\helpers\Html;
// use yii\grid\GridView;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\web\Session;
use app\models\Deptprogram;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProgramSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$session = Yii::$app->session;
$this->title = 'Detail per Komponen';
if (Yii::$app->user->identity->unit_id == 'DINKES'){
    $this->params['breadcrumbs'][] = ['label' => 'Data POA '.$session['deptPeriodValue'], 'url' => ['deptperiod/list', 'period' => $session['deptPeriodValue']]];
}else{
    $this->params['breadcrumbs'][] = ['label' => 'POA '.$session['deptPeriodValue'], 'url' => ['deptperiod/create', 'p' => 'def']];   
}
$this->params['breadcrumbs'][] = $this->title;
?>

<h1></h1>
<p>
    <div class="row">
        <div class="col-sm-1">
            <?php if(Yii::$app->user->identity->username == 'admin'){ //$session['poaLabel'] == ' Awal' && ?> 
                <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['exportxlskomponen'], ['title' => 'Export Excel', 'class' => 'btn btn-default']) ?>
            <?php }else{ ?>
                <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['exportxlskomponen'], ['title' => 'Export Excel', 'class' => 'btn btn-default']) ?>
            <?php } ?>

            <?php if($session['poaLabel'] == ' Perubahan' && Yii::$app->user->identity->username == 'admin'){ ?>
                <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['exportxlsubah'], ['title' => 'Export Excel', 'class' => 'btn btn-default']) ?>
            <?php } ?>
        </div>
        <div class="col-sm-11">
            <?= Html::dropDownList('komponen', null, ArrayHelper::map(Deptprogram::find()->where(['dept_program.tahun' => $session['deptPeriodValue'], 'dept_program.aktif' => 1])
                                                                    ->all(),'id','nama_program' ),
                                                    [
                                                        'options'=>[$session['komponen']=>['Selected'=>true]],
                                                        // 'style' => 'margin-top:5px !important;', 
                                                        'prompt'=>'Pilih Upaya/Program',
                                                        'onchange'=>'
                                                            $.pjax.reload({
                                                                url: "'.Url::to(['rekap-komponen-detail']).'?id="+$(this).val(),
                                                                container: "#pjax-gridview",
                                                                timeout: 1000,
                                                            });',
                                                        'class'=>'form-control'
                                                    ]);
            ?>  
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
        'panel' => ['type' => 'primary', 'heading' => 'Data POA ' .$session['poaLabel']],
        'toggleDataContainer' => ['class' => 'btn-group mr-2'],
        'toolbar' => false,
        'columns' => [
            // ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'nama_sub_kegiatan',
                'label' => 'Kegiatan',
                'group' => true,  // enable grouping
                'contentOptions' => ['style' => 'font-size:11px;'],
                'pageSummaryOptions' => ['colspan' => '13', 'append' => 'Total', 'style' => 'text-align:right'],
                'groupFooter' => function ($model, $key, $index, $widget) { // Closure method
                    return [
                        'mergeColumns' => [[0,12]], // columns to merge in summary
                        'content' => [             // content to show in each summary cell
                            1 => 'Total per Kegiatan',
                            13 => GridView::F_SUM,
                        ],
                        'contentFormats' => [      // content reformatting for each summary cell
                            13 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        ],
                        'contentOptions' => [      // content html attributes for each summary cell
                            1 => ['style' => 'text-align:right'],
                            13 => ['style' => 'font-size:11px; text-align:right'],
                        ],
                        // html attributes for group summary row
                        'options' => ['class' => 'info table-info','style' => 'font-weight:bold; text-align:right; font-size:11px;']
                    ];
                }
            ],
            // [
            //     'attribute' => 'nama_kegiatan',
            //     'label' => 'Komponen',
            //     'group' => true,  // enable grouping
            //     'contentOptions' => ['style' => 'font-size:11px;']
            // ],
            // [
            //     'attribute' => 'nama_sub_kegiatan',
            //     'label' => 'Kegiatan',
            //     'group' => true,  // enable grouping
            //     'contentOptions' => ['style' => 'font-size:11px;']
            // ],
            [
                'attribute' => 'bentuk_kegiatan',
                'label' => 'Bentuk Kegiatan',
                'group' => true,  // enable grouping
                'subGroupOf' => 0,// supplier column index is the parent group,
                'contentOptions' => ['style' => 'font-size:11px;'],
                // 'pageSummaryOptions' => ['colspan' => '10', 'append' => 'Total', 'style' => 'text-align:right'],
                'groupFooter' => function ($model, $key, $index, $widget) { // Closure method
                    return [
                        'mergeColumns' => [[1,12]], // columns to merge in summary
                        'content' => [             // content to show in each summary cell
                            2 => 'Jumlah per Bentuk Kegiatan',
                            13 => GridView::F_SUM,
                        ],
                        'contentFormats' => [      // content reformatting for each summary cell
                            13 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        ],
                        'contentOptions' => [      // content html attributes for each summary cell
                            2 => ['style' => 'text-align:right'],
                            13 => ['style' => 'font-size:11px; text-align:right'],
                        ],
                        // html attributes for group summary row
                        'options' => ['class' => 'info table-info','style' => 'font-weight:bold; text-align:right; font-size:11px;']
                    ];
                }
            ],
            // [
            //     'attribute' => 'sasaran',
            //     'label' => 'Sasaran',
            //     'contentOptions' => ['style' => 'font-size:11px;']
            // ],
            // 'target',
            // [
            //     'attribute' => 'lokasi',
            //     'label' => 'Lokasi',
            //     'contentOptions' => ['style' => 'font-size:11px;']
            // ],
            // 'pelaksana',
            [
                'attribute' => 'nama_rekening',
                'label' => 'Rekening',
                'contentOptions' => ['style' => 'font-size:11px;']
            ],
            [
                'attribute' => 'rincian',
                'label' => 'Rincian',
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
            [
                'attribute' => 'satuan_1',
                'label' => 'Sat 1',
                'contentOptions' => ['style' => 'width: 5%'],
                'contentOptions' => ['style' => 'font-size:11px;']
            ],
            [
                'label' => 'Vol 2',
                'attribute' =>'vol_2',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right'],
                'format'=>['decimal',0],
                'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
            ],
            [
                'attribute' => 'satuan_2',
                'label' => 'Sat 2',
                'contentOptions' => ['style' => 'width: 5%'],
                'contentOptions' => ['style' => 'font-size:11px;']
            ],
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
            [
                'attribute' => 'satuan_3',
                'label' => 'Sat 3',
                'contentOptions' => ['style' => 'width: 5%'],
                'contentOptions' => ['style' => 'font-size:11px;']
            ],
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
            [
                'attribute' => 'satuan_4',
                'label' => 'Sat 4',
                'contentOptions' => ['style' => 'width: 5%'],
                'contentOptions' => ['style' => 'font-size:11px;']
            ],
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

            // ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php Pjax::end() ?>

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