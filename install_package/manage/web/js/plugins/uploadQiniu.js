/**
 * @copyright
 * @link 
 * @create Created on 2016/9/1
 */
'use strict';

/**
 * 上传七牛图片
 *
 * _initFun($element,config)里的$element元素
 * data-tpl：
 * 供百度模板引擎使用的模板文件，为空则使用默认模板
 * data-multiple：
 * 是否多图片上传
 * data-wrap：
 * 上传状态所插入的元素
 * data-thumb:
 * 示例：1/w/200/h/200
 *
 * 上传基础css样式
 * .upload-item-wrap .up_progress_bar{height: 15px;background-color: #337ab7;}
 * .upload-item-wrap button{margin-right:5px;}
 * .upload-item-wrap .up_file{margin-bottom:5px;}
 * .upload-item-wrap .upload-item-image{width:150px;margin:15px 15px 0 0;position:relative;}
 * .upload-item-wrap .upload-item-image .up_progress{position:absolute;left:0;top:0;height:150px;width:100%;text-align:center;}
 * .upload-item-wrap .upload-item-image .up_progress .up_progress_percent{position:absolute;left:0;width:100%;top:50%;height:14px;margin-top:-7px;}
 * .upload-item-wrap .upload-item-image .up_progress .up_progress_bar{position:absolute;left:0;top:50%;margin-top:-7px;}
 * .upload-item-wrap .upload-item-image .up_file{width:100%;height:150px;background:#fafafa url("/images/loading.gif") center no-repeat;text-align: center;overflow: hidden;}
 * .upload-item-wrap .upload-item-image img{height:100%;}
 *
 * @author
 * @since 1.0
 */
var uploadQiniuApp = function () {
    var _windowName = window.name;

    /**
     * 初始化
     * @param $element
     * @param config
     * @param type 1文件 0图片
     * @param extensions
     * @private
     */
    var _initFun = function ($element,config,type,extensions) {


        if(typeof $element == 'string') $element = $($element);

        if(typeof extensions == 'undefined'){
            if(type){
                extensions = 'jpg,jpeg,png,gif,bmp,ico';
            }else{
                extensions = 'jpg,jpeg,gif,ico,bmp,png,doc,docx,xls,xlsx,ppt,pptx,pdf,txt,rar,zip,tar,7-zip,gzip,apk';
            }
        }


        var options = {
            runtimes: 'html5,flash,html4',
            browse_button: '',
            get_new_uptoken: false,
            domain: '',
            max_file_size: '2048mb',
            filters : {
                 max_file_size: '2048mb',
                 mime_types: [
                    {title : "Files", extensions : extensions}
                 ]
             },
            flash_swf_url: '/js/plupload/Moxie.swf',
            max_retries: 3,
            chunk_size: '4mb',
            auto_start: true,
            unique_names:true
        };
        //options.filters.max_file_size = options.max_file_size;

        $element.each(function (i,n) {
            var $this = $(n),
                _tpl = $this.data('tpl'),
                _isMultiple = $this.data('multiple')||false,
                _thumb = $this.data('thumb')||'1/w/150/h/150',
                _wrap = $this.data('wrap'),
                $wrap,
                $input = $this.find('input'),
                id = $this.attr('id'),
                _uid = _uuid(14);
            if(!id || id == ''){
                id = 'upload_file_'+_uid;
                $this.attr('id',id);
            }

            if(!_wrap){
                $wrap = $('<div id="upload_file_wrap_'+_uid+'" class="upload-item-wrap clearfix"></div>');
                $this.after($wrap);
                $this.data('wrap','upload_file_wrap_'+_uid);
            }else{
                $wrap = $('#'+_wrap);
            }

            // 默认模板
            if(!_tpl){
                _tpl = '<div id="<%=id%>" class="upload-item upload-item-'+ (type?'image':'file') +'">' +
                    '<div class="up_file"></div>' +
                    '<div class="up_progress"><div class="up_progress_percent">0%</div>' +
                    '<div class="up_progress_bar" style="width: 0%;"></div></div>' +
                    '<button class="up_edit btn btn-xs btn-default" type="button" style="display: none;">修改</button>' +
                    '<button class="up_del btn btn-xs btn-default" type="button">删除</button></div>';
            }

            //数据初始化
            _initImage($this,$input,_tpl,$wrap,config.domain,config.downtoken_url,type);
            if(!_isMultiple && $input.val() != '') $this.hide();

            // 绑定上传插件
            var _qiniu = new QiniuJsSDK();

            var newConfig = $.extend({},options,{
                browse_button:id,
                multi_selection:_isMultiple,
                init:{
                    'FilesAdded':function (up, files) {
                        plupload.each(files, function(file) {
                            var _html = baidu.template(_tpl,file);
                            if(_isMultiple){
                                $wrap.append(_html);
                            }else{
                                $wrap.html(_html);
                            }

                            var $file = $('#'+file.id);
                            $file.find('.up_edit').hide();
                            $file.find('.up_progress').show();
                            // 移除未上传完成的图片
                            $file.find('.up_del').bind('click',function () {
                                if(confirm('确定中断本次上传吗？')){
                                    up.removeFile(file);
                                    $file.remove();
                                }
                            });
                        });
                    },
                    'UploadProgress': function(up, file) {
                        // 上传进度
                        var $file = $('#'+file.id);
                        $file.find('.up_progress_bar').css({width:file.percent+'%'});
                        $file.find('.up_progress_percent').text(file.percent + "%");
                    },
                    'FileUploaded': function(up, file, info) {
                        if(!_isMultiple) $this.hide();
                        var $file = $('#'+file.id);

                        var domain = up.getOption('domain'),
                            res = $.parseJSON(info),
                            fileUrl = typeof res.url == 'undefined'?domain + res.key:res.url;

                        if(type){
                            _insertImage($file,fileUrl,_thumb);
                        }else{
                            _insertFile($file,fileUrl);
                        }

                        _insertData($input,$file.index(),res);

                        // 编辑
                        _editFile($this,$file);

                        // 删除
                        _deleteFile($file,$input);

                        if(typeof config.onsuccess == 'function'){
                            config.onsuccess(up,res,fileUrl);
                        }
                    }
                    ,'Error': function (up, err, errTip) {
                        if(typeof config.onerror == 'function'){
                            config.oncomplete(up, err, errTip);
                        }else{
                            alert(errTip);
                        }
                    },
                    'UploadComplete': function() {
                        window.name = _windowName; // 解决插件上传后改变name的bug
                        if(typeof config.oncomplete == 'function'){
                            config.oncomplete(uploader);
                        }
                    }
                }
            },config);
            newConfig.filters.max_file_size = newConfig.max_file_size;

            var uploader = _qiniu.uploader(newConfig);
        });
    };

    /**
     * 插入值
     * @param $input
     * @param index
     * @param value
     * @private
     */
    function _insertData($input, index, value) {
        if(typeof value.url) delete value.url;
        var _val = _getData($input);
        _val[index] = value;
        $input.val(JSON.stringify(_val));
    }

    /**
     * 获取值
     * @param $input
     * @param index 索引，可选
     * @returns {*}
     * @private
     */
    function _getData($input,index) {
        var _val = $input.val();
        if(!_val){
            _val = [];
        }else{
            _val = $.parseJSON(_val);
        }
        if(typeof index == 'undefined'){
            return _val;
        }else{
            return _val[index];
        }
    }

    /**
     * 删除值
     * @param $input
     * @param index
     * @private
     */
    function _deleteData($input, index) {
        var _val = _getData($input);
        _val.splice(index,1);
        if(_val.length == 0){
            $input.val('').parent().show();
        }else{
            $input.val(JSON.stringify(_val));
        }
    }

    /**
     * 绑定删除图片
     * @param $file
     * @param $input
     * @private
     */
    function _deleteFile($file,$input) {
        $file.find('.up_del').unbind('click')
            .click(function () {
                if(confirm('您确定要删除此项吗？')){
                    _deleteData($input,$file.index());
                    $file.remove();
                }
            });
    }

    /**
     * 绑定编辑图片
     * @param $e 上传按钮的jq对象
     * @param $file
     * @private
     */
    function _editFile($e,$file) {
        $file.find('.up_edit').show().click(function () {
            $e.trigger('click');
        });
    }

    /**
     * 插入一个图片
     * @param $file
     * @param imgSrc
     * @param thumb
     * @private
     */
    function _insertImage($file,imgSrc,thumb) {
        $file.find('.up_edit').show();
        $file.find('.up_progress').hide();
        $file.find('.up_file').html('<img src="'+imgSrc+'?imageView2/'+thumb+'">');
        window.name = _windowName;
    }

    /**
     * 插入文件
     * @param $file
     * @param fileUrl
     * @private
     */
    function _insertFile($file,fileUrl) {
        $file.find('.up_edit').show();
        $file.find('.up_progress').hide();

        $file.find('.up_file').html('<a href="'+fileUrl+'" target="_blank">'+fileUrl.split('?')[0]+'</a>');
        window.name = _windowName;
    }

    /**
     * 初始化数据
     * @param $e
     * @param $input
     * @param tpl
     * @param $wrap
     * @param domain
     * @param downloadUrl
     * @param type
     * @private
     */
    function _initImage($e,$input,tpl,$wrap,domain,downloadUrl,type) {
        var _generate = function (fileUrl) {
            var $file = $(baidu.template(tpl,{'file':fileUrl}));

            if(type){
                var _thumb = $e.data('thumb')||'1/w/150/h/150';
                _insertImage($file,fileUrl,_thumb);
            }else{
                _insertFile($file,fileUrl);
            }
            _editFile($e,$file);
            _deleteFile($file,$input);

            if(typeof $e.data('multiple') == 'undefined'){
                $wrap.html($file);
            }else{
                $wrap.append($file);
            }
        };

        $.each(_getData($input),function(i,val){
            if(val == null || val == '' || typeof val == 'undefined') return true;

            if(typeof downloadUrl == 'undefined'){
                _generate(domain+val.key);
            }else{
                $.post(downloadUrl,{domain:domain,key:val.key},function (response) {
                    if(typeof response == 'string') response = $.parseJSON(response);
                    _generate(response.url);
                });
            }
        });
    }

    /**
     * 生成uuid
     * @param len 长度
     * @param radix 基数
     * @param connector
     * @returns {string}
     */
    function _uuid(len, radix,connector) {
        if(typeof connector == 'undefined') connector = '';
        var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.split('');
        var uuid = [], i;
        radix = radix || chars.length;

        if (len) {
            // Compact form
            for (i = 0; i < len; i++) uuid[i] = chars[0 | Math.random()*radix];
        } else {
            var r;

            uuid[8] = uuid[13] = uuid[18] = uuid[23] = connector;
            uuid[14] = '4';

            for (i = 0; i < 36; i++) {
                if (!uuid[i]) {
                    r = 0 | Math.random()*16;
                    uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r];
                }
            }
        }

        return uuid.join('');
    }

    /**
     * 上传图片
     * @param $element
     * @param config
     * @param extensions
     * @private
     */
    var _image = function ($element,config,extensions) {
        _initFun($element,config,1,extensions);
    };

    /**
     * 上传文件
     * @param $element
     * @param config
     * @param extensions
     * @private
     */
    var _file = function ($element,config,extensions) {
        _initFun($element,config,0,extensions);
    };

    /**
     * 返回
     */
    return {
        image:_image,
        file:_file
    }
}();