<?php
/**
 * @var $model
 */
$this->title = '用户详情';

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
    <?=generateHtml($model,'username')?>
    <?=generateHtml($profile,'nickname')?>
    <?=generateHtml($model,'cellphone',$model->cellphone_code.' - '.$model->cellphone)?>
    <?=generateHtml($model,'email')?>
    <?=generateHtml($model,'create_time')?>
    <?=generateHtml($model,'is_enable',$model->is_enable?'<span class="label label-success">启用</span>':'<span class="label label-info">禁用</span>')?>

    <?=generateHtml($profile,'avatar',$profile->avatar?\common\helpers\HtmlHelper::getImgHtml($profile->avatar,['height'=>110]):'-')?>
    <?php
    if($profile->gender == 'male'){
        echo generateHtml($profile,'gender','男');
    }elseif ($profile->gender == 'female'){
        echo generateHtml($profile,'gender','女');
    }else{
        echo generateHtml($profile,'gender','保密');
    }
    ?>
    <?=generateHtml($profile,'birthday')?>
    <?=generateHtml($profile,'blood')?>
    <tr>
        <th>地址</th>
        <td><?=$profile->country?:'--'?>，<?=$profile->province?:'--'?>（省|直辖市），<?=$profile->city?:'--'?>（市），<?=$profile->area?:'--'?>（县|区）<br><?=$profile->street?:'--'?></td>
    </tr>
    <?=generateHtml($profile,'signature')?>
    </tbody>
</table>