<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\FinancialrealizationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Financialrealizations';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="financialrealization-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Financialrealization', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'activity_detail_id',
            'bulan',
            'realisasi_vol_1',
            'realisasi_vol_2',
            //'realisasi_unit_cost',
            //'realisasi_jumlah',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
