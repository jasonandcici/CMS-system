/**
 * User: Jinqn
 * Date: 14-04-08
 * Time: 下午16:34
 * 上传图片对话框逻辑代码,包括tab: 远程图片/上传图片/在线图片/搜索图片
 */

(function () {
    if(typeof dialog.isMultiple === 'undefined') dialog.isMultiple = true;
    if(typeof dialog.isUploadPlugin === 'undefined') dialog.isUploadPlugin = false;

    var remoteImage,
        uploadImage,
        onlineImage,
        searchImage;

    window.onload = function () {
        // 对数据进行处理
        editor.options.fileManagerCategoryList = linear(editor.getOpt('fileManagerCategoryList'));

        initTabs();
        initAlign();
        initButtons();

        if(dialog.isUploadPlugin){
            $('.r-other').hide();
            $('#width,#height').attr('readonly',true);
        }
    };

    function linear(data, str, parentId, count, parentName)
    {
        if(typeof str === 'undefined') str = ' ├ ';
        if(typeof parentId === 'undefined') parentId = 0;
        if(typeof count === 'undefined') count = 0;
        if(typeof parentName === 'undefined') parentName = 'pid';

        var arr = [];
        $.each(data,function (i,n) {
            if(n[parentName] == parentId){
                var newStr = '';
                for(var c=0;c<count;c++){
                    newStr +=str;
                }
                n['str'] = newStr;
                n['count'] = count + 1;
                n['hasChild'] = true;
                arr.push(n);
                $.merge(arr,linear(data,str,n['id'],count+1,parentName));
                arr[arr.length-1]['hasChild'] = false;
            }
        });
        return arr;
    }

    /* 初始化tab标签 */
    function initTabs() {
        var tabs = $G('tabhead').children;
        for (var i = 0; i < tabs.length; i++) {
            domUtils.on(tabs[i], "click", function (e) {
                var $target = $(e.target || e.srcElement);
                setTabFocus($target.attr('data-content-id'),$target);
            });
        }

        var img = editor.selection.getRange().getClosedNode();
        if (img && img.tagName && img.tagName.toLowerCase() == 'img') {
            setTabFocus('remote');
        } else {
            setTabFocus('upload');
        }
    }

    /* 初始化tabbody */
    function setTabFocus(id,$target) {
        if(!id) return;
        var i, bodyId, tabs = $G('tabhead').children;
        for (i = 0; i < tabs.length; i++) {
            bodyId = tabs[i].getAttribute('data-content-id');
            if (bodyId == id) {
                domUtils.addClass(tabs[i], 'focus');
                domUtils.addClass($G(bodyId), 'focus');
            } else {
                domUtils.removeClasses(tabs[i], 'focus');
                domUtils.removeClasses($G(bodyId), 'focus');
            }
        }
        switch (id) {
            case 'remote':
                remoteImage = remoteImage || new RemoteImage();
                break;
            case 'upload':
                setAlign(editor.getOpt('imageInsertAlign'));
                uploadImage = uploadImage || new UploadImage('queueList');
                break;
            case 'online':
                setAlign(editor.getOpt('imageManagerInsertAlign'));
                if($target.data('tab')) return;
                onlineImage = onlineImage || new OnlineImage('imageList');
                onlineImage.reset();
                $target.data('tab',true);
                break;
            case 'search':
                setAlign(editor.getOpt('imageManagerInsertAlign'));
                searchImage = searchImage || new SearchImage();
                break;
        }
    }

    /* 初始化onok事件 */
    function initButtons() {

        dialog.onok = function () {
            var remote = false, list = [], id, tabs = $G('tabhead').children;
            for (var i = 0; i < tabs.length; i++) {
                if (domUtils.hasClass(tabs[i], 'focus')) {
                    id = tabs[i].getAttribute('data-content-id');
                    break;
                }
            }

            switch (id) {
                case 'remote':
                    list = remoteImage.getInsertList();
                    break;
                case 'upload':
                    list = uploadImage.getInsertList();
                    var count = uploadImage.getQueueCount();
                    if (count) {
                        $('.info', '#queueList').html('<span style="color:red;">' + '还有2个未上传文件'.replace(/[\d]/, count) + '</span>');
                        return false;
                    }
                    break;
                case 'online':
                    list = onlineImage.getInsertList();
                    break;
                case 'search':
                    list = searchImage.getInsertList();
                    remote = true;
                    break;
            }

            if(list) {
                editor.execCommand('insertimage', list);
                remote && editor.fireEvent("catchRemoteImage");
            }
        };
    }


    /* 初始化对其方式的点击事件 */
    function initAlign(){
        /* 点击align图标 */
        domUtils.on($G("alignIcon"), 'click', function(e){
            var target = e.target || e.srcElement;
            if(target.className && target.className.indexOf('-align') != -1) {
                setAlign(target.getAttribute('data-align'));
            }
        });
    }

    /* 设置对齐方式 */
    function setAlign(align){
        align = align || 'none';
        var aligns = $G("alignIcon").children;
        for(i = 0; i < aligns.length; i++){
            if(aligns[i].getAttribute('data-align') == align) {
                domUtils.addClass(aligns[i], 'focus');
                $G("align").value = aligns[i].getAttribute('data-align');
            } else {
                domUtils.removeClasses(aligns[i], 'focus');
            }
        }
    }
    /* 获取对齐方式 */
    function getAlign(){
        var align = $G("align").value || 'none';
        return align == 'none' ? '':align;
    }


    /* 在线图片 */
    function RemoteImage(target) {
        this.container = utils.isString(target) ? document.getElementById(target) : target;
        this.init();
    }
    RemoteImage.prototype = {
        init: function () {
            this.initContainer();
            this.initEvents();
        },
        initContainer: function () {
            this.dom = {
                'url': $G('url'),
                'width': $G('width'),
                'height': $G('height'),
                'border': $G('border'),
                'vhSpace': $G('vhSpace'),
                'title': $G('title'),
                'align': $G('align')
            };
            var img = editor.selection.getRange().getClosedNode();
            this.img = img;
            if (img) {
                this.setImage(img);
            }
        },
        initEvents: function () {
            var _this = this,
                locker = $G('lock');

            /* 改变url */
            domUtils.on($G("url"), 'keyup', updatePreview);
            domUtils.on($G("border"), 'keyup', updatePreview);
            domUtils.on($G("title"), 'keyup', updatePreview);

            domUtils.on($G("width"), 'keyup', function(){
                if(locker.checked) {
                    var proportion =locker.getAttribute('data-proportion');
                    $G('height').value = Math.round(this.value / proportion);
                } else {
                    _this.updateLocker();
                }
                updatePreview();
            });
            domUtils.on($G("height"), 'keyup', function(){
                if(locker.checked) {
                    var proportion =locker.getAttribute('data-proportion');
                    $G('width').value = Math.round(this.value * proportion);
                } else {
                    _this.updateLocker();
                }
                updatePreview();
            });
            domUtils.on($G("lock"), 'change', function(){
                var proportion = parseInt($G("width").value) /parseInt($G("height").value);
                locker.setAttribute('data-proportion', proportion);
            });

            function updatePreview(){
                _this.setPreview();
            }
        },
        updateLocker: function(){
            var width = $G('width').value,
                height = $G('height').value,
                locker = $G('lock');
            if(width && height && width == parseInt(width) && height == parseInt(height)) {
                locker.disabled = false;
                locker.title = '';
            } else {
                locker.checked = false;
                locker.disabled = 'disabled';
                locker.title = lang.remoteLockError;
            }
        },
        setImage: function(img){
            /* 不是正常的图片 */
            if (!img.tagName || img.tagName.toLowerCase() != 'img' && !img.getAttribute("src") || !img.src) return;

            var wordImgFlag = img.getAttribute("word_img"),
                src = wordImgFlag ? wordImgFlag.replace("&amp;", "&") : (img.getAttribute('_src') || img.getAttribute("src", 2).replace("&amp;", "&")),
                align = editor.queryCommandValue("imageFloat");

            /* 防止onchange事件循环调用 */
            if (src !== $G("url").value) $G("url").value = src;
            if(src) {
                /* 设置表单内容 */
                if(img.style.width){
                    $G("width").value = img.width || '';
                }else{
                    $G("width").value = '';
                }
                if(img.style.height){
                    $G("height").value = img.height || '';
                }else {
                    $G("height").value = '';
                }

                $G("border").value = img.getAttribute("border") || '0';
                $G("vhSpace").value = img.getAttribute("vspace") || '0';
                $G("title").value = img.title || img.alt || '';
                setAlign(align);
                this.setPreview();
                this.updateLocker();
            }
        },
        getData: function(){
            var data = {};
            for(var k in this.dom){
                data[k] = this.dom[k].value;
            }
            return data;
        },
        setPreview: function(){
            var url = $G('url').value,
                ow = $G('width').value,
                oh = $G('height').value,
                border = $G('border').value,
                title = $G('title').value,
                preview = $G('preview'),
                width,
                height;

            width = ((!ow || !oh) ? preview.offsetWidth:Math.min(ow, preview.offsetWidth));
            width = width+(border*2) > preview.offsetWidth ? width:(preview.offsetWidth - (border*2));
            height = (!ow || !oh) ? '':width*oh/ow;

            if(url) {
                var $img = $('<img src="' + url + '" width="' + width + '" height="' + height + '" border="' + border + 'px solid #000" title="' + title + '" />');
                $(preview).html($img);
                layer.load(2);
                $G('width').value = '';
                $G('height').value = '';
                $img.imagesLoaded(function (instance) {
                    layer.closeAll();
                }).done( function( instance ) {
                    $G('width').value = instance.elements[0].naturalWidth;
                    $G('height').value = instance.elements[0].naturalHeight;
                })
            }
        },
        getInsertList: function () {
            var $img = $(this.img),
                data = this.getData();
            if(data['url']) {
                var imgData = {
                    src: data['url'],
                    _src: data['url'],
                    width: data['width'] || '',
                    height: data['height'] || '',
                    border: data['border'] || '',
                    floatStyle: data['align'] || '',
                    vspace: data['vhSpace'] || '',
                    alt: data['title'] || ''
                };

                return [imgData];
            } else {
                return [];
            }
        }
    };



    /* 上传图片 */
    function UploadImage(target) {
        this.$wrap = target.constructor == String ? $('#' + target) : $(target);
        this.init();
    }
    UploadImage.prototype = {
        init: function () {
            this.imageList = [];
            this.initContainer();
            this.initUploader();
            this.initUploadSetting();
        },
        initContainer: function () {
            this.$queue = this.$wrap.find('.filelist');
            this.$settingForm = $('#upload-setting');
        },
        /* 初始化容器 */
        initUploader: function () {
            var _this = this,
                $ = jQuery,    // just in case. Make sure it's not an other libaray.
                $wrap = _this.$wrap,
            // 图片容器
                $queue = $wrap.find('.filelist'),
            // 状态栏，包括进度和控制按钮
                $statusBar = $wrap.find('.statusBar'),
            // 文件总体选择信息。
                $info = $statusBar.find('.info'),
            // 上传按钮
                $upload = $wrap.find('.uploadBtn'),
            // 上传按钮
                $filePickerBtn = $wrap.find('.filePickerBtn'),
            // 上传按钮
                $filePickerBlock = $wrap.find('.filePickerBlock'),
            // 没选择文件之前的内容。
                $placeHolder = $wrap.find('.placeholder'),
            // 总体进度条
                $progress = $statusBar.find('.progress').hide(),
            // 添加的文件数量
                fileCount = 0,
            // 添加的文件总大小
                fileSize = 0,
            // 优化retina, 在retina下这个值是2
                ratio = window.devicePixelRatio || 1,
            // 缩略图大小
                thumbnailWidth = 113 * ratio,
                thumbnailHeight = 113 * ratio,
            // 可能有pedding, ready, uploading, confirm, done.
                state = '',
            // 所有文件的进度信息，key为file id
                percentages = {},
                supportTransition = (function () {
                    var s = document.createElement('p').style,
                        r = 'transition' in s ||
                            'WebkitTransition' in s ||
                            'MozTransition' in s ||
                            'msTransition' in s ||
                            'OTransition' in s;
                    s = null;
                    return r;
                })(),
            // WebUploader实例
                uploader,
                actionUrl = editor.getActionUrl(editor.getOpt('imageActionName')),
                acceptExtensions = (editor.getOpt('imageAllowFiles') || []).join('').replace(/\./g, ',').replace(/^[,]/, ''),
                imageMaxSize = editor.getOpt('imageMaxSize'),
                imageCompressBorder = editor.getOpt('imageCompressBorder');

            if (!WebUploader.Uploader.support()) {
                $('#filePickerReady').after($('<div>').html(lang.errorNotSupport)).hide();
                return;
            } else if (!editor.getOpt('imageActionName')) {
                $('#filePickerReady').after($('<div>').html(lang.errorLoadConfig)).hide();
                return;
            }

            var _formData = editor.getOpt('formData');
            if(!_formData) _formData = {};

            uploader = _this.uploader = WebUploader.create({
                pick: {
                    id: '#filePickerReady',
                    label: lang.uploadSelectFile,
                    multiple:dialog.isMultiple
                },
                accept: {
                    title: 'Images',
                    extensions: acceptExtensions,
                    //mimeTypes: 'image/*'
                    mimeTypes: 'image/gif,image/jpeg,image/png,image/jpg,image/bmp'
                },
                swf: '../../third-party/webuploader/Uploader.swf',
                server: actionUrl,
                fileVal: editor.getOpt('imageFieldName'),
                formData:_formData,
                duplicate: true,
                fileSingleSizeLimit: imageMaxSize,    // 默认 2 M
                compress: editor.getOpt('imageCompressEnable') ? {
                    width: imageCompressBorder,
                    height: imageCompressBorder,
                    // 图片质量，只有type为`image/jpeg`的时候才有效。
                    quality: 90,
                    // 是否允许放大，如果想要生成小图的时候不失真，此选项应该设置为false.
                    allowMagnify: false,
                    // 是否允许裁剪。
                    crop: false,
                    // 是否保留头部meta信息。
                    preserveHeaders: true
                }:false
            });
            uploader.addButton({
                id: '#filePickerBlock'
            });
            uploader.addButton({
                id: '#filePickerBtn',
                label: lang.uploadAddFile
            });

            setState('pedding');

            // 当有文件添加进来时执行，负责view的创建
            function addFile(file) {
                var $li = $('<li id="' + file.id + '">' +
                    '<p class="title">' + file.name + '</p>' +
                    '<p class="imgWrap"></p>' +
                    '<p class="progress"><span></span></p>' +
                    '</li>'),

                    $btns = $('<div class="file-panel">' +
                    '<span class="cancel">' + lang.uploadDelete + '</span>' +
                    '<span class="rotateRight">' + lang.uploadTurnRight + '</span>' +
                    '<span class="rotateLeft">' + lang.uploadTurnLeft + '</span></div>').appendTo($li),
                    $prgress = $li.find('p.progress span'),
                    $wrap = $li.find('p.imgWrap'),
                    $info = $('<p class="error"></p>').hide().appendTo($li),

                    showError = function (code) {
                        switch (code) {
                            case 'exceed_size':
                                text = lang.errorExceedSize;
                                break;
                            case 'interrupt':
                                text = lang.errorInterrupt;
                                break;
                            case 'http':
                                text = lang.errorHttp;
                                break;
                            case 'not_allow_type':
                                text = lang.errorFileType;
                                break;
                            default:
                                text = lang.errorUploadRetry;
                                break;
                        }
                        $info.text(text).show();
                    };

                if (file.getStatus() === 'invalid') {
                    showError(file.statusText);
                } else {
                    $wrap.text(lang.uploadPreview);
                    if (browser.ie && browser.version <= 7) {
                        $wrap.text(lang.uploadNoPreview);
                    } else {
                        uploader.makeThumb(file, function (error, src) {
                            if (error || !src) {
                                $wrap.text(lang.uploadNoPreview);
                            } else {
                                var $img = $('<img src="' + src + '">');
                                $wrap.empty().append($img);
                                $img.on('error', function () {
                                    $wrap.text(lang.uploadNoPreview);
                                });
                            }
                        }, thumbnailWidth, thumbnailHeight);
                    }
                    percentages[ file.id ] = [ file.size, 0 ];
                    file.rotation = 0;

                    /* 检查文件格式 */
                    if (!file.ext || acceptExtensions.indexOf(file.ext.toLowerCase()) == -1) {
                        showError('not_allow_type');
                        uploader.removeFile(file);
                    }
                }

                file.on('statuschange', function (cur, prev) {
                    if (prev === 'progress') {
                        $prgress.hide().width(0);
                    } else if (prev === 'queued') {
                        $li.off('mouseenter mouseleave');
                        $btns.remove();
                    }
                    // 成功
                    if (cur === 'error' || cur === 'invalid') {
                        showError(file.statusText);
                        percentages[ file.id ][ 1 ] = 1;
                    } else if (cur === 'interrupt') {
                        showError('interrupt');
                    } else if (cur === 'queued') {
                        percentages[ file.id ][ 1 ] = 0;
                    } else if (cur === 'progress') {
                        $info.hide();
                        $prgress.css('display', 'block');
                    } else if (cur === 'complete') {
                    }

                    $li.removeClass('state-' + prev).addClass('state-' + cur);
                });

                $li.on('mouseenter', function () {
                    $btns.stop().animate({height: 24});
                });
                $li.on('mouseleave', function () {
                    $btns.stop().animate({height: 0});
                });

                $btns.on('click', 'span', function () {
                    var index = $(this).index(),
                        deg;

                    switch (index) {
                        case 0:
                            uploader.removeFile(file);
                            return;
                        case 1:
                            file.rotation += 90;
                            break;
                        case 2:
                            file.rotation -= 90;
                            break;
                    }

                    if (supportTransition) {
                        deg = 'rotate(' + file.rotation + 'deg)';
                        $wrap.css({
                            '-webkit-transform': deg,
                            '-mos-transform': deg,
                            '-o-transform': deg,
                            'transform': deg
                        });
                    } else {
                        $wrap.css('filter', 'progid:DXImageTransform.Microsoft.BasicImage(rotation=' + (~~((file.rotation / 90) % 4 + 4) % 4) + ')');
                    }

                });

                $li.insertBefore($filePickerBlock);
            }

            // 负责view的销毁
            function removeFile(file) {
                var $li = $('#' + file.id);
                delete percentages[ file.id ];
                updateTotalProgress();
                $li.off().find('.file-panel').off().end().remove();
            }

            function updateTotalProgress() {
                var loaded = 0,
                    total = 0,
                    spans = $progress.children(),
                    percent;

                $.each(percentages, function (k, v) {
                    total += v[ 0 ];
                    loaded += v[ 0 ] * v[ 1 ];
                });

                percent = total ? loaded / total : 0;

                spans.eq(0).text(Math.round(percent * 100) + '%');
                spans.eq(1).css('width', Math.round(percent * 100) + '%');
                updateStatus();
            }

            function setState(val, files) {

                if (val != state) {

                    var stats = uploader.getStats();

                    $upload.removeClass('state-' + state);
                    $upload.addClass('state-' + val);

                    switch (val) {

                        /* 未选择文件 */
                        case 'pedding':
                            $queue.addClass('element-invisible');
                            $statusBar.addClass('element-invisible');
                            $placeHolder.removeClass('element-invisible');
                            $progress.hide(); $info.hide();
                            uploader.refresh();
                            break;

                        /* 可以开始上传 */
                        case 'ready':
                            $placeHolder.addClass('element-invisible');
                            $queue.removeClass('element-invisible');
                            $statusBar.removeClass('element-invisible');
                            $progress.hide(); $info.show();
                            $upload.text(lang.uploadStart);
                            uploader.refresh();
                            break;

                        /* 上传中 */
                        case 'uploading':
                            $progress.show(); $info.hide();
                            $upload.text(lang.uploadPause);
                            break;

                        /* 暂停上传 */
                        case 'paused':
                            $progress.show(); $info.hide();
                            $upload.text(lang.uploadContinue);
                            break;

                        case 'confirm':
                            $progress.show(); $info.hide();
                            $upload.text(lang.uploadStart);

                            stats = uploader.getStats();
                            if (stats.successNum && !stats.uploadFailNum) {
                                setState('finish');
                                return;
                            }
                            break;

                        case 'finish':
                            $progress.hide(); $info.show();
                            if (stats.uploadFailNum) {
                                $upload.text(lang.uploadRetry);
                            } else {
                                $upload.text(lang.uploadStart);
                            }
                            break;
                    }

                    state = val;
                    updateStatus();

                }

                if (!_this.getQueueCount()) {
                    $upload.addClass('disabled')
                } else {
                    $upload.removeClass('disabled')
                }

            }

            function updateStatus() {
                var text = '', stats;

                if (state === 'ready') {
                    text = lang.updateStatusReady.replace('_', fileCount).replace('_KB', WebUploader.formatSize(fileSize));
                } else if (state === 'confirm') {
                    stats = uploader.getStats();
                    if (stats.uploadFailNum) {
                        text = lang.updateStatusConfirm.replace('_', stats.successNum).replace('_', stats.successNum);
                    }
                } else {
                    stats = uploader.getStats();
                    text = lang.updateStatusFinish.replace('_', fileCount).
                        replace('_KB', WebUploader.formatSize(fileSize)).
                        replace('_', stats.successNum);

                    if (stats.uploadFailNum) {
                        text += lang.updateStatusError.replace('_', stats.uploadFailNum);
                    }
                }

                $info.html(text);
            }

            uploader.on('fileQueued', function (file) {
                fileCount++;
                fileSize += file.size;

                if (fileCount === 1) {
                    $placeHolder.addClass('element-invisible');
                    $statusBar.show();
                }

                addFile(file);

                if(!dialog.isMultiple){
                    $('#filePickerBlock,#filePickerBtn').css('visibility','hidden');
                }
            });

            uploader.on('fileDequeued', function (file) {
                if (file.ext && acceptExtensions.indexOf(file.ext.toLowerCase()) != -1 && file.size <= imageMaxSize) {
                    fileCount--;
                    fileSize -= file.size;
                }

                removeFile(file);
                updateTotalProgress();

                if(!dialog.isMultiple){
                    $('#filePickerBlock,#filePickerBtn').css('visibility','visible');
                }
            });

            uploader.on('filesQueued', function (file) {
                if (!uploader.isInProgress() && (state == 'pedding' || state == 'finish' || state == 'confirm' || state == 'ready')) {
                    setState('ready');
                }
                updateTotalProgress();
            });

            uploader.on('all', function (type, files) {
                switch (type) {
                    case 'uploadFinished':
                        setState('confirm', files);
                        break;
                    case 'startUpload':
                        uploader.options.formData = {};
                        var _setting = $.extend({},_formData,getFormData(_this.$settingForm));
                        if(typeof _setting['data[enable_watermark]'] === 'undefined'){
                            delete _setting['data[watermark_position]'];
                        }
                        uploader.option('formData', _setting);

                        /* 添加额外的GET参数 */
                        var params = utils.serializeParam(editor.queryCommandValue('serverparam')) || '',
                            url = utils.formatUrl(actionUrl + (actionUrl.indexOf('?') == -1 ? '?':'&') + 'encode=utf-8&' + params);
                        uploader.option('server', url);
                        setState('uploading', files);
                        break;
                    case 'stopUpload':
                        setState('paused', files);
                        break;
                }
            });

            uploader.on('uploadBeforeSend', function (file, data, header) {
                //这里可以通过data对象添加POST参数
                if (actionUrl.toLowerCase().indexOf('jsp') != -1) {
                    header['X_Requested_With'] = 'XMLHttpRequest';
                }
            });

            uploader.on('uploadProgress', function (file, percentage) {
                var $li = $('#' + file.id),
                    $percent = $li.find('.progress span');

                $percent.css('width', percentage * 100 + '%');
                percentages[ file.id ][ 1 ] = percentage;
                updateTotalProgress();
            });

            uploader.on('uploadSuccess', function (file, ret) {
                var $file = $('#' + file.id);
                try {
                    var responseText = (ret._raw || ret),
                        json = utils.str2json(responseText);
                    if (json.state == 'SUCCESS') {
                        _this.imageList.push(json);
                        $file.append('<span class="file-name"><i title="修改名称" class="editor-file-name editor-'+json.id+'" data-id="'+json.id+'" data-title="'+json.original+'">✎</i><em>'+json.original+'</em></span><span class="success"></span>');
                        if(!dialog.isMultiple){
                            $('#upload-btns,#filePickerBlock').fadeOut(function () {
                                $(this).css('visibility','hidden');
                            });
                        }
                    } else {
                        $file.find('.error').text(json.state).show();
                        if(!dialog.isMultiple){
                            $('#filePickerBlock,#filePickerBtn').css('visibility','visible');
                        }
                    }
                } catch (e) {
                    $file.find('.error').text(lang.errorServerUpload).show();
                    if(!dialog.isMultiple){
                        $('#filePickerBlock,#filePickerBtn').css('visibility','visible');
                    }
                }
            });

            uploader.on('uploadError', function (file, code) {
                if(!dialog.isMultiple){
                    $('#filePickerBlock,#filePickerBtn').css('visibility','visible');
                }
            });
            uploader.on('error', function (code, file) {
                if (code == 'Q_TYPE_DENIED' || code == 'F_EXCEED_SIZE') {
                    addFile(file);
                }
                if(!dialog.isMultiple){
                    $('#filePickerBlock,#filePickerBtn').css('visibility','visible');
                }
            });
            uploader.on('uploadComplete', function (file, ret) {

            });

            $upload.on('click', function () {
                if ($(this).hasClass('disabled')) {
                    return false;
                }

                if (state === 'ready') {
                    uploader.upload();
                } else if (state === 'paused') {
                    uploader.upload();
                } else if (state === 'uploading') {
                    uploader.stop();
                }
            });

            $upload.addClass('state-' + state);
            updateTotalProgress();
        },
        getQueueCount: function () {
            var file, i, status, readyFile = 0, files = this.uploader.getFiles();
            for (i = 0; file = files[i++]; ) {
                status = file.getStatus();
                if (status == 'queued' || status == 'uploading' || status == 'progress') readyFile++;
            }
            return readyFile;
        },
        destroy: function () {
            this.$wrap.remove();
        },
        getInsertList: function () {
            var i, data, list = [],
                align = getAlign(),
                prefix = editor.getOpt('imageUrlPrefix');
            for (i = 0; i < this.imageList.length; i++) {
                data = this.imageList[i];
                list.push({
                    src: prefix + data.url,
                    _src: prefix + data.url,
                    alt: data.original,
                    width: data.width,
                    height: data.height,
                    floatStyle: align
                });
            }
            return list;
        },
        initUploadSetting:function () {
            var _this = this;
            $('#upload-maxsize').text(editor.getOpt('imageMaxSize')/1048576);

            var _cateHtml = '';
            $.each(editor.getOpt('fileManagerCategoryList'),function (i,n) {
                if(n.type === 'image')
                    _cateHtml +='<option value="'+n.id+'">'+n.str+n.title+'</option>';
            });
            $('#upload-setting-category').append(_cateHtml);

            $('#js-enable-watermark').change(function () {
                var $this = $(this);
                if($this.is(':checked')){
                    $($this.data('target')).show();
                }else{
                    $($this.data('target')).hide();
                }
            });

            this.$queue.on('click','.editor-file-name',function () {
                var $this = $(this),
                    _id = $this.data('id');
                var _html = '<form action="javascript:;" id="right-menu-form"><input type="hidden" name="data[id]" value="'+_id+'">' +
                    '<div class="form-group"><label>名称</label><input type="text" name="data[title]" value="'+$this.data('title')+'"></div>'+
                    '</form>';
                layer.open({
                    type: 1,
                    shade: [0.3,'#fff'],
                    title: '编辑名称',
                    area: ['350px', '185px'],
                    content:_html,
                    btn:['确定','取消'],
                    yes:function () {
                        OnlineImage.prototype.requestCustom('update',getFormData($('#right-menu-form')),function (res) {
                            $this.data('title',res.data.title);
                            $this.next().text(res.data.title);
                            $.each(_this.imageList,function (i,n) {
                                if(n.id == _id){
                                    _this.imageList[i].title = res.data.title;
                                    _this.imageList[i].original = res.data.title;
                                    return false;
                                }
                            });

                            layer.closeAll();
                            layer.msg('操作成功',{icon:1,area:['180px','64px']});
                        },'fileManagerActionName');
                    }
                });
            });
        }
    };


    /* 在线图片 */
    function OnlineImage(target) {
        this.container = utils.isString(target) ? document.getElementById(target) : target;
        this.init();
    }
    OnlineImage.prototype = {
        init: function () {
            this.reset();
            this.initEvents();
            this.initTree();
            this.initFileManage();
        },
        /* 初始化容器 */
        initContainer: function () {
            this.container.innerHTML = '';
            this.list = document.createElement('ul');
            this.clearFloat = document.createElement('li');

            domUtils.addClass(this.list, 'list right-menu-content clearfix');
            //domUtils.addClass(this.clearFloat, 'clearFloat');

            //this.list.appendChild(this.clearFloat);
            this.container.appendChild(this.list);

            this.$list = $(this.list);
            this.$loading = $('<div class="loading"><span>数据加载中...</span></div>');
            this.$noData = $('<p class="no-data">没有找到数据~</p>');

            this.$container = $(this.container);
            this.$container.append(this.$loading.add(this.$noData));

            this.$searchForm = $('#search-form');
        },
        /* 初始化滚动事件,滚动到地步自动拉取数据 */
        initEvents: function () {
            var _this = this;

            /* 滚动拉取图片 */
            domUtils.on($G('imageList'), 'scroll', function(e){
                var panel = this;
                if (panel.scrollHeight - (panel.offsetHeight + panel.scrollTop) < 20) {
                    _this.getImageData();
                }
            });
            /* 选中图片 */
            _this.$container.on('click','li',function () {
                var $this = $(this);
                if(dialog.isMultiple){
                    if($this.hasClass('selected')){
                        $this.removeClass('selected');
                    }else{
                        $this.addClass('selected');
                    }
                }else{
                    $this.addClass('selected').siblings().removeClass('selected');
                }
            });
        },
        /* 初始化第一次的数据 */
        initData: function () {
            /* 拉取数据需要使用的值 */
            this.state = 0;
            this.listSize = editor.getOpt('imageManagerListSize');
            this.listIndex = 0;
            this.listEnd = false;
            this.page = 0;

            /* 第一次拉取数据 */
            this.getImageData();
        },
        /* 重置界面 */
        reset: function() {
            this.initContainer();
            this.initData();
        },
        /* 向后台拉取图片列表数据 */
        getImageData: function () {
            var _this = this;

            if(!_this.listEnd && !this.isLoadingData) {
                this.isLoadingData = true;
                var url = editor.getActionUrl(editor.getOpt('imageManagerActionName')),
                    isJsonp = utils.isCrossDomainUrl(url);

                _this.$loading.show().css('visibility','visible');
                _this.$noData.hide();

                var postData = {
                    start: this.listIndex,
                    size: this.listSize,
                    page:this.page + 1
                };
                postData = utils.extend(postData, getFormData(_this.$searchForm));
                if(postData['data[category_id]'] === 'all') delete postData['data[category_id]'];

                ajax.request(url, {
                    'timeout': 100000,
                    'dataType': isJsonp ? 'jsonp':'',
                    'data': utils.extend(postData, editor.queryCommandValue('serverparam')),
                    'method': 'get',
                    'onsuccess': function (r) {
                        _this.$loading.css('visibility','hidden');
                        try {
                            var json = isJsonp ? r:eval('(' + r.responseText + ')');
                            if (json.state == 'SUCCESS') {
                                _this.pushData(json.list);

                                _this.listIndex = parseInt(json.start) + parseInt(json.list.length);
                                if((typeof json.page !== 'undefined' && typeof json.pageCount !== 'undefined' && json.page == json.pageCount) || _this.listIndex >= json.total) {
                                    _this.listEnd = true;
                                    _this.$loading.hide();
                                }
                                _this.isLoadingData = false;

                                if(typeof json.page !== 'undefined') _this.page = json.page
                                if(_this.$list.is(':empty')) _this.$noData.show();
                            }
                        } catch (e) {
                            if(r.responseText.indexOf('ue_separate_ue') != -1) {
                                var list = r.responseText.split(r.responseText);
                                _this.pushData(list);
                                _this.listIndex = parseInt(list.length);
                                _this.listEnd = true;
                                _this.isLoadingData = false;
                            }
                        }
                    },
                    'onerror': function () {
                        _this.isLoadingData = false;
                        _this.$loading.css('visibility','hidden');
                    }
                });
            }
        },
        /* 添加图片到列表界面上 */
        pushData: function (list) {
            var _this = this,
                urlPrefix = editor.getOpt('imageManagerUrlPrefix');

            var $lis = [];
            $.each(list,function (i,n) {
                if(n && n.url) {
                    var $li = $('<li title="'+n.title+'">');
                    $li.data('data',n);
                    $li.html('<div class="file-wrapper"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH/C1hNUCBEYXRhWE1QPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNS42LWMwNjcgNzkuMTU3NzQ3LCAyMDE1LzAzLzMwLTIzOjQwOjQyICAgICAgICAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtbG5zOnhtcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDoxMTUwRkMzN0JBMkUxMUU3OTA4RUE3MDQyNkFBRjMwMCIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDoxMTUwRkMzNkJBMkUxMUU3OTA4RUE3MDQyNkFBRjMwMCIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ0MgMjAxNSAoV2luZG93cykiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpCREYwMEJFOEJBMkMxMUU3QThCNkQ1MTM0NURCNDYyNCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpCREYwMEJFOUJBMkMxMUU3QThCNkQ1MTM0NURCNDYyNCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PgH//v38+/r5+Pf29fTz8vHw7+7t7Ovq6ejn5uXk4+Lh4N/e3dzb2tnY19bV1NPS0dDPzs3My8rJyMfGxcTDwsHAv769vLu6ubi3trW0s7KxsK+urayrqqmop6alpKOioaCfnp2cm5qZmJeWlZSTkpGQj46NjIuKiYiHhoWEg4KBgH9+fXx7enl4d3Z1dHNycXBvbm1sa2ppaGdmZWRjYmFgX15dXFtaWVhXVlVUU1JRUE9OTUxLSklIR0ZFRENCQUA/Pj08Ozo5ODc2NTQzMjEwLy4tLCsqKSgnJiUkIyIhIB8eHRwbGhkYFxYVFBMSERAPDg0MCwoJCAcGBQQDAgEAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" ' +
                        'data-original="'+urlPrefix + n.url+'" draggable="false">' +
                        '<span class="file-title">'+n.title+'</span></div><span class="icon"></span>');
                    $lis = $lis.length>0?$lis.add($li):$li;
                }
            });

            if($lis.length > 0){
                _this.$list.append($lis);

                var $li = _this.$list.find('li:first'),
                    liWidth = $li.width(),
                    liHeight = $li.height();
                $lis.find('img').lazyload({
                    container:_this.$container,
                    effect : "fadeIn",
                    load:function () {
                        _this.scale(this,liWidth, liHeight);
                    }
                });
            }
        },
        /* 改变图片大小 */
        scale: function (img, w, h, type) {
            var ow = img.width,
                oh = img.height;

            if (type == 'justify') {
                if (ow >= oh) {
                    img.width = w;
                    img.height = h * oh / ow;
                    img.style.marginLeft = '-' + parseInt((img.width - w) / 2) + 'px';
                } else {
                    img.width = w * ow / oh;
                    img.height = h;
                    img.style.marginTop = '-' + parseInt((img.height - h) / 2) + 'px';
                }
            } else {
                if (ow >= oh) {
                    img.width = w * ow / oh;
                    img.height = h;
                    img.style.marginLeft = '-' + parseInt((img.width - w) / 2) + 'px';
                } else {
                    img.width = w;
                    img.height = h * oh / ow;
                    img.style.marginTop = '-' + parseInt((img.height - h) / 2) + 'px';
                }
            }
        },
        getInsertList: function () {
            var list = [],
                urlPrefix = editor.getOpt('imageManagerUrlPrefix'), align = getAlign();
            $.each(this.$list.children(),function (i,n) {
                var $this = $(n);
                if($this.hasClass('selected')){
                    var data = $(n).data('data');
                    list.push({
                        src: urlPrefix+data.file,
                        _src: urlPrefix + data.file,
                        width:data.width,
                        height:data.height,
                        alt: data.title,
                        floatStyle: align
                    });
                }
            });

            return list;
        },
        initTree:function () {
            var _this = this,
                ztreeData = getZtreeData(),
                $zTree = $("#js-ztree"),
                zTreeObj = $.fn.zTree.init($zTree, {
                view: {
                    showLine: false,
                    showIcon: false,
                    selectedMulti: false,
                    dblClickExpand: false,
                    addDiyDom: function (treeId, treeNode) {
                        var spaceWidth = 5;
                        var switchObj = $("#" + treeNode.tId + "_switch"),
                            icoObj = $("#" + treeNode.tId + "_ico");
                        switchObj.remove();
                        icoObj.before(switchObj);

                        if (treeNode.level > 1) {
                            var spaceStr = "<span style='display: inline-block;width:" + (spaceWidth * treeNode.level) + "px'></span>";
                            switchObj.before(spaceStr);
                        }
                    }
                },
                data:{
                    simpleData: {
                        enable: true,
                        pIdKey:'pid'
                    },
                    key:{
                        name:'title',
                        title:'title'
                    }
                },
                callback: {
                    beforeClick: function (treeId, treeNode) {
                        _this.$container.find('ul.list').empty();
                        _this.start = 0;
                        _this.page = 0;
                        _this.listEnd = false;
                        _this.isLoadingData = false;
                        _this.$searchForm.find('input:hidden').val(treeNode.id);
                        _this.initData();
                        /*if(!$('#'+treeNode.tId+'_switch').hasClass('noline_open')){
                            zTreeObj.expandNode(treeNode);
                        }*/
                        zTreeObj.expandNode(treeNode);
                    },
                    onRightClick:function (event, treeId, treeNode) {
                        if (!treeNode && event.target.tagName.toLowerCase() !== "button" && $(event.target).parents("a").length === 0) {
                            $zTree.find('.right-select').removeClass('right-select');
                            showRightMenu("root", event.clientX, event.clientY);
                        } else if (treeNode && !treeNode.noRightMenu) {
                            $('#'+treeNode.tId).addClass('right-select');
                            showRightMenu("node", event.clientX, event.clientY);
                        }
                    }
                }
            },ztreeData);
            
            var _nodes = zTreeObj.getNodes();
            if (_nodes.length>0) {
                zTreeObj.selectNode(_nodes[0]);
                var ztreeAddBtn = $('<span class="ztree-add-btn" id="ztree-add-btn" title="新增分组">✚</span>');
                $('#'+_nodes[0].tId+'_a').append(ztreeAddBtn);
                ztreeAddBtn.click(function (e) {
                    addCategory(0);
                    e.stopPropagation();
                });
            }

            function getZtreeData() {
                var data = editor.getOpt('fileManagerCategoryList');
                var newData = [{id:'all',pid:0,title:'所有图片','sort':0,checked:true,noRightMenu:true}];
                $.each(data, function (i, n) {
                    if(n.type === 'image') newData.push(n);
                });

                newData.push({id:'',pid:0,title:'未分组','sort':0,noRightMenu:true});
                return newData;
            }
            
            // 右键菜单
            function showRightMenu(type, x, y) {
                var $rightMenu = $('#ztree-right-menu'),
                    $body = $('body');
                if($rightMenu.length < 1){
                    // 插入菜单
                    var $rightMenuAdd = $('<li>✚ &nbsp;新增分组</li>'),
                        $rightMenuEditor = $('<li class="right-menu-editor">✎ &nbsp;编辑分组</li>'),
                        $rightMenuEmpty = $('<li class="right-menu-empty">〼 &nbsp;清空图片</li>'),
                        $rightMenuDel = $('<li class="right-menu-del">✖ &nbsp;删除分组</li>');
                    $rightMenu = $('<ul id="ztree-right-menu" class="ztree-right-menu"></ul>');
                    $rightMenu.append($rightMenuAdd.add($rightMenuEditor).add($rightMenuEmpty).add($rightMenuDel));
                    $body.append($rightMenu);

                    $body.bind("mousedown", function (event) {
                        if (!(event.target.id === "ztree-right-menu" || $(event.target).parents("#ztree-right-menu").length>0)) {
                            $rightMenu.css({"visibility" : "hidden"});
                            $zTree.find('.right-select').removeClass('right-select');
                        }
                    });

                    // 绑定操作
                    $rightMenuAdd.click(function () {
                        $rightMenu.hide();
                        var pid = getRightMenuSelectId(zTreeObj);
                        addCategory(pid)
                    });

                    $rightMenuEditor.click(function () {
                        $rightMenu.hide();
                        var treeNode = getRightMenuSelectId(zTreeObj,true);
                        if(treeNode){
                            layer.open({
                                type: 1,
                                shade: [0.3,'#fff'],
                                title: '编辑分组',
                                area: ['350px', '240px'],
                                content:getRightMenuForm({title:treeNode.title,pid:treeNode.pid},treeNode.id),
                                btn:['确定','取消'],
                                yes:function () {
                                    _this.requestCustom('update',$.extend({},getFormData($('#right-menu-form')),{'data[id]':treeNode.id}),function (res) {
                                        var cList = editor.getOpt('fileManagerCategoryList');
                                        $.each(cList,function (i,n) {
                                            if(n.id == res.data.id){
                                                cList.splice(i,1);
                                                return false;
                                            }
                                        });
                                        cList.push(res.data);
                                        editor.options.fileManagerCategoryList = linear(cList);

                                        var nodeList = zTreeObj.transformToArray(zTreeObj.getNodes()),
                                            isRemoveNode = false;
                                        $.each(nodeList,function (i,n) {
                                            if(n.id == res.data.id){
                                                if(n.pid == res.data.pid || (n.pid == null && res.data.pid==0)){
                                                    $.extend(n,res.data);
                                                    zTreeObj.updateNode(n);
                                                }else{
                                                    zTreeObj.removeNode(n);
                                                    isRemoveNode = true;
                                                }
                                                return false;
                                            }
                                        });
                                        if(isRemoveNode){
                                            $.each(nodeList,function (i,n) {
                                                if(res.data.pid == 0){
                                                    zTreeObj.addNodes(null,nodeList.length-2, res.data,true);
                                                    return false;
                                                }else if(n.id == res.data.pid){
                                                    zTreeObj.addNodes(n,-1, res.data,true);
                                                    return false;
                                                }
                                            });
                                        }

                                        rightMenuUpdateNode();
                                        layer.closeAll();
                                        layer.msg('操作成功',{icon:1,area:['180px','64px']});
                                    });
                                }
                            });

                        }else{
                            layer.msg('无法获取节点数据。');
                        }
                    });

                    $rightMenuDel.click(function () {
                        $rightMenu.hide();
                        var _id = getRightMenuSelectId(zTreeObj);
                        layer.confirm('<h4>您确定要删除此分组吗？</h4><span style="font-size: 12px;">删除此分组后可以在“未分组”中找到此分组下的图片。</span>', {
                            icon:0,
                            shade: [0.3,'#fff'],
                            title: '警告'
                        }, function(){
                            layer.closeAll();
                            _this.requestCustom('delete',{'data[id]':_id},function (res) {
                                var cList = editor.getOpt('fileManagerCategoryList');
                                $.each(cList,function (i,n) {
                                    if(n.id == _id){
                                        cList.splice(i,1);
                                        return false;
                                    }
                                });
                                editor.options.fileManagerCategoryList = linear(cList);

                                $.each(zTreeObj.transformToArray(zTreeObj.getNodes()),function (i,n) {
                                    if(n.id == _id){
                                        zTreeObj.removeNode(n);
                                        return false;
                                    }
                                });
                                rightMenuUpdateNode();
                                layer.msg('操作成功',{icon:1,area:['180px','64px']});
                            });
                        });
                    });

                    $rightMenuEmpty.click(function () {
                        $rightMenu.hide();
                        var _id = getRightMenuSelectId(zTreeObj);
                        layer.confirm('<h4>您确定要清空当前分组下的所有图片吗？</h4><span style="font-size: 12px;">清空后不可恢复。</span>', {
                            icon:0,
                            shade: [0.3,'#fff'],
                            title: '警告'
                        }, function(){
                            layer.closeAll();
                            _this.requestCustom('empty',{'data[id]':_id},function (res) {
                                _this.$container.find('ul.list').empty();
                                _this.start = 0;
                                _this.page = 0;
                                _this.listEnd = false;
                                _this.isLoadingData = false;
                                _this.$searchForm.find('input:hidden').val(_id);
                                _this.initData();
                                layer.msg('操作成功',{icon:1,area:['180px','64px']});
                            });
                        });
                    });
                }
                $rightMenu.show();
                if (type==="root") {
                    $rightMenu.find('.right-menu-editor').hide();
                    $rightMenu.find('.right-menu-del').hide();
                    $rightMenu.find('.right-menu-empty').hide();
                } else {
                    $rightMenu.find('.right-menu-editor').show();
                    $rightMenu.find('.right-menu-del').show();
                    $rightMenu.find('.right-menu-empty').show();
                }

                y += $body[0].scrollTop+2;
                x += $body[0].scrollLeft+2;
                $rightMenu.css({"top":y+"px", "left":x+"px", "visibility":"visible"});
            }

            function addCategory(pid) {
                layer.open({
                    type: 1,
                    shade: [0.3,'#fff'],
                    title: '新增分组',
                    area: ['350px', '240px'],
                    content:getRightMenuForm({title:'',pid:pid}),
                    btn:['确定','取消'],
                    yes:function () {
                        _this.requestCustom('create',getFormData($('#right-menu-form')),function (res) {
                            var cList = editor.getOpt('fileManagerCategoryList');
                            cList.push(res.data);
                            editor.options.fileManagerCategoryList = linear(cList);

                            var nodeList = zTreeObj.transformToArray(zTreeObj.getNodes());
                            $.each(nodeList,function (i,n) {
                                if(res.data.pid==0){
                                    zTreeObj.addNodes(null,nodeList.length -1, res.data);
                                    return false;
                                }else if(n.id == res.data.pid){
                                    zTreeObj.addNodes(n,-1, res.data);
                                    return false;
                                }
                            });
                            rightMenuUpdateNode();
                            layer.closeAll();
                            layer.msg('操作成功',{icon:1,area:['180px','64px']});
                        });
                    }
                });
            }

            function getRightMenuSelectId (ztree,returnZTreeNode) {
                var id = 0;
                $.each(ztree.transformToArray(ztree.getNodes()),function (i,n) {
                    if($('#'+n.tId).hasClass('right-select')){
                        if(returnZTreeNode){
                            id = n;
                        }else{
                            if(n.id) id = n.id;
                        }

                        return false;
                    }
                });
                return id;
            }

            function getRightMenuForm(data,exclude) {
                var optionHtml = '';
                $.each(editor.getOpt('fileManagerCategoryList'),function (i,n) {
                    if(n.type === 'image')
                        optionHtml +='<option'+(data.pid == n.id?' selected':'')+' value="'+n.id+'"'+(exclude == n.id?' disabled':'')+'>'+(typeof n.str === 'undefined'?'':n.str)+n.title+'</option>';
                });

                return '<form action="javascript:;" id="right-menu-form"><input type="hidden" name="data[type]" value="image">' +
                    '<div class="form-group"><label>组名</label><input type="text" name="data[title]" value="'+data.title+'"></div>' +
                    '<div class="form-group"><label>父级</label><select name="data[pid]"><option value="0">顶级分组</option>'+optionHtml+'</select></div>' +
                    '</form>';
            }

            function rightMenuUpdateNode() {
                var _cateHtml = '<option value="">选择分组</option>';
                $.each(editor.getOpt('fileManagerCategoryList'),function (i,n) {
                    if(n.type === 'image')
                        _cateHtml +='<option value="'+n.id+'">'+ n.str +n.title+'</option>';
                });
                $('#upload-setting-category').html(_cateHtml);
            }

            // 筛选
            _this.$searchForm.submit(function () {
                _this.$container.find('ul.list').empty();
                _this.start = 0;
                _this.page = 0;
                _this.listEnd = false;
                _this.initData();
            });
            _this.$searchForm.find('.btn-reset').click(function () {
                _this.$searchForm.find('input:text').val('');
                _this.$searchForm.trigger('submit');
            });
        },
        requestCustom:function (action,data,callback,optAction) {
            var _this = this;

            if(!_this.isLoadingData) {
                _this.isLoadingData = true;
                if(typeof optAction ==='undefined') optAction = 'fileManagerCategoryAction';
                var url = editor.getActionUrl(editor.getOpt(optAction)),
                    isJsonp = utils.isCrossDomainUrl(url);

                var postData = editor.getOpt('formData');
                if(!postData) postData = {};
                postData['data[action]'] = action;
                for (var i in data){
                    postData[i] = data[i];
                }

                var loading = layer.load(2);
                ajax.request(url, {
                    'timeout': 100000,
                    'dataType': isJsonp ? 'jsonp':'',
                    'data': utils.extend(postData, editor.queryCommandValue('serverparam')),
                    'method': 'post',
                    'onsuccess': function (r) {
                        layer.closeAll();
                        _this.isLoadingData = false;
                        var json = isJsonp ? r:eval('(' + r.responseText + ')');
                        if (json.state === 'SUCCESS') {
                            if(typeof callback === 'function'){
                                callback(json);
                            }else{
                                layer.msg('操作成功',{icon:1,area:['180px','62px']});
                            }
                        }else{
                            layer.msg(json.state);
                        }
                    },
                    'onerror': function () {
                        layer.close(loading);
                        layer.msg('操作失败。',{icon:2,area:['180px','62px']});
                        _this.isLoadingData = false;
                    }
                });
            }
        },
        initFileManage:function () {
            var _this = this;
            $(document).mousedown(function(e){
                var $target = $(e.target),
                    $ul = $target.parents('.right-menu-content');
                if(e.which === 3 && $ul.length > 0) {
                    var $node = $target.parents('li');
                    if($node.hasClass('filePickerBlock')) return false;

                    var $rightMenu = $('#file-right-menu'),
                        $body = $('body');

                    var $selected = $ul.find('li.selected');
                    if($selected.length < 2){
                        $selected.removeClass('selected');
                        $rightMenu.find('.right-menu-editor').show();
                    }else{
                        $rightMenu.find('.right-menu-editor').hide();
                    }
                    $node.addClass('selected');

                    if($rightMenu.length < 1){
                        // 插入菜单
                        var $rightMenuMove = $('<li class="right-menu-move">✈ &nbsp;移动图片</li>'),
                            $rightMenuEditor = $('<li class="right-menu-editor">✎ &nbsp;编辑名称</li>'),
                            $rightMenuDel = $('<li class="right-menu-del">✖ &nbsp;删除图片</li>');
                        $rightMenu = $('<ul id="file-right-menu" class="ztree-right-menu"></ul>');
                        $rightMenu.append($rightMenuEditor.add($rightMenuMove).add($rightMenuDel));
                        $body.append($rightMenu);

                        $body.bind("mousedown", function (event) {
                            if (!(event.target.id === "file-right-menu" || $(event.target).parents("#file-right-menu").length>0)) {
                                $rightMenu.css({"visibility" : "hidden"});
                            }
                        });

                        // 绑定操作
                        $rightMenuMove.click(function () {
                            $rightMenu.hide();

                            var _ids = [];
                            var $selected = $ul.find('.selected');
                            $selected.each(function (i,n) {
                                var data = $(n).data('data');
                                _ids.push(data.id);
                            });
                            if(_ids.length < 1) return;
                            if(_ids.length === 1) _ids = _ids[0];

                            var currCate = $('#search-form').find('.category-id').val();
                            layer.open({
                                type: 1,
                                shade: [0.3,'#fff'],
                                title: '移动到',
                                area: ['350px', '185px'],
                                content:getRightMenuForm({category_id:currCate}),
                                btn:['确定','取消'],
                                yes:function () {
                                    var formData = getFormData($('#right-menu-form'));
                                    formData['data[id]'] = _ids;
                                    if(formData['data[category_id]'] == currCate){
                                        $selected.removeClass('selected');
                                        layer.closeAll();
                                    }else{
                                        _this.requestCustom('move',formData,function (res) {
                                            if(currCate !== 'all'){
                                                $selected.fadeOut(function () {
                                                    $(this).remove();
                                                });
                                            }else{
                                                $selected.removeClass('selected');
                                            }

                                            layer.closeAll();
                                            layer.msg('操作成功',{icon:1,area:['180px','64px']});
                                        },'fileManagerActionName');
                                    }
                                }
                            });
                        });

                        $rightMenuEditor.click(function () {
                            $rightMenu.hide();

                            var $selected = $ul.find('.selected'),
                                data = $selected.data('data');

                            layer.open({
                                type: 1,
                                shade: [0.3,'#fff'],
                                title: '编辑名称',
                                area: ['350px', '185px'],
                                content:getRightMenuForm({title:data.title}),
                                btn:['确定','取消'],
                                yes:function () {
                                    _this.requestCustom('update',$.extend({},getFormData($('#right-menu-form')),{'data[id]':data.id}),function (res) {
                                        $selected.data('data',res.data).attr('title',res.data.title).find('.file-title').text(res.data.title);
                                        $selected.removeClass('selected');
                                        layer.closeAll();
                                        layer.msg('操作成功',{icon:1,area:['180px','64px']});
                                    },'fileManagerActionName');
                                }
                            });

                        });

                        $rightMenuDel.click(function () {
                            $rightMenu.hide();
                            var _ids = [];
                            var $selected = $ul.find('.selected');
                            $selected.each(function (i,n) {
                                var data = $(n).data('data');
                                _ids.push(data.id);
                            });
                            if(_ids.length < 1) return;
                            if(_ids.length === 1) _ids = _ids[0];
                            layer.confirm('<h4>您确定要删除选中图片吗？</h4><span style="font-size: 12px;">删除后不可恢复。</span>', {
                                icon:0,
                                shade: [0.3,'#fff'],
                                title: '警告'
                            }, function(){
                                layer.closeAll();
                                _this.requestCustom('delete',{'data[id]':_ids},function (res) {
                                    $selected.fadeOut(function () {
                                        $(this).remove();
                                    });

                                    layer.msg('操作成功',{icon:1,area:['180px','64px']});
                                },'fileManagerActionName');
                            });
                        });
                    }

                    $rightMenu.show();
                    var y = e.clientY + $body[0].scrollTop+2;
                    var x = e.clientX + $body[0].scrollLeft+2;
                    $rightMenu.css({"top":y+"px", "left":x+"px", "visibility":"visible"});

                    return false;
                }
            });

            function getRightMenuForm(data) {
                var optionHtml = '';
                if(typeof data.title !=='undefined'){
                    optionHtml = '<div class="form-group"><label>名称</label><input type="text" name="data[title]" value="'+data.title+'"></div>';
                }
                if(typeof data.category_id !=='undefined'){
                    optionHtml += '<div class="form-group"><label>分组</label><select name="data[category_id]">';
                    $.each(editor.getOpt('fileManagerCategoryList'),function (i,n) {
                        if(n.type === 'image')
                            optionHtml +='<option'+(data.category_id == n.id?' selected':'')+' value="'+n.id+'">'+(typeof n.str === 'undefined'?'':n.str)+n.title+'</option>';
                    });
                    optionHtml +='</select></div>';
                }

                return '<form action="javascript:;" id="right-menu-form"><input type="hidden" name="data[type]" value="image">' +
                    optionHtml +
                    '</form>';
            }

            document.oncontextmenu = function(e){
                if($(e.target).parents('.right-menu-content').length > 0){
                    return false;
                }
            }
        }
    };

    /*搜索图片 */
    function SearchImage() {
        this.init();
    }
    SearchImage.prototype = {
        init: function () {
            this.initEvents();
        },
        initEvents: function(){
            var _this = this;

            /* 点击搜索按钮 */
            domUtils.on($G('searchBtn'), 'click', function(){
                var key = $G('searchTxt').value;
                if(key && key != lang.searchRemind) {
                    _this.getImageData();
                }
            });
            /* 点击清除妞 */
            domUtils.on($G('searchReset'), 'click', function(){
                $G('searchTxt').value = lang.searchRemind;
                $G('searchListUl').innerHTML = '';
                $G('searchType').selectedIndex = 0;
            });
            /* 搜索框聚焦 */
            domUtils.on($G('searchTxt'), 'focus', function(){
                var key = $G('searchTxt').value;
                if(key && key == lang.searchRemind) {
                    $G('searchTxt').value = '';
                }
            });
            /* 搜索框回车键搜索 */
            domUtils.on($G('searchTxt'), 'keydown', function(e){
                var keyCode = e.keyCode || e.which;
                if (keyCode == 13) {
                    $G('searchBtn').click();
                }
            });

            /* 选中图片 */
            domUtils.on($G('searchList'), 'click', function(e){
                var target = e.target || e.srcElement,
                    li = target.parentNode.parentNode;

                if (li.tagName.toLowerCase() == 'li') {
                    if (domUtils.hasClass(li, 'selected')) {
                        domUtils.removeClasses(li, 'selected');
                    } else {
                        domUtils.addClass(li, 'selected');
                    }
                }
            });
        },
        /* 改变图片大小 */
        scale: function (img, w, h) {
            var ow = img.width,
                oh = img.height;

            if (ow >= oh) {
                img.width = w * ow / oh;
                img.height = h;
                img.style.marginLeft = '-' + parseInt((img.width - w) / 2) + 'px';
            } else {
                img.width = w;
                img.height = h * oh / ow;
                img.style.marginTop = '-' + parseInt((img.height - h) / 2) + 'px';
            }
        },
        getImageData: function(){
            var _this = this,
                key = $G('searchTxt').value,
                type = $G('searchType').value,
                keepOriginName = editor.options.keepOriginName ? "1" : "0",
                url = "http://image.baidu.com/i?ct=201326592&cl=2&lm=-1&st=-1&tn=baiduimagejson&istype=2&rn=32&fm=index&pv=&word=" + key + type + "&ie=utf-8&oe=utf-8&keeporiginname=" + keepOriginName + "&" + +new Date;

            $G('searchListUl').innerHTML = lang.searchLoading;
            ajax.request(url, {
                'dataType': 'jsonp',
                'charset': 'GB18030',
                'onsuccess':function(json){
                    var list = [];
                    if(json && json.data) {
                        for(var i = 0; i < json.data.length; i++) {
                            if(json.data[i].objURL) {
                                list.push({
                                    title: json.data[i].fromPageTitleEnc,
                                    src: json.data[i].objURL,
                                    url: json.data[i].fromURL
                                });
                            }
                        }
                    }
                    _this.setList(list);
                },
                'onerror':function(){
                    $G('searchListUl').innerHTML = lang.searchRetry;
                }
            });
        },
        /* 添加图片到列表界面上 */
        setList: function (list) {
            var i, item, p, img, link, _this = this,
                listUl = $G('searchListUl');

            listUl.innerHTML = '';
            if(list.length) {
                for (i = 0; i < list.length; i++) {
                    item = document.createElement('li');
                    p = document.createElement('p');
                    img = document.createElement('img');
                    link = document.createElement('a');

                    img.onload = function () {
                        _this.scale(this, 113, 113);
                    };
                    img.width = 113;
                    img.setAttribute('src', list[i].src);

                    link.href = list[i].url;
                    link.target = '_blank';
                    link.title = list[i].title;
                    link.innerHTML = list[i].title;

                    p.appendChild(img);
                    item.appendChild(p);
                    item.appendChild(link);
                    listUl.appendChild(item);
                }
            } else {
                listUl.innerHTML = lang.searchRetry;
            }
        },
        getInsertList: function () {
            var child,
                src,
                align = getAlign(),
                list = [],
                items = $G('searchListUl').children;
            for(var i = 0; i < items.length; i++) {
                child = items[i].firstChild && items[i].firstChild.firstChild;
                if(child.tagName && child.tagName.toLowerCase() == 'img' && domUtils.hasClass(items[i], 'selected')) {
                    src = child.src;
                    list.push({
                        src: src,
                        _src: src,
                        alt: src.substr(src.lastIndexOf('/') + 1),
                        floatStyle: align
                    });
                }
            }
            return list;
        }
    };

    /**
     * 获取表单数据
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

})();
