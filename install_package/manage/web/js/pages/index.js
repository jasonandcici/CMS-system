// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Copyright (c)
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | Created on 2015/9/19.
// +----------------------------------------------------------------------

/**
 * 首页应用
 */

var indexApp = function () {
  var $body = $('body'),
    $navmain = $('#nav-main'),
    $mainFrame = $('#mainFrame'),
    $mainFrameWraper = $mainFrame.parent();

  /**
   * 初始化
   * */
  var initFun = function () {

    /* 工具提示 */
    $('.j_tooltip').tooltip();

    /* 初始化加载历史记录页面 */
    var history = localStorage.getItem('history');
    history = history && /^\{{1}/.test(history)?JSON.parse(history):null;

    if(!history || (new Date().getTime() - history.currTime) > 6*60*60*1000){
      $mainFrame.attr('src',$mainFrame.data('src'));
    }else{
      $mainFrame.attr('src',history.url);
    }


    /* 导航绑定历史记录 依赖jquery.history */
    History.Adapter.bind(window,'statechange',function(){
      var State = History.getState();
      $navmain.find('a[href="#'+ State.data.menu +'"]').tab('show');
    });

    // 初始化顶部导航
    var page_title = $('title').text(),
      nav_history = History.getState().data.menu;
    if(nav_history){
      $navmain.find('a[href="#'+ nav_history +'"]').tab('show');
    }else{
      _pusHistory($navmain.find('li:eq(0)>a'));
    }

    $navmain.find('a[role="tab"]').click(function(e){
      e.preventDefault();
      _pusHistory($(this));
    });

    function _pusHistory ($e){
      var target = $e.attr('href').replace('#','');
      History.pushState({menu:target},$e.text()+ ' · ' + page_title,'?menu='+target);
    }

    /* 关闭loading */
    $mainFrame[0].onload = function () {
      frameCloseLoadingFun();
    };

    /* 记录首页网址,用于iframe里获取 */
    var currUrl = location.href;
    $mainFrame.attr('data-curr',currUrl.slice(0,currUrl.indexOf('?')));

    /* 切换站点 */
    $('#j_changeSite').on('click','a',function () {
      if($(this).hasClass('active')) return false;

      var _csrf = {};
      _csrf[$('meta[name="csrf-param"]').attr('content')] = $('meta[name="csrf-token"]').attr('content');
      $('#changeSiteModel').modal('hide');
      commonApp.loading('切换站点中...');

      $.post($(this).attr('href'),_csrf,function (response) {
        setTimeout(function () {
          window.history.go(0);
        },1500);
        setHistoryFun($('.brand').data('welcome'));
        commonApp.loading(false);
        commonApp.notify.success('切换站点成功！');
      });
      return false;
    });

    // 退出
    $('#js-logout').click(function () {
      var $this = $(this);
      commonApp.fieldUpdateRequest($this,{
        showNotify:false,
        autoCloseLoading:false,
        successCallback:function (res) {
          $('.blockElement').find('p').text("操作成功，页面跳转中...");
          $('.main-wraper').addClass('loading-hide');
          localStorage.removeItem('history');
          location.href = res.jumpLink;
        }
      });

      return false;
    });

    // 清除缓存
    $('#js-clear-cache').click(function () {
      var $this = $(this);
      $('#nav-right-dropdown>button').dropdown('toggle');
      commonApp.dialog.warning('您确定要清除系统中所有数据缓存吗？',{
        confirm:function () {
          commonApp.fieldUpdateRequest($this);
        }
      });
      return false;
    });
  };

  /**
   * 记录当前浏览页面到本地
   * */
  var setHistoryFun = function(url){
    localStorage.setItem('history',JSON.stringify({'url':url,'currTime':new Date().getTime()}));
  };

  /**
   * 页面布局效果
   * */
  var layoutFun = function(){

    /* 主导航右侧dropdown */
    $('#nav-right-dropdown').on('show.bs.dropdown', function () {
      var $maskdropdown = $('#mask-dropdown');
      if($maskdropdown.size()<1){
        $maskdropdown = $('<div class="mask-dropdown" id="mask-dropdown">');
        $body.append($maskdropdown);
      }
      $maskdropdown.show();
    }).on('hide.bs.dropdown', function () {
      $('#mask-dropdown').hide();
    });

    /* 左侧导航显示 */
    var $mainAside = $('#main-aside');
    $('#nav-aside-btn').click(function(){
      var $this = $(this),
          $mask = $('#mask-aside');

      if($mask.size() < 1){
        $mask = $('<div class="mask-aside fade" id="mask-aside">');
        $body.append($mask);
        $mask.click(function(){
          $this.trigger('click');
        });
      }

      if($this.hasClass('open')){
        $this.removeClass('open');
        $mainAside.removeClass('open-aside');
        $mask.removeClass('in');
        $mask.one($.support.transition.end, function() {
          $mask.removeClass('show');
        });
      }else{
        $this.addClass('open');
        $mainAside.addClass('open-aside');
        $mask.addClass('show');
        setTimeout(function(){
          $mask.addClass('in');
        },10)
      }
    });

    /* 左侧导航accordion效果 */
    $('#accordion').on('click','.accordion-header',function(){
      var $this = $(this),
          $parent = $this.parent(),
          $tab = $navmain.find('a[href="#'+$parent.attr('id')+'"]');

      if($tab.attr('aria-expanded') == 'true') return false;

      if($this.data('bindevent')){
        $tab.trigger('click');
      }else {
        var $cnt = $this.next(),
          $siblingsCnt = $parent.siblings();
        $tab.on('show.bs.tab', function () {
          $cnt.hide();
          $siblingsCnt.filter('.active').addClass('actived');
        });
        $tab.on('shown.bs.tab', function () {
          $cnt.slideDown(function(){
            $(this).removeAttr('style');
          });
          $siblingsCnt.filter('.actived').find('.accordion-body').slideUp(function(){
            $(this).removeAttr('style').parent().removeClass('actived');
          });
        });

        $this.data('bindevent', true);
        $tab.trigger('click');
      }
    });

    //侧边导航状态
    $mainAside.on('click','a',function () {
      var $this = $(this);
      if($this.attr('href') !== 'javascript:;'){
          $mainAside.find('li.active').removeClass('active');
          $this.parent().addClass('active');
        $('#mask-aside.in').trigger('click');
       }
    });

  };

  /**
   * 左侧导航
   * */

  var navLeftAccordionFun = function ($navContent){
    $navContent.find('li:not(.tree-nch)').each(function(){
      var $this = $(this),
          $child = $this.children(),
          $a = $child.filter('a'),
          $icon = $a.find('.tree-icon');

      if($icon.data('bindclick')) return;
      $icon.data('bindclick',true);
      var $ul = $child.filter('ul'),
        accordionFun = function(){
          if($this.hasClass('tree-open')){
            $this.removeClass('tree-open');
            $ul.slideUp();
          }else{
            // 获取扩展菜单的数据
            if($icon.hasClass('extend_nav') && $ul.is(':empty')){
              var $_loading = $('<span class="tree-loading"></span>'),
                $_a = $icon.parent();
              $_a.append($_loading);

              $.ajax({
                type: 'get',
                url: $_a.data('url'),
                complete: function () {
                  $_loading.fadeOut(function(){
                    $_loading.remove();
                  });
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                  var _html = XMLHttpRequest.responseText;
                  if(!_html || _html != '') _html = '<p>错误原因是：<br>' + XMLHttpRequest.responseText + '</p>';
                  commonApp.dialog.error('<h4 class="t">获取数据失败</h4><p>'+ errorThrown + '<br>textStatus:' + textStatus+ '<br>status:' + XMLHttpRequest.status + '</p>' + _html,{cancelClass:'hide'});
                },
                success: function (result) {
                  $this.addClass('tree-open');
                  $ul.html(result).slideDown(function(){
                    navLeftAccordionFun($ul);
                  });
                }
              });
            }else{
              $this.addClass('tree-open');
              $ul.slideDown();
            }
          }
        };

      // 绑定展开和折叠事件
      $icon.click(function(e){
        accordionFun();
        e.stopPropagation();
        e.preventDefault();
      });

      //绑定空链接
      $a.click(function(){
        if($a.attr('href') == 'javascript:;'){
          accordionFun();
        }
      });

    });
  };

  /**
   * 主框架的loading效果
   * */
  var frameLoadingFun = function(txt){
    if(!txt) txt = '页面加载中，请稍后…';
    $mainFrameWraper.block({
      overlayCSS:{
        backgroundColor: '#fff',
        opacity:0.6
      },
      fadeIn:200,
      blockMsgClass:'frame-loading',
      message:'<div class="icon-loading"></div><p>'+ txt +'</p>',
      centerX: false,
      centerY: false
    });
   // $mainFrame.removeClass('in');
  };

  /**
   * 关闭loading效果
   * @param callback
   */
  var frameCloseLoadingFun = function(callback){
    $mainFrameWraper.unblock({
      fadeOut: 300
    });
    //$mainFrame.addClass('in');
    if(callback && typeof callback == 'function') callback($mainFrame,$mainFrameWraper);
  };

  /**
   * 更新动态的侧边导航
   * @param name
   */
  var clearNavAsideFun = function (name) {
    $('#'+name).removeClass('tree-open').children('ul').removeAttr('style').empty();
  };


  /**
   * 开放接口
   * */
  return {
    init: function () {
      initFun();
      layoutFun();
      frameLoadingFun();
      navLeftAccordionFun($('#accordion>.accordion-wrap'));
    },
    frameLoading:frameLoadingFun,
    frameCloseLoading:frameCloseLoadingFun,
    setHistory:setHistoryFun,
    clearNavAside:clearNavAsideFun
  }
}();