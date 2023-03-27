<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Realization */

$this->title = 'Create Realization';
$this->params['breadcrumbs'][] = ['label' => 'Realizations', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="realization-create">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
