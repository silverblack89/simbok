<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Unit */

if (Yii::$app->user->identity->username == 'admin'){
    $this->title = 'Ubah Unit: ' . $model->id;
    $this->params['breadcrumbs'][] = ['label' => 'Units', 'url' => ['index']];
    $this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
    $this->params['breadcrumbs'][] = 'Ubah';
}else{
    $this->title = 'Data ' .Yii::$app->user->identity->alias;
    $this->params['breadcrumbs'][] = 'Info Puskesmas';
}

?>
<div class="unit-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
