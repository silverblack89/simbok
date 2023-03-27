<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ActivitydetailSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Activitydetails';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="activitydetail-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Activitydetail', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'activity_data_id',
            'account_id',
            'rincian',
            'vol_1',
            //'vol_2',
            //'unit_cost',
            //'jumlah',
            //'jan',
            //'feb',
            //'mar',
            //'apr',
            //'mei',
            //'jun',
            //'jul',
            //'agu',
            //'sep',
            //'okt',
            //'nov',
            //'des',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
