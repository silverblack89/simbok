<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Deptrealization */

$this->title = 'Create Deptrealization';
$this->params['breadcrumbs'][] = ['label' => 'Deptrealizations', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="deptrealization-create">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
