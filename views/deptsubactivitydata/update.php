<?php

use yii\helpers\Html;
use yii\web\session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Deptsubactivitydata */

$this->title = 'Ubah Bentuk Kegiatan';
$this->params['breadcrumbs'][] = ['label' => $session['deptPeriodValue'], 'url' => ['deptperiod/create']];
$this->params['breadcrumbs'][] = ['label' => 'Data Program', 'url' => ['deptprogram/list']];
$this->params['breadcrumbs'][] = ['label' => 'Data Kegiatan', 'url' => ['deptactivity/list', 'id' => $session['deptProgramId']]];
$this->params['breadcrumbs'][] = ['label' => 'Data SubKegiatan', 'url' => ['deptsubactivity/list', 'id' => $session['deptActivityId']]];
$this->params['breadcrumbs'][] = ['label' => 'Bentuk Kegiatan', 'url' => ['deptsubactivitydata/list', 'id' => $session['deptSubActivityId']]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="deptsubactivitydata-update">

    <?php if($modul == 'new'){ ?>
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->
        <?php
        // echo Progress::widget([
        //     'bars' => [
        //         ['percent' => $session['prosentase'], 'label' => $session['prosentase'].'% dari Pagu BOK (RP. ' .number_format($session['pagu'], 0, ',', '.'). ')', 'options' => ['class' => $session['barColor']]],
        //     ],
        //     'options' => ['class' => $session['barStatus']]
        // ]);
    ?>
    <?php }else{ 
        if($modul=='program'){?> 
            <h1><?= Html::encode('Rinci Menu Kegiatan') ?></h1>
        <?php }elseif ($modul=='activity'){ ?>
            <h1><?= Html::encode('Komponen') ?></h1>
        <?php }elseif ($modul=='subactivity'){ ?>
            <h1><?= Html::encode('Kegiatan') ?></h1>
        <?php }else{ ?>
            <h1><?= Html::encode('Lokasi Tujuan Pemindahan Data Kegiatan') ?></h1>
        <?php } ?>
    <?php } ?>

    <?= $this->render('_form', [
        'model' => $model,
        'model2' => $model2,
        'modul' => $modul,
        'dataProvider' => $dataProvider,
        'title' => $this->title
    ]) ?>

</div>
