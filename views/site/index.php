<?php
use yii\helpers\Html;
use kartik\spinner\Spinner;
use yii\widgets\ActiveForm;
use app\models\Period;
use app\models\ActivityData;
use yii\web\session;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\bootstrap\ButtonDropdown;

$session = Yii::$app->session;

/* @var $this yii\web\View */

$this->title = 'SIMBOK';
?>

<div class="site-index">

    <div class="jumbotron">
        <img src=<?= Html::encode(Url::base(). '/img/logo.png') ?>  class="img-responsive;" alt="" height="10%" width="10%">
        <h1>SELAMAT DATANG!</h1>

        <p class="lead"><h4>SIMBOK KABUPATEN SRAGEN</h4></p> <?php //echo $session['loginTime']; ?>

        <?php

        if(Yii::$app->user->isGuest){
            echo Html::button('<span class="glyphicon glyphicon-log-in"></span> Masuk', 
            ['value' => Url::to(['/site/login']), 'title' => 'Masuk', 'class' => 'showModalButton btn btn-primary']);

            // echo Html::a('<span class="glyphicon glyphicon-play"></span> Masuk', array('site/login')); 
        }else{
            $model = new Period();
            $options = ['class' => 'btn btn-lg btn-success', 'id' => 'start']; 
            if (Yii::$app->user->identity->username == 'admin'){
                $session['periodValue'] = date('Y');
                //echo Html::a('<span class="glyphicon glyphicon-list"></span> Tampilkan Data', array('period/list', 'period' => date('Y')), $options);  

                echo ButtonDropdown::widget([
                    'encodeLabel' => false,
                    'label' => '<span class="glyphicon glyphicon-list"></span> Tampilkan Data',
                    'dropdown' => [
                        'items' => [
                            ['label' => \Yii::t('yii', 'Dinas'),
                                // 'linkOptions' => [
                                //     'data' => [
                                //         'method' => 'POST',
                                //         // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                //     ],
                                // ],
                                'url' => Url::to(['deptperiod/level', 'lvl' => 'dns']),
                                'visible' => true,   // same as above
                            ],
                            
                            ['label' => \Yii::t('yii', 'Puskesmas'),
                                // 'linkOptions' => [
                                //     'data' => [
                                //         'method' => 'POST',
                                //         'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                //     ],
                                // ],
                                // 'url' => Url::to(['exportxlsadm', 'unit_id' => $model['unit_id']]),
                                'url' => Url::to(['period/level', 'lvl' => 'pkm']),
                                'visible' => true,   // same as above
                            ],
                        ],
                    ],
                    'options' => ['class' => 'btn btn-xs btn-success custom_button'],
                ]);
            }else{
                if (Yii::$app->user->identity->group_id == 'ADM'){
                    $session['periodValue'] = date('Y');

                    // if (Yii::$app->user->identity->username == 'verificator'){
                    //     echo Html::a('<span class="glyphicon glyphicon-list"></span> Tampilkan Data', array('period/list', 'period' => date('Y')), $options); 
                    // }else{
                        
                    // } 
                }elseif (Yii::$app->user->identity->group_id == 'SEK'){
                    $session['deptPeriodValue'] = date('Y');
                    echo '<p>';
                        echo Html::a('<span class="glyphicon glyphicon-play"></span> Memulai Data', array('deptperiod/create'), $options); 
                    echo '</p>';
                    echo '<p>';
                        echo Html::a('<span class="glyphicon glyphicon-list"></span> POA Puskesmas', array('period/level', 'lvl' => 'pkm'), ['class' => 'btn btn-lg btn-primary']);
                    echo '</p>';
                }else{
                    $session['periodValue'] = date('Y'); 
                    echo Html::a('<span class="glyphicon glyphicon-play"></span> Memulai Data', array('period/create', 'p' => 'def'), $options);  
                }
            }
        }
        ?>
    </div>

    <?php 
        if(Yii::$app->user->isGuest){
            Modal::begin([
                    'header'=>'<h4>Login User</h4>',
                    'id'=>'modal',
                    'size'=>'modal-sm',
                    'clientOptions' => ['backdrop' => 'static', 'keyboard' => FALSE],
                    // 'footer' => ''
                ]);
            echo "<div id='modalContent'></div>";
            Modal::end();
        }else{
            Modal::begin([
                'header'=>'<h4>Periode POA</h4>',
                'id'=>'modal',
                'size'=>'modal-sm',
                'clientOptions' => ['backdrop' => 'static', 'keyboard' => FALSE],
                // 'footer' => ''
            ]);
        echo "<div id='modalContent'></div>";
        Modal::end();  
        }
    ?>
</div>

<?php
$js=<<< JS
$(".alert").animate({opacity: 1.0}, 3000).fadeOut("slow");
JS;
$this->registerJs($js, yii\web\View::POS_READY);
?>

<?php
$this->registerJs('
    $("#select").on("change", function () {
        baseUrl = window.origin;
        createCookie("sumberdana", $(this).val(), "1"); 
        var link = baseUrl+"'.Url::to(['sumberdana']).'";
        
        $.get(link, function(data) {
            //alert(data);
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

    $("#start").on("click", function () {
        var sudan = document.getElementById("select").value;
        if(sudan == ""){
            alert("Sumber dana belum dipilih");
            return false;
        }else{
            return true;
        }
    });
');
?>