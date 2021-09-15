// +----------------------------------------------------------------------
// | nantong
// +----------------------------------------------------------------------
// | Copyright (c)
// +----------------------------------------------------------------------
// | Author:
// +----------------------------------------------------------------------
// | Created on 2016/4/1.
// +----------------------------------------------------------------------

/**
 * role
 * */

var roleApp = function () {

  /**
   * 初始化
   * */

  var _initFun = function () {

    // 权限设置弹出框
    $('a.j_access').click(function(){
      var $this = $(this);

      commonApp.dialog.iframe($this.text(),$this.attr('href'),{
        confirm:function(){
          var $dialog = $(this);
          window.frames['dialog-iframe'].roleApp.saveAccess(function(){
            setTimeout(function () {
              $dialog.find('button.close').trigger('click');
            }, 2000);
          });
          return false;
        }
      });

      return false;
    });
  };

  /**
   * 权限展示
   * @param nodeList 节点列表
   * @param accessList 权限列表
   */
  var accessFun = function(nodeList,accessList){
    for(var i in nodeList){
      if(nodeList[i].level == 1) nodeList[i].open = true;
      for(var a in accessList){
        if(accessList[a].node_id == nodeList[i].id){
          nodeList[i].checked = true;
          break;
        }
      }

      nodeList[i].name = nodeList[i].title + ' (' + nodeList[i].name + ')';
    }

    // 生成tree
    $.fn.zTree.init($("#j_tree_access"),{
      check: {
        enable: true
      },
      data: {
        simpleData: {
          enable: true,
          pIdKey:'pid'
        }
      },
      callback:{
        onClick:function(e,treeId, treeNode){
          $('#'+treeNode.tId + '_a').prev().trigger('click');
        }
      }
    } , nodeList);
  };

  /**
   * 权限设置
   * @param callback
   */
  var saveAccessFun = function (callback){
    var treeObj = $.fn.zTree.getZTreeObj("j_tree_access");
    var nodes = treeObj.getCheckedNodes(true);

    var _selected = [];
    for(var i in nodes){
      _selected.push(nodes[i].id + '_' + nodes[i].level);
    }
    if(_selected.length < 1) return;



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
        if(result.status == 1){
          commonApp.notify.success(result.title,notifyConf);
          if(callback && typeof callback == 'function') callback();
        }else{
          commonApp.notify.error(result.title,notifyConf);
        }
      }
    });
  };

  return {
    init: function () {
      _initFun();
    },
    access:accessFun,
    saveAccess:saveAccessFun
  }
}();