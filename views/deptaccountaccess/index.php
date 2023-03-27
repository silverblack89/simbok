<?php

use yii\helpers\Html;
// use yii\grid\GridView;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\DeptaccountaccessSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Rekening Aktif';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="deptaccountaccess-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <!-- <?= Html::a('Create Deptaccountaccess', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::button('<span class="glyphicon glyphicon-plus"></span> Tambah', ['value' => Url::to(['deptaccountaccess/create', 'id'=>$id]), 'class' => 'showModalButton btn btn-success']) ?> -->
    </p>

    <?php echo $this->render('_form', ['model' => $model]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'pjax' => true,
        'pjaxSettings' =>[
            'neverTimeout'=>true,
            'options'=>[
                'id'=>'deptaccountaccess',
            ]
        ],  
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'id',
            // 'dept_sub_activity_id',
            'account.nama_rekening',

            ['class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['style' => 'width:5%;text-align:center'],
                'template' => '{delete}'
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
