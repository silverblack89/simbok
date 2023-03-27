<?php

use yii\helpers\Html;
use yii\web\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Ukpagu */

$this->title = 'Ubah Pagu: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => $session['periodValue'], 'url' => ['period/create', 'p' => 'def']];
$this->params['breadcrumbs'][] = ['label' => 'Realisasi', 'url' => ['ukm/index', 'tahun' => $session['tahun']]];
$this->params['breadcrumbs'][] = ['label' => 'Pagu', 'url' => ['index', 'tahun' => $session['tahun']]];
$this->params['breadcrumbs'][] = 'Ubah';
?>
<div class="ukpagu-update">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
