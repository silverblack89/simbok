<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Bok */

$this->title = 'Create Bok';
$this->params['breadcrumbs'][] = ['label' => 'Boks', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bok-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
