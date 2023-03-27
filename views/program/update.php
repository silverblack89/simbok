<?php

use yii\helpers\Html;
use yii\app\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Program */

$this->title = 'Ubah Data Program';
$this->params['breadcrumbs'][] = ['label' => 'Data Program ('.$session['programYear'].')', 'url' => ['index', 'tahun' => $session['programYear']]];
$this->params['breadcrumbs'][] = ['label' => $session['programNama'], 'url' => ['view', 'id' => $session['programId']]];
$this->params['breadcrumbs'][] = 'Ubah Data Program';
?>
<div class="program-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
