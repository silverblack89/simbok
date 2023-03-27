<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
// use yii\grid\GridView;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\AccountaccessSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Rekening Aktif';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="accountaccess-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <!-- <?= Html::a('Create', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::button('<span class="glyphicon glyphicon-plus"></span> Tambah', ['value' => Url::to(['accountaccess/create', 'id'=>$id]), 'class' => 'showModalButton btn btn-success']) ?> -->
    </p>


    <?php echo $this->render('_form', ['model' => $model]); ?>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'pjax' => true,
        'pjaxSettings' =>[
            'neverTimeout'=>true,
            'options'=>[
                'id'=>'accountaccess',
            ]
        ],  
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'id',
            // 'activity_id',
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
