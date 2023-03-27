<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Capout */

$this->title = 'Create Capout';
$this->params['breadcrumbs'][] = ['label' => 'Capouts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="capout-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
