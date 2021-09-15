<?php
/**
 * @block topButton 顶部按钮
 * @var $roleList
 * @var $userAccessButton
 */

use common\helpers\UrlHelper;
use manage\assets\ListAsset;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\web\View;

$this->title = '管理员角色';

ListAsset::register($this);
$this->registerJs("listApp.init();", View::POS_READY);
?>
<?php if($userAccessButton['role-create']) { $this->beginBlock('topButton'); ?>
<?= Html::a('创建新角色', ['role-create'], ['class' => 'btn btn-primary']) ?>
<?php $this->endBlock();} ?>
<!-- 数据列表开始 -->
<div class="panel panel-default list-data">
    <div class="panel-body">
        <div class="table-responsive scroll-bar">
            <table class="table table-hover" id="list_data">
                <thead>
                <tr>
                    <td>角色名称</td>
                    <!--<td>角色标识</td>-->
                    <?php if($userAccessButton['role-auth'] || $userAccessButton['role-update'] || $userAccessButton['role-delete']){?>
                    <td align="center"><?=Yii::t('common','Operation')?></td>
                    <?php } ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach($roleList as $item){?>
                    <tr>
                        <td>
                            <a href="<?=UrlHelper::to(['role-update','name'=>$item->name])?>" class="text-primary"><?=StringHelper::truncate(Html::encode($item->description),16)?></a>
                        </td>
                        <?php if($userAccessButton['role-auth'] || $userAccessButton['role-update'] || $userAccessButton['role-delete']){?>
                        <td class="opt" align="center">
                            <?php if($userAccessButton['role-auth']){?>
                            <?= Html::a('权限管理', ['role-auth','name' => $item->name], ['class' => 'text-primary j_access']) ?>
                            <?php } if($userAccessButton['role-auth'] && ($userAccessButton['role-update'] || $userAccessButton['role-delete'])){?>
                                <span>|</span>
                            <?php } if($userAccessButton['role-update']){ ?>
                            <?= Html::a(Yii::t('common','Modify'), ['role-update','name' => $item->name], ['class' => 'text-primary']) ?>
                            <?php } if($userAccessButton['role-delete']){ if($item->name != 'admin'){?>
                            <?= Html::a(Yii::t('common','Delete'), ['role-delete','name' => $item->name],['class'=>'j_batch','data-action'=>'del']) ?>
                            <?php }else{ echo '<span class="text-muted">删除</span>';}} ?>
                        </td>
                        <?php } ?>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <?=empty($roleList)?'<p class="list-data-default">'.Yii::t('common','No Data Found !').'</p>':''?>
        </div>
    </div>
</div><!-- 数据列表结束 -->

    <?php if($this->context->isSuperAdmin){?>
    <nav class="nav-operation clearfix">
        <div class="tools">
            <a href="<?=UrlHelper::to(['repair'])?>" class="btn btn-xs btn-primary" id="js-auth-repair">权限点自动修复</a>
            <span class="text-muted">用于修复无法打开“权限管理”或无法设置权限等问题</span>
        </div>
    </nav>
    <?php } ?>
<?php $this->beginBlock('endBlock'); ?>
<style>
    .modal-lg{
        width: 100%;
        height: 100%;
        margin: 0;
    }
    .modal-lg .modal-content{
        height: 100%;
        box-shadow: none;
        border:none;
    }
    .modal-lg .modal-body{
        position: absolute;
        bottom: 62px;
        left: 0;
        right: 0;
        top: 52px;
    }
    .modal-lg .modal-footer{
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        text-align: center;
    }
    .modal-lg .bootbox-body,
    .modal-lg iframe{
        height: 100%;
    }
</style>
    <script>
        $(function(){
            $('a.j_access').click(function(){
                var $this = $(this);

                commonApp.dialog.iframe($this.text(),$this.attr('href'),{
                    size:'large',
                    confirm:function(){
                        var $dialog = $(this);
                        window.frames['dialog-iframe'].saveAccess(function(){
                            setTimeout(function () {
                                $dialog.find('button.close').trigger('click');
                            }, 2000);
                        });
                        return false;
                    }
                });

                return false;
            });

            $('#js-auth-repair').click(function () {
                var $this = $(this);
                commonApp.dialog.warning('您确定要执行此操作吗？',{
                    confirm:function () {
                        commonApp.fieldUpdateRequest($this);
                    }
                });
                return false;
            });
        });
    </script>
<?php $this->endBlock(); ?>