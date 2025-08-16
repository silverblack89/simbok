<?php

use yii\helpers\Html;
// use yii\grid\GridView;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\web\Session;
use app\models\Deptprogram;
use app\models\Deptactivity;
use app\models\Deptsubactivity;
use app\models\Unit;
use yii\helpers\ArrayHelper;
// use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProgramSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$session = Yii::$app->session;

if(Yii::$app->user->identity->group_id == 'SEK'){
    $disabled = true;
    unset($session['seksi']);
    $session['seksi'] = strtoupper(Yii::$app->user->identity->username);
}else{
    $disabled = false;
}

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
<div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">Filter Data</h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <p>
                    <div class="col-sm-3">
                        <?= Html::dropDownList('seksi', null, ArrayHelper::map(Unit::find()->where(['IS', 'kecamatan', NULL])->orderBy('puskesmas')->all(),'id','puskesmas' ),
                            [
                                'id' => 'seksi',
                                'options'=>[$session['seksi']=>['Selected'=>true]],
                                'prompt'=>'Pilih Seksi',
                                'class'=>'form-control',
                                'disabled' => $disabled
                            ]);
                        ?>  
                    </div>
                </p>
            </div>
            <div class="row">
                <p>
                    <div class="col-sm-10">
                        <?= Html::dropDownList('program', null, ArrayHelper::map(Deptprogram::find()
                                            ->select('dept_program.*')
                                            ->where(['dept_program.tahun' => $session['deptPeriodValue'], 'dept_program.aktif' => 1])
                                            ->all(),'id','nama_program' ),
                            [
                                'id' => 'program',
                                'options'=>[$session['deptprogram']=>['Selected'=>true]],
                                'prompt'=>'Pilih Menu',
                                'onchange'=>'$.post( "'.Yii::$app->urlManager->createUrl('deptperiod/get-service?id=').'"+$(this).val(), 
                                            function( data ) {
                                                // alert(data);
                                                $( "select#komponen" ).html( data );
                                                $( "select#subkomponen" ).empty();
                                                $( "select#subkomponen" ).append("<option>Pilih Komponen</option>");
                                            });', 
                                'class'=>'form-control'
                            ]);
                        ?>  
                    </div>
                </p>
            </div>
            <div class="row">
                <p>
                    <div class="col-sm-10">
                    <?= Html::dropDownList('komponen', null, ArrayHelper::map(Deptactivity::find()->where(['dept_program_id' => $session['deptprogram']])->all(),'id','nama_kegiatan' ),
                        [
                            'id' => 'komponen',
                            'options'=>[$session['deptactivity']=>['Selected'=>true]],
                            'prompt'=>'Pilih Rincian',
                            'onchange'=>'$.post( "'.Yii::$app->urlManager->createUrl('deptperiod/get-activity?id=').'"+$(this).val(), 
                                        function( data ) {
                                            // alert(data);
                                            $( "select#subkomponen" ).html( data );
                                        });', 
                            'class'=>'form-control'
                        ]);
                    ?>  
                    </div>
                </p>
            </div>
            <div class="row">
                <p>
                    <div class="col-sm-10">
                        <?php if(isset($session['deptactivity'])){?>
                            <?= Html::dropDownList('subkomponen', null, ArrayHelper::map(Deptsubactivity::find()->where(['dept_activity_id' => $session['deptactivity']])->all(),'id','nama_sub_kegiatan' ),
                                [
                                    'id' => 'subkomponen',
                                    'options'=>[$session['deptsubactivity']=>['Selected'=>true]],
                                    'prompt'=>'Pilih Komponen',
                                    'class'=>'form-control'
                                ]);
                            ?>  
                        <?php }else{ ?>
                            <?= Html::dropDownList('subkomponen', null, [],
                                [
                                    'id' => 'subkomponen',
                                    'prompt'=>'Pilih Komponen',
                                    'class'=>'form-control'
                                ]);
                            ?>  
                        <?php } ?>
                    </div>
                </p>
                <p>
                    <div class="col-lg-1 pull-right">
                        <?= Html::a('<span class="glyphicon glyphicon-filter"></span> Proses', ['rekap-komponen-detail', 'cond' => 'fltr'], ['class' => 'btn btn-primary pull-right', 
                        'id' => 'proses',
                        'onchange'=>'
                            $.pjax.reload({
                                container: "#detail",
                                timeout: false,
                            });'
                        ]) ?>
                    </div>
                </p>
            </div>
        </div>
    </div>

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
    </div>
</p>

<?php //Pjax::begin(['id' => 'pjax-gridview']) ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        // 'options' => ['style' => 'font-size:11px;'],
        'id' => 'GridView',
        'showPageSummary' => true,
        'pageSummaryRowOptions' => ['class' => 'kv-page-summary success', 'style' => 'text-align:right'],
        // 'pjax' => true,
        'striped' => true,
        'hover' => false,
        'panel' => ['type' => 'primary', 'heading' => 'Data POA ' .$session['poaLabel']],
        'toggleDataContainer' => ['class' => 'btn-group mr-2'],
        'toolbar' => false,
        'columns' => [
            // ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'nama_kegiatan',
                'label' => 'Rincian Menu',
                'group' => true,  // enable grouping
                'contentOptions' => ['style' => 'font-size:11px;'],
                'pageSummaryOptions' => ['colspan' => '14', 'append' => 'Total', 'style' => 'text-align:right'],
                'groupFooter' => function ($model, $key, $index, $widget) { // Closure method
                    return [
                        'mergeColumns' => [[0,13]], // columns to merge in summary
                        'content' => [             // content to show in each summary cell
                            1 => 'Jumlah per Rincian Menu',
                            14 => GridView::F_SUM,
                        ],
                        'contentFormats' => [      // content reformatting for each summary cell
                            14 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        ],
                        'contentOptions' => [      // content html attributes for each summary cell
                            1 => ['style' => 'text-align:right'],
                            14 => ['style' => 'font-size:11px; text-align:right'],
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
                'subGroupOf' => 0,// supplier column index is the parent group
                'contentOptions' => ['style' => 'font-size:11px;'],
                // 'pageSummaryOptions' => ['colspan' => '13', 'append' => 'Total', 'style' => 'text-align:right'],
                'groupFooter' => function ($model, $key, $index, $widget) { // Closure method
                    return [
                        'mergeColumns' => [[1,13]], // columns to merge in summary
                        'content' => [             // content to show in each summary cell
                            2 => 'Total per Komponen',
                            14 => GridView::F_SUM,
                        ],
                        'contentFormats' => [      // content reformatting for each summary cell
                            14 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        ],
                        'contentOptions' => [      // content html attributes for each summary cell
                            2 => ['style' => 'text-align:right'],
                            14 => ['style' => 'font-size:11px; text-align:right'],
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
                // 'group' => true,  // enable grouping
                // 'subGroupOf' => 0,// supplier column index is the parent group,
                'contentOptions' => ['style' => 'font-size:11px;'],
                // 'pageSummaryOptions' => ['colspan' => '10', 'append' => 'Total', 'style' => 'text-align:right'],
                // 'groupFooter' => function ($model, $key, $index, $widget) { // Closure method
                //     return [
                //         'mergeColumns' => [[1,12]], // columns to merge in summary
                //         'content' => [             // content to show in each summary cell
                //             2 => 'Jumlah per Bentuk Kegiatan',
                //             13 => GridView::F_SUM,
                //         ],
                //         'contentFormats' => [      // content reformatting for each summary cell
                //             13 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                //         ],
                //         'contentOptions' => [      // content html attributes for each summary cell
                //             2 => ['style' => 'text-align:right'],
                //             13 => ['style' => 'font-size:11px; text-align:right'],
                //         ],
                //         // html attributes for group summary row
                //         'options' => ['class' => 'info table-info','style' => 'font-weight:bold; text-align:right; font-size:11px;']
                //     ];
                // }
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
            [
                'attribute' => 'sub_dpa',
                'label' => 'Sub Kegiatan DPA',
                'contentOptions' => ['style' => 'font-size:11px;']
            ],
            [
                'attribute' => 'puskesmas',
                'label' => 'Seksi',
                'contentOptions' => ['style' => 'font-size:11px;width: 5%;']
            ],

            // ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php //Pjax::end() ?>

<?php
    $js=<<< JS
    $("#proses").on("click", function (e) {
        createCookie("seksi",document.getElementById("seksi").value, "1");
        createCookie("program",document.getElementById("program").value, "1");
        createCookie("komponen",document.getElementById("komponen").value, "1");
        createCookie("subkomponen",document.getElementById("subkomponen").value, "1");
        baseUrl = window.origin;
        var link = baseUrl+"'.Url::to(['rekap-komponen-detail']).'";
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
// $this->registerJs($js, yii\web\View::POS_HEAD);
?>