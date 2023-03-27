<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Deptperiod */

$this->title = 'Periode';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="deptperiod-create">
    <div class="col-md-12 school-options-dropdown text-center">
        <div class="dropdown btn-group">

            <h1><?= Html::encode('Periode POA') ?></h1>

            <?= $this->render('_form', [
                'model' => $model,
                // 'dataProvider' => $dataProvider,
                // 'tahun' => $tahun,
            ]) ?>
        </div>
    </div>

</div>

