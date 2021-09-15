// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Copyright (c)
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | Created on 2015/9/19.
// +----------------------------------------------------------------------

/* blockui 默认配置 */
$.blockUI.defaults.css = {};
$.blockUI.defaults.overlayCSS = {};

// jquery.ui日历插件
$.datepicker.setDefaults({
  dateFormat: "yy-mm-dd",
  dayNamesMin:['日','一','二','三','四','五','六'],
  monthNames:['一月','二月','三月','四月','五月','六月','七月','八月','九月','十月','十一月','十二月'],
  monthNamesShort:['一月','二月','三月','四月','五月','六月','七月','八月','九月','十月','十一月','十二月']
});

// 弹出框默认设置为中文
bootbox.setDefaults({
  locale:'zh_CN'
});

/**
 * 日期对象格式化 "yyyy-mm-dd hh:ii:ss"
 * @param format
 * @returns {*}
 */
Date.prototype.format =function(format) {
  var o = {
    "m+" : this.getMonth()+1, //month
    "d+" : this.getDate(),    //day
    "h+" : this.getHours(),   //hour
    "i+" : this.getMinutes(), //minute
    "s+" : this.getSeconds(), //second
    "q+" : Math.floor((this.getMonth()+3)/3),  //quarter
    "S" : this.getMilliseconds() //millisecond
  };
  if(/(y+)/.test(format)) format=format.replace(RegExp.$1,
    (this.getFullYear()+"").substr(4- RegExp.$1.length));
  for(var k in o)if(new RegExp("("+ k +")").test(format))
    format = format.replace(RegExp.$1,
      RegExp.$1.length==1? o[k] :
        ("00"+ o[k]).substr((""+ o[k]).length));
  return format;
};

/**
 * 公用方法和配置
 */

var commonApp = function () {

  var _frameUrl;
  /**
   * 初始化
   * */
  var _initFun = function () {
    /* 判断是否存在是否存在指定父框架 */
    inFrameFun(function(){
      _frameUrl = parent.document.getElementById('mainFrame').getAttribute('data-curr');

      // 关闭父框架loading
      parent.indexApp.frameCloseLoading();

      // 离开页面触发框架loading效果
      $(window).bind('beforeunload',function(){
        parent.indexApp.frameLoading();
      });

      // 记录当前页面到本地
      if ($('#system-error').length < 1) parent.indexApp.setHistory(location.href);
    },null,'mainFrame');

    // 关闭对话框中的iframe的loading
    inFrameFun(function(){
      var $iframe = $(parent.document).find('#dialog-iframe');
      $iframe.ready(function(){
        $iframe.parent().unblock({
          fadeOut: 300
        });
      });
    },null,'dialog-iframe');

    /* 绑定公共方法 */

    //后退
    $('.j_goback').click(function(){
      var _step = $(this).data('step'),
        _url = document.referrer;

      if(!_step) _step = -1;
      if(_step == -1){
        if(_frameUrl && _url.slice(0,_url.indexOf('?')) != _frameUrl){
          self.location = _url;
        }else{
          history.go(_step);
        }
      }else{
        history.go(_step);
      }
    });

    // 用弹出框iFrame打开链接
    // data-title 弹出框标题
    // data-size 弹出框尺寸不填默认尺寸，large，small
    // data-height 弹出框高度
    // data-button 是否显示按钮，默认显示
    // data-init($dialog) 对话框插入页面前的回掉
    // data-callback($dialog,iframe) iframe点击确定后的回调函数,如果回调函数返回false则阻止关闭弹出框
    $('.j_dialog_open').click(function(){
      var $this = $(this);

      dialogIframeFun($this.data('title')||$this.text(),$this.attr('href'),{
        size:$this.data('size')||null,
        showButton: $this.data('button'),
        cancelClass:$this.data('cancelClass')||'btn-default',
        dialogIframeHeight:$this.data('height')||300,
        onInit:function($dialog){
          $dialog.data('trigger',$this);
          var initFun = $this.data('init');
          if(initFun && typeof(eval(initFun)) == "function") eval(initFun+'($dialog)');
        },
        onShow:function($dialog){
          var showFun = $this.data('show');
          if(showFun && typeof(eval(showFun)) == "function") eval(showFun+'($dialog)');
        },
        confirm:function(){
          var result,
            callback = $this.data('callback');
          if(callback && typeof(eval(callback)) == "function") eval('result = '+callback+'($(this),window.frames["dialog-iframe"])');
          if(typeof result != 'undefined' && !result){
            return false;
          }
        }
      });
      return false;
    });

    // 日期选择
    $('.j_date_piker').each(function(i,n){
      var $this = $(this),
        currVal = $this.val(),
        $input = $('<input type="text" name="date_piker_'+ i +'" class="form-control" readonly style="background-color: #fff;">');
      if(currVal && currVal != 0) $input.val($.datepicker.formatDate('yy-mm-dd',new Date($this.val()*1000)));
      $this.after($input);
      $input.datepicker({
        changeMonth: true,
        changeYear: true,
        altField:$this,
        altFormat:'@',
        onSelect:function(date){
          $this.val($this.val()/1000);
        }
      });
    });

    // 日期带详细时间选择
    $('.j_time_piker').each(function(i,n){
      var $this = $(this),
        currVal = $this.val(),
        $input = $('<input type="text" name="time_piker_'+ i +'" class="form-control">');
      if(currVal && currVal != 0) $input.val(new Date($this.val()*1000).format('yyyy-mm-dd hh:ii'));

      $this.after($input);
      $input.datetimepicker({
        closeText:'确定',
        hourText:'小时',
        minuteText:'分钟',
        timeText:'时间',
        gotoCurrent: true,
        onSelect:function(datetime){
          $this.val($.datepicker.formatDate('@',new Date(datetime))/1000);
        }
      });
    });
  };

  /**
   * 判断是否在iframe中
   * @parm  function trueCallback 为true回调
   * @parm  function faseCallback 为false回调
   * */
  var inFrameFun = function (trueCallback,faseCallback,frameName){
    if(!frameName) frameName = 'mainFrame';
    if(window.self != window.top && $(self).attr('name') == frameName){
      if(trueCallback && typeof trueCallback == 'function') trueCallback();
    }else{
      if(faseCallback && typeof faseCallback == 'function') faseCallback();
    }
  };

  /**
   * 检测是否支持本地存储
   * @returns {boolean}
   */
  var localStorageFun = function(trueCallback,faseCallback){
    var isSuport = function(){
      try {
        return 'localStorage' in window && window['localStorage'] !== null;
      } catch (e) {
        return false;
      }
    }();
    if(isSuport){
      if(trueCallback && typeof trueCallback == 'function') trueCallback();
    }else{
      if(faseCallback && typeof faseCallback == 'function') faseCallback();
    }
  };

  /**
   * 浮动导航
   * FLOAT_NAV_DATA 变量可定义在全局里，用于扩展浮动导航
   * @private
   */
  var _floadnavFun = function(){

    var $ul = $('<ul class="nav-float" style="display: none;"></ul>'),
      _float_data = [{
          name:'<a href="javascript:;" title="返回顶部"><span class="iconfont">&#xe61c</span></a>',
          navId:'',
          navClass:'nav-float-back',
          eventTag:'a',
          click: function(){
            $('body,html').animate({scrollTop:0},300);
          }
        }];
    if(typeof FLOAT_NAV_DATA != 'undefined') $.extend(_float_data,FLOAT_NAV_DATA);

    for(var i in _float_data){
      var $li = $('<li class="'+ _float_data[i].navClass +'" id="'+ _float_data[i].navId +'">'+ _float_data[i].name +'</li>');

      var $eventTag = $li.find(_float_data[i].eventTag) || $li;
      $eventTag.click(function(){
        _float_data[i].click($li);
      });

      $ul.prepend($li);
    }
    $('body').append($ul);

    var _scroll = true,
      scrollFun = function($window){
        if(_scroll){
          if($window.scrollTop() > 100){
            if($ul.is(':hidden')) $ul.stop().fadeIn(250);
          }else{
            if($ul.is(':visible')) $ul.stop().fadeOut(250);
          }
          _scroll = false;
          setTimeout(function(){
            _scroll = true;
          },20);
        }
      };
    $(window).scroll(function(){
      scrollFun($(this));
    });
  };

    /**
     * 获取url参数
     * @parm  string name 参数名
     *
     * @return string
     * */

    var getUrlParamFun = function (name,url) {
        var _search;
        if(url){
            var _u = url.split('?');
            _search = typeof _u[1] !=='undefined'?_u[1]:'';
        }else {
            _search = window.location.search.substr(1);
        }

        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
        var r = _search.match(reg);
        if (r != null) return unescape(r[2]); return null;
    };

    /**
     * 设置url参数
     * @param name
     * @param value
     * @returns {string}
     */
    var setUrlParamFun = function (name,value,url){
        var _search,_url;
        if(url){
            var _u = url.split('?');
            _search = typeof _u[1] !=='undefined'?_u[1]:'';
            _url = url;
        }else {
            _search = window.location.search.substr(1);
            _url = window.location.href;
        }


        if(_search){
            var _searches = _search.split('&'),
                _exit = false;
            for (var i=0;i<=_searches.length;i++){
                if(typeof _searches[i] != 'undefined'){
                    var _tmp = _searches[i].split('=');
                    if(_tmp[0] == name){
                        _url = _url.replace(_searches[i],_tmp[0]+'='+value);
                        _exit = true;
                        break;
                    }
                }
            }
            if(!_exit) _url = _url+'&'+name+'='+value;
        }else{
            _url = _url+(_url.indexOf('?')>-1?'':'?')+name+'='+value;
        }
        return _url;
    };


  /**
   * 返回文件路径
   * @param filename 文件名
   * @returns {*}
   */
  var getFilePathFun = function(filename){
    var _currJsPath,
    _js=document.scripts;
    for(var i=_js.length;i>0;i--){
      if(_js[i-1].src.indexOf(filename)>-1){
        _currJsPath = _js[i-1].src.substring(0,_js[i-1].src.lastIndexOf("/")+1);
        break;
      }
    }
    return _currJsPath;
  };

  /**
   * 表单数据转换为 键=>值
   * @param $form
   * @returns {{}}
   */
  var getFormDataFun = function($form){
    var o = {};
    var a = $form.serializeArray();
    $.each(a, function () {
      if (o[this.name] !== undefined) {
        if (!o[this.name].push) {
          o[this.name] = [o[this.name]];
        }
        o[this.name].push(this.value || '');
      } else {
        o[this.name] = this.value || '';
      }
    });
    return o;
  };

  /**
   * dialog对话框扩展
   * @parm  string or array info 为数组时长度为2，分别为title和content
   * @parm  object config cancelButton、confirmButton按钮对应文字，cancel、confirm点击回调函数
   * */

  /* 对话框主题样式 */
  var dialogTheme = {
    success:'<div class="dialog-icon text-success"><span class="iconfont">&#xe60c;</span></div><div class="dialog-cnt clearfix">{{info}}</div>',
    error:'<div class="dialog-icon text-danger"><span class="iconfont">&#xe60d;</span></div><div class="dialog-cnt clearfix">{{info}}</div>',
    confirm:'<div class="dialog-icon text-warning"><span class="iconfont">&#xe60e;</span></div><div class="dialog-cnt clearfix">{{info}}</div>'
  };

  var dialogFun = function (info, config){
    if(typeof info == 'string'){
      info = ['系统提示',info];
    }
    var setting = {
      theme:null,
      size:null,
      className:null,
      showButton:true,
      cancelButton:'取消',
      confirmButton:'确定',
      cancelClass:'btn-default',
      confirmClass:'btn-primary',
      cancel: $.noop(),
      confirm:$.noop(),
      onShow: $.noop(),
      onInit: $.noop()
    };
    $.extend(setting, config);

    var _message = info[1];
    if(setting.theme){
      _message = dialogTheme[setting.theme].replace('{{info}}',info[1]);
    }

    var _buttons = setting.showButton?{
      cancel: {
        label: setting.cancelButton,
        className: setting.cancelClass,
        callback: setting.cancel
      },
      confirm: {
        label: setting.confirmButton,
        className: setting.confirmClass,
        callback: setting.confirm
      }
    }:null;

    bootbox.dialog({
      title: info[0],
      message:_message,
      size:setting.size,
      className:setting.className,
      buttons: _buttons,
      onEscape:setting.cancel,
      onShow:setting.onShow,
      onInit:setting.onInit
    });
  };

  /* 成功对话框 */
  var successDialog = function(info, config){
    var setting = {theme:'success',confirmClass:'btn-success'}
    $.extend(setting,config);
    dialogFun(info, setting);
  };

  /* 错误对话框 */
  var errorDialog  = function(info, config){
    var setting = {theme:'error',confirmClass:'btn-danger'};
    $.extend(setting,config);
    dialogFun(info, setting);
  };

  /* 警告对话框 */
  var warningDialog  = function(info, config){
    var setting = {theme:'confirm',confirmClass:'btn-warning'};
    $.extend(setting,config);
    dialogFun(info, setting);
  };

  /**
   * 页面loading效果
   * @parm string|boolean|object txt loading文字，隐藏，配置
   * */
  var loadingFun = function(txt){
    var $wrapper = $('body');
    if(typeof txt == 'object' && txt.wrapper){
      $wrapper = txt.wrapper;
    }
    if(!txt || typeof txt == 'boolean'){
      $wrapper.unblock({
        fadeOut: 300
      });
      return;
    }
    if(!txt) txt = '页面加载中，请稍后…';
    var _setting = {
      overlayCSS:{
        backgroundColor: '#fff',
        opacity:0.5
      },
      fadeIn:200,
      blockMsgClass:'frame-loading',
      message:'<div class="icon-loading"></div><p>'+ txt +'</p>',
      centerX: false,
      centerY: false
    };

    if(typeof txt == 'object'){
      $.extend(_setting,txt);
    }

    $wrapper.block(_setting);
  };

  /**
   * dialog里嵌套iframe
   * @parm string title 对话框标题
   * @parm string 对话框中的iframe 的 url
   * @parm object config 配置
   * */
  var dialogIframeFun = function(title, url, config){
    var _setting = {
      dialogIframeLoading:'加载中，请稍后…',
      dialogIframeHeight:300
    };
    $.extend(_setting,config);

    // 弹出对话框
    var _html = '<iframe class="scroll-bar" id="dialog-iframe" name="dialog-iframe" src="'+ url +'" height="'+ _setting.dialogIframeHeight +'"></iframe>';
    dialogFun([title,_html],_setting);

    // 显示loading
    dialogIframeLoadingFun($('#dialog-iframe'),_setting.dialogIframeLoading);
  };

  var dialogIframeLoadingFun = function($frame,txt){

    loadingFun({
      message:'<div class="icon-loading"></div><p>'+ (txt?txt:'数据加载中，请稍后…') +'</p>',
      wrapper:$frame.parent()
    });
  };


  /**
   * 表单验证
   * @parm  object $form 表单的jquery对象
   * @parm  object config 表单配置
   * @parm  object ajaxConfig 异步提交配置
   * */
  var formValidationFun = function($form,config){
    var _validate ={},
      _ajax = {};

    if(config){
      if(config.validate) _validate = config.validate;
      if(config.ajax) _ajax = config.ajax;
    }

    var setting = {
      ignore: '',
      errorElement:'p',
      errorPlacement:function(error, element){
        var $e = $(element),
          $parent = $e.parents('.form-group');
        $e.parent().append(error);
        $parent.addClass('has-error');
      },
      success:function(label){
        var $parent = label.parents('.form-group');
        $parent.removeClass('has-error');
        label.remove();
      },
      submitHandler: function(form) {
        var $form = $(form),
          ajaxSetting = _formAjax($form);
        $.extend(ajaxSetting,_ajax);
        $.ajax(ajaxSetting);
      }
    };

    $.extend(setting,_validate);
    $form.each(function(){
      $(this).validate(setting);
    });
  };

  function _formAjax($form,config){
    var setting = {
      type: $form.attr('method'),
      url: $form.attr('action'),
      data: $form.serialize(),
      dataType: 'json',
      beforeSend: function (XMLHttpRequest) {
        loadingFun('表单提交中，请稍后...');
      },
      complete: function () {
        loadingFun(false);
      },
      error: function (XMLHttpRequest, textStatus, errorThrown) {
        var _html = '';
        if(XMLHttpRequest.responseText !=null && XMLHttpRequest.responseText != '') _html = '<p>错误原因是：<br>' + XMLHttpRequest.responseText + '</p>';

        errorDialog('<h4 class="t">很遗憾，提交失败</h4><p>'+ errorThrown + '<br>textStatus:' + textStatus+ '<br>status:' + XMLHttpRequest.status + '</p>' + _html,{cancelClass:'hide'});
      },
      success: function (result) {
        var _message = '';
        if(result.message != null && typeof result.message == 'object'){
          _message = '<pre>'+ JSON.stringify(result.message) + '</pre>';
        }
        else if(typeof result.message == 'string'){
          _message = '<p>'+ result.message + '</p>';
        }

        if(result.status == 1){
          this.successCallback(result);
          successDialog('<h4 class="t">恭喜您，'+ result.title + '</h4>' + _message +
            '<p>如果想留在本页继续操作，请点击<span class="text-success">继续操作</span>按钮继续操作。如果您要返回上一页，请<span class="text-success">点击确定</span>按钮返回上一页。</p>',{
            cancelButton:'继续操作',
            confirm:function(){
              if(result.jumpLink == 'javascript:history.back(-1);'){
                var _url = document.referrer;
                if(_frameUrl && _url.slice(0,_url.indexOf('?')) != _frameUrl){
                  self.location = _url;
                }else{
                  history.go(-1);
                }
              }else{
                location.href = result.jumpLink;
              }
            },
            cancel:function () {
              if(typeof result.updateUrl !== 'undefined'){
                  self.location = result.updateUrl;
              }
            }
          });
        }else{
          errorDialog('<h4 class="t">很遗憾，'+ result.title + '</h4><p>错误原因是：</p>'+ _message,{cancelClass:'hide'});
        }
      },
      successCallback:function(){
        return $.noop();
      }
    };
    $.extend(setting,config);
    return setting;
  }

  /**
   * Yii2 activeForm 表单异步提交
   * @param $form
   * @param config
   */
  var formYiiAjaxFun = function($form,config){
    $form.on('beforeSubmit',function(e){
      var _setting = _formAjax($form,config);

      $.extend(_setting,{complete:function(){
        loadingFun(false);
        $form.data('yiiActiveForm').validated = false;
      }},config);

      $.ajax(_setting);
    }).on('submit', function (e) {
      e.preventDefault();
    });

  };

  /**
   * 数据（checkbox）全择反选和获取id
   * @parm object $chckbox 数据checkbox的jquery对象集合
   * @parm boolean status 操作状态
   * */

  /* 过滤 */
  var _chooseFilter = function($checkbox, exp){
    $checkbox = $checkbox.not('[disabled]');
    if(exp){
      $checkbox = $checkbox.filter(exp);
    }
    return $checkbox
  };

  /* 全选和取消 */
  var _chooseAllFun = function($checkbox, status){
    if(typeof status == 'undefined') status = true;
    if(status){
      $checkbox.prop('checked',true);
      $checkbox.parents('tr').addClass('warning');
    }else{
      $checkbox.prop('checked',false);
      $checkbox.parents('tr').removeClass('warning');
    }
  };

  /* 反选 */
  var _chooseReverseFun = function($checkbox){
    $checkbox.each(function(){
      var $this = $(this),
        $tr = $this.parents('tr');
      if($this.is(':checked')){
        $this.prop('checked',false);
        $tr.removeClass('warning');
      }else{
        $this.prop('checked',true);
        $tr.addClass('warning');
      }
    });
  };

  /* 获取已选项id */
  var _chooseGetId = function($checkbox,status){

    if(typeof status == 'undefined') status = true;
    var temp = [];
    $checkbox.each(function(){
      var $this = $(this);

      if(status){
        if($this.is(':checked')){
          temp.push($this.val());
        }
      }else{
        if(!$this.is(':checked')){
          temp.push($this.val());
        }
      }
    });
    return temp;
  };

  /**
   * 消息框 notific
   * @parm string message 消息内容
   * @parm string type 消息类型
   * @parm object config 配置
   * */

  var notifyFun = function(message,type,config){
    if(!type) type = 'information';
    if(!config) config = {};
    var _setting = {
      text:message,
      type:type,
      layout:'topCenter',
      theme:'bootstrapTheme',
      template: '<div class="noty_message"><span class="noty_text"></span><div class="noty_close"></div></div>',
      timeout:4000,
      maxVisible:15,
      animation: {
        open  : 'animated bounceInDown',
        close : 'animated bounceOutUp',
        easing: 'swing',
        speed: 500
      }
    };
    $.extend(_setting,config);
    noty(_setting);
  };

  /* 成功消息提示 */
  var notifySuccess = function(message,config){
    if(!config) config = {};
    var _setting = {
      template:'<div class="noty_message"><span class="iconfont">&#xe60c;</span><span class="noty_text"></span><div class="noty_close"></div></div>'
    }
    $.extend(_setting,config);
    notifyFun(message,'success',_setting);
  };

  /* 错误消息提示 */
  var notifyError = function(message,config){
    if(!config) config = {};
    var _setting = {
      template:'<div class="noty_message"><span class="iconfont">&#xe60d;</span><span class="noty_text"></span><div class="noty_close"></div></div>'
    }
    $.extend(_setting,config);
    notifyFun(message,'error',_setting);
  };

  /* 失败消息提示 */
  var notifyWarning = function(message,config){
    if(!config) config = {};
    var _setting = {
      template:'<div class="noty_message"><span class="iconfont">&#xe60e;</span><span class="noty_text"></span><div class="noty_close"></div></div>'
    }
    $.extend(_setting,config);
    notifyFun(message,'warning',_setting);
  };

  /* 消息提示 */
  var notifyInfo = function(message,config){
    if(!config) config = {};
    var _setting = {
      template:'<div class="noty_message"><span class="iconfont">&#xe60f;</span><span class="noty_text"></span><div class="noty_close"></div></div>'
    }
    $.extend(_setting,config);
    notifyFun(message,'information',_setting);
  };


  /**
   * 字段更新，异步请求
   * @parm object $e 触发元素，jquery对象
   * @parm object config 异步请求配置
   * */

  var fieldUpdateRequestFun = function ($e,config){
    var _setting = {
      type: 'get',
      url: $e.attr('href'),
      dataType: 'json',
      beforeSend: function (XMLHttpRequest) {
        commonApp.loading(this.loadingTxt);
      },
      error: function (XMLHttpRequest, textStatus, errorThrown) {
        commonApp.loading(false);
        var _html = '';
        if(XMLHttpRequest.responseText !=null && XMLHttpRequest.responseText != '') _html = '<p>错误原因是：<br>' + XMLHttpRequest.responseText + '</p>';
        this.errorCallback('操作失败：'+ errorThrown + '. textStatus:' + textStatus + '. status:'+ XMLHttpRequest.status + '.'+ _html);
      },
      success: function (result) {
        if(result.status == 1){
          if(this.autoCloseLoading) commonApp.loading(false);
          if(this.showNotify) commonApp.notify.success(this.successTxt);
          this.successCallback(result);
        }else{
          commonApp.loading(false);
          this.errorCallback(result);
          commonApp.notify.error(this.errorTxt);
        }
      },
      // 自定义其他配置
      successCallback: function(){
        return $.noop();
      },
      errorCallback: function(){
        return $.noop();
      },
      autoCloseLoading:true,
      showNotify:true,
      loadingTxt:'系统操作中，请稍后...',
      successTxt:'恭喜您，操作成功！',
      errorTxt:'很遗憾，操作失败！'
    };
    $.extend(_setting,config);
    $.ajax(_setting);
  };

  /**
   *  数组转换为tree
   * @param data
   * @param parentId
   * @param parentName
   * @returns {Array}
   * @private
   */
  var _arrayToTree = function (data, parentId, parentName) {
    if(typeof parentId == 'undefined') parentId = 0;
    if(typeof parentName == 'undefined') parentName = 'pid';

    var arr = [];
    $.each(data,function (i,n) {

      if(n[parentName] == parentId){
        n['child'] = _arrayToTree(data,n.id,parentName);

        /*$.each(n.child,function (ii,nn) {
          n.child[ii]['hasParent'] = (n.child.length > 0);
        });*/

        arr.push(n);
      }
    });
    return arr;
  };

  /**
   * 返回接口
   * */
  return {
    init: function () {
      _initFun();
      _floadnavFun();
    },
    getUrlParam:getUrlParamFun,
    setUrlParam:setUrlParamFun,
    inFrame:inFrameFun,
    localStorage:localStorageFun,
    getFilePath:getFilePathFun,
    getFormData:getFormDataFun,
    formValidation:formValidationFun,
    formYiiAjax:formYiiAjaxFun,
    loading:loadingFun,
    dialog:{
      theme:dialogTheme,
      "default":dialogFun,
      success:successDialog ,
      error:errorDialog ,
      warning:warningDialog,
      iframe:dialogIframeFun,
      iframeLoading:dialogIframeLoadingFun
    },
    notify:{
      "default":notifyFun,
      success:notifySuccess,
      error:notifyError,
      warning:notifyWarning,
      info:notifyInfo
    },
    chooseData:{
      filter:_chooseFilter,
      all:_chooseAllFun,
      reverse:_chooseReverseFun,
      getId:_chooseGetId
    },
    fieldUpdateRequest:fieldUpdateRequestFun,
    arrayHelper:{
      tree:_arrayToTree
    }
  }
}();