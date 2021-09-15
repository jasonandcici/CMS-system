// +----------------------------------------------------------------------
// | uploadUeditor
// +----------------------------------------------------------------------
// | Copyright (c)
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | Created on 2016/2/23.
// +----------------------------------------------------------------------

/**
 * 基于百度编辑器的上传组件
 * 其中附件上传需要修改源码（注意：UEditor1.4.3.3版本以后无需修改）
 * 需要在ueditor.all.min.js文件中找到d.execCommand("insertHtml",l)这个c可能根据实际情况修改
 * 或ueditor.all.js文件中找到me.execCommand("insertHtml", html)之后插入d.fireEvent('afterinsertfile',c)
 *
 * html结构：
 * <div class="clearfix" id="upload">
 *  <input class="upload_input" type="hidden" name="imgUpload" value='{这里是从数据库读出的值}'>
 *    <ul class="upload_list"></ul>
 *    <a class="upload_btn" href="javascript:;">上传</a>
 *</div>
 *
 * 使用方法：
 * uploadUeditor.multipleImage($('#upload'));
 *
 * uploadUeditor.singleImage($('#upload'));
 *
 * uploadUeditor.multipleAttachment($('#upload'));
 *
 * uploadUeditor.singleAttachment($('#upload'));
 * */

var uploadUeditor = function () {

    var uploadEditor, // ueditor实例
        type, // 上传类型 0：多图上传 1：单图片上传 2：更换图片
        $trigger; // 当前触发的项目

    /**
     * 初始化
     * */
    var _initFun = function (config) {
        if (typeof UE === 'undefined') {
            console.log('没有引入ueditor');
            return false;
        }

        if(typeof config === 'undefined') config = {};

        $('body').append('<textarea id="uploadEditor" style="height: 50px;display: none;"></textarea>');
        uploadEditor = UE.getEditor("uploadEditor", $.extend({},{
            isShow: false,
            focus: false,
            enableAutoSave: false,
            autoSyncData: false,
            autoFloatEnabled: false,
            wordCount: false,
            sourceEditor: null,
            scaleEnabled: true,
            toolbars: [["insertimage", "attachment"]]
        },(typeof config.serverUrl ==='undefined'?{}:{serverUrl:config.serverUrl})));
        uploadEditor.ready(function () {
            uploadEditor.addListener("beforeInsertImage", _beforeInsertImage);
            uploadEditor.addListener("afterinsertfile", _afterUpfile);
        });
        return true;
    };

    // 插入图片监听
    function _beforeInsertImage(t, result) {
        var $eles = getElements($trigger);

        var new_data = JSON.parse($eles.upload_input.val() || '[]');
        if (type == 2) {
            var index = $eles.upload_list.data('index');
            new_data[index].file = result[0].src;
            new_data[index].width = result[0].width;
            new_data[index].height = result[0].height;
            if(!new_data[index].alt){
                $eles.upload_list.find('li:eq(' + index + ') .upload_info').val(result[0].alt);
                new_data[index].alt = result[0].alt;
            }

            $eles.upload_list.find('li:eq(' + index + ') img').attr('src', result[0].src);
        } else {
            if (type == 1 && new_data.length > 0) return;

            var tmpArr = [];
            for (var i in result) {
                var tmp = {
                    alt: result[i].alt,
                    file: result[i].src,
                    width:result[i].width,
                    height:result[i].height
                };
                tmpArr.push(tmp);
                new_data.push(tmp);
                if (type == 1) break;
            }

            $eles.upload_list.append(_createImageHtml(tmpArr, type));
        }

        $eles.upload_input.val(JSON.stringify(new_data));

        if (type == 1) $eles.upload_btn.hide();
    }

    // 插入附件监听
    function _afterUpfile(t, result) {
        var $eles = getElements($trigger);

        var new_data = JSON.parse($eles.upload_input.val() || '[]');

        if (type == 2) {
            var index = $eles.upload_list.data('index');
            new_data[index].file = result[0].url;
            new_data[index].title = result[0].title;
            $eles.upload_list.find('li:eq(' + index + ') .t').attr('href', result[0].url).html(_getAttachmentIcon(result[0].title) + result[0].title);
        } else {
            if (type == 1 && new_data.length > 0) return;

            var tmpArr = [];
            for (var i in result) {
                var tmp = {
                    title: result[i].title,
                    file: result[i].url
                };
                tmpArr.push(tmp);
                new_data.push(tmp);
                if (type == 1) break;
            }

            $eles.upload_list.append(_createAttachmentHtml(tmpArr, type));
        }

        if(new_data.length > 0){
            $eles.upload_input.val(JSON.stringify(new_data));

            if (type == 1) $eles.upload_btn.hide();
        }
    }

    if (!_initFun) return false;

    /**
     * 返回控件元素jq对象
     * @param $wrapper
     * @returns {{upload_btn: *, upload_list: *, upload_input: *}}
     */
    function getElements($wrapper) {
        return {
            upload_btn: $wrapper.find('.upload_btn'),
            upload_list: $wrapper.find('.upload_list'),
            upload_input: $wrapper.find('.upload_input')
        }
    }

    /*********************************************************************************************************************
     * 图片上传初始化
     * @private
     */

        // 单图片上传
    var _singleImage = function ($object) {
            _triggerImageDialog($object, 1, function (dialog) {
                type = 1;
                dialog.iframeUrl = dialog.iframeUrl + '?type=1';
                dialog.title = '单图片上传';
                dialog.isMultiple = false;
                dialog.isUploadPlugin = true;
            });
        };

    // 多图片上传
    var _multipleImage = function ($object) {
        _triggerImageDialog($object, 0, function (dialog) {
            type = 0;
            dialog.title = '多图片上传';
            dialog.isMultiple = true;
            dialog.isUploadPlugin = true;
        });
    };

    // ueditor图片上传组件
    function _triggerImageDialog($object, uploadType, callback) {
        $object.each(function () {
            var $this = $(this),
                $eles = getElements($this);

            //初始化显示图片
            $eles.upload_list.html(_createImageHtml(JSON.parse($eles.upload_input.val() || '[]'), uploadType));

            if (uploadType == 1 && $eles.upload_input.val()) $eles.upload_btn.hide();

            // 弹出框
            $eles.upload_btn.click(function () {
                $trigger = $this;
                var dialog = uploadEditor.getDialog("insertimage");
                callback(dialog);
                dialog.render();
                dialog.open();
            });

            // 删除
            $this.on('click', '.upload_del', function () {
                _uploadDel($(this), $eles.upload_input, $eles.upload_btn);
            });

            // 更换图片
            $this.on('click', '.upload_change', function () {
                var $li = $(this).parents('li'),
                    dialog = uploadEditor.getDialog("insertimage");

                $eles.upload_list.data('index', $li.index());
                type = 2;
                $trigger = $eles.upload_list.parent();
                dialog.iframeUrl = dialog.iframeUrl + '?type=1';
                dialog.title = '更换图片';
                dialog.isMultiple = false;
                dialog.isUploadPlugin = true;
                dialog.render();
                dialog.open();
                commonApp.inFrame(function () {
                    setTimeout(function () {
                        parent.indexApp.frameCloseLoading();
                    },500);
                });
            });

            // 预览
            $this.on('click', '.upload_preview', function () {
                window.open($(this).parents('li').find('img').attr('src'));
            });

            // 更改图片描述
            $this.on('change', '.upload_info', function () {
                var $this = $(this),
                    _index = $this.parents('li').index(),
                    _val = JSON.parse($eles.upload_input.val() || '[]');
                _val[_index].alt = $this.val();
                $eles.upload_input.val(JSON.stringify(_val));
            });

            // 排序
            $this.on('click','.upload_move_up,.upload_move_down',function () {
                var $this = $(this),
                    $li = $this.parents('li'),
                    isUp = $this.hasClass('upload_move_up'),
                    $obj = isUp?$li.prev():$li.next();

                if($obj.size() < 1) return;

                var _val = JSON.parse($eles.upload_input.val() || '[]'),
                    _index = $li.index(),
                    _currData = _val[_index];

                if(isUp){
                    _val[_index] = _val[_index-1];
                    _val[_index-1] = _currData;
                    $obj.before($li.clone());
                }else{
                    _val[_index] = _val[_index+1];
                    _val[_index+1] = _currData;
                    $obj.after($li.clone())
                }
                $li.remove();
                $eles.upload_input.val(JSON.stringify(_val));
            });
        });
    }

    /**
     * 生成图片html
     * @param data
     * @param uploadType
     * @returns {string}
     * @private
     */
    function _createImageHtml(data, uploadType) {
        var _html = '';
        for (var i in data) {
            _html += '<li><div class="left"><div class="pic-wraper"><div class="pic"><div class="inner">' +
                '<a class="upload_preview" href="javascript:;"><img src="' + data[i].file + '" alt="'+data[i].alt+'"></a></div></div></div></div>' +
                '<div class="info-form"><textarea class="form-control upload_info" name="upload_description" maxlength="250" placeholder="图片描述">' + (data[i].alt || '') + '</textarea>' +
                '<div class="opt">' +
                (uploadType==1?'':'<a class="upload_move_down pull-right" href="javascript:;" title="下移"><span class="iconfont">&#xe62d;</span></a><a class="upload_move_up pull-right" href="javascript:;" title="上移"><span class="iconfont">&#xe62e;</span></a>') +
                '<a class="btn btn-xs btn-info upload_change" href="javascript:;">更换图片</a>' +
                '<a class="btn btn-xs btn-link upload_del" data-type="' + uploadType + '" href="javascript:;">删除图片</a></div></div></li>';

            if (uploadType == 1) break;
        }
        return _html;
    }

    /*********************************************************************************************************************
     * 附件上传初始化
     * @private
     */
    var _singleAttachment = function ($object) {
        _triggerAttachmentDialog($object, 1, function (dialog) {
            type = 1;
            dialog.iframeUrl = dialog.iframeUrl + '?type=1';
            dialog.title = '单文件上传';
            dialog.isMultiple = false;
            dialog.isUploadPlugin = true;
        });
    };

    var _multipleAttachment = function ($object) {
        _triggerAttachmentDialog($object, 0, function (dialog) {
            type = 0;
            dialog.title = '多文件上传';
            dialog.isMultiple = true;
            dialog.isUploadPlugin = true;
        });
    };

    // ueditor附件上传组件
    function _triggerAttachmentDialog($object, uploadType, callback) {
        $object.each(function () {
            var $this = $(this),
                $eles = getElements($this);

            //初始化显示附件列表
            $eles.upload_list.html(_createAttachmentHtml(JSON.parse($eles.upload_input.val() || '[]'), uploadType));

            if (uploadType == 1 && $eles.upload_input.val()) $eles.upload_btn.hide();

            // 弹出框
            $eles.upload_btn.click(function () {
                $trigger = $this;
                var dialog = uploadEditor.getDialog("attachment");
                callback(dialog);
                dialog.render();
                dialog.open();
                commonApp.inFrame(function () {
                    setTimeout(function () {
                        parent.indexApp.frameCloseLoading();
                    },500);
                });
            });

            // 删除
            $this.on('click', '.upload_del', function () {
                _uploadDel($(this), $eles.upload_input, $eles.upload_btn);
            });

            // 更换文件
            $this.on('click', '.upload_change', function () {
                var $li = $(this).parents('li'),
                    dialog = uploadEditor.getDialog("attachment");

                $eles.upload_list.data('index', $li.index());
                type = 2;
                $trigger = $eles.upload_list.parent();
                dialog.iframeUrl = dialog.iframeUrl + '?type=1';
                dialog.title = '更换附件';
                dialog.isMultiple = false;
                dialog.isUploadPlugin = true;
                dialog.render();
                dialog.open();
            });

            // 修改信息
            $this.on('click', '.upload_edit', function () {
                var $p = $(this).parents('li'),
                    $t = $p.find('.t'),
                    $n = $t.find('.n'),
                    _title = prompt("请输入文件标题", $n.text());
                if (_title) {
                    $n.html(_title);
                    var _index = $p.index(),
                        _val = JSON.parse($eles.upload_input.val());
                    _val[_index].title = _title;
                    $eles.upload_input.val(JSON.stringify(_val));
                }
            });

        });
    }

    /**
     * 生成附件列表html
     * @param data
     * @param uploadType
     * @returns {string}
     * @private
     */
    function _createAttachmentHtml(data, uploadType) {
        var _html = '';
        for (var i in data) {
            _html += '<li><span class="opt fade"><a class="upload_edit" href="javascript:;">修改</a><a class="upload_change" href="javascript:;">更换</a>' +
                '<a class="upload_del" data-type="' + uploadType + '" href="javascript:;">删除</a></span>' +
                '<a class="t" href="' + data[i].file + '" target="_blank">' + _getAttachmentIcon(data[i].file) + '<span class="n">' + data[i].title + '</span></a> </li>';
            if (uploadType == 1) break;
        }
        return _html;
    }

    /**
     * 获取附件图标
     * @param $file
     * @private
     */
    function _getAttachmentIcon($file) {
        return '<span class="iconfont">&#xe627;</span>';
    }

    /**
     * 删除附件
     * @param $del
     * @param $input
     * @param $btn
     * @private
     */
    function _uploadDel($del, $input, $btn) {
        var $li = $del.parents('li');

        $li.fadeOut(function () {
            var _data = JSON.parse($input.val());
            _data.splice($li.index(), 1);
            $input.val(_data.length > 0 ? JSON.stringify(_data) : '');
            $li.remove();
            if ($del.data('type') == 1) $btn.fadeIn();
        });
    }

    return {
        init:_initFun,
        singleImage: _singleImage,
        multipleImage: _multipleImage,
        singleAttachment: _singleAttachment,
        multipleAttachment: _multipleAttachment
    }
}();
