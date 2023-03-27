<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\Session;
use yii\helpers\Url;
use yii\bootstrap\Modal;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $searchModel app\models\UkpaguSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Pagu';
$this->params['breadcrumbs'][] = ['label' => $session['periodValue'], 'url' => ['period/create', 'p' => 'def']];
$this->params['breadcrumbs'][] = ['label' => 'Realisasi', 'url' => ['ukm/index', 'tahun' => $session['tahun']]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ukpagu-index">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <div class="panel panel-primary">
        <div class="panel-heading">
                <h3 class="panel-title">Data Pagu</h3>
        </div>
        <div class="panel-body">

            <p>
                <!-- <?= Html::a('Tambah', ['create'], ['class' => 'btn btn-success']) ?> -->
                <?= Html::button('<span class="glyphicon glyphicon-plus"></span> Entri Baru', ['value' => Url::to(['ukpagu/create', 'tahun' => $tahun]), 'class' => 'showModalButton btn btn-success']) ?>
            </p>

            <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                // 'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    // 'id',
                    [
                        'attribute' => 'uklabel',
                        'label' => 'Upaya Kesehatan',
                        'value' => 'uklabel.uk_desk'
                    ],
                    // 'unit_id',
                    [
                        'attribute'  => 'jumlah',
                        'format'=>['decimal',0],
                        'contentOptions' => ['class' => 'col-md-1 text-right', 'style' => 'width: 20%'],
                    ],

                    // ['class' => 'yii\grid\ActionColumn'],
                    ['class' => 'yii\grid\ActionColumn',
                        'contentOptions' => ['style' => 'width: 13%'],
                        'template' => '{update} {delete}',
                        'buttons' => [
                            'delete' => function ($url, $model) {
                                return 
                                Html::a('<span class="glyphicon glyphicon-trash"></span> Hapus', ['ukpagu/delete', 'id' => $model->id], [
                                    'class'=>'btn btn-xs btn-danger custom_button',
                                    'data' => ['confirm' => Yii::t('app', 'Apakah Anda yakin akan menghapus data ini?'),'method' => 'post']]); 
                            },
                            'update' => function ($url, $model, $session) {
                                return 
                                // Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['ukpagu/update', 'id' => $model->id]); 
                                Html::button('<span class="glyphicon glyphicon-pencil"></span> Ubah', ['value' => Url::to(['ukpagu/update', 'id'=>$model->id]), 'class' => 'showModalButton btn btn-xs btn-warning custom_button']);
                            },
                        ]
                    ],
                ],
            ]); ?>
        </div>
    </div>

</div>

<?php 
    Modal::begin([
            // 'header'=>'<h4>Login User</h4>',
            'id'=>'modal',
            'size'=>'modal-md',
            'clientOptions' => ['backdrop' => 'dinamis', 'keyboard' => FALSE],
            // 'footer' => ''
        ]);
    echo "<div id='modalContent'></div>";
    Modal::end();
?>
