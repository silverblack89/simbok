<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\DeptsubactivitydetailSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Deptsubactivitydetails';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="deptsubactivitydetail-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Deptsubactivitydetail', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'dept_sub_activity_data_id',
            'account_id',
            'vol_1',
            'satuan_1',
            //'vol_2',
            //'satuan_2',
            //'unit_cost',
            //'jumlah',
            //'tw1',
            //'tw2',
            //'tw3',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
