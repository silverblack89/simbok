<?php

use yii\helpers\Html;
use yii\bootstrap\Progress;
use yii\web\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Deptsubactivitydetail */

$this->title = 'Tambah Detail Kegiatan';
$this->params['breadcrumbs'][] = ['label' => $session['deptPeriodValue'], 'url' => ['deptperiod/create']];
$this->params['breadcrumbs'][] = ['label' => 'Rincian Menu Kegiatan', 'url' => ['deptprogram/list']];
$this->params['breadcrumbs'][] = ['label' => 'Komponen', 'url' => ['deptactivity/list', 'id' => $session['deptProgramId']]];
$this->params['breadcrumbs'][] = ['label' => 'Kegiatan', 'url' => ['deptsubactivity/list', 'id' => $session['deptActivityId']]];
$this->params['breadcrumbs'][] = ['label' => 'Bentuk Kegiatan', 'url' => ['deptsubactivitydata/list', 'id' => $session['deptSubActivityId']]];
$this->params['breadcrumbs'][] = ['label' => 'Detail Kegiatan', 'url' => ['deptsubactivitydata/view', 'id' => $session['deptSubActivityDataId']]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="deptsubactivitydetail-create">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
        'title' => $this->title,
        'sd' => $sd,
        'akun' => $akun,
    ]) ?>

</div>
