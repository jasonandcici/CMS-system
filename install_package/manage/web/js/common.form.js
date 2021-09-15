// +----------------------------------------------------------------------
// | SimplePig
// +----------------------------------------------------------------------
// | Copyright (c)
// +----------------------------------------------------------------------
// | Author:
// +----------------------------------------------------------------------
// | Created on 2016/3/13.
// +----------------------------------------------------------------------

/**
 * 表单页公用js
 * */

var formApp = function () {

  /**
   * 初始化
   * */

  var _initFun = function () {
    /* select美化 */
    $('select[prety]').each(function(){
      var $this = $(this),
        _placeholder = $this.data('placeholder');
      if(!_placeholder) _placeholder = '';
      $this.select2({
        placeholder: _placeholder
      });
    });

      $('select[multiple]').each(function(){
          var $this = $(this),
              _placeholder = $this.data('placeholder');
          if(!_placeholder) _placeholder = '';
          $this.select2({
              placeholder: _placeholder
          });
      });

    // 附件上传
    var $uploadSingleFile = $('.j_upload_single_file');
    if($uploadSingleFile.size() > 0) uploadUeditor.singleAttachment($uploadSingleFile);

    var $uploadMultipleFile = $('.j_upload_multiple_file');
    if($uploadMultipleFile.size() > 0) uploadUeditor.multipleAttachment($uploadMultipleFile);

    var $uploadSingleImg = $(".j_upload_single_img");
    if($uploadSingleImg.size() > 0) uploadUeditor.singleImage($uploadSingleImg);

    var $uploadMultipleImg = $(".j_upload_multiple_img");
    if($uploadMultipleImg.size() > 0) uploadUeditor.multipleImage($uploadMultipleImg);

    // 日期选择
    $('.js-date').datepicker({
        dateFormat:'yy-mm-dd',
        beforeShow: function () {
           setTimeout(function () {
                 $('#ui-datepicker-div').css("z-index", 999);
              }, 100);
         }
    });
    $('.js-date-time').datetimepicker({
        dateFormat:'yy-mm-dd',
        beforeShow: function () {
            setTimeout(function () {
                $('#ui-datepicker-div').css("z-index", 999);
            }, 100);
        }
    });


      // 数据关联
      $('.j_related_selector').each(function (i, n) {
          var $this = $(this),
              $button = $this.find('.related_btn'),
              $list = $this.find('.related_list'),
              $input = $this.find('.related_input'),
              $count = $this.find('.related_count'),
              relatedData = function () {
                  var _val = $input.val();
                  return _val ? _val.split(',') : [];
              },
              addText = $this.data('addText') || "点击添加",
              editText = $this.data('editText') || "点击修改",
              callback = $this.data('callback');

          $button.dataSelector({selectedIds: relatedData()}, function () {
              var currData = relatedData();
              $count.text(currData.length);
              if($list.length>0 && currData.length>0){
                  $button.text(editText);
                  relatedSelectorAjax(currData,$button.attr('href'),$list);
              }
          }, function (selectedIds) {
              if(selectedIds.length>0){
                  $button.text(editText);
              }else{
                  $button.text(addText);
              }
              $count.text(selectedIds.length);
              $input.val(selectedIds.join(","));
              if(callback && typeof eval(callback) === 'function'){
                  eval(callback+'(selectedIds,"select",$this)');
              }
              relatedSelectorAjax(selectedIds,$button.attr('href'),$list);
          });

          $list.on('click','.related_list_del',function () {
              var $del = $(this),
                  _id = $del.data('id'),
                  newData = [];
              $.each(relatedData(),function (i,n) {
                  if(n != _id) newData.push(n);
              });
              if(newData.length>0){
                  $button.text(editText);
              }else{
                  $button.text(addText);
                  $list.hide();
              }
              $count.text(newData.length);
              var options = $button.data('options');
              options.selectedIds = newData;
              $button.data('options',options);
              $input.val(newData.join(","));
              $del.parent('li').remove();
              if(callback && typeof eval(callback) === 'function'){
                  eval(callback+'(newData,"delete",$this)');
              }
          });
      });

      function relatedSelectorAjax(ids,url,$list) {
          if(ids.length < 1){
              $list.hide().html('');
              return true;
          }
          var model = commonApp.getUrlParam('m',url),
              data = {};
          if(model){
              data[model.substring(0,1).toUpperCase()+model.substring(1)+'Search[id]'] = ids;
          }else{
              model = commonApp.getUrlParam('r',url);
              model = model.split('/');
              model = model[1];
              if(model === 'user'){
                  data['UserSearch[id]'] = ids;
              }else if(model === 'category'){
                  data['PrototypeCategorySearch[id]'] = ids;
              }else{
                  return false;
              }
          }
          $list.before('<span class="related_loading"></span>');
          $.ajax({
              url:url,
              data:data,
              dataType:'json',
              success:function (res) {
                  var _html = '';
                  $.each(res,function (i,n) {
                      _html += '<li>'+ (model === 'user'?n.username+'('+n.userProfile.nickname+')':n.title) +'<a href="javascript:;" class="related_list_del" data-id="'+n.id+'">删除</a></li>';
                  });
                  $list.show().html(_html);
              },
              error:function () {
                  commonApp.notify.error('获取关联数据失败。');
              },
              complete:function () {
                  $list.prev('.related_loading').remove();
              }
          });
      }
  };

  /**
   * 百度编辑器扩展
   */
  var _editorPlugin = function () {
    // 第三方视频通用代码解析
    UE.registerUI('button',function(editor,uiName){
      editor.registerCommand(uiName,{
        execCommand:function(){
          var code = prompt("请输入第三方视频通用代码","");
          if (code!=null && code!=""){
            var uri = '',_w = 100,_h = 100;
            if(/^\<(iframe|embed){1}\s{1}/.test(code)){
              var _temp = $(code);
              uri = _temp.attr('src');
              _w = _temp.attr('width')||100;
              _h = _temp.attr('height')||100;
            }else{
              uri = code;
            }
            editor.execCommand("inserthtml",'<iframe src="'+uri+'" frameborder="0" width="'+_w+'" height="'+_h+'" allowfullscreen=""></iframe>');
          }
        }
      });

      return new UE.ui.Button({
        name:uiName,
        title:'插入第三方视频通用代码',
        cssRules :'background-position: -680px -40px;',
        onclick:function () {
          editor.execCommand(uiName);
        }
      });
    });
  };

  return {
    init: function () {
      _initFun();
    },
      editorPlugin:function () {
          if(typeof UE != 'undefined') {
              _editorPlugin();
          }
      }
  }
}();
