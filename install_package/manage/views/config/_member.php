<?php
/**
 * @var $config
 * @var $scope
 */

use common\helpers\ArrayHelper;
use yii\helpers\Html;

$config = ArrayHelper::index($config,'name');
?>

<?php $this->beginBlock('layout'); ?>
    <div class="form-group">
        <label class="col-sm-4 control-label">页面布局</label>
        <div class="col-sm-17">
            <div class="row">
                <div class="col-sm-12">
                    <div class="input-group">
                        <span class="input-group-addon">用户模块</span>
                        <?=Html::activeTextInput($config['layout'],'['.$config['layout']->id.']value',['class'=>'form-control'])?>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="input-group">
                        <span class="input-group-addon">通行证</span>
                        <?=Html::activeTextInput($config['layoutPassport'],'['.$config['layoutPassport']->id.']value',['class'=>'form-control'])?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('relationContent'); ?>
    <div class="form-group">
        <label class="col-sm-4 control-label"><?=$config['relationContent']->title?></label>
        <div class="col-sm-17 js-relation-content" data-type="relation">
            <?=Html::activeHiddenInput($config['relationContent'],'['.$config['relationContent']->id.']value',['class'=>'form-control'])?>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>名称</th>
                    <th>标识</th>
                    <th>模型</th>
                    <th>模板</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
            <button type="button" class="btn btn-default">+ 添加</button>
        </div>
    </div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('publishContent'); ?>
    <!--<div class="form-group">
        <label class="col-sm-4 control-label"><?=$config['publishContent']->title?></label>
        <div class="col-sm-17 js-relation-content" data-type="publish">
            <?=Html::activeHiddenInput($config['publishContent'],'['.$config['publishContent']->id.']value',['class'=>'form-control js-relation-content'])?>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>名称</th>
                    <th>标识</th>
                    <th>模型</th>
                    <th>模板</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
            <button type="button"  class="btn btn-default">+ 添加</button>
        </div>
    </div>-->
<?php $this->endBlock(); ?>

<?php $this->beginBlock('endBlock'); ?>
    <script type="text/html" id="relation-tpl">
        <% for(var i in data){%>
        <tr>
            <td>
                <input class="form-control js-r-n" data-name="title" value="<%=data[i].title%>">
            </td>
            <td>
                <input class="form-control js-r-s" data-name="slug" onKeyUp="value=value.replace(/[\W]/g,'')" placeholder="只能填写英文字符" value="<%=data[i].slug%>">
            </td>
            <td>
                <select class="form-control js-r-m" data-name="model_id">
                    <option value="">请选择</option>
                    <% for(var m in modelList){
                    if(isPublish && modelList[m].type == '1') continue;
                    %>
                    <option <%=modelList[m].id == data[i].model_id?'selected':''%> value="<%=modelList[m].id%>"><%=modelList[m].title%></option>
                    <% }%>
                </select>
            </td>
            <td>
                <input class="form-control js-r-n" data-name="template" value="<%=data[i].template%>" placeholder="为空则与标识一致">
            </td>
            <td><a class="btn btn-sm btn-default js-delete" href="javascript:;">删除</a></td>
        </tr>
        <% }%>
    </script>
    <script>
        $(function () {
            var mList = <?php
                $mList = \common\entity\models\PrototypeModelModel::find()->select(['id','title','type'])->asArray()->all();
                echo empty($mList)?'[]':json_encode($mList)?>;

            $('.js-relation-content').each(function (i,n) {
                var $this = $(this),
                    $body = $this.find('tbody'),
                    $input = $this.find('input:hidden'),
                    _val = getVal($input);

                $body.html(template('relation-tpl',{data:_val,'isPublish':$this.data('type') === 'publish',modelList:mList}));


                $this.find('button').click(function () {
                    var _v = {title:'',slug:'',model_id:'',template:''};
                    setVal($input,_v);
                    $body.append(template('relation-tpl',{data:[_v],'isPublish':$this.data('type') === 'publish',modelList:mList}));
                });
            });

            $('body').on('change','.js-r-n',config).on('change','.js-r-s',config).on('change','.js-r-m',config)
                .on('click','.js-delete',function () {
                var $parent = $(this).parents('tr');
                setVal($parent.parents('table').prev('input:hidden'),false,$parent.index());
                $parent.remove();
            });
            function config() {
                var $parent = $(this).parents('tr'),
                    _data = {};
                $parent.find('input,select').each(function (i,n) {
                    var $this = $(n);
                    _data[$this.data('name')] = $this.val();
                });
                setVal($parent.parents('table').prev('input:hidden'),_data,$parent.index());
            }

            function getVal($input) {
                var _val = $input.val();
                return  _val?$.parseJSON(_val):[];
            }

            function setVal($input,value,index) {
                var _val = getVal($input);
                if(typeof index !== 'undefined'){
                    if(value){
                        _val[index] = value;
                    }else{
                        _val.splice(index,1);
                    }
                }else{
                    _val.push(value);
                }

                $input.val(_val.length>0?JSON.stringify(_val):'');
            }
        });
    </script>
<?php $this->endBlock(); ?>