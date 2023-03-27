<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\PerfomanceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Perfomances';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="perfomance-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Perfomance', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'triwulan',
            'tahun',
            'activity_data_id',
            'target_awal',
            //'satuan_awal',
            //'target_real',
            //'satuan_real',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
