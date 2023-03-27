<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\session;
use yii\bootstrap\Modal;
use yii\helpers\Url;

$session = Yii::$app->session;
/* @var $this yii\web\View */
/* @var $searchModel app\models\ActivitySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Data Kegiatan';
$this->params['breadcrumbs'][] = ['label' => 'Data Program ('.$session['programYear'].')', 'url' => ['program/index', 'tahun' => $session['programYear']]];
$this->params['breadcrumbs'][] = ['label' => 'Data Pelayanan', 'url' => ['service/index', 'id' => $session['programId']]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="activity-index">

    <h1><?= Html::encode($name) ?></h1>

    <p>
        <?= Html::a('Tambah Kegiatan', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

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
            'status',

            // ['class' => 'yii\grid\ActionColumn'],
            ['class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['style' => 'width: 8%'],
                'template' => '{view} {update} {delete} {access}',
                'buttons' => [
                    'access' => function($url, $model){
                        // return Html::a('<span class="glyphicon glyphicon-wrench"></span>', array('/accountaccess', 'id' => $model->id), ['class'=>'btn btn-xs btn-link custom_button']);
                        return Html::button('<span class="glyphicon glyphicon-wrench"></span>', ['value' => Url::to(['/accountaccess', 'id' => $model->id]), 'class' => 'showModalButton btn btn-xs btn-link', 'style' => 'margin-left:-5px']);
                    }
                ]
            ],
            [
                'label' => 'Rekening',
                'value' => function($model){
                    return $model->getAccountAccess($model->id);
                },
                'contentOptions' => ['style' => 'font-size:9px'],
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
