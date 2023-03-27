<?php

use yii\helpers\Html;
use yii\web\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Deptsubactivity */

$this->title = 'Tambah SubKegiatan';
$this->params['breadcrumbs'][] = ['label' => 'Data Program ('.$session['programYear'].')', 'url' => ['deptprogram/index', 'tahun' => $session['programYear']]];
$this->params['breadcrumbs'][] = ['label' => 'Data Kegiatan', 'url' => ['deptactivity/index', 'id' => $session['deptProgramId']]];
$this->params['breadcrumbs'][] = ['label' => 'Data SubKegiatan', 'url' => ['deptsubactivity/index', 'id' => $session['deptActivityId']]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="deptsubactivity-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
