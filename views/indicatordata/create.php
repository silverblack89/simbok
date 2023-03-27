<?php

use yii\helpers\Html;
use yii\web\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Indicatordata */

$this->title = 'Tambah Data';
$this->params['breadcrumbs'][] = ['label' => $session['periodValue'], 'url' => ['period/create']];
$this->params['breadcrumbs'][] = ['label' => 'Data Upaya Program', 'url' => ['program/list']];
$this->params['breadcrumbs'][] = ['label' => 'Data Indikator', 'url' => ['indicator/list', 'id' => $session['programId']]];
$this->params['breadcrumbs'][] = ['label' => 'Data Realisasi', 'url' => ['indicatordata/index', 'id' => $session['indicatorId']]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="indicatordata-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
