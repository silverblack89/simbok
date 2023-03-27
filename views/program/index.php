<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\web\Session;

$session = Yii::$app->session;
/* @var $this yii\web\View */
/* @var $searchModel app\models\ProgramSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Data Program';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="program-index">

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

            'nama_program',

            [
                'attribute' => 'aktif',
                'value' => function ($model) {
                    return $model->aktif ? 'Ya' : 'Tidak';
                },
            ],

            [
                'class' => 'yii\grid\CheckboxColumn',
                'header' => false,
                'checkboxOptions' => function($model) {
                    return ['checked' => $model->akses == 1 ? true : false, 'class' => 'checkbox-row', 'id' => 'akses'];
                }
            ],

            // ['class' => 'yii\grid\ActionColumn'],
            ['class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['style' => 'width: 8%'],
                'template' => '{custom} {view} {update} {delete}',
                'buttons' => [
                    'custom' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-list"></span>', ['service/index', 'id'=>$model->id]);
                    },
                ]
            ],
        ],
    ]); ?>
    <?php Pjax::end() ?>

</div>

<?php
$this->registerJs('
$(".checkbox-row").on("click", function (e) {
    baseUrl = window.origin;
    createCookie("id", $(this).val(), "1");
    createCookie("checked", $(this).prop("checked"), "1"); 

    // alert($(this).prop("checked"));

    var link = baseUrl+"'.Url::to(['get-access']).'";
    
    $.get(link, function(data) {
        alert(data)
    });

    // alert(link);

    // Function to create the cookie 
    function createCookie(name, value, days) { 
        var expires; 
        
        if (days) { 
            var date = new Date(); 
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000)); 
            expires = "; expires=" + date.toGMTString(); 
        } 
        else { 
            expires = ""; 
        } 
        
        document.cookie = escape(name) + "=" +  
            escape(value) + expires + "; path=/"; 
    } 
});
');
?>
