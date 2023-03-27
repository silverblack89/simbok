<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\Session;
use yii\bootstrap\Modal;
use yii\helpers\Url;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $searchModel app\models\DeptsubactivitySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Data SubKegiatan';
$this->params['breadcrumbs'][] = ['label' => 'Data Program ('.$session['programYear'].')', 'url' => ['deptprogram/index', 'tahun' => $session['programYear']]];
$this->params['breadcrumbs'][] = ['label' => 'Data Kegiatan', 'url' => ['deptactivity/index', 'id' => $session['deptProgramId']]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="deptsubactivity-index">

    <h1><?= Html::encode($name) ?></h1>

    <p>
        <?= Html::a('Tambah SubKegiatan', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'id',
            // 'dept_activity_id',
            'kode_rekening',
            'nama_sub_kegiatan',
            [
                'attribute' => 'aktif',
                'value' => function ($model) {
                    return $model->aktif ? 'Ya' : 'Tidak';
                },
            ],

            'status',

            // ['class' => 'yii\grid\ActionColumn'],
            ['class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['style' => 'width: 8%'],
                'template' => '{view} {update} {delete} {access}',
                'buttons' => [
                    'access' => function($url, $model){
                        // return Html::a('<span class="glyphicon glyphicon-wrench"></span>', array('/deptaccountaccess', 'id' => $model->id), ['class'=>'btn btn-xs btn-link custom_button']);
                        return Html::button('<span class="glyphicon glyphicon-wrench"></span>', ['value' => Url::to(['/deptaccountaccess', 'id' => $model->id]), 'class' => 'showModalButton btn btn-xs btn-link', 'style' => 'margin-left:-5px']);
                    }
                ]
            ],
        ],
    ]); ?>
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
