<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Verification */

// $this->title = 'Update Verification: ' . $model->id;
// $this->params['breadcrumbs'][] = ['label' => 'Verifications', 'url' => ['index']];
// $this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
// $this->params['breadcrumbs'][] = 'Update';
?>
<div class="verification-update">

<h3><?= Html::encode($programName) ?></h3>

    <?= $this->render('_form', [
        'model' => $model,
        'model2' => $model2,
        'dataProvider' => $dataProvider,
        'kunci' => $kunci,
        'status' => $status
    ]) ?>

</div>
