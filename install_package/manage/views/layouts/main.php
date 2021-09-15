<?php
/**
 * @var $content
 *
 * @params $titleClass
 * @params $subTitle
 *
 * @blocks $topButton
 */

$this->beginContent(Yii::$app->layoutPath.'/base.php'); ?>
    <div class="container-fluid">
        <div class="page-header" id="page-header-main">
            <h1<?=isset($this->params['titleClass'])?' class="'.$this->params['titleClass'].'"':''?>><span id="page-header-main-h1"><?=$this->title?></span><?=isset($this->params['subTitle'])?' <small id="page-header-main-small">'.$this->params['subTitle'].'</small>':''?></h1>
            <div class="fun" id="page-header-main-fun">
                <?php if (isset($this->blocks['topButton'])): ?>
                    <?= $this->blocks['topButton'] ?>
                <?php endif; ?>
            </div>
        </div>
        <?= $content ?>
        <!-- 底部开始 -->
        <footer>
            <nav>
                <a href="<?=\yii\helpers\Url::to(['/site/welcome'])?>">后台首页</a>
            </nav>
	        <?=$this->context->config['site']['copyright']?>
        </footer><!-- 底部结束-->
    </div>
<?php $this->endContent(); ?>