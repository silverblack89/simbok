<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Uklabel */

$this->title = 'Update Uklabel: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Uklabels', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="uklabel-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
