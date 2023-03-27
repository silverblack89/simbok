<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\web\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Program */

$this->title = $session['programNama'];
$this->params['breadcrumbs'][] = ['label' => 'Data Program ('.$session['programYear'].')', 'url' => ['index', 'tahun' => $session['programYear']]];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="program-view">

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
            'nama_program',
            'tahun',
            // 'aktif',
            [
                'attribute' => 'aktif',
                'value' => function ($model) {
                    return $model->aktif ? 'Ya' : 'Tidak';
                },
            ],
        ],
    ]) ?>

    <p>
        <?= Html::a('Tambah Indikator', ['indicator/create', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'summary' => '',
        // 'filterModel' => $searchModel,
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'dateFormat' => 'dd/MM/yyyy',
            'decimalSeparator' => ',',
            'thousandSeparator' => '.',
            'currencyCode' => 'IDR',
            'nullDisplay' => '',       
            'locale'=>'id_ID'   
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'label' => 'Nama Indikator',
                'attribute' => 'nama_indikator',
                'enableSorting' => false,
            ],

            [
                'attribute' => 'Data Komulatif',
                'value' => function ($model) {
                    return $model->data_komulatif ? 'Ya' : 'Tidak';
                },
            ],
            
            // ['class' => 'yii\grid\ActionColumn'],
            ['class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['style' => 'width: 5%'],
                'template' => '{update} {delete}',
                'buttons' => [
                    'delete' => function ($url, $model) {
                        return 
                        Html::a('<span class="glyphicon glyphicon-trash"></span>', ['indicator/delete', 'id' => $model->id], ['data' => ['confirm' => Yii::t('app', 'Apakah Anda yakin akan menghapus data ini?'),'method' => 'post',],]); 
                    },
                    'update' => function ($url, $model) {
                        return Html::a('', array('indicator/update', 'id'=>$model->id), ['class'=>'glyphicon glyphicon-pencil']);
                    },
                ]
            ],
        ],
    ]); ?>

</div>
