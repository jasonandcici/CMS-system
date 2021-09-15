/**
 * @copyright
 * @link 
 * @author 朱言俊
 * @create Created on 2017/10/19
 */
'use strict';


/**
 * 智能编辑器 coralueditor
 *
 * @author 
 * @since 1.0
 */
var coralUeditor = function () {
    var // 插件名
        plugName = 'coralueditor',

        // 基础路径
        basePath = getBasePath(),

        // 配置项
        config = {
            ueditorServerUrl:'',
            // 资源配置
            localSourceUrl:null,
            localSourceBatch:{
                url:null,
                data:{},
                beforeOperationCallback:null,
                afterOperationCallback:null
            },

            localCategoryUrl:null,
            localCategoryBatch:{
                url:null,
                data:{},
                beforeOperationCallback:null,
                afterOperationCallback:null
            },

            remoteSourceUrl:null,
            remoteCategoryUrl:null,

            // 请求资源回掉
            requestSourceCallback:null,
            requestCategoryCallback:null,

            // 正文宽度
            contentWidth:['100%'],

            // 素材颜色列表
            colors: ['#da251c', '#e15518', '#e5881e', '#f8c301', '#fff500', '#9f2925', '#dd127b', '#e5abc3', '#3d2c7c', '#01458e', '#0193de', '#5dbbc3', '#a11c79', '#90c81f', '#00923f', '#711c77', '#ffffff', '#999999', '#000000', 'multicolour'],

            // 是否启用“插入编辑框”工具
            enableAreaTool:true
        },

        // 模板引擎，为lodash或artTemplate
        toolsTpl = {},

        // 本插件弹出框的jquery对象
        $dkEditor,

        // 本插件弹出框中的ueditor对象
        coralUeditor,

        // 上传弹出框中的ueditor
        uploadEditor,
        $uploadTrigger,

        // 模板变量 compileTpl() 方法为其赋值
        tpls= {},

        saveTplDialog;

    /**
     * 初始化
     */
    var _initFun = function () {
        var $body = $('body');
        if($body.data(plugName)) return true;
        $body.data(plugName,true);

        // 注册插件
        if(config.enableAreaTool) registerEditorAreaTool();

        UE.registerUI(config.plugName,function(editor,uiName){
            return new UE.ui.Button({
                name:uiName,
                title:'智能编辑模式',
                cssRules:'background-position: 1px 1px;background-image:url("'+basePath+'images/icons.png");',
                onclick:function () {
                    // 设置编辑器层级，防止fullscreen影响
                    var $ue = $(editor.container),
                        oldZIndex = $ue.css('z-index');
                    $ue.css('z-index',-1);

                    // 初始化智能编辑器模板
                    $dkEditor = $('#'+plugName+'-wrapper');
                    if($dkEditor.length < 1){
                        $dkEditor = $('<div id="'+plugName+'-wrapper" class="'+plugName+'-wrapper">').appendTo($body).show();
                        $body.addClass(plugName+'-body');

                        loading($dkEditor,'系统操作中...',plugName+'-wrapper-main-loading');
                        $.get(basePath+plugName+'.html',function (res) {
                            var render = toolsTpl.template.compile(res);
                            $dkEditor.append(render(config));
                            $('#'+plugName+'-wrapper-main-loading').fadeOut(300,function () {
                                $(this).remove();
                            });

                            // 主导航绑定tab
                            var saveTplBtn = $('#js-'+plugName+'-save-tpl');
                            $dkEditor.find('.'+plugName+'-left-nav a').click(function () {
                                var $this = $(this);
                                $this.parent().addClass('active').siblings().removeClass('active');
                                var $container = $($this.data('target')).addClass('active');
                                $container.siblings().removeClass('active');

                                if($this.data('type') === 'local'){
                                    saveTplBtn.show();
                                }else{
                                    saveTplBtn.hide();
                                }

                                initCategory($this,$container);
                                initSource($container);
                                return false;
                            }).first().trigger('click');


                            // 渲染智能编辑器
                            var tools = window.UEDITOR_CONFIG.toolbars[0];
                            $.each(tools,function (i, n) {
                                if(n === plugName || n=== 'fullscreen') tools.splice(i,1);
                            });
                            if(config.enableAreaTool) tools.push(plugName+'editorarea');

                            var $mainInner = $('#'+plugName+'-main-inner'),
                                $btnClose = $('#js-'+plugName+'-close');
                            UE.getEditor(plugName+'-main-content',{
                                serverUrl:config.ueditorServerUrl,
                                toolbars:[tools],
                                autoFloatEnabled:false,
                                autoHeightEnabled:false,
                                initialFrameHeight:1000,
                                iframeJsUrl:basePath+'css/iframe.js?plugName='+plugName
                            }).ready(function() {
                                coralUeditor = this;

                                registerEditorAreaPopup(coralUeditor);

                                $dkEditor.find('.'+plugName+'-left-nav .active').trigger('click');

                                //正文宽度切换
                                var $iframeholder = $dkEditor.find('.edui-editor-iframeholder'),
                                    $toolbarbox = $dkEditor.find('.edui-editor-toolbarbox');
                                $('#'+plugName+'-content-width').find('a').click(function () {
                                    var $this = $(this);
                                    $this.addClass('active').siblings().removeClass('active');
                                    $mainInner.css('width',$this.data('width'));

                                    $iframeholder.height($mainInner.height() - $toolbarbox.height() - 23);
                                });
                                setEditorHeight($iframeholder,$mainInner,$toolbarbox);

                                //确定按钮的绑定
                                $('#js-'+plugName+'-confirm').click(function () {
                                    editor.setContent(coralUeditor.getContent());
                                    $btnClose.trigger('click');
                                });

                                // 存储为模板
                                saveTplBtn.click(function () {
                                    if(typeof saveTplDialog === 'undefined'){
                                        registerSaveTplDialog(coralUeditor,function (dialog) {
                                            saveTplDialog = dialog;
                                            saveTplDialog.title = "存储为模板";
                                            saveTplDialog.operation = 'create';
                                            saveTplDialog.content = tpls.saveSource($.extend({},config,{data:{id:'',title:'',category_id:'',thumb:'',color:'',tags:''}}));
                                            saveTplDialog.render();
                                            saveTplDialog.open();
                                        });
                                    }else{
                                        saveTplDialog.title = "存储为模板";
                                        saveTplDialog.operation = 'create';
                                        saveTplDialog.content = tpls.saveSource($.extend({},config,{data:{id:'',title:'',category_id:'',thumb:'',color:'',tags:''}}));
                                        saveTplDialog.render();
                                        saveTplDialog.open();
                                    }
                                    return false;
                                });
                            });

                            // 关闭
                            $btnClose.click(function () {
                                $dkEditor.hide();
                                $body.removeClass(plugName+'-body');
                                $ue.css('z-index',oldZIndex);
                            });
                        });
                    }

                    // 显示智能编辑器
                    var timer = setInterval(function(){
                        if(typeof coralUeditor !== 'undefined'){
                            clearInterval(timer);
                            $dkEditor.show();
                            $body.addClass(plugName+'-body');
                            // 初始化编辑器内容
                            coralUeditor.setContent(editor.getContent());
                        }
                    },10);
                }
            });
        });

        // 图片上传
        $body.append('<textarea id="'+plugName+'-uploads-ueditor" style="height: 50px;display: none;"></textarea>');
        UE.getEditor(plugName+'-uploads-ueditor', {
            serverUrl:config.ueditorServerUrl,
            isShow: false,
            focus: false,
            enableAutoSave: false,
            autoSyncData: false,
            autoFloatEnabled: false,
            wordCount: false,
            sourceEditor: null,
            scaleEnabled: true,
            toolbars: [["insertimage"]]
        })
            .ready(function () {
            uploadEditor = this;
            uploadEditor.addListener("beforeInsertImage", function (t, result) {
                if(result.length < 1) return;

                $uploadTrigger.find('.upload_input').val(result[0].src);
                $uploadTrigger.find('.upload_list').html('<li><div class="left"><div class="pic-wraper"><div class="pic"><div class="inner"><a class="upload_preview" href="javascript:;"><img src="'+result[0].src+'" alt="'+result[0].alt+'"></a></div></div></div></div><div class="opt"><a class="btn btn-xs btn-link upload_del" href="javascript:;">删除图片</a></div></li>');
                $uploadTrigger.find('.upload_btn').hide();
            });

            $body.on('click','.js-'+plugName+'-upload .upload_btn',function () {
                var _dialog = uploadEditor.getDialog("insertimage"),
                 $mask = $('#'+_dialog.modalMask.id);
                var _maskIsVisible = $mask.is(':visible');
                _dialog.render();
                _dialog.open();
                _dialog.addListener('hide',function () {
                    if(_maskIsVisible){
                        $mask.addClass(plugName+'-show');
                        setTimeout(function () {
                            $mask.show().removeClass(plugName+'-show');
                        },100);
                    }
                });
                $uploadTrigger = $(this).parents('.js-'+plugName+'-upload');
            });

            $body.on('click','.js-'+plugName+'-upload .upload_del',function () {
                var _trigger = $(this).parents('.js-'+plugName+'-upload');
                _trigger.find('.upload_input').val('');
                _trigger.find('.upload_list').empty();
                _trigger.find('.upload_btn').fadeIn();
            });
        });
    };

    /**
     * 初始化素材分类
     * @param $container
     * @param $tab
     * @param callback
     */
    var requestingCategory = {req0:false,req1:false,req3:false,req4:false};
    function initCategory($tab,$container) {
        if($container.data('initCategory')) return false;
        if(typeof config.localCategoryData === 'undefined'){
            var reqIndex = $tab.parent().index();
            if(requestingCategory['req'+reqIndex]) return false;
            requestingCategory['req'+reqIndex] = true;
            $.get($tab.attr('href'),function (res) {
                if(typeof res === 'string') res = JSON.parse(res);

                if($.isFunction(config.requestCategoryCallback)){
                    res = config.requestCategoryCallback(res, $tab.data('type'));
                }

                if($tab.data('type') === 'local') config.localCategoryData = res;

                $container.find('.'+plugName+'-filter-category').html(tpls.category({list:res}));

                $container.data('initCategory',true);
                requestingCategory['req'+reqIndex] = false;
            });
        }else{
            $container.find('.'+plugName+'-filter-category').html(tpls.category({list:config.localCategoryData}));
            $container.data('initCategory',true);
        }
    }

    /**
     * 初始化素材资源
     * @param $container
     * @returns {boolean}
     */
    var requestingSource = {req0:false,req1:false,req3:false,req4:false};
    function initSource($container) {
        if($container.data('initSource')) return false;
        var $filterForm = $container.find('.'+plugName+'-filter').first(),
            $filterContent = $container.find('.'+plugName+'-filter-content-main').first(),
            $filterLoading = $container.find('.'+plugName+'-filter-pager').first(),
            index = $container,
            $categoryInput = $container.find('.'+plugName+'-filter-category-input').first(),
            reqFun = function (page,callback) {
                if(!requestingSource['req'+index]){
                    requestingSource['req'+index] = true;
                    if(typeof page === 'undefined') page = $filterLoading.data('page');
                    getSource($filterForm,$filterContent,$filterLoading,page,function ($f,$c,$l,p) {
                        requestingSource['req'+index] = false;
                        if($.isFunction(callback)) callback($f,$c,$l,page,p);
                    })
                }
            };

        // 初始化资源
        reqFun(1,function ($f,$c,$l) {
            $container.data('initSource',true);

            $filterForm.removeClass(plugName+'-filter-disabled').submit(function () {
                getSource($f,$c,$l,1);
                return false;
            });

            $container.on('click','.'+plugName+'-filter-category a',function () {
                var $this = $(this);
                $this.parent().addClass('active').siblings().removeClass('active');
                $categoryInput.val($this.data('id'));
                $filterForm.trigger('submit');
                return false;
            });

            $container.find('.'+plugName+'-filter-color :radio').change(function () {
                $filterForm.trigger('submit');
            });

            $filterForm.bind('reset',function () {
                $container.find('.'+plugName+'-filter-category>.active').add($container.find('.'+plugName+'-filter-color>.active'))
                    .removeClass('active');
                $categoryInput.val('');
                setTimeout(function () {
                    $filterForm.trigger('submit')
                },50);
            });
        });

        // 绑定加载资源事件
        $container.find('.'+plugName+'-filter-content:first').on('scroll', function(e){
            var panel = this;
            if (panel.scrollHeight - (panel.offsetHeight + panel.scrollTop) < 30) {
                reqFun();
            }
        });

        $filterLoading.find('a').click(function () {
            reqFun();
        });

        // 插入资源
        $filterContent.on('click','li',function () {
            coralUeditor.execCommand('inserthtml', $(this).data('tpl'));
        });
    }

    /**
     * 获取素材资源
     * @param $filterForm
     * @param $filterContent
     * @param $filterLoading
     * @param page
     * @param callback
     * @returns {boolean}
     */
    function getSource($filterForm,$filterContent,$filterLoading,page,callback) {
        if(!page){
            if($.isFunction(callback)) callback();
            return false;
        }

        if(typeof page === 'undefined') page = 1;
        var reqData = getFormData($filterForm);
        reqData.page = page;
        var $content = $filterForm.parent(),
            type = $content.data('type'),
            $filterWrapper = $filterContent.parent();
        $.ajax({
            url: $filterForm.attr('action'),
            data: reqData,
            dataType:'json',
            beforeSend:function () {
                if(page === 1){
                    loading($content,'系统操作中...');
                    $filterLoading.hide();
                }else{
                    $filterLoading.addClass('filter-loading-gif').show();
                }
            },
            success: function (res) {
                if($.isFunction(config.requestSourceCallback)){
                    res = config.requestSourceCallback(res,type);
                }

                var $li = [];
                $.each(res.items,function (i,n) {
                    n.type = type;
                    var $liTmp = $(tpls.source(n));
                    $liTmp.attr('id',plugName+'-source'+'-'+type+'-'+n.id);
                    if(type === 'local'){
                        n.tpl = n.content;
                        $liTmp.data(n);
                    }else{
                        $liTmp.data({'id':n.id,'tpl':n.content});
                    }
                    // 删除模板
                    $liTmp.find('a.item-del').click(function (e) {
                        var $this = $(this);
                        if(confirm('您确定要删除此模板吗？')){
                            $.ajax({
                                url:setUrlParam(config.localSourceBatch.url,'type','delete'),
                                type:'post',
                                data:$.extend({},{'data[id]':$liTmp.data('id')},config.localSourceBatch.data),
                                dataType:'json',
                                beforeSend:function () {
                                    loading($content,'系统操作中...');
                                },
                                success:function (res) {
                                    if(res.status){
                                        $liTmp.fadeOut(function () {
                                            $liTmp.remove();
                                        });
                                    }else{
                                        alert(res.message);
                                    }
                                },
                                complete:function () {
                                    $content.find('.'+plugName+'-loading').remove();
                                },
                                error:function () {
                                    alert('操作失败。');
                                }
                            });
                        }
                        e.stopPropagation();
                    });

                    // 编辑模板
                    $liTmp.find('a.item-edit').click(function (e) {
                        if(typeof saveTplDialog === 'undefined'){
                            registerSaveTplDialog(coralUeditor,function (dialog) {
                                saveTplDialog = dialog;
                                saveTplDialog.title = "编辑模板信息 <small style='font-weight: normal;color: #666;'>(不会修改模板内容)</small>";
                                saveTplDialog.operation = 'update';
                                saveTplDialog.content = tpls.saveSource($.extend({},config,{data:$liTmp.data()}));
                                saveTplDialog.render();
                                saveTplDialog.open();
                            });
                        }else{
                            saveTplDialog.title = "编辑模板信息 <small style='font-weight: normal;color: #666;'>(不会修改模板内容)</small>";
                            saveTplDialog.operation = 'update';
                            saveTplDialog.content = tpls.saveSource($.extend({},config,{data:$liTmp.data()}));
                            saveTplDialog.render();
                            saveTplDialog.open();
                        }
                        e.stopPropagation();
                    });

                    $li = $li.length < 1?$liTmp:$li.add($liTmp);
                });

                if(res.totalCount < 1) $li = '<li class="disabled">没有找到数据。</li>';

                if(page===1){
                    $filterWrapper.scrollTop(0);
                    $filterContent.html($li);
                }else{
                    $filterContent.append($li);
                }

                if(res.currentPage < res.pageCount){
                    $filterLoading.data('page',res.currentPage+1).show();
                }else{
                    $filterLoading.data('page',0).hide();
                }

                if(res.totalCount > 0){
                    $li.find('img').lazyload({
                        container:$filterWrapper,
                        effect : "fadeIn"
                    });
                }

            },
            error:function () {
              alert('请求资源失败。');
            },
            complete:function () {
                if(page === 1){
                    $content.find('.'+plugName+'-loading').remove();
                }else{
                    $filterLoading.removeClass('filter-loading-gif');
                }
                if($.isFunction(callback)) callback($filterForm,$filterContent,$filterLoading,page);
            }
        });
    }

    /**
     * loading
     */
    function loading($e,txt,id) {
        if(typeof txt === 'undefined') txt = '数据加载中...';
        var _$loading = $('<div class="'+plugName+'-loading"'+(typeof id === 'undefined'?'':'id="'+id+'"')+'><div class="'+plugName+'-loading-bg"></div><div class="'+plugName+'-loading-content"><div class="'+plugName+'-loading-icon"></div><div class="'+plugName+'-loading-text">'+txt+'</div></div></div>');
        $e.append(_$loading);
        return _$loading;
    }

    /**
     * 设置编辑器高度
     * @param a
     * @param b
     * @param c
     */
    function setEditorHeight(a, b, c) {
        var drag = true,
            temp = function () {
                if (drag) {
                    a.height(b.height() - c.height() - 23);
                    drag = false;
                    setTimeout(function () {
                        drag = true;
                    },10);
                }
            };
        temp();
        $(window).resize(temp);
    }

    /**
     * 表单数据转换为 键=>值
     * @param $form
     * @returns {{}}
     */
    function getFormData($form){
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
    }

    /**
     * 设置url参数
     * @param url
     * @param name
     * @param value
     * @returns {*}
     */
    function setUrlParam (url,name,value){
        var tmpUrl = url.split('?');
        var _search = (typeof tmpUrl[1] ==='undefined'?'':tmpUrl[1]);
        if(_search){
            var _searches = _search.split('&'),
                _exit = false;
            for (var i=0;i<=_searches.length;i++){
                if(typeof _searches[i] !== 'undefined'){
                    var _tmp = _searches[i].split('=');
                    if(_tmp[0] === name){
                        url = url.replace(_searches[i],_tmp[0]+'='+value);
                        _exit = true;
                        break;
                    }
                }
            }
            if(!_exit) url = url+'&'+name+'='+value;
        }else{
            url = url+(url.indexOf('?')>-1?'':'?')+name+'='+value;
        }
        return url;
    }

    /**
     * 编译模板
     */
    function compileTpl() {
        tpls.category = toolsTpl.template.compile('<% for(var i in list){ %>' +
            '<li <%=list[i].child.length > 0?" class=\'dropdown current\'":"" %>>' +
            '<% if(list[i].child.length > 0){ %>' +
            '<a href="javascript:;" data-id="<%=list[i].id%>" class="dropdown-toggle"><%=list[i].title%> <span class="caret"></span></a>%>' +
            '<ul class="dropdown-menu">' +
            '<% for(var c in list[i].child){ %>' +
            '<li><a href="javascript:;" data-id="<%=list[i].child[c].id%>"><%=list[i].child[c].title%></a></li>' +
            '<% } %>' +
            '</ul>' +
            '<% }else{ %>' +
            '<a href="javascript:;" data-id="<%=list[i].id%>"><%=list[i].title%></a>' +
            '<% } %>' +
            '</li>' +
            '<% } %>');

        tpls.source = toolsTpl.template.compile('<li>' +
            '<img src ="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH/C1hNUCBEYXRhWE1QPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNS42LWMwNjcgNzkuMTU3NzQ3LCAyMDE1LzAzLzMwLTIzOjQwOjQyICAgICAgICAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtbG5zOnhtcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDoxMTUwRkMzN0JBMkUxMUU3OTA4RUE3MDQyNkFBRjMwMCIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDoxMTUwRkMzNkJBMkUxMUU3OTA4RUE3MDQyNkFBRjMwMCIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ0MgMjAxNSAoV2luZG93cykiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpCREYwMEJFOEJBMkMxMUU3QThCNkQ1MTM0NURCNDYyNCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpCREYwMEJFOUJBMkMxMUU3QThCNkQ1MTM0NURCNDYyNCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PgH//v38+/r5+Pf29fTz8vHw7+7t7Ovq6ejn5uXk4+Lh4N/e3dzb2tnY19bV1NPS0dDPzs3My8rJyMfGxcTDwsHAv769vLu6ubi3trW0s7KxsK+urayrqqmop6alpKOioaCfnp2cm5qZmJeWlZSTkpGQj46NjIuKiYiHhoWEg4KBgH9+fXx7enl4d3Z1dHNycXBvbm1sa2ppaGdmZWRjYmFgX15dXFtaWVhXVlVUU1JRUE9OTUxLSklIR0ZFRENCQUA/Pj08Ozo5ODc2NTQzMjEwLy4tLCsqKSgnJiUkIyIhIB8eHRwbGhkYFxYVFBMSERAPDg0MCwoJCAcGBQQDAgEAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" data-original="<%=thumb%>" alt="<%=title%>">' +
            '<div class="'+plugName+'-filter-content-hot" title="<%=title%>"></div>' +
            '<div class="'+plugName+'-filter-content-title"><%=title%></div>'+
            '<% if(type === "local"){ %><div class="'+plugName+'-filter-content-opt"><a class="item-edit" href="javascript:;">编辑</a><a class="item-del" href="javascript:;">删除</a></div><% } %>'+
            '<% if(type === "remote"){ %><div class="like"><a href="#" class="'+plugName+'-icon '+plugName+'-icon-like active" title="收藏"></a></div><% } %>' +
            '</li>');

        tpls.saveSource = toolsTpl.template.compile('<form action="javascript:;" method="post" style="width:500px;padding:10px;">' +
            '<% if(data.id){%> <input type="hidden" name="data[id]" value="<%=data.id%>"> <% }%>'+
            '<div class="form-group"><label>标题</label><input type="text" class="form-control" name="data[title]" required value="<%=data.title%>"></div>'+
            '<div class="form-group"><label>类别</label><select class="form-control" name="data[category_id]" required><option value="">请选择</option>' +
                '<% for(var i in localCategoryData){ %>' +
                    '<option value="<%=localCategoryData[i].id%>" <%=localCategoryData[i].id == data.category_id?" selected":""%>><%=localCategoryData[i].title%></option>'+
                    '<% if(localCategoryData[i].child.length > 0){ %>' +
                        '<% for(var c in localCategoryData[i].child){ %>' +
                        '<option value="<%=localCategoryData[i].child[c].id%>" <%=localCategoryData[i].child[c].id == data.category_id?" selected":""%>>&nbsp;— <%=localCategoryData[i].child[c].title%></option>'+
                        '<% } %>' +
                '<% }} %>' +
            '</select></div><div class="clearfix">'+
            '<div class="form-group pull-left" style="width:20%;"><label>预览图</label><div class="list-img js-'+plugName+'-upload clearfix"><input type="hidden" class="upload_input" name="data[thumb]" value="<%=data.thumb%>"><ul class="upload_list">' +

            '<% if(data.thumb){%><li><div class="left"><div class="pic-wraper"><div class="pic"><div class="inner"><a class="upload_preview" href="javascript:;"><img src="<%=data.thumb%>"></a></div></div></div></div><div class="opt"><a class="btn btn-xs btn-link upload_del" href="javascript:;">删除图片</a></div></li><% } %>'+

            '</ul><a class="upload upload_btn" href="javascript:;" <%=data.thumb?\' style=display:none;\':""%>><span class="iconfont"></span></a></div></div>'+
            '<div class="form-group pull-right" style="width:75%;"><label>颜色</label><div class="btn-group <%=plugName%>-filter-color clearfix" data-toggle="buttons" style="width: 100%;margin-bottom: 0;">' +
                '<% for(var i in colors){ %><% if(colors[i] !== "multicolour"){%><label class="btn<%=colors[i]==data.color?" active":""%> btn-primary<%=colors[i] == "#ffffff"?" "+plugName+"-color-fff":"" %>" style="background-color: <%=colors[i]%>;"><input type="radio" name="data[color]" autocomplete="off" value="<%=colors[i]%>"><%=colors[i]%></label><% }else{ %> <label class="btn btn-primary <%=plugName%>-color-multicolour" title="其他"><input<%=colors[i]==data.color?" checked":""%> type="radio" name="data[color]" autocomplete="off" value="<%=colors[i]%>"><%=colors[i]%></label><% }} %>'+
            '</div></div></div>'+
            '<div class="form-group"><label>标签</label><input type="text" class="form-control js-'+plugName+'-tags" name="data[tags]" value="<%=data.tags%>"></div>'+
            '</form>');
    }

    /**
     *  注册编辑框工具
     */
    function registerEditorAreaTool() {
        UE.registerUI(plugName+'editorarea',function(editor,uiName){
            return new UE.ui.Button({
                name:uiName,
                title:'插入编辑框',
                cssRules:'background-position: -29px 1px;background-image:url("'+basePath+'images/icons.png");',
                onclick:function () {
                    editor.execCommand('inserthtml', '<section class="'+plugName+'"><P>请输入内容</P></section>');
                }
            });
        });
    }

    /**
     * 注册编辑器popup
     * @param editor
     */
    function registerEditorAreaPopup(editor) {
        var popup = new UE.ui.Popup({
            editor: editor,
            content: "",
            className: "edui-bubble",
            // 向前插入段落
            _insertParagraphFront: function () {
                editor.undoManger.save();
                $("<p><br/></p>").insertBefore(popup.anchorEl)
            },
            // 向后插入段落
            _insertParagraphBehind: function () {
                editor.undoManger.save();
                $("<p><br/></p>").insertAfter(popup.anchorEl)
            },
            // 删除
            _delete: function () {
                editor.undoManger.save();
                var t = popup.anchorEl;
                editor.selection.getRange().selectNode(t).deleteContents();
                popup.hide();
            },
            // 复制
            _copy:function () {

            },
            // 剪切
            _cut:function () {

            },
            // 上移
            _moveUp:function () {

            },
            // 下移
            _moveDown:function () {

            },
            // 设置背景
            _setBackground :function () {

            },
            // 设置样式
            _setStyle:function () {

            },
            // 隐藏popup
            hide: function(notNofity) {
                if (!this._hidden && this.getDom()) {
                    this.getDom().style.display = "none";
                    this._hidden = true;
                    // 删除选择框
                    $(editor.document).find('.'+plugName+'-selected').remove();

                    if (!notNofity) {
                        this.fireEvent("hide");
                    }
                }
            }
        });
        popup.render();
        editor.addListener("selectionchange", function(t, causeByUi) {
            if (!causeByUi) return;
            var dialogs = editor.ui._dialogs;
            if(!editor.selection.getRange().getClosedNode() && "br" !== editor.selection.getStart().tagName){
                var els = editor.selection.getStartElementPath();
                for (var i = 0; i < els.length; i++){
                    if ("SECTION" === els[i].tagName && $(els[i]).hasClass(plugName)) {
                         var html = popup.formatHtml(
                            '<nobr>' +
                            /*'<span onclick="$$._insertParagraphFront();" class="edui-clickable">复制</span>&nbsp;'+
                            '<span onclick="$$._insertParagraphFront();" class="edui-clickable">剪切</span>&nbsp;'+*/
                            '<span onclick="$$._delete();" class="edui-clickable">删除</span>&nbsp;'+
                            '<span onclick="$$._insertParagraphFront();" class="edui-clickable">前空行</span>&nbsp;'+
                            '<span onclick="$$._insertParagraphBehind();" class="edui-clickable">后空行</span>'+
                            /*'<span onclick="$$._moveUp();" class="edui-clickable">上移</span>&nbsp;'+
                            '<span onclick="$$._moveDown();" class="edui-clickable">下移</span>&nbsp;'+
                            '<span onclick="$$._setStyle();" class="edui-clickable">背景</span>&nbsp;'+
                            '<span onclick="$$._setBackground();" class="edui-clickable">样式</span>'+*/
                            '</nobr>'
                        );

                        if (html) {
                            popup.getDom("content").innerHTML = html;
                            popup.anchorEl = els[i];
                            popup.showAnchor(popup.anchorEl);
                            // 插入选择框
                            var $el = $(popup.anchorEl);
                            if($el.children('.'+plugName+'-selected').length < 1){
                                $el.prepend('<section class="'+plugName+'-selected">' +
                                    '<section class="'+plugName+'-selected-top"></section>' +
                                    '<section class="'+plugName+'-selected-bottom"></section>' +
                                    '<section class="'+plugName+'-selected-top-left"></section>' +
                                    '<section class="'+plugName+'-selected-top-right"></section>' +
                                    '<section class="'+plugName+'-selected-bottom-left"></section>' +
                                    '<section class="'+plugName+'-selected-bottom-right"></section>' +
                                    '<section class="'+plugName+'-selected-left"></section>' +
                                    '<section class="'+plugName+'-selected-right"></section>' +
                                    '<section class="'+plugName+'-selected-left-bottom"></section>' +
                                    '<section class="'+plugName+'-selected-left-top"></section>' +
                                    '<section class="'+plugName+'-selected-right-bottom"></section>' +
                                    '<section class="'+plugName+'-selected-right-top"></section>' +
                                    '</section>')
                            }
                        } else {
                            popup.hide();
                        }


                        break;
                    }
                }
            }
        });
    }

    /**
     * 注册 保存为模板弹出框
     * 注册的dialog在拖拽时可能会报错，需要找到报错点（共两处） 加上if({编辑器实例}.getDom("contmask") != null) ... 即可
     * @param editor
     * @param callback
     * @returns {baidu.ui.editor.Dialog}
     */
    function registerSaveTplDialog(editor,callback) {
        var _fun = function () {
            var dialog =new UE.ui.Dialog({
                editor:editor,
                name:plugName,
                title:"模板",
                className:plugName+'-dialog '+plugName+'-save-tpl-dialog',
                content:'',
                buttons:[
                    {
                        className:'edui-okbutton',
                        label:'确定',
                        onclick:function () {
                            if(!dialog.operation) dialog.operation = 'create';
                            var $form = $('.'+plugName+'-save-tpl-dialog').find('form');
                            var data = getFormData($form);
                            if($.isFunction(config.localSourceBatch.beforeOperationCallback)){
                                data = config.localSourceBatch.beforeOperationCallback(data,dialog.operation);
                            }

                            // 验证
                            var checkString = '',isEmptyColor = true;
                            $.each(data,function (i,n) {
                                if($.inArray(i,['data[title]','data[category_id]','data[thumb]']) !== -1 && UE.utils.isEmptyObject(n)){
                                    checkString +='“'+{'data[title]':'标题','data[category_id]':'类别','data[thumb]':'预览图'}[i]+'”不能为空。';
                                }
                                if(i === 'data[color]') isEmptyColor = false;
                            });
                            if(isEmptyColor) checkString += '“颜色”不能为空。';
                            if(!UE.utils.isEmptyObject(checkString)){
                                alert(checkString);
                                return false;
                            }

                            if(dialog.operation === 'create'){
                                var newCnt = '';
                                $(coralUeditor.getContent()).each(function (i,n) {
                                    if(!UE.dom.domUtils.isEmptyNode(n)){
                                        newCnt += n.outerHTML;
                                    }
                                });

                                if(!newCnt){
                                    alert('内容不能为空。');
                                    return false;
                                }
                                data['data[content]'] = newCnt;
                            }

                            $.ajax({
                                url: setUrlParam(config.localSourceBatch.url,'type',dialog.operation),
                                type:'POST',
                                dataType:'json',
                                data:$.extend({},data,config.localSourceBatch.data || {}),
                                beforeSend:function () {
                                    loading($form,'系统操作中...',plugName+'-req-savetpl-loading');
                                },
                                success: function (res) {
                                    if(res.status){
                                        if(dialog.operation === 'update'){
                                            var $li = $('#'+plugName+'-source-local-'+data['data[id]']);
                                            $li.data({
                                                title:data['data[title]'],
                                                category_id:data['data[category_id]'],
                                                color:data['data[color]'],
                                                tags:data['data[tags]'],
                                                thumb:data['data[thumb]']
                                            }).find('.coralueditor-filter-content-title').text(data['data[title]']);
                                            $li.find('.coralueditor-filter-content-hot').attr('title',data['data[title]']);
                                        }else{
                                            $('#'+plugName+'-tab-local').find('button:reset').trigger('click');
                                        }
                                    }
                                    if($.isFunction(config.localSourceBatch.afterOperationCallback)){
                                        config.localSourceBatch.afterOperationCallback(res,dialog.operation,dialog);
                                    }else{
                                        if(res.status){
                                            alert('操作成功！');
                                        }else{
                                            alert(res.message);
                                        }
                                        dialog.close(true);
                                    }
                                },
                                error:function () {
                                    alert('操作失败。');
                                },
                                complete:function () {
                                    $('#'+plugName+'-req-savetpl-loading').remove();
                                }
                            });
                        }
                    },
                    {
                        className:'edui-cancelbutton',
                        label:'取消',
                        onclick:function () {
                            dialog.close(false);
                        }
                    }
                ]});

            dialog.addListener('show',function () {
                $('.js-'+plugName+'-tags').tagsinput({trimValue: true});
            });

            dialog.addListener('close',function () {
                $('.js-'+plugName+'-tags').tagsinput('destroy');
            });

            return dialog;
        };

        if(typeof config.localCategoryData === 'undefined'){
            $.ajax({
                url: config.localCategoryUrl,
                dataType:'json',
                beforeSend:function () {
                    loading($dkEditor,'系统操作中...',plugName+'-req-category-loading');
                },
                success: function (res) {
                    if($.isFunction(config.requestCategoryCallback)){
                        res = config.requestCategoryCallback(res);
                    }
                    config.localCategoryData = res;
                    if($.isFunction(callback)) callback(_fun());
                },
                error:function () {
                    alert('请求资源失败。');
                },
                complete:function () {
                    $('#'+plugName+'-req-category-loading').remove();
                }
            });
        }else{
            if($.isFunction(callback)) callback(_fun());
        }
    }

    /**
     * 获取当前文件路径
     * @returns {*}
     */
    function getBasePath (){
        var _currJsPath,
            _js=document.scripts;
        for(var i=_js.length;i>0;i--){
            if(_js[i-1].src.indexOf(plugName+'.js')>-1 || _js[i-1].src.indexOf(plugName+'.min.js')>-1){
                _currJsPath = _js[i-1].src.substring(0,_js[i-1].src.lastIndexOf("/")+1);
                break;
            }
        }
        return _currJsPath;
    }

    /**
     * 检测第三方插件是否齐全
     */
    function _checkThirdParty(conf,callback) {
        var basePath = getBasePath();

        $("<link>").attr({ rel: "stylesheet", type:"text/css", href: basePath+'css/'+plugName+'.css'}).appendTo("head");

        var btn = false;
        if(typeof jQuery.fn.button === 'undefined'){
            $.getScript(basePath+"third-party/bootstrap.button.js").complete(function(){
                btn = true;
            });
        }else{
            btn = true;
        }

        if(typeof _ !== 'undefined'){
            toolsTpl = _;
        }else if (typeof template !== 'undefined'){
            toolsTpl.template = template;
        }else{
            $.getScript(basePath+"third-party/lodash.min.js").complete(function(){
                toolsTpl = _;
            });
        }

        var tag = false;
        if(typeof jQuery.fn.button === 'undefined' && conf.localSourceBatch && conf.localSourceBatch.url && conf.localCategoryUrl){
            $("<link>").attr({ rel: "stylesheet", type:"text/css", href: basePath+'third-party/bootstrap-tagsinput/bootstrap-tagsinput.css'}).appendTo("head");
            $.getScript(basePath+"third-party/bootstrap-tagsinput/bootstrap-tagsinput.min.js").complete(function(){
                tag = true;
            });
        }else{
            tag = true;
        }

        var lazy = false;
        if(typeof jQuery.fn.lazyload === 'undefined'){
            $.getScript(basePath+"third-party/jquery.lazyload.js").complete(function(){
                lazy = true;
            });
        }else{
            lazy = true;
        }

        var timer = setInterval(function(){
            if(btn && lazy && tag && typeof toolsTpl !== 'undefined'){
                clearInterval(timer);
                callback(conf);
            }
        },10);
    }

    /**
     * 返回
     */
    return {
        init: function(conf,callback){
            if(typeof(jQuery)==="undefined"){
                alert('请引入jquery。');
            }else{
                _checkThirdParty(conf,function (setting) {
                    // 设置默认配置
                    $.extend(config,setting);
                    config.contentWidth = $.merge(['100%'],config.contentWidth);
                    config.plugName = plugName;

                    // 初始化编辑器
                    _initFun();
                    compileTpl();
                    if($.isFunction(callback)) callback(config);
                });
            }
        }
    }
}();