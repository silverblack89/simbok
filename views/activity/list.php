<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Activitydata;
use app\models\ActivitydataSearch;
use yii\bootstrap\Progress;
use yii\web\Session;
/* @var $this yii\web\View */
/* @var $searchModel app\models\ActivitySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$session = Yii::$app->session;

$this->title = 'Sub Komponen';
$this->params['breadcrumbs'][] = ['label' => $session['periodValue']. $session['poaLabel'], 'url' => ['period/create', 'p' => 'def']];
$this->params['breadcrumbs'][] = ['label' => 'Rincian Menu', 'url' => ['program/list']];
$this->params['breadcrumbs'][] = ['label' => 'Komponen', 'url' => ['service/list', 'id' => $session['programId']]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="activity-list">

    <h1><?= Html::encode($session['serviceName']) ?></h1>

    <?php
        if($session['poa'] == 'def'){
            echo Progress::widget([
                'bars' => [
                    ['percent' => $session['prosentase'], 'label' => $session['prosentase'].'% dari Pagu BOK (RP. ' .number_format($session['pagu'], 0, ',', '.'). ')', 'options' => ['class' => $session['barColor']]],
                ],
                'options' => ['class' => $session['barStatus']]
            ]);
        }

        if($session['poa'] == 'perubahan'){
            echo Progress::widget([
                'bars' => [
                    ['percent' => $session['prosentase'], 'label' => $session['prosentase'].'% dari Pagu Perubahan (RP. ' .number_format($session['pagu_ubah'], 0, ',', '.'). ')', 'options' => ['class' => $session['barColor']]],
                ],
                'options' => ['class' => $session['barStatus']]
            ]);
        }
    ?>

    <!-- <p>
        <?= Html::a('Tambah Kegiatan', array('create', 'id' => $id), ['class' => 'btn btn-success']) ?>
    </p> -->

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'id',
            // 'service_id',
            // 'nama_kegiatan:ntext',
            [
                'label' => 'Nama',
                'attribute' =>'nama_kegiatan',
                'enableSorting' => false,
            ],
            // 'aktif',
            [
                'attribute' =>'status',
                'enableSorting' => false,
                'contentOptions' => function($model){
                    if($model->status == 'Wajib'){
                        return ['style' => 'font-weight:bold;color:red;'];
                    }else{
                        return ['style' => 'font-weight:bold;color:orange;'];
                    }
                },
            ],

            ['class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['style' => 'width: 2%'],
                'template' => '{create}',
                'buttons' => [
                    'create' => function ($url, $model){    
                        $session = Yii::$app->session;
                        $session->open();
                        // $session['activityId'] = $model->id; 
                        // $session['activityName'] = $model->nama_kegiatan;   

                        if($session['poa'] == 'def'){
                            return Html::a('<span class="glyphicon glyphicon-check"></span> Proses', ['/activitydata/list', 'id'=>$model->id], ['class'=>'btn btn-xs btn-success btn-xs custom_button']);
                        }else{
                            return Html::a('<span class="glyphicon glyphicon-check"></span> Proses Perubahan', ['/activitydataubah/list', 'id'=>$model->id], ['class'=>'btn btn-xs btn-success btn-xs custom_button']);
                        }
                    },
                ]],
        ],
    ]); ?>

</div>
