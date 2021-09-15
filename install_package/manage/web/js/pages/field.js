/**
 * @copyright
 * @link 
 * @create Created on 2017/6/15
 */
'use strict';

/**
 * field
 *
 * @author 
 * @since 1.0
 */
var fieldApp = function () {

    var $tplContent = $('.tpl-content'),
        $tplVerification = $('.tpl-verification'),
        $changeTypeContent = $('#change-type-content'),
        $changeTypeVerification = $('#change-type-verification'),
        $changeTypeSetting = $('#change-type-setting'),
        $setting = $('#setting'),
        $fieldName = $('#field-name');

    /**
     * 初始化
     */
    var _initFun = function () {

        // 选择字段类型
        var $changeType = $('#js-change-type');
        $changeType.select2();
        $changeType.on('change',function () {
            var _val = $(this).val();
            if(_val === '') return false;
            render(_val);
        });


        render($changeType.val());

        // 内容change事件
        $changeTypeContent.on('change','.js-field-content',function () {
            var $this = $(this);
            $($this.data('target')).val($this.val());
        });

        // 验证规则change事件
        $changeTypeVerification.on('change','.js-verification-text',function () {
            var $this = $(this),
                _name = $this.data('name'),
                _val = $this.val();

            if(_val === ''){
                setVerificationData(_name,_val,true);
                if(_name === 'length'){
                    $('#field-length').val('');
                }
            }else{
                setVerificationData(_name,_val);
                if(_name === 'length'){
                    var l = _val.split(',');
                    $('#field-length').val(l[l.length-1]);
                }
            }
        });

        $changeTypeVerification.on('change','.js-verification-checkbox',function () {
            var $this = $(this);
            if($this.is(':checked')){
                setVerificationData($this.data('name'),true);
            }else{
                setVerificationData($this.data('name'),null,true);
            }
        });

        $changeTypeVerification.on('change','#js-compare-enable',function () {
            var $this = $(this),
                _val= getVerificationData();

            if($this.is(':checked')){
                _val.compare = {};
                _val.compare.enable = true;
                $('#js-compare-btn').show();
            }else{
                delete _val.compare;
                $('#js-compare-btn').hide();
            }

            $('#verification-rules').val($.isEmptyObject(_val)?'':JSON.stringify(_val));
            $('#compare-content').html('');
        });

        $changeTypeVerification.on('click','#js-compare-btn',function () {
            var $this = $(this),
                _val= getVerificationData();

            if(typeof _val.compare === 'undefined') _val.compare = {};

            if(typeof _val.compare.rules === 'undefined'){
                _val.compare.rules = [];
            }

            _val.compare.rules.push({'operator':'==','compareValue':0});

            $('#compare-content').html(template('tpl-compare',{'rules':_val.compare.rules}));
            $('#verification-rules').val($.isEmptyObject(_val)?'':JSON.stringify(_val));
        });

        $changeTypeVerification.on('change','.js-compare-value',function () {
            var $this = $(this),
                index = $this.parent().index(),
                _val= getVerificationData();

            _val.compare.rules[index][$this.data('name')] = $this.val();

            $('#verification-rules').val($.isEmptyObject(_val)?'':JSON.stringify(_val));
        });

        $changeTypeVerification.on('click','.js-compare-delete',function () {
            var $this = $(this),
                $parent = $this.parent(),
                _val= getVerificationData();

            var _rules = _val.compare.rules;
            _rules.splice($parent.index(),1);
            _val.compare.rules = _rules;
            if(_val.compare.rules.length < 1) delete _val.compare.rules;

            $parent.remove();

            $('#verification-rules').val($.isEmptyObject(_val)?'':JSON.stringify(_val));
        });

        // 其他设置内容change事件
        $changeTypeSetting.on('change','input,select',function () {
            var $this = $(this),type = $changeType.val(),_val = $this.val(),_fun = $this.data('fun');

            if(_fun){
                _val = eval(_fun+'('+_val+')');
            }
            var oldData = getSettingData(type,false);

            if(type === 'relation_data' && $this.attr('name') ==='modelName'){
                if(_val === 'category' && oldData.relationType === 0){
                    commonApp.dialog.error('当值为“栏目模型”时，关联关系必须为“一对多”');
                    $this.val(oldData.modelName);
                    return false;
                }
                setSettingData(type,$this.attr('name'),_val,false);
                $('#field-name-input').val(_val+'_id'+(oldData.relationType?'s':''));
            }else{
                setSettingData(type,$this.attr('name'),_val,false);
            }
        });
    };

    /**
     * 渲染html
     * @param type
     */
    function render(type) {
        if(type === '') return;

        var _htmlContent = '',
            _htmlVerification = '',
            _htmlCompare = '';

        // 可变内容
        $tplContent.each(function () {
            var $this = $(this),
                adaptation = $this.data('adaptation');
            if($.inArray(type,adaptation.split(',')) !== -1){
                _htmlContent +=template($this.attr('id'),{'value':$($this.data('target')).val()});
            }
        });

        // 验证规则
        var verificationRules = getVerificationData();
        $tplVerification.each(function () {
            var $this = $(this),
                _type = $this.data('name'),
                adaptation = $this.data('adaptation');

            if($.inArray(type,adaptation.split(',')) !== -1){
                if(_type === 'length'){
                    _htmlVerification +=template($this.attr('id'),{'length':getValue(verificationRules,'length')});
                }else if(_type === 'other'){
                    _htmlVerification +=template($this.attr('id'),{
                        'unique':getValue(verificationRules,'unique'),
                        'email':getValue(verificationRules,'email'),
                        'ip':getValue(verificationRules,'ip'),
                        'url':getValue(verificationRules,'url')
                    });
                }else if(_type === 'unsigned'){
                    _htmlVerification +=template($this.attr('id'),{
                        'unsigned':getValue(verificationRules,'unsigned')
                    });
                }else if(_type === 'compare'){
                    var _compare = getValue(verificationRules,'compare',{});
                    _htmlVerification +=template($this.attr('id'),{
                        'enable':getValue(_compare,'enable')
                    });
                    if(getValue(_compare,'enable')){
                        _htmlCompare = template($this.attr('id'),{
                            'rules':getValue(_compare,'rules',[])
                        });
                    }
                }else if(_type === 'match'){
                    _htmlVerification +=template($this.attr('id'),{'match':getValue(verificationRules,'match')});
                }
            }
        });

        $changeTypeContent.html(_htmlContent);
        $changeTypeVerification.html(_htmlVerification);
        $('#compare-content').html(_htmlCompare);

        // 其他隐藏显示项
        if($.inArray(type,['editor','image','image_multiple','attachment','attachment_multiple','passport','captcha','date','datetime','relation_data','relation_category','city','city_multiple']) !== -1){
            var $showList = $('.field-prototypefieldmodel-is_show_list'),
                $searchList = $('.field-prototypefieldmodel-is_search');
            if($.inArray(['relation_data','date','datetime'])){
                $showList.show();
                $searchList.hide();
            }else{
                $showList.add($searchList).hide();
            }
        }else{
            $('.field-prototypefieldmodel-is_show_list').add($('.field-prototypefieldmodel-is_search')).show();
        }
        if($.inArray(type,['radio','radio_inline','checkbox','checkbox_inline','editor','image','image_multiple','attachment','attachment_multiple','passport','relation_data','relation_category','city','city_multiple']) !== -1){
            $('.field-prototypefieldmodel-placeholder').hide();
        }else{
            $('.field-prototypefieldmodel-placeholder').show();
        }

        // 其他设置项
       var $tplSetting = $('#tpl-setting-'+type);
        if($tplSetting.length > 0){
            var _settingVal = getSettingData(type);
            $changeTypeSetting.html(template('tpl-setting-'+type,_settingVal));
            $setting.val(JSON.stringify(_settingVal));
            if(type === 'relation_data') $fieldName.hide();
        }else{
            $setting.val('');
            $changeTypeSetting.html('');
            if(type !== 'relation_data') $fieldName.show();
        }
    }


    /**
     * 获取验证规则值
     */
    function getVerificationData() {
        var _val = $('#verification-rules').val();
        if(_val === ''){
            _val = {};
        }else{
            _val = JSON.parse(_val);
        }
        return _val;
    }

    /**
     * 设置验证规则值
     * @param rule
     * @param value
     * @param isRemove
     */
    function setVerificationData(rule,value,isRemove) {
        var _val = getVerificationData();

        if(typeof isRemove !== 'undefined' && isRemove === true){
            if(typeof _val[rule] !== 'undefined'){
                delete _val[rule];
            }
        }else{
            _val[rule] = value;
        }

        $('#verification-rules').val($.isEmptyObject(_val)?'':JSON.stringify(_val));
    }

    /**
     * 从一个对象中获取值
     * @param obj
     * @param key
     * @param defaultValue
     */
    function getValue(obj, key, defaultValue) {
        if(typeof obj[key] !== 'undefined'){
            return obj[key];
        }else if(typeof defaultValue !== 'undefined'){
            return defaultValue;
        }else {
            return null;
        }
    }

    /**
     * 获取“其他设置”值
     * @param type
     * @param isNew
     */
    function getSettingData(type,isNew) {
        if(typeof isNew === 'undefined') isNew = /create{1}/.test(location.href);
        var _val = typeof isNew !== 'undefined' && isNew?'':$setting.val();
        if(_val === ''){
            _val = {};
            switch(type){
                case 'relation_data':
                    _val = {'isNodeModel':1,'modelName':null,'relationType':0};
                    break;
            }
        }else{
            _val = JSON.parse(_val);
        }
        return _val;
    }

    /**
     * 设置“其他设置”值
     * @param type
     * @param rule
     * @param value
     * @param isNew
     */
    function setSettingData(type,rule,value,isNew) {
        var _val = getSettingData(type,isNew);
        _val[rule] = value;

        if(type === 'relation_data' && rule === 'modelName'){
            if(value === 'user' || value==='category'){
                _val['isNodeModel'] = 0;
            }else{
                _val['isNodeModel'] = 1;
            }
        }

        $setting.val($.isEmptyObject(_val)?'':JSON.stringify(_val));
        return _val;
    }

    /**
     * 返回
     */
    return {
        init: _initFun
    }
}();