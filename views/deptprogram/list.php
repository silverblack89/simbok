<?php

use yii\helpers\Html;
// use yii\grid\GridView;
use kartik\grid\GridView;
use yii\bootstrap\Button;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\bootstrap\Progress;
use yii\web\Session;
use app\models\Bok;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProgramSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$session = Yii::$app->session;

$this->title = 'Rincian Menu Kegiatan';
    if (Yii::$app->user->identity->group_id == 'ADM'){
        $this->title = 'Seksi '.$namaUnit;
        $this->params['breadcrumbs'][] = ['label' => 'Periode '.$session['deptPeriodValue'], 'url' => ['deptperiod/list', 'period' => $session['deptPeriodValue']]];
        $this->params['breadcrumbs'][] = 'Verifikasi';
    }else{
        $title = 'Rincian Menu Kegiatan';
        $this->params['breadcrumbs'][] = ['label' => $session['deptPeriodValue'], 'url' => ['deptperiod/create']];   
        $this->params['breadcrumbs'][] = $this->title;
    }
?>

<div class="deptprogram-list">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->user->identity->group_id == 'SEK'){
        if($session['status_real'] == 'disabled'){
            $template = '{create} {status}';
            $width = 'width: 9%';
        }elseif($session['status_real'] == 'NULL'){
            $template = '{create} {status}';
            $width = 'width: 9%';
        }else{
            $template = '{create} {status}';
            $width = 'width: 9%';
        }

        $visible = false;
        $visibleStatus = true;

        // echo Progress::widget([
        //     'bars' => [
        //         ['percent' => $session['prosentase'], 'label' => $session['prosentase'].'% dari Pagu BOK (RP. ' .number_format($session['pagu'], 0, ',', '.'). ')', 'options' => ['class' => $session['barColor']]],
        //     ],
        //     'options' => ['class' => $session['barStatus']]
        // ]);
    }else{
        $template = '{lihat}';
        $width = 'width: 5%';

        if (Yii::$app->user->identity->group_id == 'ADM'){
            $visible = false;
        }else{
            $visible = true;
        }
        $visibleStatus = false;
    }
    ?>
    
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'bok_id', 
                'width' => '310px',
                'value' => function($model){
                    $bok = Bok::findOne($model['bok_id']);
                    return $bok->keterangan;
                },
                'contentOptions' => ['style' => 'font-weight:bold;font-size:14px'],
                // 'value' => function ($model, $key, $index, $widget) { 
                //     return $model->bok->keterangan;
                'group' => true,  // enable grouping,
                'groupedRow' => true,                    // move grouped column to a single grouped row
                'groupOddCssClass' => 'kv-grouped-row',  // configure odd group cell css class
                'groupEvenCssClass' => 'kv-grouped-row', // configure even group cell css class
            ],

            // 'id',
            // 'nama_program',
            [
                'label' => 'Nama Program',
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
                        if (Yii::$app->user->identity->group_id == 'ADM'){
                            return Html::a('<span class="glyphicon glyphicon-check"></span> Proses', array('deptactivity/list', 'id'=>$model->id), ['class'=>'btn btn-xs btn-success custom_button']);
                        }else{
                            return Html::a('<span class="glyphicon glyphicon-check"></span> Proses', array('deptactivity/list', 'id'=>$model['id']), ['class'=>'btn btn-xs btn-success custom_button']);
                        }
                    },
                    'lihat' => function ($url, $model, $session) {                
                        $session = Yii::$app->session;
                        if($session['view'] == 'verif') {
                            // return Html::a('<span class="glyphicon glyphicon-list"></span> Detail', array('deptverification/create', 'id' => $model['id'], 'revisi' => 0, 'revised' => 0), ['class'=>'btn btn-xs btn-info custom_button']);
                            return Html::button('<span class="glyphicon glyphicon-check"></span> Detail', 
                            ['value' => Url::to(['deptverification/create', 'id' => $model['id'], 'revisi' => 0, 'revised' => 0]), 'title' => 'Detail POA', 'class' => 'showModalButton btn btn-xs btn-info']);
                        }else{
                            // return Html::a('<span class="glyphicon glyphicon-list"></span> Detail', array('deptverification/view', 'id' => $model['id']), ['class'=>'btn btn-xs btn-info custom_button']);
                            return Html::button('<span class="glyphicon glyphicon-check"></span> Detail', 
                            ['value' => Url::to(['deptverification/view', 'id' => $model['id']]), 'title' => 'Detail POA', 'class' => 'showModalButton btn btn-xs btn-info']);
                        }
                    }
                ]
            ],

            // [
            //     'label' => 'Status',
            //     'content' => function($model) {
            //         return Progress::widget([         
            //             'bars' => [
            //                 ['percent' => 100, 'label' => $model['status'], 'options' => ['class' => $model['barColor']]],
            //             ],
            //         ]);
            //     },
            //     'contentOptions' => ['class' => 'col-lg-1 text-right', 'style' => 'width: 6%'],
            //     'visible' => $visible
            // ],
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