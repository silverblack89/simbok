<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Deptperfomance */

$this->title = 'Create Deptperfomance';
$this->params['breadcrumbs'][] = ['label' => 'Deptperfomances', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="deptperfomance-create">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
        'target' => $target
    ]) ?>

</div>
