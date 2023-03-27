<?php

use yii\helpers\Html;
use yii\bootstrap\Progress;
use yii\web\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Activitydetailubah */

$this->title = 'Tambah Detail';
$this->params['breadcrumbs'][] = ['label' => $session['periodValue']. $session['poaLabel'], 'url' => ['period/create', 'p' => 'def']];
$this->params['breadcrumbs'][] = ['label' => 'Data Upaya Program', 'url' => ['program/list']];
$this->params['breadcrumbs'][] = ['label' => 'Data Pelayanan', 'url' => ['service/list', 'id' => $session['programId']]];
$this->params['breadcrumbs'][] = ['label' => 'Data Kegiatan', 'url' => ['activity/list', 'id' => $session['serviceId']]];
$this->params['breadcrumbs'][] = ['label' => 'Bentuk Kegiatan', 'url' => ['activitydataubah/list', 'id' => $session['activityId']]];
$this->params['breadcrumbs'][] = ['label' => $session['activityName'], 'url' => ['activitydataubah/view', 'id' => $session['activityDataId']]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="activitydetailrubah-create">

    <h1><?= Html::encode('Tambah Detail') ?></h1>

    <?php
        echo Progress::widget([
            'bars' => [
                ['percent' => $session['prosentase'], 'label' => $session['prosentase'].'% dari Pagu Perubahan (RP. ' .number_format($session['pagu_ubah'], 0, ',', '.'). ')', 'options' => ['class' => $session['barColor']]],
            ],
            'options' => ['class' => $session['barStatus']]
        ]);
    ?>

    <?= $this->render('_form', [
        'model' => $model,
        'real' => NULL
    ]) ?>

</div>
