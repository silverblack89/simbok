<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $searchModel app\models\DeptdatarealSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Data Realisasi';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="deptdatareal-index">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><?= Html::encode($this->title) ?></h3>
        </div>
        <div class="panel-body">
            <p>
                <!-- <?= Html::a('Tambah', ['create', 'id' => $id], ['class' => 'btn btn-success']) ?> -->
                <?= Html::button('<span class="glyphicon glyphicon-plus"></span> Tambah', ['value' => Url::to(['create', 'id' => $id]), 'class' => 'showModalButton btn btn-success']) ?>
            </p>

            <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                // 'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    // 'id',
                    // 'dept_sub_activity_detail_id',
                    [
                        'attribute' => 'tanggal',
                        'contentOptions' => ['style' => 'width: 20%']
                    ],
                    'nomor',
                    [
                        'label' => 'Jumlah',
                        'attribute' =>'jumlah',
                        'enableSorting' => false,
                        'contentOptions' => ['class' => 'col-lg-1 text-right', 'style' => 'width: 4%'],
                        'format'=>['decimal',0]
                    ],

                    // ['class' => 'yii\grid\ActionColumn'],
                    ['class' => 'yii\grid\ActionColumn',
                        'contentOptions' => ['style' => 'width: 15%;text-align:center'],
                        'template' => '{update} {delete}',
                        'visible' => true,
                        'buttons' => [
                            'update' => function ($url, $model) {
                                // return Html::a('Update', array('realisasi/update', 'id' => $model->id), ['class'=>'showModalButton btn btn-xs btn-warning custom_button']);
                                return Html::button('<span class="glyphicon glyphicon-pencil"></span>', ['value' => Url::to(['deptdatareal/update', 'id' => $model->id]), 'title' => 'Ubah Data', 'class' => 'showModalButton btn btn-xs btn-warning custom_button']);
                            },
                            'delete' => function ($url, $model) {
                                // return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['realisasi/delete', 'id' => $model->id], ['title' => 'Hapus Detail', 'class'=>'btn btn-xs btn-danger custom_button', 
                                //     'data' => [
                                //         'confirm' => 'Apakah Anda yakin akan menghapus data ini?',
                                //         'method' => 'post',
                                //     ],
                                // ]);

                                return Html::button('<span class="glyphicon glyphicon-trash"></span>', [
                                    'title' => 'Hapus Realisasi', 
                                    'class' => 'btn btn-xs btn-danger',
                                    'onclick' => "if (confirm('Apakah realisasi akan dihapus?')) {
                                        $.ajax({
                                            type: 'POST',
                                            url: window.origin +'".Url::to(['deptdatareal/delete', 'id' => $model->id])."',
                                            data: '',
                                            success: function(result) {
                                                if(result == 0) {
                                                    $('#modal').modal('hide');
                                                    $.pjax.reload('#datareal', {timeout: false});
                                                }else{
                                                    alert('Gagal menghapus data');
                                                }
                                            }, 
                                            // error: function(result) {
                                            //     console.log(\"server error\");
                                            // }
                                        });
                                    }
                                    return false;
                                    ",
                                ]);
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
        // 'header'=>'<h4>Detail Kegiatan</h4>', 
        'id'=>'modal',
        'size'=>'modal-md',
        'clientOptions' => ['backdrop' => 'static', 'keyboard' => FALSE],
        // 'footer' => ''
    ]);
    echo "<div id='modalContent'></div>";
    Modal::end();
?>
