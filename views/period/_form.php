<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\web\View;
use yii\bootstrap\Button;
use yii\bootstrap\ButtonDropdown;
use yii\bootstrap\Modal;
use sjaakp\gcharts\ColumnChart;
use yii\widgets\Pjax;
use yii\web\session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Period */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="period-form">

    <?php $form = ActiveForm::begin([
        'id' => 'period-form',
    ]); ?>

    <?php if (Yii::$app->user->identity->username == 'admin'){ ?>
        <?= $form->field($model, 'pagu')->textInput(['maxlength' => true])->label('Pagu Awal')->widget(\yii\widgets\MaskedInput::className(), [
                    // 'options' => ['disabled' => $disabled],
                    'clientOptions' => [
                    'alias' => 'decimal',
                    'groupSeparator' => '.',
                    'radixPoint' => ',',
                    'autoGroup' => true
                    ]]); ?>
        <?= $form->field($model, 'pagu_geser')->textInput(['maxlength' => true])->label('Pagu Pergeseran')->widget(\yii\widgets\MaskedInput::className(), [
                    // 'options' => ['disabled' => $disabled],
                    'clientOptions' => [
                    'alias' => 'decimal',
                    'groupSeparator' => '.',
                    'radixPoint' => ',',
                    'autoGroup' => true
                    ]]); ?>
        <?= $form->field($model, 'pagu_ubah')->textInput(['maxlength' => true])->label('Pagu Perubahan')->widget(\yii\widgets\MaskedInput::className(), [
                    // 'options' => ['disabled' => $disabled],
                    'clientOptions' => [
                    'alias' => 'decimal',
                    'groupSeparator' => '.',
                    'radixPoint' => ',',
                    'autoGroup' => true
                    ]]); ?>
        <div class="form-group">
            <?= Html::submitButton('Simpan', ['class' => 'btn btn-success']) ?>
        </div>
    <?php }else{ ?>
        <div class="row">
            <div class="col-xs-4">
            </div>
            <div class="col-xs-4">
                <?= $form->field($model, 'tahun')->dropDownList([ date('Y')-1 => date('Y')-1, date('Y') => date('Y'), date('Y')+1 => date('Y')+1, ]
                ,[
                    'id' => 'select', 
                    'prompt'=>'Tahun', 
                    // 'style' => 'width:115px !important', 
                    // 'options'=>[$session['periodValue']=>['Selected'=>true]],
                    // 'onchange'=>'
                    //     $.pjax.reload({
                    //         url: "'.Url::toRoute(['create']).'?period="+$(this).val(),
                    //         container: "#pjax-piechart",
                    //         timeout: 1000,
                    //     });',
                    'class'=>'form-control']) 
                ?>
            </div>
        </div>

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
                            'url' => Url::to(['create', 'p' => 'def']),
                            'visible' => true,   // same as above
                        ],
                        
                        ['label' => \Yii::t('yii', 'Lihat Data'),
                            'linkOptions' => [
                                'data' => [
                                    'method' => 'POST',
                                    // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                ],
                            ],
                            'url' => Url::to(['datapoa', 'p' => 'awal']),
                            'visible' => true,   // same as above
                        ],
                    ],
                ],
                'options' => ['class' => 'btn btn-lg btn-success custom_button', 'style' => 'margin-top:5px !important; height:75px;'],
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
                                    'confirm' => \Yii::t('yii', 'Apakah anda yakin akan melakukan proses POA Pergeseran?'),
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
                'options' => ['class' => 'btn btn-lg btn-danger custom_button', 'disabled' => 'disabled', 'style' => 'margin-top:5px !important; height:75px'],
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
                                    'confirm' => \Yii::t('yii', 'Apakah anda yakin akan melakukan proses POA Perubahan?'),
                                ],
                            ],
                            'url' => Url::to(['create', 'p' => 'perubahan']),
                            'visible' => true,   // same as above
                        ],
                        
                        ['label' => \Yii::t('yii', 'Lihat Data'),
                            'linkOptions' => [
                                'data' => [
                                    'method' => 'POST',
                                    // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                ],
                            ],
                            'url' => Url::to(['dataubah', 'id' => Yii::$app->user->identity->unit_id, 'p' => 'perubahan']),
                            'visible' => true,   // same as above
                        ],
                    ],
                ],
                'options' => ['class' => 'btn btn-lg btn-warning custom_button', 'disabled' => 'disabled', 'style' => 'margin-top:5px !important; height:75px'],
            ]);
            ?>

            <?=                                      
            ButtonDropdown::widget([
                'encodeLabel' => false,
                'label' => '<span class="glyphicon glyphicon-file"></span> Laporan',
                'dropdown' => [
                    'items' => [
                        ['label' => \Yii::t('yii', 'Realisasi BOK'),
                            'linkOptions' => [
                                'data' => [
                                    'method' => 'POST',
                                    // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                ],
                            ],
                            'url' => Url::to(['cekprd']),
                            'visible' => false,   // same as above
                        ],
                        ['label' => \Yii::t('yii', 'Capaian Kinerja dan Keuangan'),
                            'linkOptions' => [
                                'data' => [
                                    'method' => 'POST',
                                    // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                ],
                            ],
                            'url' => Url::to(['exporttw']),
                            'visible' => false,   // same as above
                        ],
                        ['label' => \Yii::t('yii', 'Data SP2D'),
                            'linkOptions' => [
                                'data' => [
                                    'method' => 'POST',
                                    // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                ],
                            ],
                            'url' => Url::to(['ceksp2d']),
                            'visible' => true,   // same as above
                        ],
                        ['label' => \Yii::t('yii', 'Realisasi Triwulan I BOK'),
                            // 'linkOptions' => [
                            //     'data' => [
                            //         'method' => 'POST',
                            //         // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                            //     ],
                            // ],
                            // 'url' => Url::to(['cekprd']),
                            'url' => Url::to(['detailpoa', 'p' => 1]),
                            'visible' => true,   // same as above
                        ],
                        ['label' => \Yii::t('yii', 'Realisasi Triwulan II BOK'),
                            // 'linkOptions' => [
                            //     'data' => [
                            //         'method' => 'POST',
                            //         // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                            //     ],
                            // ],
                            // 'url' => Url::to(['cekprd']),
                            'url' => Url::to(['detailpoa', 'p' => 2]),
                            'visible' => true,   // same as above
                        ],
                        ['label' => \Yii::t('yii', 'Realisasi Triwulan III BOK'),
                            // 'linkOptions' => [
                            //     'data' => [
                            //         'method' => 'POST',
                            //         // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                            //     ],
                            // ],
                            // 'url' => Url::to(['cekprd']),
                            'url' => Url::to(['detailpoa', 'p' => 3]),
                            'visible' => true,   // same as above
                        ],
                        ['label' => \Yii::t('yii', 'Realisasi Triwulan IV BOK'),
                            // 'linkOptions' => [
                            //     'data' => [
                            //         'method' => 'POST',
                            //         // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                            //     ],
                            // ],
                            // 'url' => Url::to(['cekprd']),
                            'url' => Url::to(['detailpoa', 'p' => 4]),
                            'visible' => true,   // same as above
                        ],
                    ],
                ],
                'options' => ['class' => 'btn btn-lg btn-default custom_button', 'style' => 'margin-top:5px !important; height:75px'],
            ]);
            ?>
        </div>
    <?php } ?>

    <?php ActiveForm::end(); ?>
</div>

<?php
$this->registerJs('
$("#select").on("change", function (e) {
    baseUrl = window.origin;
    var bulan=document.getElementById("select").value;  
    createCookie("bulan", bulan, "1");

    // alert($(this).val());

    var link = baseUrl+"'.Url::to(['get-period']).'";
    
    $.get(link, function(data) {
        // alert(data)
    });

    // Function to create the cookie 
    function createCookie(name, value, days) { 
        var expires; 
        
        if (days) { 
            var date = new Date(); 
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000)); 
            expires = "; expires=" + date.toGMTString(); 
        } 
        else { 
            expires = ""; 
        } 
        
        document.cookie = escape(name) + "=" +  
            escape(value) + expires + "; path=/"; 
    } 
});
');
?>





