<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Deptfinancialrealization */

$this->title = 'Create Deptfinancialrealization';
$this->params['breadcrumbs'][] = ['label' => 'Deptfinancialrealizations', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="deptfinancialrealization-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
