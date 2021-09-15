<?php
/**
 * @var $config
 * @var $scope
 */

use common\helpers\ArrayHelper;
use yii\helpers\Html;

$config = ArrayHelper::index($config,'name');

if(Yii::$app->getRequest()->get('test')){
    $data = Yii::$app->getRequest()->get('SystemConfigModel');
    Yii::$app->getMailer()->transport = [
        'class' => 'Swift_SmtpTransport',
        'host' => $data[$config['host']->id]['value'],
        'username' => $data[$config['username']->id]['value'],
        'password' => $data[$config['password']->id]['value'],
        'port' => $data[$config['port']->id]['value'],
        'encryption' => $data[$config['encryption']->id]['value']?:'tls',
    ];

    $mail = Yii::$app->getMailer()->compose();
    $mail->setFrom($data[$config['username']->id]['value']);
    $mail->setTo($data[$config['receive']->id]['value']);
    $mail->setSubject("测试邮件");
    $html = '恭喜您，测试成功！';
    $mail->setHtmlBody($html);
    if($mail->send()){
        $res = json_encode(['status'=>1]);
    }else{
        $res = json_encode(['status'=>0,'message'=>"发送邮件失败。"]);
    }
    exit($res);
}
?>

<?php $this->beginBlock('receive'); ?>
    <div class="form-group">
        <label class="col-sm-4 control-label">收件邮箱</label>
        <div class="col-sm-17">
            <div class="input-group">
                <?=Html::activeTextInput($config['receive'],'['.$config['receive']->id.']value',['class'=>'form-control','id'=>'receive-input'])?>
                <span class="input-group-btn"><button class="btn btn-default" id="js-send" type="button" data-loading-text="发送中...">点击测试</button></span>
            </div>
        </div>
    </div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('endBlock'); ?>
    <script>
        $(function () {
            $('#js-send').on('click', function (e) {
                var $submit = $(this);

                if(!$('#receive-input').val()){
                    commonApp.notify.error('收件箱不能为空。');
                    return false;
                }


                var $form = $submit.parents('form');
                $submit.button('loading');
                $.ajax({
                    url:$form.attr('action')+'&test=1',
                    data:$form.serialize(),
                    type:'get',
                    dataType:'json',
                    success:function (response) {
                        if(response.status){
                            commonApp.dialog.success('发送邮件成功。');
                        }else{
                            commonApp.dialog.error(response.message);
                        }
                    },
                    error:function () {
                        commonApp.dialog.error('发送邮件失败。');
                    },
                    complete:function () {
                        $submit.button('reset');
                    }
                });
            });

	        <?php if($this->context->isSuperAdmin){?>
            $('#j_form').prepend('<p class="hint-block col-md-offset-4 text-danger" style="margin-bottom: 20px;">注意：启用邮件发送时，需要在控制台监听和处理消息队列。开启方法：<a href="https://github.com/yiisoft/yii2-queue/blob/master/docs/guide-zh-CN/driver-file.md" target="_blank">点击查看</a></p>');
	        <?php } ?>
        });
    </script>
<?php $this->endBlock(); ?>