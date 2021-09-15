<?php
/**
 * @var $authItemList
 */
use common\helpers\ArrayHelper;
use manage\assets\ZtreeAsset;
use yii\helpers\Html;
use common\widgets\ActiveForm;

$this->title = '权限管理';
$this->params['title'] = $this->title;
ZtreeAsset::register($this);
?>

<div class="container-fluid">
    <?php $form = ActiveForm::begin(['id'=>'j_form','options' => ['class'=>'form-horizontal']]); ?>
    <div class="form-group" style="text-align: center">
        <input type="hidden" name="auth" id="access" value="">
        <ul class="ztree" id="j_tree_access"></ul>
    </div>
    <?php ActiveForm::end();?>
</div>
<!-- 页面js开始 -->
<?php $this->beginBlock('endBlock'); ?>
<script>
    $(function(){
        commonApp.init();


        var authItemList = <?=json_encode($authItemList)?>;

        var newData = [];
        $.each(authItemList,function (i,n) {
            if(/^prototype\/node\//.test(n.auth) && !/^prototype\/node\/page/.test(n.auth)) {
                var _child = getChildes(authItemList, n.pid);
                if(_child.length > 7){
                    return true;
                }
            }
            newData.push(n);
        });


        // 生成tree
        var treeObj = $.fn.zTree.init($("#j_tree_access"),{
                check: {enable: true},
                data: {simpleData: {enable: true, pIdKey:'pid'}
            },
            callback:{
                onClick:function(e,treeId, treeNode){
                    $('#'+treeNode.tId + '_a').prev().trigger('click');
                }
            }
        } , newData);
        ///treeObj.expandAll(true);

        // 检查所有的节点, 如果是半选中状态, 选中它
        $scope = {};
        $scope.treeNodes = [];
        $scope.getTreeNodes = function (treeObj) {
            var allNodes = treeObj.getNodes();
            for (var i=0;i<allNodes.length; i++) {
                $scope.treeNodes.push(allNodes[i]);
                if (allNodes[i].children instanceof Array){
                    $scope.getNodes(allNodes[i]);
                }
            }
            return $scope.treeNodes;
        };

        $scope.getNodes = function (node) {
            var subNodes = node.children;
            for(var i =0; i < subNodes.length; i++ ){
                $scope.treeNodes.push(subNodes[i]);
                if (subNodes[i].children instanceof Array){
                    $scope.getNodes(subNodes[i]);
                }
            }
        };
        $scope.getTreeNodes(treeObj);

        $.each($scope.treeNodes, function (x, node) {
            if (node.getCheckStatus().half) {
                treeObj.getNodeByTId(node.tId).checked = true;
            }
        })

    });


    function getChildes(data,parentId,parentName) {
        if(typeof parentId === 'undefined') parentId = 0;
        if(typeof parentName === 'undefined') parentName = 'pid';
        var _arr = [];
        $.each(data,function (i,n) {
            if(n.id === 0) return true;
            if(n[parentName] == parentId){
                _arr.push(n);
                _arr = _arr.concat(getChildes(data,n.id,parentName));
            }
        });
        return _arr;
    }

    // 表单提交设置权限
    function saveAccess(callback){
        var treeObj = $.fn.zTree.getZTreeObj("j_tree_access");
        var nodes = treeObj.getCheckedNodes(true);

        // 获取所有内容管理的节点
        var _selected = [];
        for(var i in nodes){
            // 遍历所有节点
            if(nodes[i].auth) _selected.push(nodes[i].auth)

            if (nodes[i].checked === true && nodes[i].auth === false) {
                _selected.push("prototype/node/create?category_id="+nodes[i].id);
                _selected.push("prototype/node/delete?category_id="+nodes[i].id);
                _selected.push("prototype/node/index?category_id="+nodes[i].id);
                _selected.push("prototype/node/move?category_id="+nodes[i].id);
                _selected.push("prototype/node/sort?category_id="+nodes[i].id);
                _selected.push("prototype/node/status?category_id="+nodes[i].id);
                _selected.push("prototype/node/update?category_id="+nodes[i].id);
            }
        }

        if(_selected.length < 1) return;

        _selected = _selected.unique();

        var $form = $('#j_form');
        $('#access').val(_selected.join(','));

        var notifyConf = {
            layout:'center',
            animation: {
                open  : 'animated bounceInDown',
                close : 'animated bounceOutUp'
            }
        };

       $.ajax({
            type: $form.attr('method'),
            url: $form.attr('action'),
            data: $form.serialize(),
            dataType: 'json',
            beforeSend: function (XMLHttpRequest) {
                commonApp.loading('表单提交中，请稍后...');
            },
            complete: function () {
                commonApp.loading(false);
            },
            error:function(XMLHttpRequest, textStatus, errorThrown){
                commonApp.notify.error(errorThrown + ' - ' + textStatus,notifyConf);
            },
            success: function (result) {
                if(result.status === 1){
                    commonApp.notify.success(result.title,notifyConf);
                    if(callback && typeof callback === 'function') callback();
                }else{
                    commonApp.notify.error(result.title,notifyConf);
                }
            }
        });
    }

    // 数组去重
    Array.prototype.unique = function(){
        var arr = [];
        for(var i = 0; i < this.length; i++){
            if(arr.indexOf(this[i]) == -1){
                arr.push(this[i]);
            }
        }
        return arr;

    }

</script><!-- 页面js结束 -->
<?php $this->endBlock(); ?>