<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\web\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Activity */

$this->title = $session['activityNama'];
$this->params['breadcrumbs'][] = ['label' => 'Data Program ('.$session['programYear'].')', 'url' => ['program/index', 'tahun' => $session['programYear']]];
$this->params['breadcrumbs'][] = ['label' => 'Data Pelayanan', 'url' => ['service/index', 'id' => $session['programId']]];
$this->params['breadcrumbs'][] = ['label' => 'Data Kegiatan', 'url' => ['index', 'id' => $session['serviceId']]];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="activity-view">

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
            // 'id',
            // 'service_id',
            'nama_kegiatan:ntext',
            // 'aktif',
            [
                'attribute' => 'aktif',
                'value' => function ($model) {
                    return $model->aktif ? 'Ya' : 'Tidak';
                },
            ],
        ],
    ]) ?>

</div>
