<?php

use yii\helpers\Html;
use yii\web\session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Deptsubactivitydata */

$this->title = 'Tambah Bentuk Kegiatan';
$this->params['breadcrumbs'][] = ['label' => $session['deptPeriodValue'], 'url' => ['deptperiod/create']];
$this->params['breadcrumbs'][] = ['label' => 'Data Program', 'url' => ['deptprogram/list']];
$this->params['breadcrumbs'][] = ['label' => 'Data Kegiatan', 'url' => ['deptactivity/list', 'id' => $session['deptProgramId']]];
$this->params['breadcrumbs'][] = ['label' => 'Data SubKegiatan', 'url' => ['deptsubactivity/list', 'id' => $session['deptActivityId']]];
$this->params['breadcrumbs'][] = ['label' => 'Bentuk Kegiatan', 'url' => ['deptsubactivitydata/list', 'id' => $session['deptSubActivityId']]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="deptsubactivitydata-create">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
        'modul' => $modul,
        'title' => $this->title
    ]) ?>

</div>
