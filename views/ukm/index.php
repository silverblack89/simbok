<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\web\Session;
use yii\bootstrap\Modal;
use yii\bootstrap\ButtonDropdown;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $searchModel app\models\UkmSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Realisasi';
$this->params['breadcrumbs'][] = ['label' => $session['periodValue'], 'url' => ['period/create', 'p' => 'def']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ukm-index">

    <!-- <h1><?= Html::encode('Realisasi UKM & COVID') ?></h1> -->

    <p>
    <?= Html::a('<span class="glyphicon glyphicon-search"></span> Lihat Data Pagu', ['ukpagu/index' , 'tahun' => $tahun], ['class' => 'btn btn-info']) ?>
    <!-- <?= Html::button('<span class="glyphicon glyphicon-search"></span> Lihat Data Pagu', ['value' => Url::to(['ukpagu/index', 'tahun' => $tahun]), 'class' => 'showModalButton btn btn-info']) ?> -->
    <?= Html::a('<span class="glyphicon glyphicon-search"></span> Lihat Data SP2D', ['sp2d/index' , 'tahun' => $tahun], ['class' => 'btn btn-info']) ?>
    <!-- <?= Html::button('<span class="glyphicon glyphicon-search"></span> Lihat Data SP2D', ['value' => Url::to(['sp2d/index', 'tahun' => $tahun]), 'class' => 'showModalButton btn btn-info']) ?> -->
    </p>

    <div style="text-align:right;margin:2px;float:right">

      <?=                                      
      ButtonDropdown::widget([
         'encodeLabel' => false,
         'label' => '<span class="glyphicon glyphicon-file"></span> Laporan BOK UKM',
         'dropdown' => [
            'items' => [
                  ['label' => \Yii::t('yii', 'Tahunan'),
                     'linkOptions' => [
                        // 'data' => [
                        //       'method' => 'POST',
                        //       // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                        // ],
                     ],
                     'url' => Url::to(['laporan-ukm', 'id' => '0']),
                     'visible' => true,   // same as above
                  ],
                  ['label' => \Yii::t('yii', 'Tribulan I'),
                     'linkOptions' => [
                        // 'data' => [
                        //       'method' => 'POST',
                        //       // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                        // ],
                     ],
                     'url' => Url::to(['laporan-ukm', 'id' => '1']),
                     'visible' => true,   // same as above
                  ],
                  ['label' => \Yii::t('yii', 'Tribulan II'),
                     'linkOptions' => [
                        // 'data' => [
                        //       'method' => 'POST',
                        //       // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                        // ],
                     ],
                     'url' => Url::to(['laporan-ukm', 'id' => '2']),
                     'visible' => true,   // same as above
                  ],
                  ['label' => \Yii::t('yii', 'Tribulan III'),
                     'linkOptions' => [
                        // 'data' => [
                        //       'method' => 'POST',
                        //       // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                        // ],
                     ],
                     'url' => Url::to(['laporan-ukm', 'id' => '3']),
                     'visible' => true,   // same as above
                  ],
                  ['label' => \Yii::t('yii', 'Tribulan IV'),
                     'linkOptions' => [
                        // 'data' => [
                        //       'method' => 'POST',
                        //       // 'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                        // ],
                     ],
                     'url' => Url::to(['laporan-ukm', 'id' => '4']),
                     'visible' => true,   // same as above
                  ],
            ],
         ],
         'options' => ['class' => 'btn btn-md btn-default custom_button'],
      ]);
      ?>
   </div>

    <div class="panel panel-primary">
        <div class="panel-heading">
                <h3 class="panel-title">Data Realisasi</h3>
        </div>
        <div class="panel-body">

         <p>
               <?= Html::a('<span class="glyphicon glyphicon-plus"></span> Entri Baru', ['create', 'tahun' => $tahun], ['class' => 'btn btn-success']) ?>
         </p>

         <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

         <?= GridView::widget([
            'dataProvider' => $dataProvider,
            // 'filterModel' => $searchModel,
            'columns' => [
                  ['class' => 'yii\grid\SerialColumn'],

                  [
                     'attribute' => 'bulan',
                     'value' => function($model){
                        if($model->bulan == '1'){
                           return 'Januari';
                        }
                        if($model->bulan == '2'){
                           return 'Februari';
                        }
                        if($model->bulan == '3'){
                           return 'Maret';
                        }
                        if($model->bulan == '4'){
                           return 'April';
                        }
                        if($model->bulan == '5'){
                           return 'Mei';
                        }
                        if($model->bulan == '6'){
                           return 'Juni';
                        }
                        if($model->bulan == '7'){
                           return 'Juli';
                        }
                        if($model->bulan == '8'){
                           return 'Agustus';
                        }
                        if($model->bulan == '9'){
                           return 'September';
                        }
                        if($model->bulan == '10'){
                           return 'Oktober';
                        }
                        if($model->bulan == '11'){
                           return 'November';
                        }
                        if($model->bulan == '12'){
                           return 'Desember';
                        }
                        }
                  ],
                  'tahun',
                  [
                     'label' => 'Jumlah',
                     'contentOptions' => ['class' => 'col-lg-1 text-right', 'style' => 'width: 10%'],
                     'value' => function($model){
                        return number_format($model->re_1+$model->re_2+$model->re_3+$model->re_4+$model->re_5+$model->re_6+$model->re_7+$model->re_8+$model->re_9+$model->re_10+$model->re_11+$model->re_12+$model->re_13+$model->re_14+$model->re_15+$model->re_16+$model->re_17+$model->re_18+$model->re_19+$model->re_20+$model->re_21+$model->re_22+$model->re_23+$model->re_24+$model->re_25+$model->re_26+$model->re_27+$model->re_28+$model->re_29+$model->re_30, 0, ",", ".");
                     }
                  ],

                  // ['class' => 'yii\grid\ActionColumn'],
                  ['class' => 'yii\grid\ActionColumn',
                     'contentOptions' => ['style' => 'width: 26%'],
                     'template' => '{update} {delete} {view}',
                     'buttons' => [
                        'delete' => function ($url, $model) {
                              return 
                              Html::a('<span class="glyphicon glyphicon-trash"></span> Hapus', ['ukm/delete', 'id' => $model->id], [
                                 'class'=>'btn btn-xs btn-danger custom_button',
                                 'data' => ['confirm' => Yii::t('app', 'Apakah Anda yakin akan menghapus data ini?'),'method' => 'post']]); 
                        },
                        'update' => function ($url, $model) {
                              return 
                              Html::a('<span class="glyphicon glyphicon-pencil"></span> Ubah', ['ukm/update', 'id' => $model->id], ['class'=>'btn btn-xs btn-warning custom_button']); 
                        },
                        'view' => function ($url, $model) {
                           return 
                           Html::a('<span class="glyphicon glyphicon-file"></span> Laporan BOK Covid', ['ukm/view', 'id' => $model->id, 'bln' => $model->bulan], ['class'=>'btn btn-xs btn-info custom_button']);
                           // Html::button('<span class="glyphicon glyphicon-file"></span> Laporan', ['value' => Url::to(['ukm/view', 'id'=>$model->id]), 'class' => 'showModalButton btn btn-xs btn-info custom_button']); 
                        },
                     ]
                  ],
            ],
         ]); ?>
      </div>
   </div>
</div>

<?php 
    Modal::begin([
            // 'header'=>'<h4>Login User</h4>',
            'id'=>'modal',
            'size'=>'modal-sm',
            'clientOptions' => ['backdrop' => 'dinamis', 'keyboard' => FALSE],
            // 'footer' => ''
        ]);
    echo "<div id='modalContent'></div>";
    Modal::end();
?>