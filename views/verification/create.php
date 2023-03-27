<?php

use yii\helpers\Html;
use yii\web\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Verification */

// $this->title = 'Create Verification';
// $this->params['breadcrumbs'][] = ['label' => 'Verifications', 'url' => ['index']];
// $this->params['breadcrumbs'][] = $this->title;
?>
<div class="verification-create">

    <h3><?= Html::encode($programName) ?></h3>

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
