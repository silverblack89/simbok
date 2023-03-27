<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Perfomance */

$this->title = 'Update Perfomance: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Perfomances', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="perfomance-update">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
        'target' => $target
    ]) ?>

</div>
