<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Deptdatareal */

$this->title = 'Tambah Realisasi';
$this->params['breadcrumbs'][] = ['label' => 'Deptdatareals', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="deptdatareal-create">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
        'title' => $this->title,
        'id' => $id
    ]) ?>

</div>
