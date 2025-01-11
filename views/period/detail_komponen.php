<?php

use yii\helpers\Html;
// use yii\grid\GridView;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\web\Session;
use yii\helpers\ArrayHelper;
// use yii\widgets\Pjax;
use app\models\Program;
use app\models\Service;
use app\models\Activity;
use app\models\Unit;
use app\models\Activitydatasub;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProgramSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$session = Yii::$app->session;

if(Yii::$app->user->identity->group_id == 'PKM'){
    $disabled = true;
    unset($session['puskesmas']);
    $session['puskesmas'] = substr(Yii::$app->user->identity->alias,10);
}else{
    $disabled = false;
}

$this->title = 'Detail per Komponen';
if (Yii::$app->user->identity->unit_id == 'DINKES'){
    $this->params['breadcrumbs'][] = ['label' => 'Data POA Puskesmas '.$session['periodValue'], 'url' => ['period/list', 'period' => $session['periodValue']]];
}else{
    $this->params['breadcrumbs'][] = ['label' => 'POA '.$session['periodValue'], 'url' => ['period/create', 'p' => 'def']];   
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
                        <?= Html::dropDownList('puskesmas', null, ArrayHelper::map(Unit::find()->where(['<>', 'kecamatan', ""])->orderBy('puskesmas')->all(),'puskesmas','puskesmas' ),
                            [
                                'id' => 'puskesmas',
                                'options'=>[$session['puskesmas']=>['Selected'=>true]],
                                'prompt'=>'Pilih Puskesmas',
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
                        <?= Html::dropDownList('submenu', null, ArrayHelper::map(Activitydatasub::find()->where(['tahun' => $session['periodValue']])
                                            ->all(),'id','nama' ),
                            [
                                'id' => 'submenu',
                                'options'=>[$session['submenu']=>['Selected'=>true]],
                                'prompt'=>'Pilih Sub Kegiatan DPA',
                                'class'=>'form-control'
                            ]);
                        ?>  
                    </div>
                </p>
            </div>
            <div class="row">
                <p>
                    <div class="col-sm-10">
                        <?= Html::dropDownList('program', null, ArrayHelper::map(Program::find()
                                            ->select('program.*')
                                            ->where(['program.tahun' => $session['periodValue'], 'program.aktif' => 1])
                                            ->all(),'id','nama_program' ),
                            [
                                'id' => 'program',
                                'options'=>[$session['program']=>['Selected'=>true]],
                                'prompt'=>'Pilih Menu',
                                'onchange'=>'$.post( "'.Yii::$app->urlManager->createUrl('period/get-service?id=').'"+$(this).val(), 
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
                    <?= Html::dropDownList('komponen', null, ArrayHelper::map(Service::find()->where(['program_id' => $session['program']])->all(),'id','nama_pelayanan' ),
                        [
                            'id' => 'komponen',
                            'options'=>[$session['komponen']=>['Selected'=>true]],
                            'prompt'=>'Pilih Rincian',
                            'onchange'=>'$.post( "'.Yii::$app->urlManager->createUrl('period/get-activity?id=').'"+$(this).val(), 
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
                        <?php if(isset($session['subkomponen'])){?>
                            <?= Html::dropDownList('subkomponen', null, ArrayHelper::map(Activity::find()->where(['service_id' => $session['komponen']])->all(),'id','nama_kegiatan' ),
                                [
                                    'id' => 'subkomponen',
                                    'options'=>[$session['subkomponen']=>['Selected'=>true]],
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
                        <?= Html::a('<span class="glyphicon glyphicon-filter"></span> Proses', ['rekap-pkm-detail', 'cond' => 'fltr'], ['class' => 'btn btn-primary pull-right', 
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

    <?php if(Yii::$app->user->identity->username !== 'adminx'){ //$session['poaLabel'] == ' Awal' && ?> 
        <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['exportxlsdetail'], ['title' => 'Export Excel', 'class' => 'btn btn-default']) ?>
        <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export Desk', ['exportxlsdesk25'], ['title' => 'Export Excel Desk', 'class' => 'btn btn-success', 'id' => 'expdesk']) ?>
    <?php } ?>

    <?php if($session['poaLabel'] == ' Perubahan' && Yii::$app->user->identity->username == 'admin'){ ?>
        <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['exportxlsubah'], ['title' => 'Export Excel', 'class' => 'btn btn-default']) ?>
    <?php } ?>
</p>

<?php //Pjax::begin(['id' => 'pjax-gridview']) ?>
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
                'attribute' => 'puskesmas',
                'label' => 'Puskesmas',
                'group' => true,  // enable grouping
                'contentOptions' => ['style' => 'font-size:11px;'],
                'pageSummaryOptions' => ['colspan' => '15', 'append' => 'Total', 'style' => 'text-align:right'],
                'groupFooter' => function ($model, $key, $index, $widget) { // Closure method
                    return [
                        'mergeColumns' => [[0,14]], // columns to merge in summary
                        'content' => [             // content to show in each summary cell
                            1 => 'Jumlah Puskesmas '.$model['puskesmas'],
                            15 => GridView::F_SUM,
                        ],
                        'contentFormats' => [      // content reformatting for each summary cell
                            15 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        ],
                        'contentOptions' => [      // content html attributes for each summary cell
                            1 => ['style' => 'text-align:right'],
                            15 => ['style' => 'font-size:11px; text-align:right'],
                        ],
                        // html attributes for group summary row
                        'options' => ['class' => 'info table-info','style' => 'font-weight:bold; text-align:right; font-size:11px;']
                    ];
                }
            ],
            // [
            //     'attribute' => 'nama_pelayanan',
            //     'label' => 'Komponen',
            //     'group' => true,  // enable grouping
            //     'contentOptions' => ['style' => 'font-size:11px;']
            // ],
            [
                'attribute' => 'nama_kegiatan',
                'label' => 'Komponen',
                'group' => true,  // enable grouping
                'subGroupOf' => 0,// supplier column index is the parent group,
                'contentOptions' => ['style' => 'font-size:11px;'],
                // 'pageSummaryOptions' => ['colspan' => '10', 'append' => 'Total', 'style' => 'text-align:right'],
                'groupFooter' => function ($model, $key, $index, $widget) { // Closure method
                    return [
                        'mergeColumns' => [[1,14]], // columns to merge in summary
                        'content' => [             // content to show in each summary cell
                            2 => 'Jumlah per Komponen',
                            15 => GridView::F_SUM,
                        ],
                        'contentFormats' => [      // content reformatting for each summary cell
                            15 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        ],
                        'contentOptions' => [      // content html attributes for each summary cell
                            2 => ['style' => 'text-align:right'],
                            15 => ['style' => 'font-size:11px; text-align:right'],
                        ],
                        // html attributes for group summary row
                        'options' => ['class' => 'info table-info','style' => 'font-weight:bold; text-align:right; font-size:11px;']
                    ];
                }
            ],
            [
                'attribute' => 'sub_kegiatan',
                'label' => 'Sub Kegiatan',
                // 'group' => true,  // enable grouping
                'contentOptions' => ['style' => 'font-size:11px;']
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
<?php //Pjax::end() ?>

<?php
    $js=<<< JS
    $("#expdesk").on("click", function (e) {
        if(document.getElementById("program").value == ''){
            alert('Pilih Menu tidak boleh kosong!');
            document.getElementById("program").focus();
            return false;
        }else{
            return true;
        }
    });

    $("#proses").on("click", function (e) {
        createCookie("puskesmas",document.getElementById("puskesmas").value, "1");
        createCookie("submenu",document.getElementById("submenu").value, "1");
        createCookie("program",document.getElementById("program").value, "1");
        createCookie("komponen",document.getElementById("komponen").value, "1");
        createCookie("subkomponen",document.getElementById("subkomponen").value, "1");
        baseUrl = window.origin;
        var link = baseUrl+"'.Url::to(['rekap-pkm-detail']).'";
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