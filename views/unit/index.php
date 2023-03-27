<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\UnitSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Units';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="unit-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Tambah Unit', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'puskesmas',
            // 'kecamatan',
            'kepala',
            //'jabatan_kepala',
            //'nip_kepala',
            //'petugas',
            //'jabatan_petugas',
            //'nip_petugas',
            'jenis_puskesmas',
            'telepon_puskesmas',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
