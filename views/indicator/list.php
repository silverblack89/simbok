<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\Session;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ServiceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$session = Yii::$app->session;

$this->title = 'Data Indikator';
$this->params['breadcrumbs'][] = ['label' => $session['periodValue'], 'url' => ['period/create']];
$this->params['breadcrumbs'][] = ['label' => 'Data Upaya Program', 'url' => ['program/list']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="service-index">

    <h1><?= Html::encode($name) ?></h1>

    <!-- <p>
        <?= Html::a('Tambah Data', array('create', 'id'=>$id), ['class' => 'btn btn-success']) ?>
    </p> -->

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'label' => 'Nama Indikator',
                'attribute' =>'nama_indikator',
                'enableSorting' => false,
            ],
            
            ['class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['style' => 'width: 2%'],
                'template' => '{create}',
                'buttons' => [
                    'create' => function ($url, $model){
                        return Html::a('<span class="glyphicon glyphicon-check"></span> Proses', array('indicatordata/index', 'id'=>$model->id), ['class'=>'btn btn-xs btn-success btn-xs custom_button']);
                    },
                ]],
        ],
    ]); ?>
</div>
