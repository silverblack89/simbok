<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use yii\web\Session;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <link rel="shortcut icon" href="<?php echo Yii::$app->request->baseUrl; ?>/favicon.ico" type="image/x-icon" />
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => Yii::$app->name,
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    if(Yii::$app->user->isGuest){
        echo Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-right'],
            'items' => [
                Yii::$app->user->isGuest ? (
                    ['label' => 'Login', 'visible' => false, 'url' => ['/site/login']]
                ) : ( 
                    '<li>'
                    . Html::beginForm(['/site/logout'], 'post')
                    . Html::submitButton(
                        'Logout (' . Yii::$app->user->identity->alias . ')',
                        ['class' => 'btn btn-link logout']
                    )
                    . Html::endForm()
                    . '</li>'
                )
            ],
        ]);
    }else{
        // $session = Yii::$app->session;
        // $session['periodValue'] = date('Y');

        if (Yii::$app->user->identity->username == 'admin'){
            echo Nav::widget([
                'options' => ['class' => 'navbar-nav navbar-right'],
                'items' => [
                    // ['label' => 'Home', 'url' => ['/site/index']],
                    ['label' => 'Data Dasar', 'url' => ['/site/about'], 'items' => [
                        ['label' => 'Profile', 'url' => ['/profile']],
                        ['label' => 'Instansi', 'url' => ['/unit']],
                        ['label' => 'User', 'url' => ['/user']],
                        ['label' => 'Hak Akses', 'url' => ['/auth']],
                        ['label' => 'Program (Puskesmas)', 'url' => ['/program', 'tahun' => date('Y')]],
                        ['label' => 'Program (Dinas)', 'url' => ['/deptprogram', 'tahun' => date('Y')]],
                        ['label' => 'Sub Menu DPA', 'url' => ['/dpa', 'tahun' => date('Y')]],
                        ['label' => 'SP2D (Dinas)', 'url' => ['/deptsp2d', 'id' => '']],
                        ['label' => 'Rekening', 'url' => ['/account']],
                        ['label' => 'Satuan', 'url' => ['/satuan']],
                        ['label' => 'Pagu BOK', 'url' => ['/period/index', 'tahun' => date('Y')]],
                    ]],
                    ['label' => 'Ganti Password', 'url' => ['/user/changepassword']],
                    // ['label' => 'About', 'url' => ['/site/about', 'tag' => '']],
                    // ['label' => 'Contact', 'url' => ['/site/contact']],
                    Yii::$app->user->isGuest ? (
                        ['label' => 'Login', 'visible' => false, 'url' => ['/site/login']]
                    ) : ( 
                        '<li>'
                        . Html::beginForm(['/site/logout'], 'post')
                        . Html::submitButton(
                            'Logout (' . Yii::$app->user->identity->alias . ')',
                            ['class' => 'btn btn-link logout']
                        )
                        . Html::endForm()
                        . '</li>'
                    )
                ],
            ]);
        }else{
            if (Yii::$app->user->identity->unit_id == 'DINKES'){
                echo Nav::widget([
                    'options' => ['class' => 'navbar-nav navbar-right'],
                    'items' => [
                        ['label' => 'Ganti Password', 'url' => ['/user/changepassword']],
                    
                        Yii::$app->user->isGuest ? (
                            ['label' => 'Login', 'visible' => false, 'url' => ['/site/login']]
                        ) : ( 
                            '<li>'
                            . Html::beginForm(['/site/logout'], 'post')
                            . Html::submitButton(
                                'Logout (' . Yii::$app->user->identity->alias . ')',
                                ['class' => 'btn btn-link logout']
                            )
                            . Html::endForm()
                            . '</li>'
                        )
                    ],
                ]);
            }else{
                echo Nav::widget([
                    'options' => ['class' => 'navbar-nav navbar-right'],
                    'items' => [
                        ['label' => 'Info User', 'url' => ['/unit/update', 'id' => Yii::$app->user->identity->unit_id]],
                        ['label' => 'Ganti Password', 'url' => ['/user/changepassword']],
                    
                        Yii::$app->user->isGuest ? (
                            ['label' => 'Login', 'visible' => false, 'url' => ['/site/login']]
                        ) : ( 
                            '<li>'
                            . Html::beginForm(['/site/logout'], 'post')
                            . Html::submitButton(
                                'Logout (' . Yii::$app->user->identity->alias . ')',
                                ['class' => 'btn btn-link logout']
                            )
                            . Html::endForm()
                            . '</li>'
                        )
                    ],
                ]);
            }
        }
    }
    NavBar::end();
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <!-- <?= require_once('alert.php'); ?> -->
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">Copyright &copy; <?= date('Y') ?>. Dinas Kesehatan Sragen.</p>

        <p class="pull-right">Developed by<a href=''> Salmandev</a></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
