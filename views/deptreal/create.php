<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Deptreal */

$this->title = 'Create Deptreal';
$this->params['breadcrumbs'][] = ['label' => 'Deptreals', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="deptreal-create">

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
        'grupLabel' => $grupLabel
    ]) ?>

</div>
