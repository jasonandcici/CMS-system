<?php
/**
 * @var $thirdList
 * @var $model
 * @var $smsModel
 * @model
 */

use common\helpers\ArrayHelper;
use common\helpers\HtmlHelper;
use common\widgets\ActiveForm;
use yii\authclient\widgets\AuthChoice;

$this->params['active'] = 'reset-password';

$user = Yii::$app->getUser()->getIdentity();

echo $this->render('_nav');
?>
<?php $authAuthChoice = AuthChoice::begin([
    'baseAuthUrl' => ['/u/passport/third-auth'],
    'options' => ['class'=>'row']
]);

foreach ($authAuthChoice->getClients() as $client)
{?>
    <div class="col-sm-6 col-md-4">
        <div class="thumbnail">
            <div class="caption">
                <h3><?php
                    $thirdList = (array)$this->context->config->third->setting;
                    $thirdTitle = $thirdList[$client->id]->title;
                    echo $thirdTitle;?></h3>
                <?php if(array_key_exists($client->id,$thirdList)){?>
                    <p>您已经绑定了<?=$thirdTitle?>账号。</p>
                    <p>
                        <?=HtmlHelper::a('解绑','javascript:;',['class'=>'btn btn-default js-unbind','data-client'=>$client->id,'data-title'=>$thirdTitle,"data-loading-text"=>"发送中..."]) ?>
                    </p>
                <?php }else{ ?>
                    <p>您还未绑定<?=$thirdTitle?>。</p>
                    <p>
                        <?= $authAuthChoice->clientLink($client,'绑定',['class'=>'btn btn-primary']) ?>
                    </p>
                <?php } ?>
            </div>
        </div>
    </div>

<?php }
AuthChoice::end();
?>
<?php $this->beginBlock('endBody');?>
<script>
    $(function () {
        $('.js-unbind').click(function () {
            var $this = $(this);
            layer.confirm('您确定要解除绑定'+$this.data('title')+'账号吗？',{icon:3},function () {

                var postData = {
                    '<?=Yii::$app->getRequest()->csrfParam?>':'<?=Yii::$app->getRequest()->csrfToken?>',
                    'client_id':$this.data('client')
                };

                $this.button('loading');
                $.post('<?=$this->generateCurrentUrl()?>',postData,function (response) {
                    if(typeof response === 'string') response = JSON.parse(response);
                    if(response.status){
                        layer.msg('操作成功', {icon: 1});
                        setTimeout(function () {
                            history.go(0);
                        },1500);
                    }else{
                        layer.alert(response.message, {icon: 2});
                        $this.button('reset');
                    }
                });
            });
            return false;
        });
    });
</script>
<?php $this->endBlock();?>
