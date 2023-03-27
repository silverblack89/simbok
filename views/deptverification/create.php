<?php

use yii\helpers\Html;
use yii\app\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Deptverification */

// $this->title = 'Create Deptverification';
// $this->params['breadcrumbs'][] = ['label' => 'Deptverifications', 'url' => ['index']];
// $this->params['breadcrumbs'][] = $this->title;
?>
<div class="deptverification-create">

    <h1><?= Html::encode($programName) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'model2' => $model2,
        'dataProvider' => $dataProvider,
        'programName' => $programName,
        'status' => $status,
        'kunci' => $kunci,
        'id' => $session['programId']
    ]) ?>

</div>
