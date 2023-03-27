<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\session;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\bootstrap\Progress;

$session = Yii::$app->session;

$this->title = 'Bentuk Kegiatan';
$this->params['breadcrumbs'][] = ['label' => $session['deptPeriodValue'], 'url' => ['deptperiod/create']];
$this->params['breadcrumbs'][] = ['label' => 'Rincian Menu Kegiatan', 'url' => ['deptprogram/list']];
$this->params['breadcrumbs'][] = ['label' => 'Komponen', 'url' => ['deptactivity/list', 'id' => $session['deptProgramId']]];
$this->params['breadcrumbs'][] = ['label' => 'Kegiatan', 'url' => ['deptsubactivity/list', 'id' => $session['deptActivityId']]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="deptactivitydata-list">

    <h1><?= Html::encode($session['deptSubActivityName']) ?></h1>

    <?php if($session['deptSubActivityStatus'] == 'Wajib'){ ?> 
        <p style="margin-top:-10px"><span class="label label-danger"><?= $session['deptSubActivityStatus'] ?></span></p>
    <?php } ?>
    
    <?php if($session['deptSubActivityStatus'] == 'Pilihan'){ ?>
        <p style="margin-top:-10px"><span class="label label-warning"><?= $session['deptSubActivityStatus'] ?></span></p>
    <?php } ?>

    <?php
        echo Progress::widget([
            'bars' => [
                ['percent' => $session['prosentase'], 'label' => $session['prosentase'].'% dari Pagu BOK (RP. ' .number_format($session['pagu'], 0, ',', '.'). ')', 'options' => ['class' => $session['barColor']]],
            ],
            'options' => ['class' => $session['barStatus']]
        ]);
        
        if($session['status_poa']!=='disabled'){
            $template = '{view} {update} {delete}';
            $width = 'width: 18%';
        }else{
            if($session['revisi_poa'] == 1):
                $template = '{view} {update} {delete}';
                $width = 'width: 18%';
            else:
                $template = '{view}';
                $width = 'width: 6%';
            endif;
        }
    ?>

    <p>
        <?php if($session['status_poa']!=='disabled'){ ?>
            <!-- <?= Html::a('Tambah Bentuk Kegiatan', ['create', 'id'=>$id, 'modul'=>'new'], ['class' => 'btn btn-success']) ?> -->
            <?= Html::button('<span class="glyphicon glyphicon-plus"></span> Tambah Bentuk Kegiatan', ['value' => Url::to(['deptsubactivitydata/create', 'id'=>$id, 'modul'=>'new']), 'class' => 'showModalButton btn btn-success']) ?>
        <?php 
        }else{
            if($session['revisi_poa'] == 1){ ?>
                <!-- <?= Html::a('<span class="glyphicon glyphicon-plus"></span> Tambah Bentuk Kegiatan', array('create', 'id'=>$id, 'modul'=>'new'), ['class' => 'btn btn-success']) ?>  -->
                <?= Html::button('<span class="glyphicon glyphicon-plus"></span> Tambah Bentuk Kegiatan', ['value' => Url::to(['deptsubactivitydata/create', 'id'=>$id, 'modul'=>'new']), 'class' => 'showModalButton btn btn-success']) ?>
        <?php } 
        } ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <div style="overflow-x:auto;">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'label' => 'Bentuk Kegiatan',
                'attribute' =>'bentuk_kegiatan',
                'enableSorting' => false,
                // 'contentOptions' => ['class' => 'col-lg-1 text-right'],
            ],

            [
                'label' => 'Indikator Output',
                'attribute' =>'indikator_hasil',
                'enableSorting' => false,
            ],
  
            // [
            //     'label' => 'Target Hasil',
            //     'attribute' =>'target_hasil',
            //     'enableSorting' => false,
            // ],

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
   
            // [
            //     'label' => 'Indikator Keluaran',
            //     'attribute' =>'indikator_keluaran',
            //     'enableSorting' => false,
            // ],

            // [
            //     'label' => 'Target Keluaran',
            //     'attribute' =>'target_keluaran',
            //     'enableSorting' => false,
            // ],


            // ['class' => 'yii\grid\ActionColumn'],
            ['class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['style' => $width],
                'template' => $template,
                'buttons' => [
                    'view' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-list"></span> Detail', array('view', 'id'=>$model->id), ['class'=>'btn btn-xs btn-success custom_button']);
                    },
                    'update' => function ($url, $model, $session) {
                        // return Html::a('<span class="glyphicon glyphicon-pencil"></span> Ubah', array('update', 'id'=>$model->id, 'modul' => 'new', 'mid' => 0), ['class'=>'btn btn-xs btn-warning custom_button']);
                        return Html::button('<span class="glyphicon glyphicon-pencil"></span> Ubah', ['value' => Url::to(['deptsubactivitydata/update', 'id'=>$model->id, 'modul' => 'new', 'mid' => 0]), 'class' => 'showModalButton btn btn-xs btn-warning custom_button']);
                    },
                    'delete' => function ($url, $model, $session) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span> Hapus', array('delete', 'id'=>$model->id), ['class'=>'btn btn-xs btn-danger custom_button', 
                        'data' => [
                            'confirm' => 'Apakah Anda yakin akan menghapus data ini?',
                            'method' => 'post',
                        ],
                    ]);
                    },
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
            'size'=>'modal-md',
            'clientOptions' => ['backdrop' => 'static', 'keyboard' => FALSE],
            // 'footer' => ''
        ]);
    echo "<div id='modalContent'></div>";
    Modal::end();
?>
