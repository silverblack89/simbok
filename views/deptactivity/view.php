<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\web\session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Deptactivity */

$this->title = $session['deptActivityNama'];
$this->params['breadcrumbs'][] = ['label' => 'Data Program ('.$session['programYear'].')', 'url' => ['deptprogram/index', 'tahun' => $session['programYear']]];
$this->params['breadcrumbs'][] = ['label' => 'Data Kegiatan', 'url' => ['index', 'id' => $session['deptProgramId']]];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="deptactivity-view">

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
            // 'dept_program_id',
            [
                'label' => 'Nama Program',
                'attribute' => 'dept_program_id',
                'value' => $session['deptProgramNama'],
            ],
            'kode_rekening',
            // 'nama_kegiatan',
            'pagu',
            [
                'attribute' => 'aktif',
                'value' => function ($model) {
                    return $model->aktif ? 'Ya' : 'Tidak';
                },
            ],
        ],
    ]) ?>

</div>
