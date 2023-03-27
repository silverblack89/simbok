<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Deptreal */

$this->title = 'Update Deptreal: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Deptreals', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="deptreal-update">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
        'jan' => $jan,
        'feb' => $feb,
        'mar' => $mar,
        'apr' => $apr,
        'mei' => $mei,
        'jun' => $jun,
        'jul' => $jul,
        'agu' => $agu,
        'sep' => $sep,
        'okt' => $okt,
        'nov' => $nov,
        'des' => $des,
    ]) ?>

</div>
