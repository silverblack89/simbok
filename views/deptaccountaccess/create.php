<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Deptaccountaccess */

$this->title = 'Create Deptaccountaccess';
$this->params['breadcrumbs'][] = ['label' => 'Deptaccountaccesses', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="deptaccountaccess-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
