// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Copyright (c)
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | Created on 2015/10/21.
// +----------------------------------------------------------------------

/**
 * 数据列表页公用js
 */

var listApp = function () {

  var $checkbox = $('#list_data :checkbox'),
    $DATA_CHECKBOX = commonApp.chooseData.filter($checkbox);// 数据列表checkbox

  /**
   * 初始化
   **/
  var _initFun = function () {

    /* select美化 */
    $('select[multiple]').each(function(){
      var $this = $(this),
        _placeholder = $this.data('placeholder');
      if(!_placeholder) _placeholder = '';
      $this.select2({
        placeholder: _placeholder
      });
    });

    $('select[prety]').each(function(){
      var $this = $(this),
        _val = $this.data('val'),
        _placeholder = $this.data('placeholder')||'';
      $this.select2({
        placeholder: _placeholder
      });
      if(_val) $this.select2('val',_val);
    });

    /* 数据列表操作 */

    //数据列表checkbox，全选
    $('#j_choose_all').click(function(){
        commonApp.chooseData.all($DATA_CHECKBOX);
    });

    //数据列表checkbox，反选
    $('#j_choose_reverse').click(function(){
      commonApp.chooseData.reverse($DATA_CHECKBOX);
    });

    //清空
    $('#j_choose_empty').click(function(){
      commonApp.chooseData.all($DATA_CHECKBOX,false);
    });

    // 响应式数据列表下 出现dropdown的处理
    $('.table-responsive .btn-group').on('show.bs.dropdown', function () {
      $(this).parents('.table-responsive').addClass('table-responsive-disable');
    })
      .on('hidden.bs.dropdown', function () {
        $(this).parents('.table-responsive').removeClass('table-responsive-disable');
      });

    //数据选中效果
    $checkbox.change(function(){
      var $this = $(this),
        $tr = $this.parents('tr');
      $this.is(':checked')?$tr.addClass('warning'):$tr.removeClass('warning');
    });

    /* 数据列表tree折叠 */
    var $list_tree = $('.j_list_tree');
    // 折叠事件
    $list_tree.click(function(){
      var $this = $(this),
        _id = $this.data('id'),
        $tr = $this.parents('tr'),
        _level = $this.data('level'),
        $child = $tr.nextUntil('tr.level_'+ _level).filter('.level_'+(_level+1));

      if($this.hasClass('open')){
        $this.removeClass('open');
        $child.hide().find('.open').removeClass('open');
        delete list_tree[storage_name]['data_'+_id];

        subs(_level, _level, $tr, _id);
        function subs(_level, _l, $tr, _id){
          _l ++;
          $child = null;
          $child = $tr.nextUntil('tr.level_'+ _level).filter('.level_'+(_l+1));
          if($child.length > 0){
            $child.hide().find('.open').removeClass('open');
            delete list_tree[storage_name]['data_'+_id];
            subs(_level, _l, $tr);
          }
        }

      }else{
        $this.addClass('open');
        $child.show();
        list_tree[storage_name]['data_'+_id] = _id;
      }
      localStorage.setItem('listTree',JSON.stringify(list_tree));
    });

    // 历史折叠
    var storage_name = commonApp.getUrlParam('r').replace('/','_').replace('&','_'),
      list_tree = localStorage.getItem('listTree');
    list_tree = list_tree?JSON.parse(list_tree):{};
    if(list_tree[storage_name]){
      $list_tree.each(function(){
        var $this = $(this);
        if(list_tree[storage_name]['data_'+$this.data('id')]){
          $this.trigger('click');
        }
      });
    }else{
      list_tree[storage_name] = {};
    }

    // 导出
    $('#j_export').click(function () {
      var $input = $('#exportInput'),
          $form = $(this).parents('form');
      $form.attr('target','_blank');
      $input.val(1);
      $form.submit();
      setTimeout(function () {
        $input.val(0);
        $form.removeAttr('target');
      },1000);
    });
  };

  //获取已选数据
  var getSelectData = function(){
    var _id = commonApp.chooseData.getId($DATA_CHECKBOX);
    if(_id.length < 1){
      commonApp.notify.error('请至少选择一条数据！');
      return false;
    }else {
      return _id;
    }
  };

  /**
   * 数据字段的更新
   * */
  var _fieldUpdate = function(){
    $('.j_batch').click(function(){
      var $this = $(this),
        _id,
        _status;

      switch ($this.data('action')){
        // 删除数据
        case 'del':
          commonApp.dialog.warning('您确定要删除这条数据吗？'+($this.data('title')||''),{
            confirm:function(){
              commonApp.fieldUpdateRequest($this,{
                loadingTxt:'正在删除，请稍后...',
                successTxt:'恭喜您，删除此条数据成功！',
                errorTxt:'很遗憾，删除此条数据失败！',
                successCallback:function(result){
                  if(result.jumpLink != 'javascript:history.back(-1);'){
                    setTimeout(function(){
                      location.href = result.jumpLink;
                    },2000);
                  }else{
                    $this.parents('tr').fadeOut(function(){
                      $(this).remove();
                    });
                  }
                },
                  errorCallback:function (res) {
                    if(typeof res === 'object'){
                      this.errorTxt = res.title;
                    }else{
                      this.errorTxt = res;
                    }
                  }
              });
            }
          });
          break;
        // 批量删除
        case 'batchDel':
           _id = getSelectData();
          if(!_id) return false;
          commonApp.dialog.warning('您确定要删除选中的数据吗？',{
            confirm:function(){
              commonApp.fieldUpdateRequest($this,{
                data:{id:_id.join(',')},
                loadingTxt:'正在删除，请稍后...',
                successTxt:'删除成功，2秒后将刷新本页面！',
                errorTxt:'很遗憾，删除选中数据失败！',
                successCallback:function(){
                  setTimeout(function(){
                    history.go(0);
                  },2000);
                },
                errorCallback:function (res) {
                    if(typeof res === 'object'){
                        this.errorTxt = res.title;
                    }else{
                        this.errorTxt = res;
                    }
                }
              });
            }
          });
          break;
        //更新状态
        case 'status':
          _status = parseInt($this.data('value'));
          commonApp.fieldUpdateRequest($this,{
            data:{value:_status},
            successCallback:function(res){
                if(typeof res.jumpLink !=='undefined' && res.jumpLink !== 'javascript:history.back(-1);'){
                    location.href = res.jumpLink;
                }else{
                    if(_status==1){
                        $this.removeClass('label label-primary label-warning label-info label-danger').data('value',0).html('<span class="iconfont">&#xe62a;</span>');
                    }else{
                        $this.removeClass('label label-primary label-warning label-info label-danger').data('value',1).html('<span class="iconfont">&#xe625;</span>');
                    }
                }
            },
              errorCallback:function (res) {
                  if(typeof res === 'object'){
                      this.errorTxt = res.title;
                  }else{
                      this.errorTxt = res;
                  }
              }
          });
          break;
        //批量更新状态
        case 'batchStatus':
          _id = getSelectData();
          if(!_id) return false;
          _status = parseInt($this.data('value'));

          commonApp.fieldUpdateRequest($this,{
            data:{id:_id.join(',')},
            successCallback:function(res){
              if(typeof res.jumpLink !=='undefined' && res.jumpLink !== 'javascript:history.back(-1);'){
                  setTimeout(function(){
                      location.href = res.jumpLink;
                  },1000);
              }else{
                  setTimeout(function(){
                      history.go(0);
                  },1000);
              }
              commonApp.chooseData.all($DATA_CHECKBOX,false);
            },
              errorCallback:function (res) {
                  if(typeof res === 'object'){
                      this.errorTxt = res.title;
                  }else{
                      this.errorTxt = res;
                  }
              }
          });
          break;
        default:
          commonApp.fieldUpdateRequest($this,{
            successCallback:function(){
              setTimeout(function(){
                history.go(0);
              },2000);
            },errorCallback:function(){
              setTimeout(function(){
                history.go(0);
              },2000);
            }
          });
          break;
      }
      return false;
    });
  };

  /**
   * 数据分页跳转
   * */
  var _paginationGoFun = function(){
    $('#j_pageSize').change(function () {
      $('#j_pagination_go').submit();
    });

    $('#j_pagination_go').submit(function(){
      var _page = parseInt($(this).find(':text').val());
      if(_page == '') return false;

      var _url = $('.pagination a:first').attr('href');
      if(!_url) return false;
      _url = _url.replace('&page=1','&page=' + _page);

      var tmp = _page-1;
      if(_page == 1) tmp = 1;

      _url = _url.replace('&per-page=1','&per-page=' + tmp);
      location.href = _url;
    });
  };

  /**
   * 数据排序
   * @private
   */
  var _sortFun = function(){
    // 单排序
    $('#list_data').on('click','.sort>a',function () {
      var $this = $(this);
      if($this.hasClass('disabled')) return false;

        var $searchData = $('.search-data'),
            newData = [];
        if($searchData.length > 0){
            $.each($searchData.serializeArray(),function (i,n) {
                if(n.name === 'r' || n.name === 'category_id'){
                    return true;
                }else{
                    newData.push(n);
                }
            });
        }

      commonApp.fieldUpdateRequest($this,{
          data:newData,
        success:function (response) {
          commonApp.loading(false);
          if(response.status){
            var $tr = $this.parents('tr'),
                _isUp = $this.hasClass('sort-up'),
                $previewTr = _isUp?$tr.prev():$tr.next();

            if($previewTr.size()>0){
              var $bs = $this.parent().find('.disabled');
              if($bs.size() > 0){
                $bs.removeClass('disabled');
                var $pbs = $previewTr.find('.'+($bs.hasClass('sort-up')?'sort-up':'sort-down')).addClass('disabled');
                if(!_isUp && $previewTr.next().size() < 1){
                  $bs.next().addClass('disabled');
                  $pbs.next().removeClass('disabled');
                }else if(_isUp && $previewTr.prev().size() < 1){
                  var _page = parseInt(commonApp.getUrlParam('page'));
                  if(!_page) _page = 1;
                  if(_page == 1) $bs.prev().addClass('disabled');
                   $pbs.prev().removeClass('disabled');
                }
              }else if($previewTr.find('.j_sort>.disabled').size() > 0){
                $this.addClass('disabled');
                $previewTr.find('.j_sort>.disabled').removeClass('disabled');
              }

              _isUp?$tr.after($previewTr.clone()):$tr.before($previewTr.clone());

              $previewTr.remove();
            }else{
              var page = commonApp.getUrlParam('page')||1;
              location.href = commonApp.setUrlParam('page',parseInt(page)+(_isUp?-1:1));
            }
          }else{
            commonApp.notify.error('操作失败');
          }
        }
      });
      return false;
    });

    // 批量排序
    $('#j_sort_batch').click(function () {
      var $this = $(this);
      if($this.data('empty')) return false;
      commonApp.dialog.default(['批量排序 <small>(拖动排序)</small>',$('#tpl_sort_batch').html()],{
        onShow:function ($dialog) {
          $dialog.find('.dd').nestable({'maxDepth':$this.data('depth')||10}).nestable('collapseAll');
        },
        confirm:function () {
          var $dd = $(this).find('.dd'),
              sort = $dd.find('.input-sort').val().split(','),
              isDeep = $this.data('deep') || false,
              newData;

          if(isDeep){
              newData = _nestableHandel($dd.nestable('serialize'),$this.data('pid'),sort.sort(function (a, b) {
                  return a-b;
              }),isDeep);
          }else{
              newData = _nestableHandel($dd.nestable('serialize'),$this.data('pid'),sort,isDeep);
              $.each(newData,function (i,n) {
                  newData[i].sort = sort[i];
              });
          }

          $dd.find('.input-data').val(JSON.stringify(newData));

          commonApp.fieldUpdateRequest($this,{
            type:'POST',
            data:$dd.find('form').serialize(),
            successTxt:'操作成功，页面将在2秒后刷新！',
            successCallback:function (response) {
              setTimeout(function () {
                if(typeof parent.indexApp !='undefined')parent.indexApp.frameLoading('页面刷新中，请稍后');
                history.go(0);
              },1500);
            },
              errorCallback:function (res) {
                  if(typeof res === 'object'){
                      this.errorTxt = res.title;
                  }else{
                      this.errorTxt = res;
                  }
              }
          });
        }
      });
      return false;
    });

    // nestable返回的数据处理
    function _nestableHandel(data,pid,sort,isDeep) {
      if(typeof pid == 'undefined') pid = 0;
      var _res = [];

      if(isDeep){
        $.each(data,function (i,n) {
          data[i].sort = sort[i];
        });
        sort.splice(0,data.length);
      }

      $.each(data,function (i,n) {
        if(typeof n.children == 'undefined'){
          n.pid = pid;
          _res.push(n);
        }else{
          var child = n.children;
          delete n.children;
          n.pid = pid;
          _res.push(n);
          _res = _res.concat(_nestableHandel(child,n.id,sort,isDeep));
        }
      });
      return _res;
    }
  };

  /**
   * 移动到
   * @private
   */
  var categoryList = [];
  var _moveFun = function () {
    $('#j_move_batch').click(function () {
      var $a = $(this),
          _id = getSelectData(),
          $expandNav = $('#j_expand_nav');
      if(!_id) return false;

      if(categoryList.length < 1){
        $.ajax({
          url:$expandNav.val(),
          dataType:'json',
          async:false,
          success:function (res) {
            var _cid = $expandNav.data('cid'),
                _mid = $expandNav.data('mid'),
                dataFilter = function (data, pid, count) {
                  if(typeof pid == 'undefined') pid = 0;
                  if(typeof count == 'undefined') count = 0;

                  var resData = [];
                  $.each(data,function (key,value) {
                    var _childHasNode = false;
                    if(value['child'].length > 0){
                      _childHasNode = childHasNode(value['child']);
                    }
                    //value['nocheck'] = false;
                    value['chkDisabled'] = false;
                    if((value['child'].length == 0 || (value['child'].length > 0 && !_childHasNode)  ) &&  value['type'] > 0){
                      return true;
                    }

                    if(value.model_id != _mid) value['chkDisabled'] = true;

                    resData.push(value);

                    if(value['pid'] == pid){
                      resData = resData.concat(dataFilter(value['child'],value['id'],count+1));
                    }
                  });
                  return resData;
                },
                childHasNode = function (child) {
                  var _return = false;
                  $.each(child,function (i, item) {
                    if(item['type'] < 2){
                      _return = true;
                    }else if(item['child'].length>0){
                      _return = childHasNode(item['child']);
                    }
                    if(_return) return false;
                  });
                  return _return;
                };

            $.each(dataFilter(commonApp.arrayHelper.tree(res)),function (i,n) {
              var tmp = {
                id:n.id,
                pId:n.pid,
                name:n.title,
                open:true,
                chkDisabled:n.chkDisabled
              };

              categoryList.push(tmp);
            });
          }
        });
      }

      var setting = {
        check: {
          enable: true,
          chkStyle: "radio",
          radioType: "all"
        },
        view: {
          dblClickExpand: false
        },
        data: {
          simpleData: {
            enable: true
          }
        },
        callback: {
          onClick: function (e, treeId, treeNode) {
            var zTree = $.fn.zTree.getZTreeObj("moveTree");
            zTree.checkNode(treeNode, !treeNode.checked, null, true);
            return false;
          },
          onCheck: function (e, treeId, treeNode) {
            var zTree = $.fn.zTree.getZTreeObj("moveTree"),
                nodes = zTree.getCheckedNodes(true),
                v = "";
            for (var i=0, l=nodes.length; i<l; i++) {
              v += nodes[i].id + ",";
            }
            if (v.length > 0 ) v = v.substring(0, v.length-1);

            $('#moveTree').data('choose',v);
          }
        }
      };


      commonApp.dialog.default(['移动到栏目：','<ul id="moveTree" class="ztree"><li>loading...</li></ul>'],{
        onShow:function () {
          $.fn.zTree.init($("#moveTree"), setting, categoryList);
        },
        confirm:function () {
          var _c = $('#moveTree').data('choose');
          if(_c){

            commonApp.fieldUpdateRequest($a,{
              //type:'POST',
              data:{id:_id.join(','),cid:_c},
              successTxt:'操作成功，页面将在2秒后刷新！',
              successCallback:function (response) {
                setTimeout(function () {
                  parent.indexApp.frameLoading('页面刷新中，请稍后');
                  history.go(0);
                },1500);
              }
            });
          }
        },
        cancel:function () {
          $.fn.zTree.destroy("moveTree");
        }
      });

      return false;
    });
  };

  /**
   * dialog 弹出框下单选
   * 父级#dialog-iframe 的属性 data-choose 为已选择数据（数据格式为：{'id'=>1,……}）。data-checked 表示data-choose数据在iframe中是否选中
   * iframe页面中radio元素必须有data-value属性用于提取数据
   * @private
   */
  var _dialogChooseRadio = function(){
    var $parent = $(parent.document).find('#'+ $(self).attr('name')),
      chosenData = $parent.data('choose'),
      $radio = $('#list_data :radio');

    // 初始化
    if(chosenData && $parent.data('checked')){
      $radio.filter('[value="'+chosenData.id+'"]').attr('checked',true);
    }

    // 记录选中项
    $radio.change(function(){
      $parent.attr('data-choose',$(this).attr('data-choose'));
    });

    // 离开页面触发框架loading效果
    $(window).bind('beforeunload',function(){
      parent.commonApp.dialog.iframeLoading($parent);
    });
  };

  return {
    init: function () {
      _initFun();
      _paginationGoFun();
      _fieldUpdate();
      _sortFun();
      _moveFun();
    },
    dialog:{
      chooseRadio:_dialogChooseRadio
    }
  }
}();