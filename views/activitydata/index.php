<?php

use yii\helpers\Html;
use yii\grid\GridView;
// use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\ActivitydataSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$session = Yii::$app->session;

$this->title = 'Bentuk Kegiatan'; //$session['activityName'];
$this->params['breadcrumbs'][] = ['label' => 'Data Upaya Program', 'url' => ['program/list']];
$this->params['breadcrumbs'][] = ['label' => 'Data Pelayanan', 'url' => ['service/list', 'id' => $session['programId']]];
$this->params['breadcrumbs'][] = ['label' => 'Data Kegiatan', 'url' => ['activity/list', 'id' => $session['serviceId']]];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="activitydata-index">

    <h1><?= Html::encode($session['activityName']) ?></h1>

    <p>
        <?= Html::a('Tambah Bentuk Kegiatan', array('create', 'id'=>$id), ['class' => 'btn btn-success']) ?>
    </p>

    <?php //Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'id',
            // 'activity_id',
            // 'period_id',
            'bentuk_kegiatan',
            'sasaran',
            'target',
            'lokasi',
            'pelaksana',

            // ['class' => 'yii\grid\ActionColumn'],
            ['class' => 'yii\grid\ActionColumn',
                // 'contentOptions' => ['style' => 'width: 10%'],
                'template' => '{view} {update} {delete}',
                'buttons' => [
                    'view' => function ($url, $model) {
                        return Html::a('', array('view', 'id'=>$model->id), ['class'=>'glyphicon glyphicon-eye-open']);
                    },
                ]],
        ],
    ]); ?>

    <?php //Pjax::end(); ?>

</div>
