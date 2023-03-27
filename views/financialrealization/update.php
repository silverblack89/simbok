<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Financialrealization */

$this->title = 'Update Financialrealization: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Financialrealizations', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="financialrealization-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
