<?php
/**
 * @var $model
 */

use common\helpers\HtmlHelper;
use yii\helpers\Html;

$this->title = '评论详情';

/**
 * 生成html
 * @param $model
 * @param $fieldName
 * @param $value
 * @return string
 */
function generateHtml($model,$fieldName,$value = null){
	return
		'<tr><th width="120">'.$model->getAttributeLabel($fieldName).'</th><td>'.(($value===null?$model->$fieldName:$value)?:'--').'</td></tr>';
};

$profile = $model->userProfile;
?>
<table class="table table-bordered">
	<tbody>
    <tr>
        <th width="120">
            用户
        </th>
        <td>
            <div class="media">
                <div class="media-left">
                    <?=HtmlHelper::getImgHtml($profile->avatar?:'/manage/web/images/avatar.png',['height'=>40])?>
                </div>
                <div class="media-body">
                    <h4 class="media-heading"><?=$profile->nickname?></h4>
                    <p>
                        <?php if($profile->gender == 'male'){
	                        echo '男';
                        }elseif ($profile->gender == 'female'){
	                        echo '女';
                        }else{
	                        echo '保密';
                        }?>
                    </p>
                </div>
            </div>
        </td>
    </tr>
    <?=generateHtml($model,'content')?>
    <?php if(!empty($model->atlas)){?>
    <tr>
        <th width="120">
            图集
        </th>
        <td>
            <ul class="list-img">
            <?php
                foreach (HtmlHelper::fileDataHandle($model->atlas) as $item){
                    echo '<li style="width: auto"><a href="'.HtmlHelper::getFileItem($item).'" target="_blank">'.HtmlHelper::getImgHtml($item,['height'=>90]).'</a></li>';
                }
            ?>
            </ul>
        </td>
    </tr>
    <?php } ?>
    <tr>
        <th width="120">
            评论对象
        </th>
        <td>
	        <?php
	        if(empty($commentObject)){echo '--';}else{
		        $currCategory = $categoryList[$commentObject->category_id];
		        echo '<p>【'.$currCategory['title'].'】 ';
		        $currCategory['expand'] = json_decode($currCategory['expand']);
		        if($currCategory['expand']->enable_detail){
			        $url = empty($currCategory['slug'])?'/category_'.$currCategory['id']:'/'.$currCategory['slug'];
			        if(!$this->context->siteInfo->is_default) $url = '/'.$this->context->siteInfo->slug.$url;
			        $preview = '';
			        if($commentObject->status != 1 || ($commentObject->status == 1 && $commentObject->is_login)){
				        $preview = '?preview-token='.md5(\common\helpers\SecurityHelper::encrypt($commentObject->id,date('dYm')));
			        }
			        echo Html::a($commentObject->title, $url.'/'.$commentObject->id.$this->context->config['site']['urlSuffix'].$preview,['target'=>'_blank']);
		        }else{
			        echo '<span class="text-muted">--</span>';
		        }
		        echo '</p>';
	        }
	        ?>
        </td>
    </tr>
	<?=generateHtml($model,'is_enable',$model->is_enable?'<span class="label label-info">已通过</span>':'<span class="label label-warning">禁用</span>')?>
	<?=generateHtml($model,'create_time',date('Y-m-d H:i',$model->create_time))?>
	</tbody>
</table>