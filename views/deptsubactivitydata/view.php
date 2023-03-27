<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
// use yii\grid\GridView;
use kartik\grid\GridView;
use yii\bootstrap\Progress;
use yii\web\Session;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\bootstrap\Button;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Deptsubactivitydata */

$this->title = 'Detail Kegiatan';
$this->params['breadcrumbs'][] = ['label' => $session['deptPeriodValue'], 'url' => ['deptperiod/create']];
$this->params['breadcrumbs'][] = ['label' => 'Rincian Menu Kegiatan', 'url' => ['deptprogram/list']];
$this->params['breadcrumbs'][] = ['label' => 'Komponen', 'url' => ['deptactivity/list', 'id' => $session['deptProgramId']]];
$this->params['breadcrumbs'][] = ['label' => 'Kegiatan', 'url' => ['deptsubactivity/list', 'id' => $session['deptActivityId']]];
$this->params['breadcrumbs'][] = ['label' => 'Bentuk Kegiatan', 'url' => ['deptsubactivitydata/list', 'id' => $session['deptSubActivityId']]];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="deptsubactivitydata-view">

    <h1><?= Html::encode($session['deptSubActivityDataName']) ?></h1>

    <?php
        echo Progress::widget([
            'bars' => [
                ['percent' => $session['prosentase'], 'label' => $session['prosentase'].'% dari Pagu BOK (RP. ' .number_format($session['pagu'], 0, ',', '.'). ')', 'options' => ['class' => $session['barColor']]],
            ],
            'options' => ['class' => $session['barStatus']]
        ]);

        if($session['status_poa']!=='disabled'){
            $template = '{update} {delete}';
            $width = 'width: 25%';
        }else{
            if($session['revisi_poa'] == 1){
                $template = '{update} {delete}';
                $width = 'width: 25%';
            }else{
                if($session['status_real']!=='disabled'){
                    $template = ''; //{realisasi}
                    $width = 'width: 7%';
                }else{
                    $template = '';
                    $width = 'width: 1%';
                }
            }
        }
    ?>

    <!-- <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p> -->

    <p>
        <?php if($session['status_poa']!=='disabled'){ ?>
            <!-- <?= Html::a('<span class="glyphicon glyphicon-transfer"></span> Pindah', ['update', 'id' => $model->id, 'modul' => 'program', 'mid' => 0], ['class' => 'btn btn-primary']) ?> -->
            <?= Html::button('<span class="glyphicon glyphicon-transfer"></span> Pindah Data', 
                ['value' => Url::to(['deptsubactivitydata/update', 'id' => $model->id, 'modul' => 'program', 'mid' => 0]), 'title' => 'Pindah Data Kegiatan', 'class' => 'showModalButton btn btn-primary']) ?>
        <?php 
        } ?>
    </p>
    
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">INFORMASI KEGIATAN</h3>
        </div>
        <div class="panel-body">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    [
                        'attribute' => 'indikator_hasil',
                        'contentOptions' => ['style' => 'width: 80%'],
                    ],
                    [
                        'label' => 'Target',
                        'value' => function($model){
                            if(!empty($model->target)){
                                return $model->target.' '.$model->satuan;
                            }else{
                                return $model->target_hasil;
                            }
                        }
                    ],
                    // 'indikator_keluaran',
                    // 'target_keluaran',
                ],
            ]) ?>
        </div>
    </div>

    <p>
        <?php if($session['status_poa']!=='disabled'){ ?>
            <!-- <?= Html::a('<span class="glyphicon glyphicon-plus"></span> Tambah Detail Kegiatan', ['deptsubactivitydetail/create', 'id' => $model->id], ['class' => 'btn btn-success']) ?> -->
            <?= Html::button('<span class="glyphicon glyphicon-plus"></span> Tambah Detail Kegiatan', ['value' => Url::to(['deptsubactivitydetail/create', 'id'=>$model->id]), 'class' => 'showModalButton btn btn-success']) ?>
        <?php }else{ 
            if($session['revisi_poa'] == 1){ ?>
                <!-- <?= Html::a('<span class="glyphicon glyphicon-plus"></span> Tambah Detail Kegiatan', ['deptsubactivitydetail/create', 'id' => $model->id], ['class' => 'btn btn-success']) ?> -->
                <?= Html::button('<span class="glyphicon glyphicon-plus"></span> Tambah Detail Kegiatan', ['value' => Url::to(['deptsubactivitydetail/create', 'id'=>$model->id]), 'class' => 'showModalButton btn btn-success']) ?>
        <?php } 
        } ?>
    </p>

    <div style="overflow-x:auto;">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        // 'options' => ['style' => 'font-size:11px;'],
        'summary' => '',
        'id' => 'GridView',
        'showPageSummary' => true,
        'pageSummaryRowOptions' => ['class' => 'kv-page-summary success', 'style' => 'text-align:right'],
        'pjax' => true,
        'striped' => true,
        'hover' => false,
        // 'panel' => ['type' => 'primary', 'heading' => 'Data POA ' .$session['poaLabel']],
        'toggleDataContainer' => ['class' => 'btn-group mr-2'],
        'toolbar' => false,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn',
                'contentOptions' => ['style' => 'width: 3%'],
            ],

            [
                'attribute' => 'account',
                'label' => 'Rekening',
                'value' => function($model){
                    if(empty($model->rincian)){
                        return $model->account['nama_rekening'];
                    }else{
                        return $model->account['nama_rekening'].' ('.$model->rincian.')';
                    }
                },
                'contentOptions' => ['style' => 'width: 12%'],
                'pageSummaryOptions' => ['colspan' => '11', 'append' => 'Total', 'style' => 'text-align:right'],
            ],
            [
                'attribute' => 'sumberdana.nama',
                'label' => 'Sumber',
                'enableSorting' => false,
            ],
            [
                'label' => 'Vol 1',
                'attribute' =>'vol_1',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right', 'style' => 'width: 4%'],
                'format'=>['decimal',0]
            ],
            [
                'label' => 'Satuan 1',
                'attribute' =>'satuan_1',
                'enableSorting' => false
            ],
            
            [
                'label' => 'Vol 2',
                'attribute' =>'vol_2',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right', 'style' => 'width: 4%'],
                'format'=>['decimal',0]
            ],
            [
                'label' => 'Satuan 2',
                'attribute' =>'satuan_2',
                'enableSorting' => false
            ],
            
            [
                'label' => 'Volume 3',
                'attribute' =>'vol_3',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right', 'style' => 'width: 4%'],
                'format'=>['decimal',0]
            ],
            [
                'label' => 'Satuan 3',
                'attribute' =>'satuan_3',
                'enableSorting' => false
            ],
            
            [
                'label' => 'Volume 4',
                'attribute' =>'vol_4',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right', 'style' => 'width: 4%'],
                'format'=>['decimal',0]
            ],
            [
                'label' => 'Satuan 4',
                'attribute' =>'satuan_4',
                'enableSorting' => false
            ],
            
            [
                'label' => 'Harga Satuan',
                'attribute' =>'unit_cost',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right'],
                'format'=>['decimal',0]
            ],
            [
                'label' => 'Jumlah',
                'attribute' =>'jumlah',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right'],
                'format'=>['decimal',0],
                'pageSummary' => true,
                'pageSummaryFunc' => GridView::F_SUM
            ],

            // ['class' => 'yii\grid\ActionColumn'],
            ['class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['style' => $width],
                'template' => $template,
                'buttons' => [
                    'delete' => function ($url, $model) {
                        
                        return Html::a('<span class="glyphicon glyphicon-trash"></span> Hapus', ['deptsubactivitydetail/delete', 'id' => $model->id], ['class'=>'btn btn-xs btn-danger custom_button', 
                        'data' => [
                            'confirm' => 'Apakah Anda yakin akan menghapus data ini?',
                            'method' => 'post',
                        ],
                        ]);
                    },
                    'update' => function ($url, $model) {
                        // return Html::a('<span class="glyphicon glyphicon-pencil"></span> Ubah', array('deptsubactivitydetail/update', 'id'=>$model->id), ['class'=>'btn btn-xs btn-warning custom_button']);
                        return Html::button('<span class="glyphicon glyphicon-pencil"></span> Ubah', ['value' => Url::to(['deptsubactivitydetail/update', 'id'=>$model->id]), 'class' => 'showModalButton btn btn-xs btn-warning custom_button']);
                    },
                    'realisasi' => function ($url, $model, $session) {
                        $session = Yii::$app->session;
                        if($session['status_real']!=='disabled'){
                            return Html::a('<span class="glyphicon glyphicon-stats"></span> Realisasi', array('deptsubactivitydetail/view', 'id'=>$model->id), ['class'=>'btn btn-xs btn-info custom_button']);
                        }
                    },
                ]
            ],

            // [
            //     'label' => 'Triwulan I',
            //     'attribute' => 'tw1',
            //     'enableSorting' => false,
            //     'format' => 'raw',
            //     'contentOptions' => ['style' => 'width: 1%'],
            //     'value' => function ($model, $index, $widget) {
            //         return Html::checkbox('tw1[]', $model->tw1, ['value' => $index, 'disabled' => true]);
            //     },
            // ],

            // [
            //     'label' => 'Triwulan II',
            //     'attribute' => 'tw2',
            //     'enableSorting' => false,
            //     'format' => 'raw',
            //     'contentOptions' => ['style' => 'width: 1%'],
            //     'value' => function ($model, $index, $widget) {
            //         return Html::checkbox('tw2[]', $model->tw2, ['value' => $index, 'disabled' => true]);
            //     },
            // ],
            
            // [
            //     'label' => 'Triwulan III',
            //     'attribute' => 'tw3',
            //     'enableSorting' => false,
            //     'format' => 'raw',
            //     'contentOptions' => ['style' => 'width: 1%'],
            //     'value' => function ($model, $index, $widget) {
            //         return Html::checkbox('tw3[]', $model->tw3, ['value' => $index, 'disabled' => true]);
            //     },
            // ],

            // [
            //     'label' => 'Triwulan IV',
            //     'attribute' => 'tw4',
            //     'enableSorting' => false,
            //     'format' => 'raw',
            //     'contentOptions' => ['style' => 'width: 1%'],
            //     'value' => function ($model, $index, $widget) {
            //         return Html::checkbox('tw4[]', $model->tw4, ['value' => $index, 'disabled' => true]);
            //     },
            // ],
        ],
    ]); ?>
    </div>

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
?>
