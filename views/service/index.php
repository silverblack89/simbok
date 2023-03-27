<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\session;

$session = Yii::$app->session;
/* @var $this yii\web\View */
/* @var $searchModel app\models\ServiceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Data Pelayanan';
$this->params['breadcrumbs'][] = ['label' => 'Data Program ('.$session['programYear'].')', 'url' => ['program/index', 'tahun' => $session['programYear']]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="service-index">

    <h1><?= Html::encode($name) ?></h1>

    <p>
        <?= Html::a('Tambah Pelayanan', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'id',
            // 'program_id',
            'nama_pelayanan',
            // 'aktif',
            [
                'attribute' => 'aktif',
                'value' => function ($model) {
                    return $model->aktif ? 'Ya' : 'Tidak';
                },
            ],

            // ['class' => 'yii\grid\ActionColumn'],
            ['class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['style' => 'width: 8%'],
                'template' => '{custom} {view} {update} {delete}',
                'buttons' => [
                    'custom' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-list"></span>', ['activity/index', 'id'=>$model->id]);
                    },
                ]
                
            ],
        ],
    ]); ?>

</div>
