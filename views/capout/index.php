<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\CapoutSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Capouts';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="capout-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Capout', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'nomor',
            'unit_id',
            'jml_ke',
            'jml_confirm',
            //'tenaga_tracer',
            //'tenaga_surveilans',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
