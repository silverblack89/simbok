<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Account */

$this->title = 'Tambah Rekening';
$this->params['breadcrumbs'][] = ['label' => 'Rekening', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="account-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
