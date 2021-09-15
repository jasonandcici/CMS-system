<?php
/**
 * @var $model
 * @var $fieldModel
 */

use manage\assets\FormAsset;
use yii\helpers\Html;
use common\widgets\ActiveForm;

$this->registerJsFile('@web/js/pages/field.js',['depends' => [FormAsset::className()]]);
$this->registerJs('
    fieldApp.init();
');

if($model->type === 1){
    unset($fieldModel->filedTypeText['relation_data'],$fieldModel->filedTypeText['relation_category'],$fieldModel->filedTypeText['city_multiple'],$fieldModel->filedTypeText['city']);
}else{
    unset($fieldModel->filedTypeText['captcha']);
}
?>

<?php $this->beginBlock('topButton'); ?>
<?= Html::a(Yii::t('common','Back List'), ['index','model_id'=>$model->id], ['class' => 'btn btn-default j_goback']) ?>
<?php $this->endBlock(); ?>

<!-- 表单开始 -->
<div class="panel panel-default form-data">
    <div class="panel-body">
        <?php $form = ActiveForm::begin([
            'id'=>'j_form',
            'options'=>['class' => 'form-horizontal','data-model'=>$model->name],
            'fieldConfig'=>['template'=>'{label}<div class="col-sm-17">{input}{error}{hint}</div>', 'labelOptions'=>['class'=>'col-sm-4 control-label']]
        ]); ?>
        <?=Html::activeHiddenInput($fieldModel,'is_updated')?>
        <?=Html::activeHiddenInput($fieldModel,'updated_target')?>
        <!-- 表单控件开始 -->
        <?= $form->field($fieldModel, 'title')->textInput() ?>
        <?= $form->field($fieldModel, 'name',['options'=>['class'=>'form-group','id'=>'field-name']])->textInput(['id'=>'field-name-input']) ?>
        <?= $form->field($fieldModel, 'type')->dropDownList($fieldModel->filedTypeText,['prompt'=>'请选择','id'=>'js-change-type'])?>
        <?= $form->field($fieldModel, 'hint')->textInput() ?>
        <?= $form->field($fieldModel, 'placeholder')->textInput() ?>
        <div id="change-type-content"></div>
        <?=Html::activeHiddenInput($fieldModel,'options',['id'=>'options'])?>
        <?=Html::activeHiddenInput($fieldModel,'default_value',['id'=>'default-value'])?>
        <?=Html::activeHiddenInput($fieldModel,'field_decimal_place',['id'=>'field-decimal-place'])?>

        <!-- 其他设置 -->
        <div id="change-type-setting"></div>
        <?=Html::activeHiddenInput($fieldModel,'setting',['id'=>'setting'])?>
        <?=Html::activeHiddenInput($fieldModel,'field_length',['id'=>'field-length'])?>

        <?=$form->field($fieldModel, 'is_show_list')->radioList([1=>'是',0=>'否'],['itemOptions'=>['labelOptions'=>['class'=>'radio-inline']]]); ?>
        <?=$form->field($fieldModel, 'is_search')->radioList([1=>'是',0=>'否'],['itemOptions'=>['labelOptions'=>['class'=>'radio-inline']]]); ?>
        <!-- 验证规则 -->
        <?=$form->field($fieldModel, 'is_required')->radioList([1=>'是',0=>'否'],['itemOptions'=>['labelOptions'=>['class'=>'radio-inline']]]); ?>
        <div id="change-type-verification"></div>
        <?=Html::activeHiddenInput($fieldModel,'custom_verification_rules',['id'=>'verification-rules'])?>
        <!-- 表单控件结束 -->
        <div class="form-data-footer">
            <div class="form-group">
                <div class="col-sm-offset-4 col-sm-14">
                    <?= Html::submitButton(Yii::t('common','Submit'), ['class' => 'btn btn-primary']) ?>
                    <?= Html::a(Yii::t('common','Back List').' <span class="st">&gt;</span>', ['index','model_id'=>$model->id], ['class' => 'btn btn-link j_goback']) ?>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<!---------------------------------------------------------------------------------->
<!-- 选项 -->
<script type="text/html" class="tpl-content" id="tpl-options" data-adaptation="radio,radio_inline,checkbox,checkbox_inline,select,select_multiple" data-target="#options">
    <div class="form-group">
        <label class="col-sm-4 control-label"><b class="text-danger" style='font-family: "宋体";'>*</b> <?=$fieldModel->getAttributeLabel('options')?></label>
        <div class="col-sm-17">
            <textarea class="form-control js-field-content" data-target="#options" placeholder="值=>名称" rows="6"><%=value%></textarea>
            <div class="hint-block">格式：<code>值=>名称=>@，多个请换行（“=>@”表示当前默认选中）。</code></div>
        </div>
    </div>
</script>

<!-- 默认值 -->
<script type="text/html" class="tpl-content" id="tpl-default-value" data-adaptation="text,passport,number,int,textarea,tag" data-target="#default-value">
    <div class="form-group">
        <label class="col-sm-4 control-label"><?=$fieldModel->getAttributeLabel('default_value')?></label>
        <div class="col-sm-17">
            <input type="text" class="form-control js-field-content" data-target="#default-value" value="<%=value%>">
        </div>
    </div>
</script>

<!-- 小数点长度 -->
<script type="text/html" class="tpl-content" id="tpl-field-decimal-place" data-adaptation="number" data-target="#field-decimal-place">
    <div class="form-group">
        <label class="col-sm-4 control-label"><?=$fieldModel->getAttributeLabel('field_decimal_place')?></label>
        <div class="col-sm-17">
            <input type="number" class="form-control js-field-content" data-target="#field-decimal-place" value="<%=value%>">
        </div>
    </div>
</script>

<!---------------------------------------------------------------------------------->
<!-- 是否无符号 -->
<script type="text/html" class="tpl-verification" id="tpl-unsigned" data-adaptation="int,number" data-name="unsigned">
    <div class="form-group">
        <label class="col-sm-4 control-label">是否为正数</label>
        <div class="col-sm-17">
            <label class="checkbox-inline"><input class="js-verification-checkbox" type="checkbox" data-name="unsigned" value="1"<%=unsigned?' checked':''%>> 是否为正数</label>
        </div>
    </div>
</script>

<!-- 长度 -->
<script type="text/html" class="tpl-verification" id="tpl-field-length" data-adaptation="text,passport,textarea" data-name="length">
    <div class="form-group">
        <label class="col-sm-4 control-label"><?=$fieldModel->getAttributeLabel('field_length')?></label>
        <div class="col-sm-17">
            <input type="text" class="form-control js-verification-text" data-name="length"value="<%=length%>">
            <div class="hint-block">格式：<code>最大长度“max”，长度范围“min,max”。例如：255或8,6。</code></div>
        </div>
    </div>
</script>
<!-- 其他验证规则 -->
<script type="text/html" class="tpl-verification" id="tpl-other" data-adaptation="text,int,textarea" data-name="other">
    <div class="form-group">
        <label class="col-sm-4 control-label">其他验证规则</label>
        <div class="col-sm-17">
            <label class="checkbox-inline"><input class="js-verification-checkbox" type="checkbox" data-name="unique" value="1"<%=unique?' checked':''%>> 是否唯一</label>
            <label class="checkbox-inline"><input class="js-verification-checkbox" type="checkbox" data-name="email" value="1"<%=email?' checked':''%>> 电子邮件</label>
            <label class="checkbox-inline"><input class="js-verification-checkbox" type="checkbox" data-name="ip" value="1"<%=ip?' checked':''%>> IP地址</label>
            <label class="checkbox-inline"><input class="js-verification-checkbox" type="checkbox" data-name="url" value="1"<%=url?' checked':''%>> 网址</label>
        </div>
    </div>
</script>

<!-- 比较 -->
<script type="text/html" class="tpl-verification" id="tpl-compare" data-adaptation="number,int" data-name="compare">
    <% if (typeof rules === 'undefined'){ %>
    <div class="form-group">
        <label class="col-sm-4 control-label">比较</label>
        <div class="col-sm-17">
            <div class="checkbox">
                <label><input type="checkbox" id="js-compare-enable" data-name="enable" value="1"<%=enable?' checked':''%>> 比较</label>
            </div>
            <div id="compare-content"></div>
            <button class="btn btn-default" id="js-compare-btn" type="button" style="margin-top: 10px;display: <%=enable?'block':'none'%>;">添加规则</button>
            <p class="help-block" style="margin-top: 5px;">规则为空时，则检查“<code>当前字段</code>”的值是否与“<code>当前字段_repeat</code>”的值相同。</p>
        </div>
    </div>
    <% }else{ %>

    <% for(var l in rules){%>
    <div class="input-group" style="margin-top: 10px;">
        <span class="input-group-addon">操作符</span>
        <select class="form-control js-compare-value" data-name="operator">
            <%
            var options = ['==','===','!=','!==','>','>=','<','<='];
            for(var i=0;i<=options.length;i++){%>
            <option<%=options[i] == rules[l].operator?' selected':''%> value="<%=options[i]%>"><%=options[i]%></option>
            <% } %>
        </select>
        <span class="input-group-addon" style="border-left:none;border-right:none;">对比值</span>
        <input type="text" class="form-control js-compare-value" data-name="compareValue" value="<%=rules[l].compareValue%>">
        <a class="input-group-addon btn-info js-compare-delete" href="javascript:;"><b>x</b> 删除</a>
    </div>
    <% }%>
    <% } %>
</script>

<script type="text/html" class="tpl-verification" id="tpl-match" data-adaptation="text,passport,date,datetime,number,int,captcha,textarea,radio,radio_inline,checkbox,checkbox_inline,select,select_multiple,tag,editor" data-name="match">
<div class="form-group">
    <label class="col-sm-4 control-label">自定义验证规则</label>
    <div class="col-sm-17">
        <textarea class="form-control js-verification-text" data-name="match" placeholder="请填写正则表达式"><%=match%></textarea>
        <div class="hint-block">格式：填写正则表达式，<code>多条验证规则请换行</code>。</div>
    </div>
</div>
</script>
<!---------------------------------------------------------------------------------->
<!-- 其他配置 -->
<script type="text/html" id="tpl-setting-relation_data">
    <div class="form-group">
        <label class="col-sm-4 control-label">关联关系</label>
        <div class="col-sm-17">
            <% for(var i=0;i<<?=$model->type===0?2:1?>;i++){%>
            <label class="radio-inline">
                <input type="radio" name="relationType"<%=(i===relationType?' checked':'')%> value="<%=i%>" data-fun="parseInt"><%=(i===0?'一对一':'一对多')%>
            </label>
            <% }%>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-4 control-label"><b class="text-danger" style='font-family: "宋体";'>*</b> 关联数据模型名</label>
        <div class="col-sm-17">
            <select class="form-control" name="modelName">
                <option value="">请选择</option>
            <% var modelList = <?php
                $mList = [];
                foreach ($modelList as $item){
                    $mList[$item['name']] = $item['title'];
                }
                $mList['user'] = '会员模型';$mList['category'] = '栏目模型';
                echo json_encode($mList);unset($mList);
            ?>; for(var i in modelList){ %>
            <option value="<%=i%>"<% if(modelName == i){ %> selected<% } %>><%=modelList[i]%></option>
            <% } %>
            </select>
            <p class="hint-block">注意：<?=$model->title?>中<code>不能</code>同时出现<code>两个或以上</code>相同<code>模型名</code>的关联字段。</p>
        </div>
    </div>
</script>