<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Progress;
use yii\web\Session;

$session = Yii::$app->session;

$this->title = 'Bentuk Kegiatan'; //$session['activityName'];
$this->params['breadcrumbs'][] = ['label' => $session['periodValue']. $session['poaLabel'], 'url' => ['period/create', 'p' => 'def']];
$this->params['breadcrumbs'][] = ['label' => 'Data Upaya Program', 'url' => ['program/list']];
$this->params['breadcrumbs'][] = ['label' => 'Data Pelayanan', 'url' => ['service/list', 'id' => $session['programId']]];
$this->params['breadcrumbs'][] = ['label' => 'Data Kegiatan', 'url' => ['activity/list', 'id' => $session['serviceId']]];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="activitydataubah-list">

    <h1><?= Html::encode($session['activityName']) ?></h1>

    <?php        
        if($session['status_ubah']!=='disabled'){
            $template = '{view} {update} {delete}';
            $width = 'width: 18%';
        }else{
            if($session['revisi_ubah'] == 1):
                $template = '{view} {update} {delete}';
                $width = 'width: 18%';
            else:
                $template = '{view}';
                $width = 'width: 6%';
            endif;
        }

        echo Progress::widget([
            'bars' => [
                ['percent' => $session['prosentase'], 'label' => $session['prosentase'].'% dari Pagu Perubahan (RP. ' .number_format($session['pagu_ubah'], 0, ',', '.'). ')', 'options' => ['class' => $session['barColor']]],
            ],
            'options' => ['class' => $session['barStatus']]
        ]);
    ?>

    <p>
        <?php if($session['status_ubah']!=='disabled'){ ?>
            <?= Html::a('<span class="glyphicon glyphicon-plus"></span> Tambah Bentuk Kegiatan', array('create', 'id'=>$id, 'modul'=>'new'), ['class' => 'btn btn-success']) ?>
        <?php 
        }else{
            if($session['revisi_ubah'] == 1){ ?>
                <?= Html::a('<span class="glyphicon glyphicon-plus"></span> Tambah Bentuk Kegiatan', array('create', 'id'=>$id, 'modul'=>'new'), ['class' => 'btn btn-success']) ?> 
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

            // 'id',
            // 'activity_id',
            // 'period_id',
            // 'bentuk_kegiatan',
            [
                'label' => 'Bentuk Kegiatan',
                'attribute' =>'bentuk_kegiatan',
                'enableSorting' => false,
                // 'contentOptions' => ['class' => 'col-lg-1 text-right'],
            ],
            // 'sasaran',
            [
                'label' => 'Sasaran',
                'attribute' =>'sasaran',
                'enableSorting' => false,
                // 'contentOptions' => ['class' => 'col-lg-1 text-right'],
            ],
            // 'target',
            [
                'label' => 'Target',
                'attribute' =>'target',
                'enableSorting' => false,
                // 'contentOptions' => ['class' => 'col-lg-1 text-right'],
            ],
            // 'lokasi',
            [
                'label' => 'Lokasi',
                'attribute' =>'lokasi',
                'enableSorting' => false,
                // 'contentOptions' => ['class' => 'col-lg-1 text-right'],
            ],
            // 'pelaksana',
            [
                'label' => 'Pelaksana',
                'attribute' =>'pelaksana',
                'enableSorting' => false,
                // 'contentOptions' => ['class' => 'col-lg-1 text-right'],
            ],

            // ['class' => 'yii\grid\ActionColumn'],
            ['class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['style' => $width],
                'template' => $template,
                'buttons' => [
                    'view' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-list"></span> Detail', array('view', 'id'=>$model->id), ['class'=>'btn btn-xs btn-success custom_button']);
                    },
                    'update' => function ($url, $model, $session) {
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span> Ubah', array('update', 'id'=>$model->id, 'modul' => 'new', 'mid' => 0), ['class'=>'btn btn-xs btn-warning custom_button']);
                    },
                    'delete' => function ($url, $model, $session) {
                        if($model->activity_data_id == 0){
                            return Html::a('<span class="glyphicon glyphicon-trash"></span> Hapus', array('delete', 'id'=>$model->id), ['class'=>'btn btn-xs btn-danger custom_button', 
                            'data' => [
                                'confirm' => 'Apakah Anda yakin akan menghapus data ini?',
                                'method' => 'post',
                            ],
                        ]);
                        }
                    },
                ]],
        ],
    ]); ?>
    </div>

</div>
