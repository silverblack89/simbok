<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Accountaccess */

$this->title = 'Create Accountaccess';
$this->params['breadcrumbs'][] = ['label' => 'Accountaccesses', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="accountaccess-create">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
