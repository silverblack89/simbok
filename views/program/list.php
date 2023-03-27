<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Button;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\bootstrap\Progress;
use yii\web\Session;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProgramSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$session = Yii::$app->session;

if (Yii::$app->user->identity->unit_id == 'DINKES'){
    $this->title = $title. ' Puskesmas '.$namaUnit;
    $this->params['breadcrumbs'][] = ['label' => 'Data POA Puskesmas '.$session['periodValue'], 'url' => ['period/list', 'period' => $session['periodValue']]];
    $this->params['breadcrumbs'][] = $title;
}else{
    $this->title = 'Rincian Menu';
    $this->params['breadcrumbs'][] = ['label' => $session['periodValue']. $session['poaLabel'], 'url' => ['period/create', 'p' => 'def']];
    $this->params['breadcrumbs'][] = $this->title;
}
?>
<div class="program-list">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->user->identity->unit_id == 'DINKES'){
        $template = '{lihat}';
        $width = 'width: 5%';

        if (Yii::$app->user->identity->username == 'admin'){
            $visible = false;
        }else{
            $visible = true;
        }
        $visibleStatus = false;
    }else{
        if($session['status_real'] == 'disabled'){
            $template = '{create} {status}';
            $width = 'width: 7%';
        }elseif($session['status_real'] == 'NULL'){
            $template = '{create} {status}';
            $width = 'width: 7%';
        }else{
            // $template = '{create} {real} {status}';
            // $width = 'width: 19%';
            $template = '{create} {status}';
            $width = 'width: 7%';
        }

        $visible = false;
        $visibleStatus = true;

        if($session['poa'] == 'def'){
            echo Progress::widget([
                'bars' => [
                    ['percent' => $session['prosentase'], 'label' => $session['prosentase'].'% dari Pagu BOK (RP. ' .number_format($session['pagu'], 0, ',', '.'). ')', 'options' => ['class' => $session['barColor']]],
                ],
                'options' => ['class' => $session['barStatus']]
            ]);
        }

        if($session['poa'] == 'perubahan'){
            echo Progress::widget([
                'bars' => [
                    ['percent' => $session['prosentase'], 'label' => $session['prosentase'].'% dari Pagu Perubahan (RP. ' .number_format($session['pagu_ubah'], 0, ',', '.'). ')', 'options' => ['class' => $session['barColor']]],
                ],
                'options' => ['class' => $session['barStatus']]
            ]);
        }
    }
    ?>

    <!-- <p>
        <?= Html::a('Create Program', ['create'], ['class' => 'btn btn-success']) ?>
    </p> -->

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'id',
            // 'nama_program',
            [
                'label' => 'Nama',
                'attribute' =>'nama_program',
                'enableSorting' => false,
            ],
            // 'aktif',
            
            // ['class' => 'yii\grid\ActionColumn',
            //     'header'=>"Verifikasi",
            //     'visible' => $visibleStatus,
            //     'contentOptions' => ['style' => $width, 'align' => 'center', 'style' => 'width: 6%'],
            //     'template' => $template,
            //     'buttons' => [
            //         'status' => function ($url, $model) {  
            //             if($model['verifikasi'] == 'Revisi'){
            //                 $disabled = false;
            //             }else{
            //                 $disabled = true;
            //             }              
            //             // return Html::a($model['verifikasi'], array('verification/revisi', 'id'=>$model['id'], 'revised'=>0), ['class' => $model['buttonColor'], 'disabled' => $disabled]);
            //             return Html::button($model['verifikasi'], 
            //             ['value' => Url::to(['verification/revisi', 'id'=>$model['id'], 'revised'=>0]), 'title' => '', 'class' => $model['buttonColor'], 'disabled' => $disabled]);
            //         }
            //     ]
            // ],

            ['class' => 'yii\grid\ActionColumn',
                'header'=>"Tindakan",
                'contentOptions' => ['style' => $width],
                'template' => $template,
                'buttons' => [
                    'create' => function ($url, $model) {
                        if (Yii::$app->user->identity->id == 'admin'){
                            return Html::a('<span class="glyphicon glyphicon-check"></span> Proses', array('service/list', 'id'=>$model->id), ['class'=>'btn btn-xs btn-success custom_button']);
                        }else{
                            if($model['akses'] == 1){
                                return Html::a('<span class="glyphicon glyphicon-check"></span> Proses', array('service/list', 'id'=>$model['id']), ['class'=>'btn btn-xs btn-success custom_button']);
                            }
                        }
                    },
                    'real' => function ($url, $model, $session) {
                        // $session = Yii::$app->session;
                        // if($session['status_real']!=='disabled'){
                            return Html::a('<span class="glyphicon glyphicon-stats"></span> Realisasi Kinerja', array('indicator/list', 'id'=>$model['id']), ['class'=>'btn btn-xs btn-info custom_button']);
                        // }
                    },
                    'lihat' => function ($url, $model, $session) {                
                        $session = Yii::$app->session;
                        if($session['view'] == 'verif') {
                            // return Html::a('<span class="glyphicon glyphicon-list"></span> Detail', array('verification/create', 'id' => $model['id'], 'revisi' => 0, 'revised' => 0), ['class'=>'btn btn-xs btn-info custom_button']);
                            return Html::button('<span class="glyphicon glyphicon-check"></span> Detail', 
                            ['value' => Url::to(['verification/create', 'id' => $model['id'], 'revisi' => 0, 'revised' => 0]), 'title' => 'Detail POA', 'class' => 'showModalButton btn btn-xs btn-info']);
                        }else{
                            // return Html::a('<span class="glyphicon glyphicon-list"></span> Detail', array('verification/view', 'id' => $model['id']), ['class'=>'btn btn-xs btn-info custom_button']);
                            return Html::button('<span class="glyphicon glyphicon-check"></span> Detail', 
                            ['value' => Url::to(['verification/view', 'id' => $model['id']]), 'title' => 'Detail POA', 'class' => 'showModalButton btn btn-xs btn-info']);
                        }
                    }
                ]
            ],

            [
                'label' => 'Status',
                'content' => function($model) {
                    return Progress::widget([         
                        'bars' => [
                            ['percent' => 100, 'label' => $model['status'], 'options' => ['class' => $model['barColor']]],
                        ],
                    ]);
                },
                'contentOptions' => ['class' => 'col-lg-1 text-right', 'style' => 'width: 6%'],
                'visible' => $visible
            ],
        ],
    ]); ?>

    <?php 
        Modal::begin([
                // 'header'=>'<h4>Detail Kegiatan</h4>', 
                'id'=>'modal',
                'size'=>'modal-lg',
                'clientOptions' => ['backdrop' => 'static', 'keyboard' => FALSE],
                // 'footer' => ''
            ]);
        echo "<div id='modalContent'></div>";
        Modal::end();
    ?>

</div>