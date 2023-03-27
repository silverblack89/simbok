<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\DetailView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\web\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $searchModel app\models\DeptrealSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Realisasi Bulanan';
// $this->params['breadcrumbs'][] = ['label' => 'Data Program ('.$session['deptPeriodValue'].')', 'url' => ['deptprogram/list', 'tahun' => $session['deptPeriodValue']]];
$this->params['breadcrumbs'][] = ['label' => $session['deptPeriodValue'], 'url' => ['deptperiod/create']];
$this->params['breadcrumbs'][] = ['label' => 'Rincian Menu Kegiatan', 'url' => ['deptprogram/list']];
$this->params['breadcrumbs'][] = ['label' => 'Komponen', 'url' => ['deptactivity/list', 'id' => $session['deptProgramId']]];
$this->params['breadcrumbs'][] = ['label' => 'Kegiatan', 'url' => ['deptsubactivity/list', 'id' => $session['deptActivityId']]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="deptreal-index">

    <h1><?= Html::encode($title) ?></h1>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="row">
        <div class="col-md-8">
            <div class="panel panel-primary">
                <div class="panel-heading">
                        <h3 class="panel-title">Data Realisasi</h3>
                </div>
                <div class="panel-body">
                    <?php if($real['poa'] > 0){ ?>
                    <p>
                        <!-- <?= Html::a('Create Deptreal', ['create', 'id' => $id, 'st' => $st], ['class' => 'btn btn-success']) ?> -->
                        <?= Html::button('<span class="glyphicon glyphicon-plus"></span> Tambah', ['value' => Url::to(['deptreal/create', 'id' => $id, 'st' => $st]), 'class' => 'showModalButton btn btn-success']) ?>
                    </p>
                    <?php } ?>

                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        // 'filterModel' => $searchModel,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn',
                                'contentOptions' => ['style' => 'width: 5%'],
                            ],

                            // 'id',
                            // 'dept_program_id',
                            // 'dept_period_id',
                            [
                                'attribute' => 'bulan',
                                'value' => function($model){
                                if($model->bulan == '1'){
                                    return 'Januari';
                                }
                                if($model->bulan == '2'){
                                    return 'Februari';
                                }
                                if($model->bulan == '3'){
                                    return 'Maret';
                                }
                                if($model->bulan == '4'){
                                    return 'April';
                                }
                                if($model->bulan == '5'){
                                    return 'Mei';
                                }
                                if($model->bulan == '6'){
                                    return 'Juni';
                                }
                                if($model->bulan == '7'){
                                    return 'Juli';
                                }
                                if($model->bulan == '8'){
                                    return 'Agustus';
                                }
                                if($model->bulan == '9'){
                                    return 'September';
                                }
                                if($model->bulan == '10'){
                                    return 'Oktober';
                                }
                                if($model->bulan == '11'){
                                    return 'November';
                                }
                                if($model->bulan == '12'){
                                    return 'Desember';
                                }
                                }
                            ],
                            [
                                'attribute' => 'jumlah',
                                'format'=>['decimal',0],
                                'contentOptions'=>['class' => 'text-right']
                            ],
                            //'modified_at',

                            // ['class' => 'yii\grid\ActionColumn'],
                            ['class' => 'yii\grid\ActionColumn',
                                'contentOptions' => ['style' => 'width: 5%'],
                                'template' => '{delete}',
                                'buttons' => [
                                    'view' => function ($url, $model) {
                                        return 
                                        // Html::a('<span class="glyphicon glyphicon-eye-open"></span>', ['view', 'id' => $model->id]); 
                                        Html::button('<span class="glyphicon glyphicon-eye-open"></span>', ['value' => Url::to(['deptreal/view', 'id'=>$model->id]), 'class' => 'showModalButton btn btn-xs btn-link']);
                                    },
                                    'delete' => function ($url, $model) {
                                        return 
                                        Html::a('<span class="glyphicon glyphicon-trash"></span>', ['deptreal/delete', 'id' => $model->id], ['data' => ['confirm' => Yii::t('app', 'Apakah Anda yakin akan menghapus data ini?'),'method' => 'post',],]); 
                                    },
                                    'update' => function ($url, $model) {
                                        return 
                                        // Html::button('<span class="glyphicon glyphicon-pencil"></span>', ['value' => Url::to(['deptreal/update', 'id'=>$model->id]), 'class' => 'showModalButton btn btn-xs btn-link']);
                                        Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['deptreal/update', 'id' => $model->id]); 
                                    },
                                ]
                            ],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-primary">
                <div class="panel-heading">
                        <h3 class="panel-title">Data POA</h3>
                </div>
                <div class="panel-body">
                    <?= DetailView::widget([
                        'model' => $real,
                        'attributes' => [
                            [
                                'attribute' => 'poa',
                                'label' => 'Total POA',
                                'format'=>['decimal',0],
                                'contentOptions' => ['class' => 'text-right'],
                            ],
                            [
                                'attribute' => 'realisasi',
                                'label' => 'Total Realisasi',
                                'format'=>['decimal',0],
                                'contentOptions' => ['class' => 'text-right'],
                            ],
                            [
                                'label' => 'Capaian',
                                'value' => function($real){
                                    return number_format($real['prosentase'],2). ' %';
                                },
                                // 'format'=>['decimal',2],
                                'contentOptions' => ['class' => 'text-right'],
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
    Modal::begin([
            // 'header'=>'<h4>Detail Kegiatan</h4>', 
            'id'=>'modal',
            'size'=>'modal-sm',
            'clientOptions' => ['backdrop' => 'static', 'keyboard' => FALSE],
            // 'footer' => ''
        ]);
    echo "<div id='modalContent'></div>";
    Modal::end();

$js=<<< JS
$(".alert").animate({opacity: 1.0}, 3000).fadeOut("slow");
JS;
$this->registerJs($js, yii\web\View::POS_READY);
?>
