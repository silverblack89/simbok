<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $searchModel app\models\DeptactivitySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Data Kegiatan';
$this->params['breadcrumbs'][] = ['label' => 'Data Program ('.$session['programYear'].')', 'url' => ['deptprogram/index', 'tahun' => $session['programYear']]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="deptactivity-index">

    <h1><?= Html::encode($name) ?></h1>

    <p>
        <?= Html::a('Tambah Kegiatan', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'id',
            // 'dept_program_id',
            'kode_rekening',
            'nama_kegiatan',
            'pagu',
            [
                'attribute' => 'aktif',
                'value' => function ($model){
                    return $model->aktif ? 'Ya'  :'Tidak';
                },
            ],

            // ['class' => 'yii\grid\ActionColumn'],
            ['class' => 'yii\grid\ActionColumn',
            'contentOptions' => ['style' => 'width: 8%'],
            'template' => '{custom} {view} {update} {delete}',
            'buttons' => [
                'custom' => function ($url, $model) {
                    return Html::a('<span class="glyphicon glyphicon-list"></span>', ['deptsubactivity/index', 'id'=>$model->id]);
                },
            ]
            
        ],
        ],
    ]); ?>


</div>
