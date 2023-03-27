<?php

use yii\helpers\Html;
use yii\web\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Ukm */

$this->title = 'Tambah';
$this->params['breadcrumbs'][] = ['label' => $session['periodValue'], 'url' => ['period/create', 'p' => 'def']];
$this->params['breadcrumbs'][] = ['label' => 'Realisasi', 'url' => ['index', 'tahun' => $tahun]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ukm-create">

    <!-- <h1><?= Html::encode('Tambah Realisasi') ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
        'listuk' => $listuk,
        'tahun' => $tahun,
        'jan' => $jan,
        'feb' => $feb,
        'mar' => $mar,
        'apr' => $apr,
        'mei' => $mei,
        'jun' => $jun,
        'jul' => $jul,
        'agu' => $agu,
        'sep' => $sep,
        'okt' => $okt,
        'nov' => $nov,
        'des' => $des,
    ]) ?>

</div>
