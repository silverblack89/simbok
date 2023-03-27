<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\web\Session;

$session = Yii::$app->session;
/* @var $this yii\web\View */
/* @var $model app\models\Deptsubactivity */

$this->title = $session['deptSubActivityNama'];
$this->params['breadcrumbs'][] = ['label' => 'Data Program ('.$session['programYear'].')', 'url' => ['deptprogram/index', 'tahun' => $session['programYear']]];
$this->params['breadcrumbs'][] = ['label' => 'Data Kegiatan', 'url' => ['deptactivity/index', 'id' => $session['deptProgramId']]];
$this->params['breadcrumbs'][] = ['label' => 'Data SubKegiatan', 'url' => ['deptsubactivity/index', 'id' => $session['deptActivityId']]];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="deptsubactivity-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Ubah', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Hapus', ['delete', 'id' => $model->id], [
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
            // 'dept_activity_id',
            [
                'label' => 'Nama Kegiatan',
                'attribute' => 'dept_activity_id',
                'value' => $session['deptActivityNama'],
            ],
            // 'nama_sub_kegiatan',
            [
                'attribute' => 'aktif',
                'value' => function ($model) {
                    return $model->aktif ? 'Ya' : 'Tidak';
                },
            ],
        ],
    ]) ?>

</div>
