<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Uklabel */

$this->title = 'Create Uklabel';
$this->params['breadcrumbs'][] = ['label' => 'Uklabels', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="uklabel-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
