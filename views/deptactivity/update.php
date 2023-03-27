<?php

use yii\helpers\Html;
use yii\web\Session;

$session = Yii::$app->session;
/* @var $this yii\web\View */
/* @var $model app\models\Deptactivity */

$this->title = 'Ubah Data Kegiatan';
$this->params['breadcrumbs'][] = ['label' => 'Data Program ('.$session['programYear'].')', 'url' => ['deptprogram/index', 'tahun' => $session['programYear']]];
$this->params['breadcrumbs'][] = ['label' => 'Data Kegiatan', 'url' => ['index', 'id' => $session['programId']]];
$this->params['breadcrumbs'][] = 'Ubah Data';
?>
<div class="deptactivity-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
