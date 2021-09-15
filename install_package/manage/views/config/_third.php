<?php
/**
 * @var $config
 * @var $scope
 */

use common\helpers\ArrayHelper;
use yii\helpers\Html;

$config = ArrayHelper::index($config,'name');
?>

<?php $this->beginBlock('setting'); ?>
    <div class="form-group">
        <label class="col-sm-4 control-label"><?=$config['setting']->title?></label>
        <div class="col-sm-17 js-relation-content" data-type="relation">
            <?=Html::activeHiddenInput($config['setting'],'['.$config['setting']->id.']value',['class'=>'form-control'])?>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>客户端</th>
                    <th>clientId</th>
                    <th>clientSecret</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
            <button type="button" class="btn btn-default">+ 添加</button>
            <div class="hint-block" style="margin-top: 15px;">
                第三方申请地址：
                <a href="https://connect.qq.com/intro/login/" target="_blank">QQ</a>、
                <a href="https://open.weixin.qq.com/cgi-bin/frame?t=home/web_tmpl&lang=zh_CN" target="_blank">微信</a>、
                <a href="http://open.weibo.com/authentication" target="_blank">微博</a>
                <br>
                回调地址请：<br>
                QQ - <?=Yii::$app->getRequest()->getHostInfo()?>/u/passport/third-auth-qq.html
                <br>
                微信 - <?=Yii::$app->getRequest()->getHostInfo()?>/u/passport/third-auth-wechat.html
                <br>
                微博 - <?=Yii::$app->getRequest()->getHostInfo()?>/u/passport/third-auth-weibo.html
            </div>
        </div>
    </div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('thirdJumpLink'); ?>
<div class="form-group">
    <label class="col-sm-4 control-label"><?=$config['thirdJumpLink']->title?></label>
    <div class="col-sm-17" id="js-callback">
		<?=Html::activeHiddenInput($config['thirdJumpLink'],'['.$config['thirdJumpLink']->id.']value',['class'=>'form-control'])?>
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>授权成功</th>
                <th>授权失败</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <input class="form-control" name="success">
                </td>
                <td>
                    <input class="form-control" name="fail">
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('endBlock'); ?>
<script type="text/html" id="relation-tpl">
    <% for(var i in data){%>
    <tr>
        <td>
            <select class="form-control js-r-m" data-name="client">
                <option value="">请选择</option>
                <% for(var m in clientList){ %>
                <option <%=clientList[m].name == data[i].client?'selected':''%> value="<%=clientList[m].name%>"><%=clientList[m].title%></option>
                <% }%>
            </select>
        </td>
        <td>
            <input class="form-control js-r-s" data-name="clientId" value="<%=data[i].clientId%>">
        </td>
        <td>
            <input class="form-control js-r-n" data-name="clientSecret" value="<%=data[i].clientSecret%>">
        </td>
        <td><a class="btn btn-sm btn-default js-delete" href="javascript:;">删除</a></td>
    </tr>
    <% }%>
</script>

<script>
    $(function () {
        var clientList = [
            {"title":"微博","name":"weibo"},
            {"title":"QQ","name":"qq"},
            {"title":"微信","name":"wechat"}
            /*{"title":"百度","name":"baidu"},
            {"title":"淘宝","name":"taobao"},
            {"title":"Facebook","name":"facebook"},
            {"title":"Twitter","name":"twitter"},
            {"title":"LinkedIn","name":"linkedIn"},
            {"title":"谷歌","name":"google"},
            {"title":"GitHub","name":"gitHub"},
            {"title":"微软Live","name":"live"},*/
        ];

        $('.js-relation-content').each(function (i,n) {
            var $this = $(this),
                $body = $this.find('tbody'),
                $input = $this.find('input:hidden'),
                _val = getVal($input);

            $body.html(template('relation-tpl',{data:_val,clientList:clientList}));


            $this.find('button').click(function () {
                var _v = {client:'',title:'',clientId:'',clientSecret:''};
                setVal($input,_v);
                $body.append(template('relation-tpl',{data:[_v],clientList:clientList}));
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
                _data = {'title':''};
            $parent.find('input,select').each(function (i,n) {
                var $this = $(n),
                    name = $this.data('name');
                _data[name] = $this.val();
                if(i===0 && name === 'client'){
                    $.each(clientList,function (k,v) {
                        if(v.name === _data[name]){
                            _data['title'] = v.title;
                            return false;
                        }
                    });
                }

            });
            setVal($parent.parents('table').prev('input:hidden'),_data,$parent.index());
        }

        function getVal($input,isJson) {
            var _val = $input.val();
            return  _val?$.parseJSON(_val):(isJson?{}:[]);
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


        // 第三方授权回调
        var $callbackInput = $('#js-callback input:hidden'),
            callbackInputVal = $callbackInput.val();
        if(callbackInputVal){
            callbackInputVal = $.parseJSON(callbackInputVal);
            for (var i in callbackInputVal){
                $('#js-callback table input[name="'+i+'"]').val(callbackInputVal[i]);
            }
        }

        $('#js-callback table input').change(function () {
            var _val = getVal($callbackInput,true);
            _val[$(this).attr('name')] = $(this).val();

            $callbackInput.val(JSON.stringify(_val));
        });

    });
</script>
<?php $this->endBlock(); ?>

