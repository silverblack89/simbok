<?php

use yii\helpers\Html;
use yii\web\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Deptprogram */

$this->title = 'Tambah Program';
$this->params['breadcrumbs'][] = ['label' => 'Data Program ('.$session['programYear'].')', 'url' => ['index', 'tahun' => $session['programYear']]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="deptprogram-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
