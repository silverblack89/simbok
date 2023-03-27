<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Deptsp2d */

$this->title = 'Create Deptsp2d';
$this->params['breadcrumbs'][] = ['label' => 'Deptsp2ds', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="deptsp2d-create">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
