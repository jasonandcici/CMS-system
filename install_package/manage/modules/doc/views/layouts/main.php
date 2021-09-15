<?php
/**
 * @var $content
 */
use yii\helpers\Html;
use yii\web\View;

$this->registerLinkTag(['rel' => 'shortcut icon','href'=>'/favicon.ico']);
$this->registerLinkTag(['rel' => 'bookmark','href'=>'/favicon.ico']);

\yii\bootstrap\BootstrapAsset::register($this);
\yii\bootstrap\BootstrapPluginAsset::register($this);
//$this->registerJsFile('@web/js/dookayui.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);

$hostInfo = Yii::$app->getRequest()->getHostInfo();
$this->title = '“'.$this->context->config['site']['site_name'].'”站点接口文档';

$navLeft = array_key_exists('navLeft',$this->params)?$this->params['navLeft']:[];
?>
<?php $this->beginPage() ?>
	<!DOCTYPE html>
	<html lang="<?= Yii::$app->language ?>">
	<head>
		<meta charset="<?= Yii::$app->charset ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta http-equiv="x-ua-compatible" content="ie=edge">
		<meta name="renderer" content="webkit">
		<?= Html::csrfMetaTags() ?>
		<title><?= Html::encode($this->title) ?></title>
		<?php $this->head() ?>
        <style>
            .navbar{border-radius: 0;}
            .m-0{margin: 0!important;}
            .mb-0{margin-bottom: 0!important;}
            .mb-2{margin-bottom: 20px!important;}
            .mr-2{margin-right: 20px;}
            .mt-1{margin-top: 10px;}
            .text-danger{color: #ed4b48;}
            .text-warning{color: #ffb400;}
            .text-success{color: #26b47f;}
            .text-primary{color: #097bed;}
            .navbar-inverse .navbar-brand{color: #ffef02;}
            .api-list .panel-heading{position: relative;}
            .api-list .get-content{position: absolute;display: block;left: 0;top: 0;right: 0;bottom: 0;text-align: center;}
            .api-list .get-content span{text-align: center;line-height: 40px;}
            .api-list .get-content:focus{text-decoration: none;}
            .nav-aside{top: 0;width: 155px;}
            @media (min-width: 992px){.nav-aside{width: 130px;}}
            .nav-aside .nav > li > a{padding: 5px 15px;}
            .api-header{padding:10px 15px;background-color: #eee;border-radius: 3px;}
            .api-header h4{font-size: 14px;margin: 0;}
            .api-group{border: none;box-shadow: none;}
            .api-group>.panel-heading{border: none;background: transparent;padding-left: 0;padding-right: 0;}
            .api-group>.panel-body{border: none;background: transparent;padding: 0;}
        </style>
	</head>
	<body data-spy="scroll" data-target="#nav-aside">
	<?php $this->beginBody() ?>
    <nav class="navbar navbar-inverse" id="navbar">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="/"></a>
            </div>
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <form class="navbar-form navbar-right">
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="关键词搜索">
                    </div>
                </form>
            </div>
        </div>
    </nav>
    <div class="container">
        <h1 class="mb-2"><?=$this->title?><small>（<?=$hostInfo?>）</small></h1>
        <p>本系统Api几乎支持以下通用特性：<button class="btn btn-primary btn-xs" type="button" data-toggle="collapse" data-target="#common-rules">
                点击查看
            </button></p>
        <div class="collapse" id="common-rules">
            <p>
                <strong>1、条件筛选规则</strong><br>
                通过<code>searches</code>参数可以进行条件筛选，目前支持in筛选和模糊筛选,例如
            </p>
            <ul>
                <li>In筛选：<code>searches[id]=5,6,7</code>，查询id为“4、6、7”的三条数据；</li>
                <li>模糊筛选：<code>searches[title]=中国</code>，查询title包含“中国”的数据；</li>
            </ul>
            <p>例如：<code>/api/nodes.html?sid=1989&cid=196&searches[title]=中国&searches[id]=5,6,7</code><br>注意此特性只对<u>列表类接口</u>有效。<br>
            其中<code>sid</code>表示站点Id，<code>cid</code>表示栏目Id，分别从“管理后台》内容设计》站点管理或栏目管理”中获取。</p>

            <p>
                <strong>2、数据排序</strong><br>
                通过<code>order</code>参数可以让查询的数据按字段排序，排序的格式“order[字段名]=asc或desc”。
            </p>
            <p>例如：<code>/api/nodes.html?sid=1989&cid=196&order[category_id]=asc&order[sort]=desc</code>按照栏目id正序，其次按照sort字段倒序。</p>

            <p>
                <strong>3、数据关联</strong><br>
                通过<code>expand</code>参数可以查询数据时返回当前数据的关联数据。
            </p>
            <p>例如：<code>/api/nodes.html?sid=1989&cid=196&expand=modelInfo,categoryInfo</code>，查询的数据包含模型和栏目的数据。</p>

            <p>
                <strong>4、字段选择</strong><br>
                通过<code>fields</code>参数可以在查询数据时指定默认包含到展现数组的字段集合。
            </p>
            <p>例如：<code>/api/nodes.html?sid=1989&cid=196&fields=id,site_id,model_id,category_id,title</code><br>注意：<code>site_id,model_id,category_id</code>这几个字段必须包含，否则可能导致判断用户关联功能不正常。如果指定了<code>expand</code>字段，那么指定关联关系的字段也必须包含，不能省略。</p>

            <p>
                <strong>5、用户关联</strong><br>
                通过<code>access-token和user-relations</code>参数可以判断用户是否关联当前返回的数据，在返回的数据中会新增<code>_userRelations</code>。
            </p>
            <ul>
                <li><code>access-token</code>用户授权认证码，用户登录后可获得。</li>
                <li><code>user-relations</code>用户关联关系，多个用英文逗号分隔，“关联关系”值从“管理后台》用户管理》用户管理》用户配置》内容关联配置”中“标识”获取。</li>
            </ul>
            <p>例如：<code>/api/nodes.html?sid=1989&cid=196&access-token={授权认证码}&user-relations=collect,like</code>，表示查询用户是否“收藏”和“点赞”了当前数据。<br>注意分页只对<u><code>/api/nodes.html</code>接口</u>返回的数据有效。</p>
            <p>
                <strong>6、分页</strong><br>
                通过<code>fields</code>参数可以在查询数据时指定默认包含到展现数组的字段集合。
            </p>
            <ul>
                <li><code>per-page</code>分页大小，例如：per-page=10 表示每页显示10条数据</li>
                <li><code>page</code>数据大小，例如：page=1 表示当前第一页</li>
            </ul>
            <p>注意分页只对<u>列表类接口</u>有效。</p>
        </div>
        <p>如果使用了SMS接口，服务端需要启用守护进程。如果使用了用户相关接口，服务端需要启用定时器。
            <button class="btn btn-primary btn-xs" type="button" data-toggle="collapse" data-target="#server-config">
                点击查看配置
            </button></p>
        <div class="collapse" id="server-config">
        <pre>// 守护进程配置
[program:YourProject-queue]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php /data/www/YourProjectFolder/yii queue/listen --verbose=1 --color=0
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/data/www/YourProjectFolder/home/runtime/logs/yii-queue-worker.log

// 定时器配置：
*/1 * * * * php /data/www/YourProjectFolder/yii timer/clear-auth-token</pre></div>
        <div class="row" id="content-main">
            <div class="col-md-10">
	            <?= $content ?>
            </div>
            <div class="col-md-2 hidden-sm">
                <div class="nav-aside" id="nav-aside">
                    <ul class="nav nav-pills nav-stacked" role="tablist">
                    <?php foreach ($navLeft as $key=>$value){?>
                        <li><a href="#mao-<?=$key?>"><?=Yii::t('doc',$key)?></a></li>
                    <?php } ?>
                        <li><a href="#navbar" style="background: transparent;color: #666;">返回顶部</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php $this->endBody() ?>
	<?php if (isset($this->blocks['endBody'])): ?>
		<?= $this->blocks['endBody'] ?>
	<?php endif; ?>
    <script>
        $(function () {
            $('#nav-aside').affix({
                offset: {
                    top: function () {
                        return (this.bottom = $('#content-main').offset().top)
                    }
                }
            })
        });
    </script>
	</body>
	</html>
<?php $this->endPage() ?>