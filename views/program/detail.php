<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\web\Session;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProgramSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$session = Yii::$app->session;
?>

<h1><?= Html::encode($programName) ?></h1>

<div style="overflow-x:auto;">
<?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'id' => 'GridView',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'nama_kegiatan',
            'bentuk_kegiatan',
            'sasaran',
            // 'target',
            'lokasi',
            // 'pelaksana',
            [
                'attribute' => 'nama_rekening',
                'label' => 'Rekening',
            ],
            [
                'label' => 'Vol 1',
                'attribute' =>'vol_1',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right'],
                'format'=>['decimal',0]
            ],
            [
                'attribute' => 'satuan_1',
                'label' => 'Sat',
                'contentOptions' => ['style' => 'width: 5%']
            ],
            [
                'label' => 'Vol 2',
                'attribute' =>'vol_2',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right'],
                'format'=>['decimal',0]
            ],
            [
                'attribute' => 'satuan_2',
                'label' => 'Sat',
                'contentOptions' => ['style' => 'width: 5%']
            ],
            [
                'label' => 'Biaya',
                'attribute' =>'unit_cost',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right'],
                'format'=>['decimal',0]
            ],
            [
                'label' => 'Jumlah',
                'attribute' =>'jumlah',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right'],
                'format'=>['decimal',0]
            ],

            // ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

    <div class="form-group">
        <?= Html::button('<span class="glyphicon glyphicon-ok"></span> Verifikasi', 
            ['value' => Url::to(['program/proses-verif', 'action' => 'verif_ok']), 'title' => '', 'class' => 'showModalButton btn btn-success']) ?>

        <?= Html::button('<span class="glyphicon glyphicon-remove"></span> Batal', ['class' => 'btn btn-danger', 'data-dismiss' => 'modal']) ?>
    </div>

    </div>