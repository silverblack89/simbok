<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Activitydatasub */

$this->title = 'Create Activitydatasub';
$this->params['breadcrumbs'][] = ['label' => 'Activitydatasubs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="activitydatasub-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
