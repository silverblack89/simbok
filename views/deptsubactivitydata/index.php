<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\DeptsubactivitydataSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Deptsubactivitydatas';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="deptsubactivitydata-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Deptsubactivitydata', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'dept_sub_activity_id',
            'dept_period_id',
            'bentuk_kegiatan',
            'indikator_hasil',
            //'target_hasil',
            //'indikator_keluaran',
            //'target_keluaran',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
