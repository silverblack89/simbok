<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Progress;
use yii\web\Session;
use yii\bootstrap\Modal;
use yii\helpers\Url;

$session = Yii::$app->session;

$this->title = 'Bentuk Kegiatan'; //$session['activityName'];
$this->params['breadcrumbs'][] = ['label' => $session['periodValue']. $session['poaLabel'], 'url' => ['period/create', 'p' => 'def']];
$this->params['breadcrumbs'][] = ['label' => 'Rincian Menu', 'url' => ['program/list']];
$this->params['breadcrumbs'][] = ['label' => 'Komponen', 'url' => ['service/list', 'id' => $session['programId']]];
$this->params['breadcrumbs'][] = ['label' => 'Sub Komponen', 'url' => ['activity/list', 'id' => $session['serviceId']]];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="activitydata-list">

    <h1><?= Html::encode($session['activityName']) ?></h1>

    <?php if($session['activityStatus'] == 'Wajib'){ ?> 
        <p style="margin-top:-10px"><span class="label label-danger"><?= $session['activityStatus'] ?></span></p>
    <?php } ?>
    
    <?php if($session['activityStatus'] == 'Pilihan'){ ?>
        <p style="margin-top:-10px"><span class="label label-warning"><?= $session['activityStatus'] ?></span></p>
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
                $template = '{view} {update}';
                $width = 'width: 12%';
            endif;
        }
    ?>

    <p>
        <?php if($session['status_poa']!=='disabled'){ ?>
            <!-- <?= Html::a('<span class="glyphicon glyphicon-plus"></span> Tambah Bentuk Kegiatan', array('create', 'id'=>$id, 'modul'=>'new'), ['class' => 'btn btn-success']) ?> -->
            <?= Html::button('<span class="glyphicon glyphicon-plus"></span> Tambah Bentuk Kegiatan', ['value' => Url::to(['activitydata/create', 'id'=>$id, 'modul'=>'new']), 'class' => 'showModalButton btn btn-success']) ?>
        <?php 
        }else{
            if($session['revisi_poa'] == 1){ ?>
                <!-- <?= Html::a('<span class="glyphicon glyphicon-plus"></span> Tambah Bentuk Kegiatan', array('create', 'id'=>$id, 'modul'=>'new'), ['class' => 'btn btn-success']) ?>  -->
                <?= Html::button('<span class="glyphicon glyphicon-plus"></span> Tambah Bentuk Kegiatan', ['value' => Url::to(['activitydata/create', 'id'=>$id, 'modul'=>'new']), 'class' => 'showModalButton btn btn-success']) ?>
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
                    'update' => function ($url, $model) {
                        $arr = array($model->sasaran, $model->target, $model->lokasi, $model->pelaksana);

                        // return Html::a('<span class="glyphicon glyphicon-pencil"></span> Ubah', array('update', 'id'=>$model->id, 'modul' => 'new', 'mid' => 0), ['class'=>'btn btn-xs btn-warning custom_button']);
                        return Html::button('<span class="glyphicon glyphicon-pencil"></span> Ubah', ['value' => Url::to(['activitydata/update', 'id'=>$model->id, 'modul' => 'new', 'mid' => 0, 'arr' => $arr]), 'class' => 'showModalButton btn btn-xs btn-warning custom_button']);
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
