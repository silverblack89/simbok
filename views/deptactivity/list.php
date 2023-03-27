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
$session->open();

$this->title = 'Komponen';
$this->params['breadcrumbs'][] = ['label' => $session['deptPeriodValue'], 'url' => ['deptperiod/create']];
$this->params['breadcrumbs'][] = ['label' => 'Rincian Menu Kegiatan', 'url' => ['deptprogram/list']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="deptactivity-list">

    <h1><?= Html::encode($session['deptProgramName']) ?></h1>

    <!-- <?php
        echo Progress::widget([
            'bars' => [
                ['percent' => $session['prosentase'], 'label' => $session['prosentase'].'% dari Pagu BOK (RP. ' .number_format($session['pagu'], 0, ',', '.'). ')', 'options' => ['class' => $session['barColor']]],
            ],
            'options' => ['class' => $session['barStatus']]
        ]);
    ?> -->

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'id',
            // 'service_id',
            // 'nama_kegiatan:ntext',
            [
                'label' => 'Nama Kegiatan',
                'attribute' =>'nama_kegiatan',
                'enableSorting' => false,
            ],
            // 'aktif',

            ['class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['style' => 'width: 2%'],
                'template' => '{create}',
                'buttons' => [
                    'create' => function ($url, $model){    
                        // $session = Yii::$app->session;
                        // $session->open();
                        // $session['activityId'] = $model->id; 
                        // $session['activityName'] = $model->nama_kegiatan;   
                        return Html::a('<span class="glyphicon glyphicon-check"></span> Proses', ['/deptsubactivity/list', 'id'=>$model->id], ['class'=>'btn btn-xs btn-success btn-xs custom_button']);
                    },
                ]],
        ],
    ]); ?>

</div>
