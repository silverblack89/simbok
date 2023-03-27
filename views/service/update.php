<?php

use yii\helpers\Html;
use yii\web\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Service */

$this->title = 'Ubah Data Pelayanan';
$this->params['breadcrumbs'][] = ['label' => 'Data Program ('.$session['programYear'].')', 'url' => ['program/index', 'tahun' => $session['programYear']]];
$this->params['breadcrumbs'][] = ['label' => 'Data Pelayanan', 'url' => ['index', 'id' => $session['programId']]];
$this->params['breadcrumbs'][] = 'Ubah Data Pelayanan';
?>
<div class="service-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
