<?php
/**
 * @var $content
 */

use common\helpers\HtmlHelper;

// 注册Meta元标签
$this->registerMetaTag(['name'=>'keywords','content'=>HtmlHelper::encode($this->keywords)]);
$this->registerMetaTag(['name'=>'description','content'=>HtmlHelper::encode($this->description)]);
$this->registerLinkTag(['rel' => 'shortcut icon','href'=>'/favicon.ico']);
$this->registerLinkTag(['rel' => 'bookmark','href'=>'/favicon.ico']);

// 引入公共资源
\home\assets\Html5shivAsset::register($this);
\home\assets\RespondAsset::register($this);

// 重置jquery资源依赖
$this->assetManager->assetMap['jquery.js'] = '@web/js/jquery.min.js';

// 以下注册的资源均为示例
// 自动缩略图插件
$this->registerJsFile('@web/js/holder.min.js');

// 注册资源并依赖jquery
$this->registerJsFile('@web/js/bootstrap.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerCssFile('@web/css/bootstrap.min.css');
$this->registerCssFile('@theme/css/bootstrap-theme.min.css');

// 注册页面js，示例
$this->registerJs('
    // 内容关联操作
    // data-relation-event 属性用于设置操作后回调函数
    // data-relation-event-before 属性用于设置操作前回调函数
    $(".js-relation").each(function(i,n){
        var $this = $(n),
            _txt = $this.data("relationText");
        if(_txt){
            _txt = _txt.split(",");
            $this.hasClass("active")?$this.attr("title",_txt[1]):$this.attr("title",_txt[0]);
        }
        
        $this.click(function(){
            if($this.hasClass("disabled")) return false;
            
            var beforeCallback = $this.data("relationEventBefore");
            if(beforeCallback && typeof eval(beforeCallback) === "function"){
                var r;
                eval("r = " + beforeCallback+"($this)");
                if(r===false) return false;
            }
            
            $.ajax({
                url:$this.attr("href"),
                dataType:"json",
                success:function(res){
                    if(res.status){
                        if(res.action){
                            $this.addClass("active");
                            if(_txt) $this.attr("title",_txt[1]);
                        }else{
                            $this.removeClass("active");
                            if(_txt) $this.attr("title",_txt[0]);
                        }
                        
                        var callback = $this.data("relationEvent");
                        
                        if(callback && typeof eval(callback) === "function"){
                            eval(callback+"($this,res.action)");
                        }
                    }else{
                        alert(res.message);
                    }
                },
                error:function(XMLHttpRequest, textStatus, errorThrown){
                    if(XMLHttpRequest.status === 302){
                        location.href = "'.$this->generateUserUrl('login').'?jumpLink="+location.href;  
                    }else{
                        alert("操作失败！");
                    }      
                },
                complete:function(){
                    $this.removeClass("disabled");
                }
            });
            
            return false;
        });
    });
',\yii\web\View::POS_READY);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0,maximum-scale=1.0, user-scalable=no"/>
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="renderer" content="webkit">
    <?= HtmlHelper::csrfMetaTags() ?>
    <title><?= HtmlHelper::encode($this->title)?></title>
    <?php $this->head(); ?>
    <?=$this->context->config->custom->headerCode?>
</head>
<body<?=array_key_exists('bodyClass',$this->params) && !empty($this->params['bodyClass'])?' class="'.$this->params['bodyClass'].'"':''?>>
<?php $this->beginBody(); ?>

<?=$content?>

<?php
$this->endBody();
if (isset($this->blocks['endBody'])){
    echo $this->blocks['endBody'];
}
?>
<?=$this->context->config->custom->footerCode?>
</body>
</html>
<?php $this->endPage() ?>
