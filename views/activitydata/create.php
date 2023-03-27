<?php

use yii\helpers\Html;
use yii\bootstrap\Progress;
use yii\web\Session; 

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Activitydata */

$this->title = 'Tambah Data';
$this->params['breadcrumbs'][] = ['label' => $session['periodValue']. $session['poaLabel'], 'url' => ['period/create', 'p' => 'def']];
$this->params['breadcrumbs'][] = ['label' => 'Rincian Menu Kegiatan', 'url' => ['program/list']];
$this->params['breadcrumbs'][] = ['label' => 'Komponen', 'url' => ['service/list', 'id' => $session['programId']]];
$this->params['breadcrumbs'][] = ['label' => 'Kegiatan', 'url' => ['activity/list', 'id' => $session['serviceId']]];
$this->params['breadcrumbs'][] = ['label' => 'Bentuk Kegiatan', 'url' => ['activitydata/list', 'id' => $session['activityId']]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="activitydata-create">

    <!-- <h1><?= Html::encode('Tambah Data - '.$session['activityName']) ?></h1> -->

    <?php
        // echo Progress::widget([
        //     'bars' => [
        //         ['percent' => $session['prosentase'], 'label' => $session['prosentase'].'% dari Pagu BOK (RP. ' .number_format($session['pagu'], 0, ',', '.'). ')', 'options' => ['class' => $session['barColor']]],
        //     ],
        //     'options' => ['class' => $session['barStatus']]
        // ]);
    ?>

    <?= $this->render('_form', [
        'model' => $model,
        'modul' => $modul,
        'title' => $this->title
    ]) ?>

</div>
