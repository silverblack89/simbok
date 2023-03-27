<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;  

$this->title = 'Ganti Password';
$this->params['breadcrumbs'][] = 'Password';
?>

<div class="user-form">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'old_password')->passwordInput()->label('Password Lama') ?> 
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'new_password')->passwordInput()->label('Password baru') ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'repeat_password')->passwordInput()->label('Ulangi Password Baru') ?> 
        </div>
    </div>
    

    <div class="form-group">
        <?= Html::submitButton('Simpan', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$js=<<< JS
$(".alert").animate({opacity: 1.0}, 3000).fadeOut("slow");
JS;
$this->registerJs($js, yii\web\View::POS_READY);
?>
