<?php

use yii\helpers\Html;
use yii\app\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Deptverification */

// $this->title = 'Update Deptverification: ' . $model->id;
// $this->params['breadcrumbs'][] = ['label' => 'Deptverifications', 'url' => ['index']];
// $this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
// $this->params['breadcrumbs'][] = 'Update';
?>
<div class="deptverification-update">

    <h1><?= Html::encode($programName) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'model2' => $model2,
        'dataProvider' => $dataProvider,
        'kunci' => $kunci,
        'status' => $status
    ]) ?>

</div>
