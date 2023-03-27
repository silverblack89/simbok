<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Deptsubactivitydetail */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Deptsubactivitydetails', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="deptsubactivitydetail-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'dept_sub_activity_data_id',
            'account_id',
            'vol_1',
            'satuan_1',
            'vol_2',
            'satuan_2',
            'unit_cost',
            'jumlah',
            'tw1',
            'tw2',
            'tw3',
        ],
    ]) ?>

</div>
