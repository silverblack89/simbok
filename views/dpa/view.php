<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\web\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Dpa */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Sub Menu DPA', 'url' => ['index', 'tahun' => $session['dpaYear']]];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="dpa-view">

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
            'dept_sub_activity_id',
            'tahun',
            'nama',
            'keterangan',
        ],
    ]) ?>

</div>
