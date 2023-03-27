<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\web\Session;
use yii\widgets\Pjax;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $searchModel app\models\DpaSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Sub Menu DPA';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dpa-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <div class="row">
            <div class="col-md-10">
                <?= Html::a('Tambah', ['create'], ['class' => 'btn btn-success']) ?>
            </div>
            <div class="col-md-2">
                <?= Html::dropDownList('tahun', null, [ date('Y')-1 => date('Y')-1, date('Y') => date('Y'), date('Y')+1 => date('Y')+1, ] ,
                [
                    // 'prompt'=>'Pilih Periode',
                    'options'=>[$session['dpaYear']=>['Selected'=>true]],
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

                // 'id',
                // 'dept_sub_activity_id',
            // 'tahun',
            'nama',
            // 'keterangan',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
    <?php Pjax::end() ?>

</div>
