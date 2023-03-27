<?php

use yii\helpers\Html;
use yii\web\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Activity */

$this->title = 'Ubah Data Kegiatan';
$this->params['breadcrumbs'][] = ['label' => 'Data Program ('.$session['programYear'].')', 'url' => ['program/index', 'tahun' => $session['programYear']]];
$this->params['breadcrumbs'][] = ['label' => 'Data Pelayanan', 'url' => ['service/index', 'id' => $session['programId']]];
$this->params['breadcrumbs'][] = ['label' => 'Data Kegiatan', 'url' => ['index', 'id' => $session['serviceId']]];
$this->params['breadcrumbs'][] = 'Ubah Data Kegiatan';
?>
<div class="activity-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
