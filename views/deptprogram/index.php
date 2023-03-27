<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\web\Session;
use yii\widgets\Pjax;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $searchModel app\models\DeptprogramSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Data Program';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="deptprogram-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <div class="row">
            <div class="col-md-10">
                <?= Html::a('Tambah Program', ['create'], ['class' => 'btn btn-success']) ?>
            </div>
            <div class="col-md-2">
                <?= Html::dropDownList('tahun', null, [ date('Y')-1 => date('Y')-1, date('Y') => date('Y'), date('Y')+1 => date('Y')+1, ] ,
                [
                    // 'prompt'=>'Pilih Periode',
                    'options'=>[$session['programYear']=>['Selected'=>true]],
                    'style' => 'margin-top:5px !important;', 
                    'onchange'=>'
                        $.pjax.reload({
                            url: "'.Url::to(['index']).'?tahun="+$(this).val(),
                            container: "#pjax-gridview",
                            timeout: 1000,
                        });',
                    'class'=>'form-control pull-right'])
                ?>
            </div>
        </div>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php Pjax::begin(['id' => 'pjax-gridview']) ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'kode_rekening',
                'label' => 'Kode Rekening'
            ],

            [
                'attribute' => 'bok.keterangan',
                'label' => 'BOK'
            ],

            'nama_program',

            // 'pagu',

            [
                'attribute' => 'deptgroupsp2d1.nama',
                'label' => 'SP2D 1'
            ],

            [
                'attribute' => 'deptgroupsp2d2.nama',
                'label' => 'SP2D 2'
            ],

            [
                'attribute' => 'aktif',
                'value' => function ($model) {
                    return $model->aktif ? 'Ya' : 'Tidak';
                },
            ],

            // ['class' => 'yii\grid\ActionColumn'],
            ['class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['style' => 'width: 8%'],
                'template' => '{custom} {view} {update} {delete}',
                'buttons' => [
                    'custom' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-list"></span>', ['deptactivity/index', 'id'=>$model->id]);
                    },
                ]
            ],
        ],
    ]); ?>
    <?php Pjax::end() ?>

</div>
