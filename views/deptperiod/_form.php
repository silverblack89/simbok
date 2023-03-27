<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap\ButtonDropdown;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Deptperiod */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="deptperiod-form">

    <?php $form = ActiveForm::begin([
        'id' => 'deptperiod-form',
    ]); ?>

    <div class="row">
        <div class="col-xs-4"></div>
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
                'class'=>'form-control'])->label('')
            ?>
        </div>
    </div>

    <div style="text-align:center;">
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
                    
                    ['label' => \Yii::t('yii', 'Lihat Data'),
                        // 'linkOptions' => [
                        //     'data' => [
                        //         'method' => 'POST',
                        //         // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                        //     ],
                        // ],
                        'url' => Url::to(['datapoa', 'id' => 1, 'p' => 'def']),
                        'visible' => true,   // same as above
                    ],
                ],
            ],
            'options' => ['class' => 'btn btn-lg btn-success custom_button', 'style' => 'margin-top:5px !important'],
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
                'options' => ['class' => 'btn btn-lg btn-info custom_button', 'disabled' => 'disabled', 'style' => 'margin-top:5px !important'],
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
                'options' => ['class' => 'btn btn-lg btn-warning custom_button', 'disabled' => 'disabled', 'style' => 'margin-top:5px !important'],
            ]);
            ?>

        <?=                                      
        ButtonDropdown::widget([
            'encodeLabel' => false,
            'label' => '<span class="glyphicon glyphicon-file"></span> Laporan',
            'dropdown' => [
                'items' => [
                    ['label' => \Yii::t('yii', 'RAB per Upaya/Program'),
                        // 'linkOptions' => [
                        //     'data' => [
                        //         'method' => 'POST',
                        //         'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                        //     ],
                        // ],
                        'url' => Url::to(['rekap-komponen-detail', 'id' => 0]),
                        'visible' => true,   // same as above
                    ],
                    ['label' => \Yii::t('yii', 'Realisasi Triwulan I BOK'),
                            'url' => Url::to(['detailpoa', 'p' => 1]),
                            'visible' => true,   // same as above
                        ],
                        ['label' => \Yii::t('yii', 'Realisasi Triwulan II BOK'),
                            'url' => Url::to(['detailpoa', 'p' => 2]),
                            'visible' => true,   // same as above
                        ],
                        ['label' => \Yii::t('yii', 'Realisasi Triwulan III BOK'),
                            'url' => Url::to(['detailpoa', 'p' => 3]),
                            'visible' => true,   // same as above
                        ],
                        ['label' => \Yii::t('yii', 'Realisasi Triwulan IV BOK'),
                            'url' => Url::to(['detailpoa', 'p' => 4]),
                            'visible' => true,   // same as above
                        ],
                ],
            ],
            'options' => ['class' => 'btn btn-lg btn-default custom_button', 'style' => 'margin-top:5px !important'],
        ]);
        ?>
    </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$this->registerJs('
    $("#select").on("change", function () {
        baseUrl = window.origin;
        createCookie("tahun", $(this).val(), "1"); 
        var link = baseUrl+"'.Url::to(['get-year']).'";
        
        $.get(link, function(data) {
            // $("input#nodok").val(data)
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
