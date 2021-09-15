// +----------------------------------------------------------------------
// | tianji
// +----------------------------------------------------------------------
// | Copyright (c)
// +----------------------------------------------------------------------
// | Author:
// +----------------------------------------------------------------------
// | Created on 2016/3/5.
// +----------------------------------------------------------------------

/**
 * 添加关联数据时iframe页配合 jquery.dataSelector.js插件使用的js
 * */

Array.prototype.remove = function(el){
  return this.splice(this.indexOf(el),1);
};

var parentJquery =window.parent.jQuery,
  selectedIds = parentJquery("#dialog-iframe").data("selectedIds"),
  $checkbox = function(){
    return $("#list_data").find(':checkbox,:radio');
  };

$(function(){
  $checkbox().change(function(){
    var $this = $(this);
    if($this.is(':checked')){
      if($.inArray($this.val(),selectedIds)==-1){

        if($this.is(':radio')){
            selectedIds = [$this.val()];
        }else{
            selectedIds.push($this.val())
        }
      }
    }else{
      selectedIds.splice($.inArray($this.val(),selectedIds),1);
    }
    parentJquery("#dialog-iframe").data("selectedIds",selectedIds);
  });
  bindData(selectedIds);

  // 离开页面触发框架loading效果
  $(window).bind('beforeunload',function(){
    parent.commonApp.dialog.iframeLoading(parentJquery("#dialog-iframe"));
  });

});

function bindData(data){
  $checkbox().each(function(){
    if($.inArray($(this).val(),data)>-1)
    {
      $(this).attr("checked",true);
    }
  })
}
function getData(){
  return selectedIds;
}