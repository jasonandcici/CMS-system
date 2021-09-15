<ol class="breadcrumb">
    <li><a href="<?= $this->generateCategoryUrl(1) ?>">首页</a></li>
    <?php
    foreach ($this->context->parentCategoryList as $item){
        if(!$item['status']) continue;
        if($this->context->categoryInfo->id == $item['id'] && Yii::$app->controller->action->id != 'detail'){?>
            <li class="active"><?= $item['title'] ?></li>
        <?php }else{?>
            <li>
                <a href="<?= $this->generateCategoryUrl($item) ?>"><?= $item['title'] ?></a>
            </li>
        <?php }?>
    <?php }
    if(Yii::$app->controller->action->id == 'detail'){
        ?>
        <li class="active">详情</li>
    <?php }?>
</ol>
