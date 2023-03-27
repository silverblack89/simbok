<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\DeptrealizationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Deptrealizations';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="deptrealization-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Deptrealization', ['create'], ['class' => 'btn btn-success']) ?>
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
            'dept_sub_activity_detail_id',
            'jumlah',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
