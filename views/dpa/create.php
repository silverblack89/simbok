<?php

use yii\helpers\Html;
use yii\web\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Dpa */

$this->title = 'Tambah';
$this->params['breadcrumbs'][] = ['label' => 'Sub Menu DPA', 'url' => ['index', 'tahun' => $session['dpaYear']]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dpa-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
