<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\session;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\bootstrap\Progress;
use yii\bootstrap\ButtonDropdown;

$session = Yii::$app->session;

$this->title = 'Data POA';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="deptperiod-list">

    <h1><?= Html::encode($this->title.' Dinas Kesehatan') ?></h1>

    <?php 
    if (Yii::$app->user->identity->username == 'admin'){
        $class = 'form-control pull-left';
        $template = '{poa} {geser} {rubah} {tw1} {tw2} {tw3} {tw4}'; //{real}
        $visible = true;
        $width = 'width: 20%';
    }else{
        $class = 'form-control';
        $template = '{lihat}';
        $visible = false;
        $width = 'width: 8%';
    }
    ?>

    <p>
    <div class="form-group">
                <?= Html::dropDownList('tahun', null, [ date('Y')-1 => date('Y')-1, date('Y') => date('Y'), date('Y')+1 => date('Y')+1, ] ,
                [
                    // 'prompt'=>'Pilih Periode',
                    'options'=>[$session['deptPeriodValue']=>['Selected'=>true]],
                    'style' => 'width:80px; margin-right:5px !important;', 
                    'onchange'=>'
                        $.pjax.reload({
                            url: "'.Url::toRoute(['deptperiod/list']).'?period="+$(this).val(),
                            container: "#pjax-gridview",
                            timeout: 1000,
                        });',
                    'class'=>$class]) 
                ?>

                <?php if (Yii::$app->user->identity->username == 'admin'){ ?>
                <?=                                      
                ButtonDropdown::widget([
                    'encodeLabel' => false,
                    'label' => '<span class="glyphicon glyphicon-check"></span> POA',
                    'dropdown' => [
                        'items' => [
                            ['label' => \Yii::t('yii', 'Buka Semua'),
                                'linkOptions' => [
                                    'data' => [
                                        'method' => 'POST',
                                        // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                    ],
                                ],
                                'url' => Url::to(['unlockall', 'id' => 'P']),
                                'visible' => true,   // same as above
                            ],
                            ['label' => \Yii::t('yii', 'Kunci Semua'),
                                'linkOptions' => [
                                    'data' => [
                                        'method' => 'POST',
                                        // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                    ],
                                ],
                                'url' => Url::to(['lockall', 'id' => 'P']),
                                'visible' => true,   // same as above
                            ],
                            ['label' => \Yii::t('yii', 'Export Excel - POA Per Rekening'),
                                'linkOptions' => [
                                    'data' => [
                                        'method' => 'POST',
                                        // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                    ],
                                ],
                                'url' => Url::to(['exportxlsall']),
                                'visible' => true,   // same as above
                            ],
                        ],
                    ],
                    'options' => ['class' => 'btn btn-md btn-success custom_button'],
                ]);
                ?>

                <?php                                      
                // ButtonDropdown::widget([
                //     'encodeLabel' => false,
                //     'label' => '<span class="glyphicon glyphicon-transfer"></span> Pergeseran',
                //     'dropdown' => [
                //         'items' => [
                //             ['label' => \Yii::t('yii', 'Buka Semua'),
                //                 'linkOptions' => [
                //                     'data' => [
                //                         'method' => 'POST',
                //                         // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                //                     ],
                //                 ],
                //                 'url' => Url::to(['unlockall', 'id' => 'G']),
                //                 'visible' => true,   // same as above
                //             ],
                //             ['label' => \Yii::t('yii', 'Kunci Semua'),
                //                 'linkOptions' => [
                //                     'data' => [
                //                         'method' => 'POST',
                //                         // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                //                     ],
                //                 ],
                //                 'url' => Url::to(['lockall', 'id' => 'G']),
                //                 'visible' => true,   // same as above
                //             ],
                //         ],
                //     ],
                //     'options' => ['class' => 'btn btn-md btn-danger custom_button'],
                // ]);
                ?>

                <?php                                      
                // ButtonDropdown::widget([
                //     'encodeLabel' => false,
                //     'label' => '<span class="glyphicon glyphicon-pencil"></span> Perubahan',
                //     'dropdown' => [
                //         'items' => [
                //             ['label' => \Yii::t('yii', 'Buka Semua'),
                //                 'linkOptions' => [
                //                     'data' => [
                //                         'method' => 'POST',
                //                         // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                //                     ],
                //                 ],
                //                 'url' => Url::to(['unlockall', 'id' => 'R']),
                //                 'visible' => true,   // same as above
                //             ],
                //             ['label' => \Yii::t('yii', 'Kunci Semua'),
                //                 'linkOptions' => [
                //                     'data' => [
                //                         'method' => 'POST',
                //                         // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                //                     ],
                //                 ],
                //                 'url' => Url::to(['lockall', 'id' => 'R']),
                //                 'visible' => true,   // same as above
                //             ],
                //         ],
                //     ],
                //     'options' => ['class' => 'btn btn-md btn-warning custom_button'],
                // ]);
                ?>

                <?=                                 
                ButtonDropdown::widget([
                    'encodeLabel' => false,
                    'label' => '<span class="glyphicon glyphicon-stats"></span>  Realisasi',
                    'dropdown' => [
                        'items' => [
                            ['label' => \Yii::t('yii', 'Buka Semua'),
                                'linkOptions' => [
                                    'data' => [
                                        'method' => 'POST',
                                        // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                    ],
                                ],
                                'url' => Url::to(['unlockall', 'id' => 'L']),
                                'visible' => false,   // same as above
                            ],
                            ['label' => \Yii::t('yii', 'Kunci Semua'),
                                'linkOptions' => [
                                    'data' => [
                                        'method' => 'POST',
                                        // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                    ],
                                ],
                                'url' => Url::to(['lockall', 'id' => 'L']),
                                'visible' => false,   // same as above
                            ],
                            ['label' => \Yii::t('yii', 'Triwulan I - Buka Semua'),
                                'linkOptions' => [
                                    'data' => [
                                        'method' => 'POST',
                                        // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                    ],
                                ],
                                'url' => Url::to(['unlockallreal', 'id' => 'L', 'tw' => '1']),
                                'visible' => true,   // same as above
                            ],
                            ['label' => \Yii::t('yii', 'Triwulan II - Buka Semua'),
                                'linkOptions' => [
                                    'data' => [
                                        'method' => 'POST',
                                        // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                    ],
                                ],
                                'url' => Url::to(['unlockallreal', 'id' => 'L', 'tw' => '2']),
                                'visible' => true,   // same as above
                            ],
                            ['label' => \Yii::t('yii', 'Triwulan III - Buka Semua'),
                                'linkOptions' => [
                                    'data' => [
                                        'method' => 'POST',
                                        // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                    ],
                                ],
                                'url' => Url::to(['unlockallreal', 'id' => 'L', 'tw' => '3']),
                                'visible' => true,   // same as above
                            ],
                            ['label' => \Yii::t('yii', 'Triwulan IV - Buka Semua'),
                                'linkOptions' => [
                                    'data' => [
                                        'method' => 'POST',
                                        // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                    ],
                                ],
                                'url' => Url::to(['unlockallreal', 'id' => 'L', 'tw' => '4']),
                                'visible' => true,   // same as above
                            ],

                            ['label' => \Yii::t('yii', 'Triwulan I - Kunci Semua'),
                                'linkOptions' => [
                                    'data' => [
                                        'method' => 'POST',
                                        // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                    ],
                                ],
                                'url' => Url::to(['lockallreal', 'id' => 'L', 'tw' => '1']),
                                'visible' => true,   // same as above
                            ],
                            ['label' => \Yii::t('yii', 'Triwulan II - Kunci Semua'),
                                'linkOptions' => [
                                    'data' => [
                                        'method' => 'POST',
                                        // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                    ],
                                ],
                                'url' => Url::to(['lockallreal', 'id' => 'L', 'tw' => '2']),
                                'visible' => true,   // same as above
                            ],
                            ['label' => \Yii::t('yii', 'Triwulan III - Kunci Semua'),
                                'linkOptions' => [
                                    'data' => [
                                        'method' => 'POST',
                                        // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                    ],
                                ],
                                'url' => Url::to(['lockallreal', 'id' => 'L', 'tw' => '3']),
                                'visible' => true,   // same as above
                            ],
                            ['label' => \Yii::t('yii', 'Triwulan IV - Kunci Semua'),
                                'linkOptions' => [
                                    'data' => [
                                        'method' => 'POST',
                                        // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                    ],
                                ],
                                'url' => Url::to(['lockallreal', 'id' => 'L', 'tw' => '4']),
                                'visible' => true,   // same as above
                            ],
                        ],
                    ],
                    'options' => ['class' => 'btn btn-md btn-info custom_button'],
                ]);
                ?>

                <!-- <?= Html::a('<span class="glyphicon glyphicon-list-alt"></span> Rekap', ['rekap-rekening'], ['class' => 'btn btn-primary pull-right']) ?> -->
                <!-- <?= Html::button('<span class="glyphicon glyphicon-list-alt"></span> Rekap Per Rekening', 
                        ['value' => Url::to(['deptperiod/rekap-rekening']), 'class' => 'showModalButton btn btn-primary pull-right']) ?> -->

                <?php } ?>

                <?=                                 
                ButtonDropdown::widget([
                    'encodeLabel' => false,
                    'label' => '<span class="glyphicon glyphicon-list"></span> Rekap',
                    'dropdown' => [
                        'items' => [
                            ['label' => \Yii::t('yii', 'Komponen per Rekening'),
                                // 'linkOptions' => [
                                //     'data' => [
                                //         'method' => 'POST',
                                //         // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                //     ],
                                // ],
                                'url' => Url::to(['rekap-rekening']),
                                'visible' => true,   // same as above
                            ],
                            ['label' => \Yii::t('yii', 'Komponen per Puskesmas'),
                                'url' => Url::to(['rekap-pkm']),
                                'visible' => false,   // same as above
                            ],
                            ['label' => \Yii::t('yii', 'Detail per Komponen'),
                                'url' => Url::to(['rekap-komponen-detail', 'cond' => 'def']),
                                'visible' => true,   // same as above
                            ],
                            ['label' => \Yii::t('yii', 'Detail per Sub Kegiatan DPA'),
                                'url' => Url::to(['rekap-dpa-detail', 'id' => 0]),
                                'visible' => true,   // same as above
                            ],
                            ['label' => \Yii::t('yii', 'RAB per Menu Kegiatan'),
                                'url' => Url::to(['rekap-komponen-detail', 'cond' => 'def']),
                                'visible' => true,   // same as above
                            ],
                            ['label' => \Yii::t('yii', 'RKA APBD'),
                                'url' => Url::to(['rka-apbd', 'cond' => 'def']),
                                'visible' => true,   // same as above
                            ],
                            ['label' => \Yii::t('yii', 'SP2D'),
                                'url' => Url::to(['rekap-sp2d', 'id' => '1']),
                                'visible' => true,   // same as above
                            ],
                            ['label' => \Yii::t('yii', 'Realisasi - Rekap Per Upaya'),
                                'url' => Url::to(['rekap-real', 'cond' => 'def']),
                                'visible' => true,   // same as above
                            ],
                        ],
                    ],
                    'options' => ['class' => 'btn btn-primary pull-right'],
                ]);
                ?>
            </div>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php Pjax::begin(['id' => 'pjax-gridview']) ?>
    <div style="overflow-x:auto;">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn',
                'contentOptions' => ['style' => 'width: 6%'],
            ],
            [
                'attribute' => 'puskesmas',
                'label' => 'Seksi'
            ],
            // [
            //     'label' => 'Pagu BOK',
            //     'attribute' =>'pagu',
            //     'enableSorting' => false,
            //     'contentOptions' => ['class' => 'col-lg-1 text-right'],
            //     'format'=>['decimal',0]
            // ],
            // 'jumlah',
            // [
            //     'label' => 'POA',
            //     'attribute' =>'jumlah',
            //     'enableSorting' => false,
            //     'contentOptions' => ['class' => 'col-lg-1 text-right'],
            //     'format'=>['decimal',0]
            // ],
            // 'prosentase',
            [
                'label' => 'POA Awal',
                // 'content' => function($model) {
                //     return Progress::widget([         
                //         'bars' => [
                //             ['percent' => $model['prosentase'], 'label' => $model['prosentase'].'%', 'options' => ['class' => $model['bar_color']]],
                //         ],
                //         'options' => ['class' => $model['status_bar'], 'style' => 'width: 75%']
                //     ]);
                // },
                'attribute' =>'jumlah',
                'format'=>['decimal',0],
                'contentOptions' => ['style' => 'width: 10%; text-align:right;']
            ],

            ['class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['style' => $width],
                'header'=>"Tindakan",
                'visible' => $visible, 
                'template' => $template,
                'buttons' => [
                    'poa' => function ($url, $model) {
                        // return Html::a('<span class="glyphicon glyphicon-check"></span> POA', array('exportxlsadm', 'unit_id' => $model['unit_id']), ['class'=>'btn btn-xs btn-success custom_button']);
                        return ButtonDropdown::widget([
                            'encodeLabel' => false,
                            'label' => '<span class="'.$model['status_poa_icon'].'"></span> Menu',
                            'dropdown' => [
                                'items' => [
                                    ['label' => \Yii::t('yii', $model['status_poa']),
                                        'linkOptions' => [
                                            'data' => [
                                                'method' => 'POST',
                                                // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                            ],
                                        ],
                                        'url' => Url::to(['lock', 'id' => $model['unit_id']. 'P']),
                                        'visible' => true,   // same as above
                                    ],

                                    ['label' => \Yii::t('yii', 'Verifikasi'),
                                        // 'linkOptions' => [
                                        //     'data' => [
                                        //         'method' => 'POST',
                                        //         // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                        //     ],
                                        // ],
                                        'url' => Url::to(['deptprogram/verif', 'id' => $model['unit_id'], 'p' => 'def']),
                                        'visible' => false,   // same as above
                                    ],
                                    
                                    ['label' => \Yii::t('yii', 'Lihat Data'),
                                        'linkOptions' => [
                                            'data' => [
                                                'method' => 'POST',
                                                // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                            ],
                                        ],
                                        // 'url' => Url::to(['exportxlsadm', 'unit_id' => $model['unit_id']]),
                                        'url' => Url::to(['deptperiod/datapoaadm', 'id' => $model['unit_id'], 'bok' => '1']),
                                        'visible' => true,   // same as above
                                    ],
                                ],
                            ],
                            'options' => ['class' => 'btn btn-xs btn-success custom_button'],
                        ]);
                    },
                    
                ],
            ],

            // [
            //     'label' => 'Perubahan',
            //     'content' => function($model) {
            //         return Progress::widget([         
            //             'bars' => [
            //                 ['percent' => $model['prosentase_ubah'], 'label' => $model['prosentase_ubah'].'%', 'options' => ['class' => $model['bar_color_ubah']]],
            //             ],
            //             'options' => ['class' => $model['status_bar_ubah'], 'style' => 'width: 75%']
            //         ]);
            //     },
            // ],

            // ['class' => 'yii\grid\ActionColumn',
            //     'contentOptions' => ['style' => $width],
            //     'header'=>"Tindakan",
            //     'template' => $template,
            //     'visible' => $visible, 
            //     'buttons' => [
            //         'poa' => function ($url, $model) {
            //             // return Html::a('<span class="glyphicon glyphicon-check"></span> POA', array('exportxlsadm', 'unit_id' => $model['unit_id']), ['class'=>'btn btn-xs btn-success custom_button']);
            //             return ButtonDropdown::widget([
            //                 'encodeLabel' => false,
            //                 'label' => '<span class="'.$model['status_rubah_icon'].'"></span> Menu',
            //                 'dropdown' => [
            //                     'items' => [
            //                         ['label' => \Yii::t('yii', $model['status_rubah']),
            //                             'linkOptions' => [
            //                                 'data' => [
            //                                     'method' => 'POST',
            //                                     // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
            //                                 ],
            //                             ],
            //                             'url' => Url::to(['lock', 'id' => $model['unit_id']. 'R']),
            //                             'visible' => true,   // same as above
            //                         ],

            //                         ['label' => \Yii::t('yii', 'Lihat Data'),
            //                             'url' => Url::to(['dataubah', 'id' => $model['unit_id'], 'p' => 'perubahan']),
            //                             'visible' => true,   // same as above
            //                         ],

            //                         ['label' => \Yii::t('yii', 'Verifikasi Data'),
            //                             'url' => Url::to(['program/verif', 'id' => $model['unit_id'], 'p' => 'perubahan']),
            //                             'visible' => true,   // same as above
            //                         ],
                                    
            //                         ['label' => \Yii::t('yii', 'Export Excel'),
            //                             'linkOptions' => [
            //                                 'data' => [
            //                                     'method' => 'POST',
            //                                     // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
            //                                 ],
            //                             ],
            //                             'url' => Url::to(['#']),
            //                             'visible' => false,   // same as above
            //                         ],
            //                     ],
            //                 ],
            //                 'options' => ['class' => 'btn btn-xs btn-warning custom_button'],
            //             ]);
            //         },
            //     ],
            // ],

            [
                'label' => 'Realisasi',
                'attribute' =>'persen',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right']
            ],

            // ['class' => 'yii\grid\ActionColumn'],
            ['class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['style' => $width],
                'header'=>"Tindakan",
                'template' => $template,
                'buttons' => [
                    'tw1' => function ($url, $model) {
                        return ButtonDropdown::widget([
                            'encodeLabel' => false,
                            'label' => '<span class="'.$model['status_real_icon_tw1'].'"></span> I',
                            'dropdown' => [
                                'items' => [
                                    ['label' => \Yii::t('yii', $model['label_real_icon_tw1']),
                                        'linkOptions' => [
                                            'data' => [
                                                'method' => 'POST',
                                                // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                            ],
                                        ],
                                        'url' => Url::to(['lockreal', 'id' => $model['unit_id']. 'L', 'tw' => '1']),
                                        'visible' => true,   // same as above
                                    ],

                                    ['label' => \Yii::t('yii', 'Lihat Data'),
                                        'linkOptions' => [
                                            'data' => [
                                                'method' => 'POST',
                                                // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                            ],
                                        ],
                                        // 'url' => Url::to(['detailpoaadm', 'p' => 1, 'unit_id' => $model['unit_id'], 'sd' => '']),
                                        'url' => Url::to(['datarealadm', 'bok_id' => 1, 'unit_id' => $model['unit_id'], 'p' => 1]),
                                        'visible' => true,   // same as above
                                    ],
                                ],
                            ],
                            'options' => ['class' => $model['color_real_icon_tw1']],
                        ]);
                    },
                    'tw2' => function ($url, $model) {
                        return ButtonDropdown::widget([
                            'encodeLabel' => false,
                            'label' => '<span class="'.$model['status_real_icon_tw2'].'"></span> II',
                            'dropdown' => [
                                'items' => [
                                    ['label' => \Yii::t('yii', $model['label_real_icon_tw2']),
                                        'linkOptions' => [
                                            'data' => [
                                                'method' => 'POST',
                                                // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                            ],
                                        ],
                                        'url' => Url::to(['lockreal', 'id' => $model['unit_id']. 'L', 'tw' => '2']),
                                        'visible' => true,   // same as above
                                    ],

                                    ['label' => \Yii::t('yii', 'Lihat Data'),
                                        'linkOptions' => [
                                            'data' => [
                                                'method' => 'POST',
                                                // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                            ],
                                        ],
                                        // 'url' => Url::to(['detailpoaadm', 'p' => 2, 'unit_id' => $model['unit_id'], 'sd' => '']),
                                        'url' => Url::to(['datarealadm', 'bok_id' => 1, 'unit_id' => $model['unit_id'], 'p' => 2]),
                                        'visible' => true,   // same as above
                                    ],
                                ],
                            ],
                            'options' => ['class' => $model['color_real_icon_tw2']],
                        ]);
                    },
                    'tw3' => function ($url, $model) {
                        return ButtonDropdown::widget([
                            'encodeLabel' => false,
                            'label' => '<span class="'.$model['status_real_icon_tw3'].'"></span> III',
                            'dropdown' => [
                                'items' => [
                                    ['label' => \Yii::t('yii', $model['label_real_icon_tw3']),
                                        'linkOptions' => [
                                            'data' => [
                                                'method' => 'POST',
                                                // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                            ],
                                        ],
                                        'url' => Url::to(['lockreal', 'id' => $model['unit_id']. 'L', 'tw' => '3']),
                                        'visible' => true,   // same as above
                                    ],

                                    ['label' => \Yii::t('yii', 'Lihat Data'),
                                        'linkOptions' => [
                                            'data' => [
                                                'method' => 'POST',
                                                // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                            ],
                                        ],
                                        // 'url' => Url::to(['detailpoaadm', 'p' => 3, 'unit_id' => $model['unit_id'], 'sd' => '']),
                                        'url' => Url::to(['datarealadm', 'bok_id' => 1, 'unit_id' => $model['unit_id'], 'p' => 3]),
                                        'visible' => true,   // same as above
                                    ],
                                ],
                            ],
                            'options' => ['class' => $model['color_real_icon_tw3']],
                        ]);
                    },
                    'tw4' => function ($url, $model) {
                        return ButtonDropdown::widget([
                            'encodeLabel' => false,
                            'label' => '<span class="'.$model['status_real_icon_tw4'].'"></span> IV',
                            'dropdown' => [
                                'items' => [
                                    ['label' => \Yii::t('yii', $model['label_real_icon_tw4']),
                                        'linkOptions' => [
                                            'data' => [
                                                'method' => 'POST',
                                                // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                            ],
                                        ],
                                        'url' => Url::to(['lockreal', 'id' => $model['unit_id']. 'L', 'tw' => '4']),
                                        'visible' => true,   // same as above
                                    ],

                                    ['label' => \Yii::t('yii', 'Lihat Data'),
                                        'linkOptions' => [
                                            'data' => [
                                                'method' => 'POST',
                                                // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                            ],
                                        ],
                                        // 'url' => Url::to(['detailpoaadm', 'p' => 4, 'unit_id' => $model['unit_id'], 'sd' => '']),
                                        'url' => Url::to(['datarealadm', 'bok_id' => 1, 'unit_id' => $model['unit_id'], 'p' => 4]),
                                        'visible' => true,   // same as above
                                    ],
                                ],
                            ],
                            'options' => ['class' => $model['color_real_icon_tw4']],
                        ]);
                    },
                    // 'tw1' => function ($url, $model) {
                    //     return Html::a('<span class="'.$model['status_real_icon_tw1'].'"></span> I', array('lockreal', 'id' => $model['unit_id']. 'L', 'tw' => '1'), ['class'=>$model['color_real_icon_tw1']]);
                    // },
                    // 'tw2' => function ($url, $model) {
                    //     return Html::a('<span class="'.$model['status_real_icon_tw2'].'"></span> II', array('lockreal', 'id' => $model['unit_id']. 'L', 'tw' => '2'), ['class'=>$model['color_real_icon_tw2']]);
                    // },
                    // 'tw3' => function ($url, $model) {
                    //     return Html::a('<span class="'.$model['status_real_icon_tw3'].'"></span> III', array('lockreal', 'id' => $model['unit_id']. 'L', 'tw' => '3'), ['class'=>$model['color_real_icon_tw3']]);
                    // },
                    // 'tw4' => function ($url, $model) {
                    //     return Html::a('<span class="'.$model['status_real_icon_tw4'].'"></span> IV', array('lockreal', 'id' => $model['unit_id']. 'L', 'tw' => '4'), ['class'=>$model['color_real_icon_tw4']]);
                    // },
                    'real' => function ($url, $model) {
                        return ButtonDropdown::widget([
                            'encodeLabel' => false,
                            'label' => '<span class="'.$model['status_real_icon'].'"></span> Realisasi',
                            'dropdown' => [
                                'items' => [
                                    ['label' => \Yii::t('yii', $model['status_real']),
                                        'linkOptions' => [
                                            'data' => [
                                                'method' => 'POST',
                                                // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                            ],
                                        ],
                                        'url' => Url::to(['lock', 'id' => $model['unit_id']. 'L']),
                                        'visible' => true,   // same as above
                                    ],

                                    ['label' => \Yii::t('yii', 'Lihat Data'),
                                        // 'linkOptions' => [
                                        //     'data' => [
                                        //         'method' => 'POST',
                                        //         // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                        //     ],
                                        // ],
                                        'url' => Url::to(['program/real', 'id' => $model['unit_id'], 'p' => 'def']),
                                        'visible' => false,   // same as above
                                    ],
                                    
                                    ['label' => \Yii::t('yii', 'Export Excel'),
                                        'linkOptions' => [
                                            'data' => [
                                                'method' => 'POST',
                                                // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                            ],
                                        ],
                                        'url' => Url::to(['exporttwadm', 'unit_id' => $model['unit_id']]),
                                        'visible' => false,   // same as above
                                    ],

                                    ['label' => \Yii::t('yii', 'Lihat Data'),
                                        'url' => Url::to(['deptperiod/get-realisasi', 'unit_id' => $model['unit_id'], 'mo' => '1']),
                                        'visible' => true,   // same as above
                                    ],
                                    ['label' => \Yii::t('yii', 'COVID-19'),
                                        'url' => Url::to(['ukm/laporan-ukm-all', 'id' => '1', 'unit_id' => $model['unit_id'], 'mo' => '1']),
                                        'visible' => false,   // same as above
                                    ],
                                ],
                            ],
                            'options' => ['class' => 'btn btn-xs btn-info custom_button'],
                        ]);
                    },
                    'lihat' => function ($url, $model) {
                        // return Html::a('<span class="glyphicon glyphicon-list"></span> Lihat Data', array('program/verif', 'id' => $model['unit_id'], 'p' => 'def'), ['class'=>'btn btn-xs btn-info custom_button']);
                        return ButtonDropdown::widget([
                            'encodeLabel' => false,
                            'label' => '<span class="glyphicon glyphicon-list"></span> Lihat Data',
                            'dropdown' => [
                                'items' => [
                                    ['label' => \Yii::t('yii', 'POA Awal'),
                                        'linkOptions' => [
                                            'data' => [
                                                'method' => 'POST',
                                                // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                            ],
                                        ],
                                        'url' => Url::to(['program/verif', 'id' => $model['unit_id'], 'p' => 'def']),
                                        'visible' => true,   // same as above
                                    ],

                                    ['label' => \Yii::t('yii', 'Pergeseran'),
                                        'linkOptions' => [
                                            'data' => [
                                                'method' => 'POST',
                                                // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                            ],
                                        ],
                                        'url' => Url::to(['program/verif', 'id' => $model['unit_id'], 'p' => 'pergeseran']),
                                        'visible' => true,   // same as above
                                    ],
                                    
                                    ['label' => \Yii::t('yii', 'Perubahan'),
                                        'linkOptions' => [
                                            'data' => [
                                                'method' => 'POST',
                                                // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                            ],
                                        ],
                                        'url' => Url::to(['program/verif', 'id' => $model['unit_id'], 'p' => 'perubahan']),
                                        'visible' => true,   // same as above
                                    ],
                                ],
                            ],
                            'options' => ['class' => 'btn btn-xs btn-info custom_button'],
                        ]);
                    }
                ],
            ],
        ],
    ]); 
    
    ?>
    </div>
    <?php Pjax::end() ?>
</div>
