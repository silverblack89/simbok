<?php

use yii\helpers\Html;
use yii\web\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Financialrealization */

$this->title = 'Tambah Data';
$this->params['breadcrumbs'][] = ['label' => $session['periodValue']. $session['poaLabel'], 'url' => ['period/create', 'p' => 'def']];
$this->params['breadcrumbs'][] = ['label' => 'Data Upaya Program', 'url' => ['program/list']];
$this->params['breadcrumbs'][] = ['label' => 'Data Pelayanan', 'url' => ['service/list', 'id' => $session['programId']]];
$this->params['breadcrumbs'][] = ['label' => 'Data Kegiatan', 'url' => ['activity/list', 'id' => $session['serviceId']]];
$this->params['breadcrumbs'][] = ['label' => 'Bentuk Kegiatan', 'url' => ['activitydata/list', 'id' => $session['activityId']]];
$this->params['breadcrumbs'][] = ['label' => $session['activityName'], 'url' => ['activitydata/view', 'id' => $session['activityDataId']]];
$this->params['breadcrumbs'][] = ['label' => 'Data Realisasi Kegiatan', 'url' => ['activitydetail/view', 'id' => $session['activityDetailId']]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="financialrealization-create">

    <h1><?= Html::encode('Tambah Realisasi ('.$session['activityDetailAccount'].')') ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
