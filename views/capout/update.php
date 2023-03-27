<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Capout */

$this->title = 'Update Capout: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Capouts', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="capout-update">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
        'co1' => $co1,
        'co2' => $co2,
        'co3' => $co3,
        'co4' => $co4,
    ]) ?>

</div>
