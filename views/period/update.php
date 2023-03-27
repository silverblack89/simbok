<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Period */

// $this->title = 'Update Period: ' . $model->id;
// $this->params['breadcrumbs'][] = ['label' => 'Periods', 'url' => ['index']];
// $this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
// $this->params['breadcrumbs'][] = 'Update';
?>
<div class="period-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
