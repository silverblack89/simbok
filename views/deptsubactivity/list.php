<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Deptsubactivity;
use app\models\DeptsubactivitySearch;
use yii\bootstrap\Progress;
use yii\web\Session;
use yii\bootstrap\ButtonDropdown;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ActivitySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$session = Yii::$app->session;

$this->title = 'Kegiatan';
$this->params['breadcrumbs'][] = ['label' => $session['deptPeriodValue'], 'url' => ['deptperiod/create']];
$this->params['breadcrumbs'][] = ['label' => 'Rincian Menu Kegiatan', 'url' => ['deptprogram/list']];
$this->params['breadcrumbs'][] = ['label' => 'Komponen', 'url' => ['deptactivity/list', 'id' => $session['deptProgramId']]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="deptsubactivity-list">

    <h1><?= Html::encode($session['deptActivityName']) ?></h1>

    <?php if (Yii::$app->user->identity->group_id == 'SEK'){
        if($session['status_real'] == 'disabled'){
            $template = '{create} {status}';
            $width = 'width: 9%';
        }elseif($session['status_real'] == 'NULL'){
            $template = '{create} {status}';
            $width = 'width: 9%';
        }else{
            $template = '{create} {status}'; //{real}
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

    <!-- <?php
        echo Progress::widget([
            'bars' => [
                ['percent' => $session['prosentase'], 'label' => $session['prosentase'].'% dari Pagu BOK (RP. ' .number_format($session['pagu'], 0, ',', '.'). ')', 'options' => ['class' => $session['barColor']]],
            ],
            'options' => ['class' => $session['barStatus']]
        ]);
    ?> -->

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'id',
            // 'service_id',
            // 'nama_kegiatan:ntext',
            [
                'label' => 'Nama Sub Kegiatan',
                'attribute' =>'nama_sub_kegiatan',
                'enableSorting' => false,
            ],
            // [
            //     'label' => 'Total',
            //     'value' => function($model){
            //         return $model->getTotal($model->id);
            //     },
            //     'format' => ['decimal',0],
            //     'contentOptions' => ['style' => 'text-align:right;']
            // ],
            // 'aktif',

            [
                'attribute' =>'status',
                'enableSorting' => false,
                'contentOptions' => function($model){
                    if($model->status == 'Wajib'){
                        return ['style' => 'font-weight:bold;color:red;'];
                    }else{
                        return ['style' => 'font-weight:bold;color:orange;'];
                    }
                },
            ],

            ['class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['style' => 'width: 2%'],
                'template' => $template,
                'buttons' => [
                    'create' => function ($url, $model){    
                        // $session = Yii::$app->session;
                        // $session->open();
                        // $session['activityId'] = $model->id; 
                        // $session['activityName'] = $model->nama_kegiatan; 
                        // if(empty($model->getStatus($model->id))){
                            return Html::a('<span class="glyphicon glyphicon-check"></span> Proses', ['deptsubactivitydata/list', 'id' => $model->id], ['class'=>'btn btn-xs btn-success btn-xs custom_button']);
                        // }

                        // return $model->getStatus($model->id);
                    },
                    'real' => function ($url, $model, $session) {
                        $session = Yii::$app->session;
                        if($session['status_real']!=='disabled'){
                            // return Html::a('<span class="glyphicon glyphicon-stats"></span> Realisasi Kinerja', array('indicator/list', 'id'=>$model['id']), ['class'=>'btn btn-xs btn-info custom_button']);
                            
                            if($session['deptGroupSp2dId2'] == '0'){
                                return Html::a('<span class="glyphicon glyphicon-stats"></span> Realisasi', array('/deptreal', 'id' => $model['id'], 'st' => '1_'.$session['deptGroupSp2dId1']), ['class'=>'btn btn-xs btn-info custom_button']);
                            }else{
                                return ButtonDropdown::widget([
                                    'encodeLabel' => false,
                                    'label' => '<span class="glyphicon glyphicon-stats"></span> Realisasi',
                                    'dropdown' => [
                                        'items' => [
                                            ['label' => \Yii::t('yii', $model->getGroup($session['deptGroupSp2dId1'])),
                                                // 'linkOptions' => [
                                                //     'data' => [
                                                //         'method' => 'POST',
                                                //         // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                                //     ],
                                                // ],
                                                'url' => Url::to(['/deptreal', 'id' => $model['id'], 'st' => '1_'.$session['deptGroupSp2dId1']]),
                                                'visible' => true,   // same as above
                                            ],
        
                                            ['label' => \Yii::t('yii', $model->getGroup($session['deptGroupSp2dId2'])),
                                                // 'linkOptions' => [
                                                //     'data' => [
                                                //         'method' => 'POST',
                                                //         // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                                //     ],
                                                // ],
                                                'url' => Url::to(['/deptreal', 'id' => $model['id'], 'st' => '2_'.$session['deptGroupSp2dId2']]),
                                                'visible' => true,   // same as above
                                            ],
                                            
                                        ],
                                    ],
                                    'options' => ['class' => 'btn btn-xs btn-info custom_button'],
                                ]);
                            }
                        }
                    },
                ]],
        ],
    ]); ?>

</div>
