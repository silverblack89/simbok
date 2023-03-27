<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\User */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Authorization', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="auth-view">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            // 'id',
            'id',
            'nama_group',
            // 'auth_key',
            // 'password_hash',
            // 'password_reset_token',
            // 'email:email',
            // 'status',
            // 'unit_id',
            // 'create_at',
            // 'update_at',
        ],
    ]) ?>

    <table class="table table-striped">
        <tr>
            <th>Modules</th>
            <th>Controllers</th>
            <th>Actions</th>
            <th>Auth</th>
        </tr>
        <?php
        foreach ($routes as $row) {
        ?>
            <tr>
                <td><?= $row['module'] ?></td>
                <td><?= $row['controller'] ?></td>
                <td><?= $row['action'] ?></td>
                <td><?= Html::checkbox('auth[]', $row['auth'], [
                    'class' => 'processAuth',
                    'data-module' => $row['module'],
                    'data-controller' => $row['controller'],
                    'data-action' => $row['action'],
                ]); ?></td>
            </tr>
        <?php
        }
        ?>
    </table>
</div>

<?php
// $this->registerJs('
//     $(".processAuth").on("click", function (e) {
//         baseUrl = window.origin;
//         module = $(this).attr("data-module");
//         controller = $(this).attr("data-controller");
//         action = $(this).attr("data-action");
//         user_id = '.$model->id.';
//         checked = $(this).prop("checked");

//         var link = baseUrl+"'.Url::to(['process-auth']).'&module="+module+
//             "&controller="+controller+
//             "&action="+action+
//             "&user_id="+user_id+
//             "&checked="+checked;
        
//             $.get(link, function(data) {
//                 alert(data)
//             });

//     });
// ');

$this->registerJs('
    $(".processAuth").on("click", function (e) {
        baseUrl = window.origin;
        createCookie("module", $(this).attr("data-module"), "1");
        createCookie("controller", $(this).attr("data-controller"), "1");
        createCookie("action", $(this).attr("data-action"), "1");
        createCookie("group_id", "'.$model->id.'", "1");
        createCookie("checked", $(this).prop("checked"), "1"); 

        // alert(checked);

        var link = baseUrl+"'.Url::to(['process-auth']).'";
        
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
