<?php

use yii\helpers\Html;
use yii\web\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Ukpagu */

$this->title = 'Tambah';
$this->params['breadcrumbs'][] = ['label' => $session['periodValue'], 'url' => ['period/create', 'p' => 'def']];
$this->params['breadcrumbs'][] = ['label' => 'Realisasi', 'url' => ['ukm/index', 'tahun' => $session['tahun']]];
$this->params['breadcrumbs'][] = ['label' => 'Pagu', 'url' => ['index', 'tahun' => $session['tahun']]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ukpagu-create">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
