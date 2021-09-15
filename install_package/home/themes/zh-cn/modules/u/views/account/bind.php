<?php
/**
 * @var $model
 * @var $smsModel
 * @model
 */

use common\helpers\HtmlHelper;
use common\widgets\ActiveForm;

$this->params['active'] = 'reset-password';

$user = Yii::$app->getUser()->getIdentity();

echo $this->render('_nav');
?>

<div class="row" id="js-bind">
    <div class="col-sm-6 col-md-4">
        <div class="thumbnail">
            <div class="caption">
                <h3>手机</h3>
                <p><?=empty($user->cellphone)?'您还没有绑定手机号！':'已绑定<b class="text-danger">'.substr_replace($user->cellphone,'****','3','4').'</b>'?></p>
                <p>
                    <?php if(empty($user->cellphone)){ ?>
                    <button class="btn btn-primary" data-mode="cellphone" data-action="bind" type="button" data-loading-text="操作中...">绑定</button>
                    <?php }else{ ?>
                    <button class="btn btn-primary" data-mode="cellphone" data-action="rebind" type="button" data-loading-text="操作中...">修改</button>
                    <button class="btn btn-default" data-mode="cellphone" data-action="unbind" type="button" data-loading-text="操作中...">解绑</button>
                    <?php }?>
                </p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4">
        <div class="thumbnail">
            <div class="caption">
                <h3>邮箱</h3>
                <p><?=empty($user->email)?'您还没有绑定邮箱！':'已绑定<b class="text-danger">'.substr_replace($user->email,'****','3','4').'</b>'?></p>
                <p>
                    <?php if(empty($user->email)){ ?>
                        <button class="btn btn-primary" data-mode="email" data-action="bind" type="button" data-loading-text="操作中...">绑定</button>
                    <?php }else{ ?>
                        <button class="btn btn-primary" data-mode="email" data-action="rebind" type="button" data-loading-text="操作中...">修改</button>
                        <button class="btn btn-default" data-mode="email" data-action="unbind" type="button" data-loading-text="操作中...">解绑</button>
                    <?php }?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- 修改密码表单 -->
<?php $form = ActiveForm::begin(['id' => 'js-form','validateOnBlur'=>false, 'validateOnSubmit'=>true]); ?>
    <?=HtmlHelper::activeHiddenInput($model,'action')?>
    <?=HtmlHelper::activeHiddenInput($model,'cellphone_code')?>
    <?=HtmlHelper::activeHiddenInput($model,'account')?>
    <?=HtmlHelper::activeHiddenInput($model,'captcha')?>
<?php ActiveForm::end(); ?>

<!-- 发送验证码表单 -->
<?php
    $smsForm = ActiveForm::begin(['id' => 'js-sms-form', 'action'=>$this->generateFormUrl('sms')]);
    echo HtmlHelper::activeHiddenInput($smsModel,'type');
    echo HtmlHelper::activeHiddenInput($smsModel,'cellphone_code');
    echo HtmlHelper::activeHiddenInput($smsModel,'account');
    ActiveForm::end();
?>

<!-- 弹出框 -->
<div class="modal fade" id="operation-modal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="operation-title"></h4>
            </div>
            <!-- 绑定场景 -->
            <div class="modal-body hide" id="screen-bind">
                <div class="form-group">
                    <label id="mode-account-title"></label>
                    <div class="row">
                        <div class="col-sm-3" id="wrapper-cellphone-code">
                            <?=HtmlHelper::dropDownList('cellphone_code',$smsModel->cellphone_code,$this->context->config->sms->cellphoneCode,['class'=>'form-control js-change','data-target'=>'#smsverificationcodeform-cellphone_code,#bindform-cellphone_code'])?>
                        </div>
                        <div class="col-sm-9" id="wrapper-account">
                            <?=HtmlHelper::textInput('account',null,['class'=>'form-control js-change','data-target'=>'#smsverificationcodeform-account,#bindform-account'])?>
                        </div>
                    </div>
                </div>
                <div class="form-group mb-0">
                    <label>验证码</label>
                    <div class="input-group">
                        <?=HtmlHelper::textInput('captcha',null,['class'=>'form-control js-change','placeholder'=>'验证码','data-target'=>'#bindform-captcha'])?>
                        <span class="input-group-btn">
                            <button class="btn btn-default js-send-sms" type="button" data-loading-text="发送中...">获取验证码</button>
                        </span>
                    </div>
                </div>
            </div>
            <!-- 解绑场景 -->
            <div class="modal-body hide" id="screen-unbind">
                <h4>系统将向<strong class="text-danger" id="mode-account"></strong>发送验证码。</h4>
                <div class="form-group mb-0">
                    <div class="input-group">
                        <?=HtmlHelper::textInput('captcha',null,['class'=>'form-control js-change','placeholder'=>'验证码','data-target'=>'#bindform-captcha'])?>
                        <span class="input-group-btn">
                            <button class="btn btn-default js-send-sms" type="button" data-loading-text="发送中...">获取验证码</button>
                        </span>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="js-btn-confirm" data-loading-text="操作中...">确定</button>
            </div>
        </div>
    </div>
</div>

<?php $this->beginBlock('endBody');?>
<script>
    $(function () {
        var $form = $('#js-form'),
            $smsForm = $('#js-sms-form');
        if(!$form.data('action')) $form.data('action',$form.attr('action'));
        if(!$smsForm.data('action')) $smsForm.data('action',$smsForm.attr('action'));

        var $operationModal = $('#operation-modal'),
            $btnConfirm = $('#js-btn-confirm');

        // 操作按钮绑定事件
        $('#js-bind').find('button').click(function () {
            var $this = $(this),
                mode = $this.data('mode'),
                action = $this.data('action');

            changeOperationModalTitle(mode,action);
            changeScreen(action);
            changeMode(mode);

            $btnConfirm.data({'action':action});

            if(action !=='bind'){
                $('#smsverificationcodeform-account,#bindform-account').val(mode === 'cellphone'?'<?=$user->cellphone?>':'<?=$user->email?>');
                if(mode === 'cellphone') $('#smsverificationcodeform-cellphone_code,#bindform-cellphone_code').val('<?=$user->cellphone_code?>');
            }

            $operationModal.modal('show');
        });

        /**
         * 提交表单
         * @param $btn
         * @param $form
         */
        function submit($btn,$form) {
            $btn.button('loading');
            $.post($form.attr('action'),$form.serialize(),function (response) {
                if(typeof response === 'string') response = JSON.parse(response);
                $btn.button('reset');
                if(response.status){
                    alert('操作成功。');
                }else{
                    alert(response.message);
                }
            });
        }

        /**
         *  修改操作名
         * @param mode
         * @param action
         */
        function changeOperationModalTitle(mode,action) {
            var t = '';
            if(action === 'bind'){
                t = '绑定';
            }else if(action === 'unbind'){
                t = '解绑';
            }else{
                t='修改';
            }
            $('#operation-title').text(t+(mode==='email'?'邮箱':'手机号'));
        }

        /**
         * 切换操作场景
         * @param action string bind|unbind|unbind
         */
        function changeScreen(action) {
            if(action === 'bind'){
                $('#screen-bind').removeClass('hide');
                $('#screen-unbind').addClass('hide');
            }else{
                $('#screen-bind').addClass('hide');
                $('#screen-unbind').removeClass('hide');
            }

            $('#bindform-action').val(action === 'bind'?'bind':'unbind');
            $('#smsverificationcodeform-type').val(action === 'bind'?'register':'reset');
        }

        /**
         * 切换操作模式
         * @param mode string email|cellphone
         */
        function changeMode(mode) {
            if(mode === 'email'){
                $('#wrapper-cellphone-code').addClass('hide');
                $('#wrapper-account').attr('class','col-sm-12');
            }else{
                $('#wrapper-cellphone-code').removeClass('hide');
                $('#wrapper-account').attr('class','col-sm-9');
            }
            $('#mode-account-title').text(mode === 'email'?'邮箱':'手机号');
            $('#mode-account').text(mode === 'email'?'<?=substr_replace($user->email,'****','3','4')?>':'<?=substr_replace($user->cellphone,'****','3','4')?>');

            $smsForm.attr('action',$smsForm.data('action')+'?mode='+mode);
            $form.attr('action',$form.data('action')+'?mode='+mode);
        }

        // 同步弹出框表单控件内容
        $('.js-change').change(function () {
            var _val = $(this).val(),
                target = $(this).data('target');
            target = target.split(',');
            $.each(target,function (i,n) {
                $(n).val(_val);
            });
        });

        // 提交表单
        $btnConfirm.click(function () {
            $btnConfirm.button('loading');
            $.post($form.attr('action'),$form.serialize(),function (response) {
                if(typeof response === 'string') response = JSON.parse(response);
                $btnConfirm.button('reset');
                if(response.status){
                    if($btnConfirm.data('action') === 'rebind'){
                        changeScreen('bind');
                        $btnConfirm.removeData('action');

                        var _$t = $('#mode-account-title');
                        _$t.text('新'+_$t.text());
                    }else{
                        alert('操作成功。');
                        history.go(0);
                    }
                }else{
                    alert(response.message);
                }
            });

        });

        // 发送验证码
        $('.js-send-sms').click(function () {
            var $btn = $(this);
            if($btn.hasClass('disabled')) return false;
            $btn.button('loading');
            $.post($smsForm.attr('action'),$smsForm.serialize(),function (response) {
                if(typeof response === 'string') response = JSON.parse(response);
                if(response.status){
                    $btn.addClass('disabled');
                    var second = 60,
                        timer = setInterval(function () {
                            if(second === 0){
                                clearInterval(timer);
                                $btn.removeClass('disabled');
                                $btn.button('reset');
                                return;
                            }
                            second--;
                            $btn.text(second+'s');
                        },1000);
                }else{
                    alert(response.message);
                    $btn.button('reset');
                }
            });
        });

    });
</script>
<?php $this->endBlock();?>
