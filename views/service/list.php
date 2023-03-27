<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Progress;
use yii\web\Session;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ServiceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$session = Yii::$app->session;

$this->title = 'Komponen';
$this->params['breadcrumbs'][] = ['label' => $session['periodValue']. $session['poaLabel'], 'url' => ['period/create', 'p' => 'def']];
$this->params['breadcrumbs'][] = ['label' => 'Rincian Menu', 'url' => ['program/list']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="service-index">

    <h1><?= Html::encode($name) ?></h1>

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
        <?= Html::a('Tambah Data', array('create', 'id'=>$id), ['class' => 'btn btn-success']) ?>
    </p> -->

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'id',
            // 'program_id',
            // 'unit_id',
            // ['attribute' => 'program',
            // 'value' => 'program.nama_program'
            // ],
            // 'nama_pelayanan',
            [
                'label' => 'Nama',
                'attribute' =>'nama_pelayanan',
                'enableSorting' => false,
            ],
            // [
            //     'attribute' => 'nama_pelayanan',
            //     'value' => function ($model) {
            //         return Html::a($model->nama_pelayanan, array('activity/index', 'id'=>$model->id)); 
            //     },
            //     'format' => 'raw',
            // ],
            // 'tahun',

            // ['class' => 'yii\grid\ActionColumn',
            // 'contentOptions' => ['style' => 'width: 6%'],
            // 'template' => '{view} {update} {delete}',
            // // 'buttons' => [
            // //     'create' => function ($url, $model) {
            // //         return Html::a('', array('service/index', 'id'=>$model->id), 
            // //         ['class'=>'glyphicon glyphicon-check btn btn-xs btn-success btn-xs custom_button']);
            // //     },
            // // ]
            // // 'buttons' => [
            // //     'create' => function ($url, $model) {
            // //         return Html::a('', array('activity/index', 'id'=>$model->id),
            // //         ['class'=>'glyphicon glyphicon-new-window']);
            // //     },
            // // ]
        
            // ],
            ['class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['style' => 'width: 2%'],
                'template' => '{create}',
                'buttons' => [
                    'create' => function ($url, $model){
                        $session = Yii::$app->session;
                        return Html::a('<span class="glyphicon glyphicon-check"></span> Proses', array('activity/list', 'id'=>$model->id), ['class'=>'btn btn-xs btn-success btn-xs custom_button']);
                    },
                ]],
        ],
    ]); ?>
</div>
