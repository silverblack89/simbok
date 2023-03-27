<?php

use yii\helpers\Html;
// use yii\grid\GridView;
use kartik\grid\GridView;
use yii\web\Session;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\helpers\ArrayHelper;
use app\models\Deptgroupsp2d;
use yii\widgets\Pjax;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $searchModel app\models\Deptsp2dSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Data SP2D';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="deptsp2d-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <!-- <?= Html::a('Tambah', ['sp2d/create'], ['class' => 'btn btn-success']) ?> -->
        <?= Html::button('<span class="glyphicon glyphicon-plus"></span> Entri Baru', ['value' => Url::to(['deptsp2d/create']), 'class' => 'showModalButton btn btn-success']) ?>

        <?= Html::dropDownList('sp2d', null, (ArrayHelper::map(Deptgroupsp2d::find()->all(),'id','nama')) ,
            [
            'prompt'=>'Kelompok SP2D',
            'options'=>[$session['deptGroupSp2d']=>['Selected'=>true]],
            'style' => 'width:150px;', 
            'onchange'=>'
                $.pjax.reload({
                    url: "'.Url::toRoute(['deptsp2d/index']).'?id="+$(this).val(),
                    container: "#pjax-gridview",
                    timeout: 1000,
                })

                // alert($(this).val());
                ;',
            'class'=>'form-control pull-right']) 
        ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php Pjax::begin(['id' => 'pjax-gridview']) ?>
    <div style="overflow-x:auto">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'showPageSummary' => true,
            'pageSummaryRowOptions' => ['class' => 'kv-page-summary success', 'style' => 'text-align:right'],
            'pjax' => true,
            'striped' => true,
            'hover' => false,
            // 'panel' => ['type' => 'primary', 'heading' => 'Data POA ' .$session['poaLabel']],
            'toggleDataContainer' => ['class' => 'btn-group mr-2'],
            'toolbar' => false,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],

                // 'id',
                [
                    'attribute' => 'tanggal',
                    'contentOptions' => ['style' => 'width: 100px'],
                    'pageSummaryOptions' => ['colspan' => '5', 'append' => 'Total', 'style' => 'text-align:right'],
                ],
                [
                    'attribute' => 'groupsp2d.nama',
                    'label' => 'Kel. SP2D'
                ],
                'no_sp2d',
                'jenis_spm',
                'uraian',
                [
                    'attribute' => 'jumlah',
                    'format'=>['decimal',0],
                    'contentOptions'=>['class' => 'text-right'],
                    'pageSummary' => true,
                    'pageSummaryFunc' => GridView::F_SUM
                ],

                // ['class' => 'yii\grid\ActionColumn'],
                ['class' => 'yii\grid\ActionColumn',
                    'contentOptions' => ['style' => 'width: 13%'],
                    'template' => '{update} {delete}',
                    'buttons' => [
                        'delete' => function ($url, $model) {
                            return 
                            Html::a('<span class="glyphicon glyphicon-trash"></span> Hapus', ['deptsp2d/delete', 'id' => $model->id], [
                                'class'=>'btn btn-xs btn-danger custom_button',
                                'data' => ['confirm' => Yii::t('app', 'Apakah Anda yakin akan menghapus data ini?'),'method' => 'post']]); 
                        },
                        'update' => function ($url, $model, $session) {
                            return 
                            // Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['deptsp2d/update', 'id' => $model->id]); 
                            Html::button('<span class="glyphicon glyphicon-pencil"></span> Ubah', ['value' => Url::to(['deptsp2d/update', 'id'=>$model->id]), 'class' => 'showModalButton btn btn-xs btn-warning custom_button']);
                        },
                    ]
                ],
            ],
        ]); ?>
    </div>
    <?php Pjax::end() ?>
</div>

<?php 
    Modal::begin([
            // 'header'=>'<h4>Login User</h4>',
            'id'=>'modal',
            'size'=>'modal-md',
            'clientOptions' => ['backdrop' => 'static', 'keyboard' => FALSE],
            // 'footer' => ''
        ]);
    echo "<div id='modalContent'></div>";
    Modal::end();
?>
