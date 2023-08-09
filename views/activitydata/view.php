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
/* @var $model app\models\Activitydata */

$this->title = $session['activityName'];
$this->params['breadcrumbs'][] = ['label' => $session['periodValue']. $session['poaLabel'], 'url' => ['period/create', 'p' => 'def']];
$this->params['breadcrumbs'][] = ['label' => 'Rincian Menu', 'url' => ['program/list']];
$this->params['breadcrumbs'][] = ['label' => 'Komponen', 'url' => ['service/list', 'id' => $session['programId']]];
$this->params['breadcrumbs'][] = ['label' => 'Sub Komponen', 'url' => ['activity/list', 'id' => $session['serviceId']]];
$this->params['breadcrumbs'][] = ['label' => 'Bentuk Kegiatan', 'url' => ['activitydata/list', 'id' => $session['activityId']]];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="activitydata-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php
        if($session['poa'] == 'def'){
            if($session['pagu'] < 0){
                echo Progress::widget([
                    'bars' => [
                        ['percent' => $session['prosentase'], 'label' => number_format((int)$session['prosentase'],0,',','.').'% dari Pagu BOK (Lebih RP. ' .number_format(abs($session['pagu']), 0, ',', '.'). ')', 'options' => ['class' => $session['barColor']]],
                    ],
                    'options' => ['class' => $session['barStatus']]
                ]);
            }   
            elseif($session['pagu']> 0){
                echo Progress::widget([
                    'bars' => [
                        ['percent' => $session['prosentase'], 'label' => number_format((int)$session['prosentase'],0,',','.').'% dari Pagu BOK (Kurang RP. ' .number_format($session['pagu'], 0, ',', '.'). ')', 'options' => ['class' => $session['barColor']]],
                    ],
                    'options' => ['class' => $session['barStatus']]
                ]);
            }else{
                echo Progress::widget([
                    'bars' => [
                        ['percent' => $session['prosentase'], 'label' => $session['prosentase'].'% dari Pagu BOK', 'options' => ['class' => $session['barColor']]],
                    ],
                    'options' => ['class' => $session['barStatus']]
                ]);
            }
        }

        if($session['poa'] == 'perubahan'){
            echo Progress::widget([
                'bars' => [
                    ['percent' => $session['prosentase'], 'label' => $session['prosentase'].'% dari Pagu Perubahan (RP. ' .number_format($session['pagu_ubah'], 0, ',', '.'). ')', 'options' => ['class' => $session['barColor']]],
                ],
                'options' => ['class' => $session['barStatus']]
            ]);
        }

        if($session['status_poa']!=='disabled'){
            $template = '{update} {delete}';
            $width = 'width: 13%';
        }else{
            if($session['revisi_poa'] == 1){
                $template = '{update} {delete}';
                $width = 'width: 13%';
            }else{
                if($session['status_real']!=='disabled'){

                    //{update} Tambahan RAK
                    $template = '{realisasi}';
                    $width = 'width: 7%';
                }else{
                    //{update} Tambahan RAK
                    $template = '';
                    $width = 'width: 1%';
                }
            }
        }
    ?>

    <p>
        <?php if($session['status_poa']!=='disabled'){ ?>
        <!-- <?= Html::a('<span class="glyphicon glyphicon-pencil"></span> Ubah', ['update', 'id' => $model->id, 'modul' => 'new', 'mid' => 0], ['class' => 'btn btn-warning']) ?> -->
        <?= Html::button('<span class="glyphicon glyphicon-pencil"></span> Ubah', ['value' => Url::to(['activitydata/update', 'id'=>$model->id, 'modul' => 'new', 'mid' => 0]), 'class' => 'showModalButton btn btn-warning custom_button']); ?>
        <?= Html::a('<span class="glyphicon glyphicon-remove"></span> Hapus', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Apakah Anda yakin akan menghapus data ini?',
                'method' => 'post',
            ],
        ]) ?>
        <!-- <?= Html::a('<span class="glyphicon glyphicon-transfer"></span> Pindah', ['update', 'id' => $model->id, 'modul' => 'program', 'mid' => 0], ['class' => 'btn btn-primary pull-right']) ?> -->
        <?= Html::button('<span class="glyphicon glyphicon-transfer"></span> Pindah Data', 
                        ['value' => Url::to(['activitydata/update', 'id' => $model->id, 'modul' => 'program', 'mid' => 0]), 'title' => 'Pindah Data Kegiatan', 'class' => 'showModalButton btn btn-primary pull-right']) ?>
        <?php }else{ 
            if($session['revisi_poa'] == 1){ ?>
                <!-- <?= Html::a('<span class="glyphicon glyphicon-pencil"></span> Ubah', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?> -->
                <?= Html::button('<span class="glyphicon glyphicon-pencil"></span> Ubah', ['value' => Url::to(['activitydata/update', 'id'=>$model->id]), 'class' => 'showModalButton btn btn-warning custom_button']); ?>
                <?= Html::a('<span class="glyphicon glyphicon-remove"></span> Hapus', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => 'Apakah Anda yakin akan menghapus data ini?',
                        'method' => 'post',
                    ],
                ]) ?>
                <?= Html::button('<span class="glyphicon glyphicon-transfer"></span> Pindah Data', 
                        ['value' => Url::to(['activitydata/update', 'id' => $model->id, 'modul' => 'program', 'mid' => 0]), 'title' => 'Pindah Data Kegiatan', 'class' => 'showModalButton btn btn-primary pull-right']) ?>
        <?php } 
        } ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => ''],
        'attributes' => [
            // 'id',
            // 'activity_id',
            // 'period_id',
            'bentuk_kegiatan',
            'sasaran',
            'target',
            'lokasi',
            'pelaksana',
        ],
    ]) ?>

    <p>
        <?php if($session['status_poa']!=='disabled'){ ?>
            <!-- <?= Html::a('<span class="glyphicon glyphicon-plus"></span> Tambah Detail Kegiatan', ['activitydetail/create', 'id' => $model->id], ['class' => 'btn btn-success']) ?> -->
            <?= Html::button('<span class="glyphicon glyphicon-plus"></span> Tambah Detail Kegiatan', ['value' => Url::to(['activitydetail/create', 'id'=>$model->id]), 'class' => 'showModalButton btn btn-success']) ?>
        <?php }else{ 
            if($session['revisi_poa'] == 1){ ?>
                <!-- <?= Html::a('<span class="glyphicon glyphicon-plus"></span> Tambah Detail Kegiatan', ['activitydetail/create', 'id' => $model->id], ['class' => 'btn btn-success']) ?> -->
                <?= Html::button('<span class="glyphicon glyphicon-plus"></span> Tambah Detail Kegiatan', ['value' => Url::to(['activitydetail/create', 'id'=>$model->id]), 'class' => 'showModalButton btn btn-success']) ?>
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
        'pjaxSettings' =>[
            'neverTimeout'=>true,
            'options'=>[
                'id'=>'detail',
            ]
        ],  
        'striped' => true,
        'hover' => false,
        // 'panel' => ['type' => 'primary', 'heading' => 'Data POA ' .$session['poaLabel']],
        'toggleDataContainer' => ['class' => 'btn-group mr-2'],
        'toolbar' => false,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'id',
            // 'activity_data_id',
            // 'account_id',
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
                'contentOptions' => ['style' => 'width: 15%'],
                'pageSummaryOptions' => ['colspan' => '10', 'append' => 'Total', 'style' => 'text-align:right'],
            ],
            // 'rincian',
            [
                'label' => 'Volume 1',
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
                'label' => 'Volume 2',
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
                'label' => 'Biaya',
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
            // // 'jan',
            // [
            //     'label' => 'Jan',
            //     'attribute' => 'jan',
            //     'enableSorting' => false,
            //     'format' => 'raw',
            //     'contentOptions' => ['style' => 'width: 1%'],
            //     'value' => function ($model, $index, $widget) {
            //         return Html::checkbox('jan[]', $model->jan, ['value' => $index, 'disabled' => true]);
            //     },
            // ],
            // // 'feb',
            // [
            //     'label' => 'Feb',
            //     'attribute' => 'feb',
            //     'enableSorting' => false,
            //     'format' => 'raw',
            //     'contentOptions' => ['style' => 'width: 1%'],
            //     'value' => function ($model, $index, $widget) {
            //         return Html::checkbox('feb[]', $model->feb, ['value' => $index, 'disabled' => true]);
            //     },
            // ],
            // // 'mar',
            // [
            //     'label' => 'Mar',
            //     'attribute' => 'feb',
            //     'enableSorting' => false,
            //     'format' => 'raw',
            //     'contentOptions' => ['style' => 'width: 1%'],
            //     'value' => function ($model, $index, $widget) {
            //         return Html::checkbox('mar[]', $model->mar, ['value' => $index, 'disabled' => true]);
            //     },
            // ],
            // // 'apr',
            // [
            //     'label' => 'Apr',
            //     'attribute' => 'apr',
            //     'enableSorting' => false,
            //     'format' => 'raw',
            //     'contentOptions' => ['style' => 'width: 1%'],
            //     'value' => function ($model, $index, $widget) {
            //         return Html::checkbox('apr[]', $model->apr, ['value' => $index, 'disabled' => true]);
            //     },
            // ],
            // // 'mei',
            // [
            //     'label' => 'Mei',
            //     'attribute' => 'mei',
            //     'enableSorting' => false,
            //     'format' => 'raw',
            //     'contentOptions' => ['style' => 'width: 1%'],
            //     'value' => function ($model, $index, $widget) {
            //         return Html::checkbox('mei[]', $model->mei, ['value' => $index, 'disabled' => true]);
            //     },
            // ],
            // // 'jun',
            // [
            //     'label' => 'Jun',
            //     'attribute' => 'jun',
            //     'enableSorting' => false,
            //     'format' => 'raw',
            //     'contentOptions' => ['style' => 'width: 1%'],
            //     'value' => function ($model, $index, $widget) {
            //         return Html::checkbox('jun[]', $model->jun, ['value' => $index, 'disabled' => true]);
            //     },
            // ],
            // // 'jul',
            // [
            //     'label' => 'Jul',
            //     'attribute' => 'jul',
            //     'enableSorting' => false,
            //     'format' => 'raw',
            //     'contentOptions' => ['style' => 'width: 1%'],
            //     'value' => function ($model, $index, $widget) {
            //         return Html::checkbox('jul[]', $model->jul, ['value' => $index, 'disabled' => true]);
            //     },
            // ],
            // // 'agu',
            // [
            //     'label' => 'Agu',
            //     'attribute' => 'agu',
            //     'enableSorting' => false,
            //     'format' => 'raw',
            //     'contentOptions' => ['style' => 'width: 1%'],
            //     'value' => function ($model, $index, $widget) {
            //         return Html::checkbox('agu[]', $model->agu, ['value' => $index, 'disabled' => true]);
            //     },
            // ],
            // // 'sep',
            // [
            //     'label' => 'Sep',
            //     'attribute' => 'sep',
            //     'enableSorting' => false,
            //     'format' => 'raw',
            //     'contentOptions' => ['style' => 'width: 1%'],
            //     'value' => function ($model, $index, $widget) {
            //         return Html::checkbox('sep[]', $model->sep, ['value' => $index, 'disabled' => true]);
            //     },
            // ],
            // // 'okt',
            // [
            //     'label' => 'Okt',
            //     'attribute' => 'okt',
            //     'enableSorting' => false,
            //     'format' => 'raw',
            //     'contentOptions' => ['style' => 'width: 1%'],
            //     'value' => function ($model, $index, $widget) {
            //         return Html::checkbox('okt[]', $model->okt, ['value' => $index, 'disabled' => true]);
            //     },
            // ],
            // // 'nov',
            // [
            //     'label' => 'Nov',
            //     'attribute' => 'nov',
            //     'enableSorting' => false,
            //     'format' => 'raw',
            //     'contentOptions' => ['style' => 'width: 1%'],
            //     'value' => function ($model, $index, $widget) {
            //         return Html::checkbox('nov[]', $model->nov, ['value' => $index, 'disabled' => true]);
            //     },
            // ],
            // // 'des',
            // [
            //     'label' => 'Des',
            //     'attribute' => 'des',
            //     'enableSorting' => false,
            //     'format' => 'raw',
            //     'contentOptions' => ['style' => 'width: 1%'],
            //     'value' => function ($model, $index, $widget) {
            //         return Html::checkbox('des[]', $model->des, ['value' => $index, 'disabled' => true]);
            //     },
            // ],

            // ['class' => 'yii\grid\ActionColumn'],
            ['class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['style' => $width],
                'template' => $template,
                'buttons' => [
                    'delete' => function ($url, $model) {
                        
                        return Html::a('<span class="glyphicon glyphicon-trash"></span> Hapus', ['activitydetail/delete', 'id' => $model->id], ['class'=>'btn btn-xs btn-danger custom_button', 
                        'data' => [
                            'confirm' => 'Apakah Anda yakin akan menghapus data ini?',
                            'method' => 'post',
                        ],
                        ]);
                    },
                    'update' => function ($url, $model) {
                        // return Html::a('<span class="glyphicon glyphicon-pencil"></span> Ubah', array('activitydetail/update', 'id'=>$model->id), ['class'=>'btn btn-xs btn-warning custom_button']);
                        return Html::button('<span class="glyphicon glyphicon-pencil"></span> Ubah', ['value' => Url::to(['activitydetail/update', 'id'=>$model->id]), 'class' => 'showModalButton btn btn-xs btn-warning custom_button']);
                    },
                    // 'realisasi' => function ($url, $model, $session) {
                    //     $session = Yii::$app->session;
                    //     if($session['status_real']!=='disabled'){
                    //         return Html::a('<span class="glyphicon glyphicon-stats"></span> Realisasi', array('activitydetail/view', 'id'=>$model->id), ['class'=>'btn btn-xs btn-info custom_button']);
                    //     }
                    // },
                ]
            ],
        ],
    ]); ?>
    </div>
</div>

<?php 
    Modal::begin([
            // 'header'=>'<h4>Detail Kegiatan</h4>', 
            'id'=>'modal',
            'size'=>'modal-lg',
            'clientOptions' => ['backdrop' => 'static', 'keyboard' => FALSE],
            // 'footer' => ''
        ]);
    echo "<div id='modalContent'></div>";
    Modal::end();
?>
