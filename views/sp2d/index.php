<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\Session;
use yii\helpers\Url;
use yii\bootstrap\Modal;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $searchModel app\models\Sp2dSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'SP2D';
// $this->params['breadcrumbs'][] = ['label' => $session['periodValue'], 'url' => ['period/create', 'p' => 'def']];
// $this->params['breadcrumbs'][] = ['label' => 'Realisasi', 'url' => ['ukm/index', 'tahun' => $session['tahun']]];

if(Yii::$app->user->identity->username == 'admin'){
    $this->params['breadcrumbs'][] = ['label' => 'Data POA Puskesmas '.$session['periodValue'], 'url' => ['period/list', 'period' => $session['periodValue']]];
}else{
    $this->params['breadcrumbs'][] = ['label' => 'POA '.$session['periodValue'], 'url' => ['period/create', 'p' => 'def']];  
}
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sp2d-index">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <div class="panel panel-primary">
        <div class="panel-heading">
                <h3 class="panel-title">Data SP2D Puskesmas <?php echo $puskesmas ?></h3>
        </div>
        <div class="panel-body">

            <?php if(Yii::$app->user->identity->username == 'admin'){ $visible = true; ?>
            <p>
                <!-- <?= Html::a('Tambah', ['sp2d/create', 'unit' => $unit], ['class' => 'btn btn-success']) ?> -->
                <?= Html::button('<span class="glyphicon glyphicon-plus"></span> Entri Baru', ['value' => Url::to(['sp2d/create', 'unit' => $unit]), 'class' => 'showModalButton btn btn-success']) ?>
            </p>
            <?php }else{$visible = false;} ?>

            <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

            <div style="overflow-x:auto">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                // 'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    // 'id',
                    [
                        'attribute' => 'tanggal',
                        'contentOptions' => ['style' => 'width: 100px']
                    ],
                    'no_sp2d',
                    'jenis_spm',
                    'uraian',
                    [
                        'attribute' => 'jumlah',
                        'format'=>['decimal',0],
                        'contentOptions' => ['class' => 'col-lg-1 text-right'],
                    ],

                    // ['class' => 'yii\grid\ActionColumn'],
                    ['class' => 'yii\grid\ActionColumn',
                        'contentOptions' => ['style' => 'width: 13%'],
                        'template' => '{update} {delete}',
                        'visible' => $visible,
                        'buttons' => [
                            'delete' => function ($url, $model, $session) {
                                $session = Yii::$app->session;
                                return 
                                Html::a('<span class="glyphicon glyphicon-trash"></span> Hapus', ['sp2d/delete', 'id' => $model->id, 'unit' => $session['unit']], [
                                    'class'=>'btn btn-xs btn-danger custom_button',
                                    'data' => ['confirm' => Yii::t('app', 'Apakah Anda yakin akan menghapus data ini?'),'method' => 'post']]); 
                            },
                            'update' => function ($url, $model, $session) {
                                return 
                                // Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['sp2d/update', 'id' => $model->id]); 
                                Html::button('<span class="glyphicon glyphicon-pencil"></span> Ubah', ['value' => Url::to(['sp2d/update', 'id'=>$model->id]), 'class' => 'showModalButton btn btn-xs btn-warning custom_button']);
                            },
                        ]
                    ],
                ],
            ]); ?>
            </div>
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
