<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Authorization';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="auth-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <!-- <p>
        <?= Html::a('Create User', ['create'], ['class' => 'btn btn-success']) ?>
    </p> -->


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'username',
            [
                'label' => 'Username',
                'attribute' =>'nama_group',
                'enableSorting' => false,
            ],
            [
                'header' => 'Auth',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a("<i class='glyphicon glyphicon-lock'></i>", 
                    ['view', 'id'=>$model->id],
                    ['class'=>'btn btn-xs btn-danger custom_button']
                    );
                }
            ]
            

            // ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
