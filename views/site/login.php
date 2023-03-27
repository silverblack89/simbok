<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

// $subtitle = 'Login';
// $this->title = 'POA | ' .$subtitle;
// $this->params['breadcrumbs'][] = $subtitle;
?>
<div class="site-login">
    <h1></h1>

    <!-- <p>Isi data berikut untuk masuk tahap selanjutnya:</p> -->

    <?php $form = ActiveForm::begin([
        'id' => 'login-form',
        // 'layout' => 'horizontal',
        // 'fieldConfig' => [
        //     'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
        //     'labelOptions' => ['class' => 'col-lg-1 control-label'],
        // ]
    ]); ?>

    <?= $form->field($model, 'username')->textInput() ?> <?php //['autofocus' => true] >?>

    <?= $form->field($model, 'password')->passwordInput() ?>

    <?= $form->field($model, 'rememberMe')->checkbox([
        'label' => 'Ingatkan saya', 
        // 'template' => "<div class=\"col-lg-offset-1 col-lg-3\">{input} {label}</div>\n<div class=\"col-lg-8\">{error}</div>",
    ]) ?>

    <div class="form-group">
        <!-- <div class="col-lg-offset-1 col-lg-11"> -->
            <?= Html::submitButton('<span class="glyphicon glyphicon-check"></span> Proses', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
        <!-- </div> -->
    </div>

    <?php ActiveForm::end(); ?>
</div>
