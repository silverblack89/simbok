<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Deptperfomance */

$this->title = 'Update Deptperfomance: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Deptperfomances', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="deptperfomance-update">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
        'target' => $target
    ]) ?>

</div>
