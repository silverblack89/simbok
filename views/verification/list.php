<?php

use yii\helpers\Html;
use yii\grid\GridView;

?>

<h3><?= Html::encode('Realisasi: ' .$programName) ?></h3>

<div class="verification-list">
    <div style="overflow-x:auto;">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            // 'filterModel' => $searchModel,
            'id' => 'GridView',
            'options' => ['style' => 'font-size:13px;'],
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],

                // [
                //     'attribute' => 'nama_kegiatan',
                //     'label' => 'Jenis',
                // ],
                [
                    'attribute' => 'bentuk_kegiatan',
                    'label' => 'Kegiatan',
                ],
                'sasaran',
                // 'target',
                'lokasi',
                // 'pelaksana',
                [
                    'attribute' => 'nama_rekening',
                    'label' => 'Rekening',
                ],
                [
                    'label' => 'Vol-1',
                    'attribute' =>'realisasi_vol_1',
                    'enableSorting' => false,
                    'contentOptions' => ['class' => 'col-lg-1 text-right'],
                    'format'=>['decimal',0]
                ],
                [
                    'attribute' => 'realisasi_satuan_1',
                    'label' => 'Satuan',
                    'contentOptions' => ['style' => 'width: 5%']
                ],
                [
                    'label' => 'Vol-2',
                    'attribute' =>'realisasi_vol_2',
                    'enableSorting' => false,
                    'contentOptions' => ['class' => 'col-lg-1 text-right'],
                    'format'=>['decimal',0]
                ],
                [
                    'attribute' => 'realisasi_satuan_2',
                    'label' => 'Satuan',
                    'contentOptions' => ['style' => 'width: 5%']
                ],
                [
                    'label' => 'Biaya',
                    'attribute' =>'realisasi_unit_cost',
                    'enableSorting' => false,
                    'contentOptions' => ['class' => 'col-lg-1 text-right'],
                    'format'=>['decimal',0]
                ],
                [
                    'label' => 'Jumlah',
                    'attribute' =>'realisasi_jumlah',
                    'enableSorting' => false,
                    'contentOptions' => ['class' => 'col-lg-1 text-right'],
                    'format'=>['decimal',0]
                ],

                // ['class' => 'yii\grid\ActionColumn'],
            ],
        ]); ?>
    </div>
</div>