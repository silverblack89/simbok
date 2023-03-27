<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\RealizationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Realizations';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="realization-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Realization', ['create'], ['class' => 'btn btn-success']) ?>
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
            'activity_detail_id',
            'jumlah',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
