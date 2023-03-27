<?php

use yii\helpers\Html;
// use yii\grid\GridView;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\web\Session;
use app\models\Activitydatasub;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProgramSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$session = Yii::$app->session;
$this->title = 'Detail per Sub Kegiatan';
if (Yii::$app->user->identity->unit_id == 'DINKES'){
    $this->params['breadcrumbs'][] = ['label' => 'Data POA '.$session['periodValue'], 'url' => ['period/list', 'period' => $session['periodValue']]];
}else{
    $this->params['breadcrumbs'][] = ['label' => 'POA '.$session['periodValue'], 'url' => ['period/create', 'p' => 'def']];   
}
$this->params['breadcrumbs'][] = $this->title;
?>

<h1></h1>
<p>
    <div class="row">
        <div class="col-sm-1">
            <?php if(Yii::$app->user->identity->username == 'admin'){ //$session['poaLabel'] == ' Awal' && ?> 
                <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['exportxlssub'], ['title' => 'Export Excel', 'class' => 'btn btn-default']) ?>
            <?php }else{ ?>
                <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['exportxlssub'], ['title' => 'Export Excel', 'class' => 'btn btn-default']) ?>
            <?php } ?>

            <?php if($session['poaLabel'] == ' Perubahan' && Yii::$app->user->identity->username == 'admin'){ ?>
                <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['exportxlsubah'], ['title' => 'Export Excel', 'class' => 'btn btn-default']) ?>
            <?php } ?>
        </div>
        <div class="col-sm-11">
            <?= Html::dropDownList('komponen', null, ArrayHelper::map(Activitydatasub::find()->where(['tahun' => $session['periodValue']])
                                                                    ->all(),'id','nama' ),
                                                    [
                                                        'options'=>[$session['subKegiatan']=>['Selected'=>true]],
                                                        // 'style' => 'margin-top:5px !important;', 
                                                        'prompt'=>'Pilih Sub Kegiatan',
                                                        'onchange'=>'
                                                            $.pjax.reload({
                                                                url: "'.Url::to(['sub-kegiatan']).'?id="+$(this).val(),
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
                'attribute' => 'nama_kegiatan',
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