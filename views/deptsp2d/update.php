<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Deptsp2d */

$this->title = 'Update Deptsp2d: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Deptsp2ds', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="deptsp2d-update">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
