<?php

use yii\helpers\Html;
// use yii\grid\GridView;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\bootstrap\Button;
use yii\web\Session;
use app\models\Bok;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProgramSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$session = Yii::$app->session;
$this->title = 'Data POA '.$namaUnit;
if (Yii::$app->user->identity->group_id == 'ADM'){
    $visible = true;
    // $this->params['breadcrumbs'][] = ['label' => 'Data POA Puskesmas '.$session['periodValue'], 'url' => ['period/list', 'period' => $session['periodValue']]];
    $this->params['breadcrumbs'][] = ['label' => $session['deptPeriodValue']. $session['poaLabel'], 'url' => ['deptperiod/list', 'period' => $session['deptPeriodValue']]];
}else{
    $visible = false;
    $this->params['breadcrumbs'][] = ['label' => 'POA '.$session['deptPeriodValue'], 'url' => ['deptperiod/create']];   
}
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class="row">
        <div class="col-md-4">
    <?php if(Yii::$app->user->identity->group_id == 'SEK') {?>
        <?= Html::dropDownList('bok', null, ArrayHelper::map(Bok::find()->where(['<>', 'id', 2])->all(),'id','keterangan'),
        [
            'options'=>[$session['bok']=>['Selected'=>true]],
            'prompt'=>'Pilih Menu Kegiatan',
            'onchange'=>'
                $.pjax.reload({
                    url: "'.Url::to(['deptperiod/datapoa']).'?id="+$(this).val(),
                    container: "#week-all",
                    timeout: 1000,
                });',
            'class'=>'form-control']) 
        ?>
    <?php }else{ ?>
        <?= Html::dropDownList('bok', null, ArrayHelper::map(Bok::find()->where(['<>', 'id', 2])->all(),'id','keterangan'),
        [
            'options'=>[$session['bok']=>['Selected'=>true]],
            'prompt'=>'Pilih Menu Kegiatan',
            'onchange'=>'
                $.pjax.reload({
                    url: "'.Url::to(['deptperiod/datapoaadm', 'id' => $id]).'&bok="+$(this).val(),
                    container: "#week-all",
                    timeout: 1000,
                });',
            'class'=>'form-control']) 
        ?>
    <?php } ?>
        </div>
        <div class="col-md-4">
            <?php if($session['poaLabel'] == ' Awal' && Yii::$app->user->identity->username !== 'admin'){ ?>
                <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['exportxls'], ['title' => 'Export Excel', 'class' => 'btn btn-default']) ?>
                <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export Desk', ['exportxlsdesk'], ['title' => 'Export Excel Desk', 'class' => 'btn btn-success']) ?>       
            <?php }else{ ?>
                <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['exportxlsadm', 'unit_id' => $id], ['title' => 'Export Excel', 'class' => 'btn btn-default']) ?>
                <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export Desk', ['exportxlsdesk'], ['title' => 'Export Excel Desk', 'class' => 'btn btn-success']) ?>
            <?php } ?>

            <?php if($session['poaLabel'] == ' Perubahan'){ ?>
                <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export', ['exportxlsubah'], ['title' => 'Export Excel', 'class' => 'btn btn-default']) ?>
            <?php } ?>
        </div>
    </div>

<div style="overflow-x:auto; margin-top:10px">

<?php //Pjax::begin(['id' => 'week-all']) ?>
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
                'id'=>'week-all',
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
                'attribute' => 'nama_kegiatan',
                'label' => 'Komponen',
                'group' => true,  // enable grouping
                'contentOptions' => ['style' => 'font-size:11px;'],
                'pageSummaryOptions' => ['colspan' => '10', 'append' => 'Total', 'style' => 'text-align:right'],
                'groupFooter' => function ($model, $key, $index, $widget) { // Closure method
                    return [
                        'mergeColumns' => [[0,9]], // columns to merge in summary
                        'content' => [             // content to show in each summary cell
                            1 => 'Jumlah per Komponen',
                            10 => GridView::F_SUM,
                        ],
                        'contentFormats' => [      // content reformatting for each summary cell
                            10 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        ],
                        'contentOptions' => [      // content html attributes for each summary cell
                            1 => ['style' => 'text-align:right'],
                            10 => ['style' => 'font-size:11px; text-align:right'],
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
            [
                'attribute' => 'nama_sub_kegiatan',
                'label' => 'Sub komponen',
                'group' => true,  // enable grouping
                'subGroupOf' => 0,// supplier column index is the parent group,
                'contentOptions' => ['style' => 'font-size:11px;'],
                // 'pageSummaryOptions' => ['colspan' => '10', 'append' => 'Total', 'style' => 'text-align:right'],
                'groupFooter' => function ($model, $key, $index, $widget) { // Closure method
                    return [
                        'mergeColumns' => [[1,9]], // columns to merge in summary
                        'content' => [             // content to show in each summary cell
                            2 => 'Jumlah per Kegiatan',
                            10 => GridView::F_SUM,
                        ],
                        'contentFormats' => [      // content reformatting for each summary cell
                            10 => ['format' => 'number', 'decimals' => 0, 'decPoint'=>',', 'thousandSep'=>'.'],
                        ],
                        'contentOptions' => [      // content html attributes for each summary cell
                            2 => ['style' => 'text-align:right'],
                            10 => ['style' => 'font-size:11px; text-align:right'],
                        ],
                        // html attributes for group summary row
                        'options' => ['class' => 'info table-info','style' => 'font-weight:bold; text-align:right; font-size:11px;']
                    ];
                }
            ],
            [
                'attribute' => 'bentuk_kegiatan',
                'label' => 'Bentuk Kegiatan',
                // 'group' => true,  // enable grouping
                'contentOptions' => ['style' => 'font-size:11px;']
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
                'contentOptions' => ['style' => 'font-size:11px;']
            ],
            [
                'attribute' => 'rincian',
                'label' => 'Rincian ',
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
                'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
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
                'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
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
                'contentOptions' => ['style' => 'font-size:11px; text-align:right'],
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
            // [
            //     'label' => 'Seksi',
            //     'attribute' => 'unit_id',
            // ],

            ['class' => 'yii\grid\ActionColumn',
            'header' => 'Seksi',
            'contentOptions' => ['style' => 'width: 5%;text-align:center'],
            'template' => '{user}',
            'visible' => $visible,
            'buttons' => [
                'user' => function ($url, $model) {
                    // return Html::a('<span class="glyphicon glyphicon-pencil"></span> '.$model['unit_id'], array('deptsubactivitydata/update', 'id'=>$model['id'], 'modul' => 'new', 'mid' => 0), ['class'=>'btn btn-xs btn-warning custom_button']);
                    return Html::button('<span class="glyphicon glyphicon-pencil"></span> '.$model['unit_id'], ['value' => Url::to(['deptsubactivitydata/update', 'id' => $model['id'], 'modul' => 'usr', 'mid' => 0]), 'title' => 'Ubah User', 'class' => 'showModalButton btn btn-xs btn-warning custom_button']);
                },
            ]
        ],

            // ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

<?php //Pjax::end() ?>
</div>

<?php
    Modal::begin([
        // 'header'=>'<h4>Detail Kegiatan</h4>', 
        'id'=>'modal',
        'size'=>'modal-md',
        'clientOptions' => ['backdrop' => 'static', 'keyboard' => FALSE],
        // 'footer' => ''
    ]);
    echo "<div id='modalContent'></div>";
    Modal::end();

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