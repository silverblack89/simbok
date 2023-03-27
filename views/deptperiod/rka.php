<?php

use yii\helpers\Html;
// use yii\grid\GridView;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\web\Session;
use app\models\Deptprogram;
use app\models\Deptsubactivity;
use app\models\Sumberdana;
use app\models\Dpa;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProgramSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$session = Yii::$app->session;
$this->title = 'Data RKA APBD';
if (Yii::$app->user->identity->group_id == 'ADM'){
    // $this->params['breadcrumbs'][] = ['label' => 'Data POA Puskesmas '.$session['periodValue'], 'url' => ['period/list', 'period' => $session['periodValue']]];
    $this->params['breadcrumbs'][] = ['label' => $session['deptPeriodValue']. $session['poaLabel'], 'url' => ['deptperiod/list', 'period' => $session['deptPeriodValue']]];
}else{
    $this->params['breadcrumbs'][] = ['label' => 'POA '.$session['deptPeriodValue'], 'url' => ['deptperiod/create']];   
}
$this->params['breadcrumbs'][] = $this->title;
?>

<h1></h1>
<div class="row">
    <div class="col-md-3">
        <?= Html::dropDownList('sumber', null, ArrayHelper::map(Sumberdana::find()->all(),'id','nama'),
        [
            'id' => 'sumber',
            'options'=>[$session['sumber']=>['Selected'=>true]],
            'prompt'=>'Pilih Sumber Dana',
            'style' => 'font-weight:bold !important;', 
            // 'onchange'=>'
            //     $.pjax.reload({
            //         url: "'.Url::to(['rekap-dpa-detail']).'?id="+$(this).val(),
            //         container: "#pjax-gridvie,
            //         timeout: 1000,
            //     });',
            'class'=>'form-control']) 
        ?>
    </div>

    <div class="col-md-6">
    <!-- Html::dropDownList('subkegiatan', null, ArrayHelper::map(Deptsubactivity::find()->where(['IS NOT', 'kode_rekening', NULL])->all(),'id','nama_sub_kegiatan') -->
        <?= Html::dropDownList('subkegiatan', null, ArrayHelper::map(Dpa::find()->where(['tahun' => $session['deptPeriodValue']])->all(),'dept_sub_activity_id','nama'),
        [
            'id' => 'subkegiatan',
            'options'=>[$session['subkegiatan']=>['Selected'=>true]],
            'prompt'=>'Pilih Sub Kegiatan',
            'style' => 'font-weight:bold !important;', 
            // 'onchange'=>'
            //     $.pjax.reload({
            //         url: "'.Url::to(['rekap-dpa-detail']).'?id="+$(this).val(),
            //         container: "#pjax-gridvie,
            //         timeout: 1000,
            //     });',
            'class'=>'form-control']) 
        ?>
    </div>

    <div class="col-sm-1">
        <?= Html::a('<span class="glyphicon glyphicon-filter"></span> Filter', ['rka-apbd', 'cond' => 'fltr'], ['class' => 'btn btn-primary', 'id' => 'filter']) ?>
    </div>

    <div class="col-md-2 pull-right">
        <?php if($session['poaLabel'] == ' Awal' && Yii::$app->user->identity->username !== 'admin'){ ?>
            <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['exportrka'], ['title' => 'Export Excel', 'class' => 'btn btn-default pull-right']) ?>
        <?php }else{ ?>
            <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['exportrkaadm'], ['title' => 'Export Excel', 'class' => 'btn btn-default pull-right']) ?>
        <?php } ?>

        <?php if($session['poaLabel'] == ' Perubahan'){ ?>
            <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['exportrkaubah'], ['title' => 'Export Excel', 'class' => 'btn btn-default pull-right']) ?>
        <?php } ?>
    </div>
</div>

<div style="overflow-x:auto; margin-top:10px">

<?php Pjax::begin(['id' => 'week-all']) ?>
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
                'label' => 'Komponen',
                'group' => true,  // enable grouping
                'contentOptions' => ['style' => 'font-size:10px;'],
                'pageSummaryOptions' => ['colspan' => '10', 'append' => 'Total', 'style' => 'text-align:right'],
                'groupFooter' => function ($model, $key, $index, $widget) { // Closure method
                    return [
                        'mergeColumns' => [[0,10]], // columns to merge in summary
                        'content' => [             // content to show in each summary cell
                            1 => 'Jumlah per Komponen',
                            11 => GridView::F_SUM,
                        ],
                        'contentFormats' => [      // content reformatting for each summary cell
                            11 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        ],
                        'contentOptions' => [      // content html attributes for each summary cell
                            1 => ['style' => 'text-align:right'],
                            11 => ['style' => 'font-size:10px; text-align:right'],
                        ],
                        // html attributes for group summary row
                        'options' => ['class' => 'info table-info','style' => 'font-weight:bold; text-align:right; font-size:10px;']
                    ];
                }
            ],
            // [
            //     'attribute' => 'nama_kegiatan',
            //     'label' => 'Komponen',
            //     'group' => true,  // enable grouping
            //     'contentOptions' => ['style' => 'font-size:11px;']
            // ],
            [
                'attribute' => 'nama_sub_kegiatan',
                'label' => 'Kegiatan',
                'group' => true,  // enable grouping
                'subGroupOf' => 0,// supplier column index is the parent group,
                'contentOptions' => ['style' => 'font-size:10px;'],
                // 'pageSummaryOptions' => ['colspan' => '10', 'append' => 'Total', 'style' => 'text-align:right'],
                'groupFooter' => function ($model, $key, $index, $widget) { // Closure method
                    return [
                        'mergeColumns' => [[1,10]], // columns to merge in summary
                        'content' => [             // content to show in each summary cell
                            2 => 'Jumlah per Kegiatan',
                            11 => GridView::F_SUM,
                        ],
                        'contentFormats' => [      // content reformatting for each summary cell
                            11 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        ],
                        'contentOptions' => [      // content html attributes for each summary cell
                            2 => ['style' => 'text-align:right'],
                            11 => ['style' => 'font-size:10px; text-align:right'],
                        ],
                        // html attributes for group summary row
                        'options' => ['class' => 'info table-info','style' => 'font-weight:bold; text-align:right; font-size:10px;']
                    ];
                }
            ],
            [
                'attribute' => 'bentuk_kegiatan',
                'label' => 'Bentuk Kegiatan',
                // 'group' => true,  // enable grouping
                'contentOptions' => ['style' => 'font-size:10px;']
            ],
            [
                'attribute' => 'sumber_dana',
                'contentOptions' => ['style' => 'font-size:10px;']
            ],
            [
                'attribute' => 'nama_rekening',
                'label' => 'Rekening',
                // 'value' => function($model){
                //     if(empty($model['rincian'])){
                //         return $model['nama_rekening'];
                //     }else{
                //         return $model['nama_rekening'].' ('.$model['rincian'].')';
                //     }
                // },
                'contentOptions' => ['style' => 'font-size:10px;']
            ],
            [
                'attribute' => 'rincian',
                'label' => 'Rincian ',
                'contentOptions' => ['style' => 'font-size:10px;']
            ],
            [
                'label' => 'Vol 1',
                'attribute' =>'vol_1',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right'],
                'format'=>['decimal',0],
                'contentOptions' => ['style' => 'font-size:10px; text-align:right'],
            ],
            // [
            //     'attribute' => 'satuan_1',
            //     'label' => 'Satuan',
            //     'contentOptions' => ['style' => 'width: 5%'],
            //     'contentOptions' => ['style' => 'font-size:11px;']
            // ],
            [
                'label' => 'Vol 2',
                'attribute' =>'vol_2',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right'],
                // 'format'=>['decimal',0],
                'value' => function($model){
                    if (empty($model['vol_2'])){
                        return "";
                    }else{
                        return $model['vol_2'];
                    }
                },
                'contentOptions' => ['style' => 'font-size:10px; text-align:right'],
            ],
            // [
            //     'attribute' => 'satuan_2',
            //     'label' => 'Satuan',
            //     'contentOptions' => ['style' => 'width: 5%'],
            //     'contentOptions' => ['style' => 'font-size:11px;']
            // ],
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
                'contentOptions' => ['style' => 'font-size:10px; text-align:right'],
            ],
            // [
            //     'attribute' => 'satuan_3',
            //     'label' => 'Sat 3',
            //     'contentOptions' => ['style' => 'width: 5%'],
            //     'contentOptions' => ['style' => 'font-size:11px;']
            // ],
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
                'contentOptions' => ['style' => 'font-size:10px; text-align:right'],
            ],
            [
                'label' => 'Biaya',
                'attribute' =>'unit_cost',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right'],
                'format'=>['decimal',0],
                'pageSummary' => 'Total',
                'pageSummaryOptions' => ['class' => 'text-right'],
                'contentOptions' => ['style' => 'font-size:10px; text-align:right']
            ],
            [
                'label' => 'Jumlah',
                'attribute' =>'jumlah',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right'],
                'format'=>['decimal',0],
                'contentOptions' => ['style' => 'font-size:10px; text-align:right'],
                'pageSummary' => true,
                'pageSummaryFunc' => GridView::F_SUM
            ],

            // ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

<?php Pjax::end() ?>
</div>

<?php
   $js=<<< JS
   $("#filter").on("click", function (e) {
       createCookie("sumber",document.getElementById("sumber").value, "1");
       createCookie("subkegiatan",document.getElementById("subkegiatan").value, "1");
       baseUrl = window.origin;
       var link = baseUrl+"'.Url::to(['rka-apbd']).'";
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