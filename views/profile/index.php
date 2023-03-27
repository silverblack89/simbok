<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Profiles';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="profile-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Profile', ['create'], ['class' => 'btn btn-success']) ?>
    </p>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'nama',
            'alamat',
            'kota_kab',
            'provinsi',
            //'telepon',
            //'kepala',
            //'jabatan_kepala',
            //'nip_kepala',
            //'sekretaris',
            //'jabatan_sekretaris',
            //'nip_sekretaris',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
