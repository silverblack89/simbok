<?php

use yii\helpers\Html;
use yii\web\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Ukm */

$this->title = 'Update Ukm: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => $session['periodValue'], 'url' => ['period/create', 'p' => 'def']];
$this->params['breadcrumbs'][] = ['label' => 'Realisasi', 'url' => ['index', 'tahun'=>$tahun]];
$this->params['breadcrumbs'][] = 'Ubah';
?>
<div class="ukm-update">

    <!-- <h1><?= Html::encode('Ubah Realisasi') ?></h1> -->

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
