<?php

use yii\helpers\Html;
use yii\web\session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Indicator */

$this->title = 'Ubah Data Indikator';
$this->params['breadcrumbs'][] = ['label' => 'Data Program', 'url' => ['program/index']];
$this->params['breadcrumbs'][] = ['label' => $session['programNama'], 'url' => ['program/view', 'id' => $session['programId']]];
$this->params['breadcrumbs'][] = 'Ubah Data Indikator';
?>
<div class="indicator-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
