<?php
    use yii\helpers\Html;
    use yii\web\Session;

    $session = Yii::$app->session;
?>

<div class="verification-revision">

    <!-- <h3><?= Html::encode($session['verifId']) ?></h3> -->

    <?= $this->render('_form', [
        'model' => $model,
        'kunci' => $kunci,
        'status' => $status,
        'revised' => $revised
    ]) ?>

</div>