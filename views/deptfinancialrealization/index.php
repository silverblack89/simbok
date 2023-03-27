<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\DeptfinancialrealizationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Deptfinancialrealizations';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="deptfinancialrealization-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Deptfinancialrealization', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'dept_sub_activity_detail_id',
            'dept_sub_activity_detail_ubah_id',
            'bulan',
            'realisasi_vol_1',
            //'realisasi_satuan_1',
            //'realisasi_vol_2',
            //'realisasi_satuan_2',
            //'realisasi_vol_3',
            //'realisasi_satuan_3',
            //'realisasi_vol_4',
            //'realisasi_satuan_4',
            //'realisasi_unit_cost',
            //'realisasi_jumlah',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
