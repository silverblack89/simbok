<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\session;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\bootstrap\Progress;
use sjaakp\gcharts\PieChart;
use yii\bootstrap\ButtonDropdown;

$session = Yii::$app->session;

$this->title = 'Periode POA '.$model->unit_id;
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="period-select">
    <div style="margin:auto; text-align:center">
        <h1><?= Html::encode($this->title) ?></h1>
    </div>

    <div style="max-width: 100px; margin:auto; text-align:center;">
        <?= Html::dropDownList('tahun', null, [ date('Y')-1 => date('Y')-1, date('Y') => date('Y'), date('Y')+1 => date('Y')+1, ] ,
        [
            // 'prompt'=>'Pilih Periode',
            // 'style' => 'width:115px !important', 
            'options'=>[$session['periodValue']=>['Selected'=>true]],
            'onchange'=>'
                $.pjax.reload({
                    url: "'.Url::toRoute(['period/select']).'?period="+$(this).val(),
                    container: "#pjax-piechart",
                    timeout: 1000,
                });',
            'class'=>'form-control']) 
        ?>
    </div>
    
    <div style="max-width: 400px; margin:auto; text-align:center; padding: 10px;">
        <div class="form-group">
        <?=                                      
        ButtonDropdown::widget([
            'encodeLabel' => false,
            'label' => '<span class="glyphicon glyphicon-check"></span> POA',
            'dropdown' => [
                'items' => [
                    ['label' => \Yii::t('yii', 'Proses'),
                        'linkOptions' => [
                            'data' => [
                                'method' => 'POST',
                                // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                            ],
                        ],
                        'url' => Url::to(['create']),
                        'visible' => true,   // same as above
                    ],
                    
                    ['label' => \Yii::t('yii', 'Export Excel'),
                        'linkOptions' => [
                            'data' => [
                                'method' => 'POST',
                                // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                            ],
                        ],
                        'url' => Url::to(['exportxls']),
                        'visible' => true,   // same as above
                    ],
                ],
            ],
            'options' => ['class' => 'btn btn-md btn-success custom_button'],
        ]);
        ?>
        
        <?=                                      
        ButtonDropdown::widget([
            'encodeLabel' => false,
            'label' => '<span class="glyphicon glyphicon-transfer"></span> Pergeseran',
            'dropdown' => [
                'items' => [
                    ['label' => \Yii::t('yii', 'Proses'),
                        'linkOptions' => [
                            'data' => [
                                'method' => 'POST',
                                // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                            ],
                        ],
                        'url' => Url::to(['']),
                        'visible' => true,   // same as above
                    ],
                    
                    ['label' => \Yii::t('yii', 'Export Excel'),
                        'linkOptions' => [
                            'data' => [
                                'method' => 'POST',
                                // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                            ],
                        ],
                        'url' => Url::to(['']),
                        'visible' => true,   // same as above
                    ],
                ],
            ],
            'options' => ['class' => 'btn btn-md btn-info custom_button disabled'],
        ]);
        ?>

        <?=                                      
        ButtonDropdown::widget([
            'encodeLabel' => false,
            'label' => '<span class="glyphicon glyphicon-pencil"></span> Perubahan',
            'dropdown' => [
                'items' => [
                    ['label' => \Yii::t('yii', 'Proses'),
                        'linkOptions' => [
                            'data' => [
                                'method' => 'POST',
                                // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                            ],
                        ],
                        'url' => Url::to(['']),
                        'visible' => true,   // same as above
                    ],
                    
                    ['label' => \Yii::t('yii', 'Export Excel'),
                        'linkOptions' => [
                            'data' => [
                                'method' => 'POST',
                                // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                            ],
                        ],
                        'url' => Url::to(['']),
                        'visible' => true,   // same as above
                    ],

                    // ['label' => \Yii::t('yii', '_________________________________')],

                    ['label' => \Yii::t('yii', 'Capaian Kinerja dan Keuangan'),
                        'linkOptions' => [
                            'data' => [
                                'method' => 'POST',
                                // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                            ],
                        ],
                        'url' => Url::to(['exporttw']),
                        'visible' => true,   // same as above
                    ],
                    
                    ['label' => \Yii::t('yii', 'Rincian Kegiatan Pemanfaatan BOK'),
                        'linkOptions' => [
                            'data' => [
                                'method' => 'POST',
                                // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                            ],
                        ],
                        'url' => Url::to(['']),
                        'visible' => true,   // same as above
                    ],
                ],
            ],
            'options' => ['class' => 'btn btn-md btn-warning custom_button disabled'],
        ]);
        ?>
        </div>
    </div>

    <div style="max-width: 400px; margin:auto; text-align:center;">
        <?php Pjax::begin(['id' => 'pjax-piechart']) ?>
        <?=PieChart::widget([
            'height' => '400px',
            'dataProvider' => $dataProvider,
                'columns' => [
                    'Puskesmas:string',  // first column: domain
                    'prosentase'    // second column: data
                ],
            'options' => [
                // 'title' => 'Prosentase POA',
                // 'is3D' => true,
                'pieHole' => 0.4,
                'legend' => 'none',
                // 'legend' => ['position' => 'bottom'],
                'chartArea' =>['top' => 0],
                'pieSliceText' => 'label',
                // 'sliceVisibilityThreshold' => 0.022
            ],
        ]) 
        ?>
        <?php Pjax::end() ?>
    </div>
</div>
