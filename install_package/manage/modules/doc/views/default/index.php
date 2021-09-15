<?php
/**
 * @var $dataList
 */

$this->params['navLeft'] = $dataList;
?>

<?php foreach ($dataList as $key=>$value){?>
<div class="panel panel-default api-group" id="mao-<?=$key?>">
    <div class="panel-heading">
        <h3 class="m-0"><?=Yii::t('doc',$key)?></h3>
    </div>
    <div class="panel-body api-list">
        <div class="panel-group mb-0" id="<?=$key?>" role="tablist" aria-multiselectable="true">
            <?php foreach ($value as $i=>$item){?>
            <div class="panel panel-default">
                <div class="panel-heading" role="tab">
                    <div class="collapsed" role="button" data-toggle="collapse" data-parent="#<?=$key?>" data-target="#<?=$key.'-'.$i?>" aria-expanded="false">
		                <b class="mr-2 text-<?php
                            switch ($item['type']){
                                case 'POST':
                                    echo 'warning';
                                    break;
                                case 'PUT':
                                case 'PATCH':
                                case 'PATCH,PUT':
                                case 'PUT,PATCH':
	                                echo 'primary';
                                    break;
	                            case 'DELETE':
		                            echo 'danger';
		                            break;
                                default:
	                                echo 'success';
                                    break;
                            }
                        ?>"><?=ucwords($item['type'])?></b><?=$item['title']?>
                    </div>
                    <a class="get-content js-content" href="<?=$item['url']?>" data-target="#<?=$key.'-'.$i?>-content"></a>
                </div>
                <div id="<?=$key.'-'.$i?>" class="panel-collapse collapse" role="tabpanel">
                    <div class="panel-body api-detail">
                        <h4>接口</h4>
                        <div class="api-header">
                            <h4 class="api" data-api="<?php $arrApi = explode('?',$item['api']);echo $arrApi[0];?>"><?=$item['api']?></h4>
                        </div>
                        <div id="<?=$key.'-'.$i?>-content"></div>
                    </div>
                </div>
            </div>
            <?php }?>
        </div>
    </div>
</div>
<?php } ?>
<?php $this->beginBlock('endBody');?>
<script>
    $(function () {
        // 获取数据
        $('.js-content').click(function () {
            var $this = $(this);
            if($this.hasClass('disabled')) return false;
            $this.addClass('disabled');
            var $loading = $('<span style="display: none;">Loading...</span>');
            $this.html($loading);
            $loading.fadeIn();

            $.ajax({
                type: "GET",
                url: $this.attr('href'),
                dataType: "html",
                success:function (res) {
                    $this.fadeOut();
                    $($this.data('target')).html(res);
                    $($this.prev().data('target')).collapse('show');
                },
                error:function(){
                    $this.removeClass('disabled');
                    $loading.text('获取数据失败');
                }
            });


            return false;
        });

        // 重置affix
        $('#common-rules,#server-config').on('hidden.bs.collapse', function () {
            $('#nav-aside').affix('checkPosition');
        }).on('shown.bs.collapse', function () {
            $('#nav-aside').affix('checkPosition');
        });

        // 获取请求类型字段
        var $body = $('body');
        $body.on('click','.js-get-fields',function () {
            var $this = $(this);
            if($this.hasClass('disabled')) return false;
            $this.addClass('disabled');
            var $p = $this.parents('.api-detail');
            $.ajax({
                type: "post",
                url: $this.attr('href'),
                data:$p.find('form').serializeArray(),
                dataType: "json",
                success:function (res) {
                    if(res.status){
                        var $fieldList,
                            _action = $this.data('action');
                        if(_action === 'get'){
                            $fieldList = $p.find('.field-list');
                            if($fieldList.length < 1){
                                $fieldList = $('<pre class="field-list"></pre>');
                                $fieldList.html(res.message);
                                $p.append('<h4>字段</h4>');
                                $p.append($fieldList);
                            }else{
                                $fieldList.html(res.message);
                            }

                            if(typeof res.expand !== 'undefined'){
                                $.each(res.expand,function (i,n) {
                                    var _html = '';
                                    $.each(n,function (ii,nn) {
                                        _html += '<option value="'+ii+'">'+nn+'</option>';
                                    });
                                    $p.find('select[data-name="'+i+'"]').html(_html);
                                });
                            }
                        }else{
                            $fieldList = $p.find('#'+_action);
                            if($fieldList.length<1){
                                $p.find('.nav-tabs').append('<li role="presentation"><a href="#'+_action+'" role="tab" data-toggle="tab">post参数</a></li>');
                                $fieldList = $('<div role="tabpanel" class="tab-pane" id="'+_action+'"></div>');
                                $fieldList.html($(res.message).find('#'+_action).html());
                                $this.before($fieldList)
                            }else{
                                $fieldList.html($(res.message).find('#'+_action+'-post').html());
                            }
                            $p.find('.nav-tabs a[href="#'+_action+'"]').trigger('click');
                        }
                    }else{
                        alert(res.message);
                    }
                },
                error:function(e){
                    console.log(e);
                },
                complete:function () {
                    $this.removeClass('disabled');
                }
            });
            return false;
        });

        //api参数替换
        $body.on('change','.js-change',function () {
           var $this = $(this),
               $api = $this.parents('.api-detail').find('.api'),
               _val = $this.val(),
               _name = $this.data('name'),
               _url = $api.text();

            if(!_val){
                _val = '{'+$this.data('alias')+'}';
            }

           if(getUrlParamFun(_name,_url) === null){
                var _tmp = $api.data('api').replace('{'+$this.data('alias')+'}',_val);
                _url = _url.split('?');
                if(typeof _url[1] === 'undefined'){
                    $api.text(_tmp);
                }else{
                    $api.text(_tmp+'?'+_url[1]);
                }
           }else{
               $api.text(setUrlParamFun(_name,_val,_url));
           }
        });
    });

    /**
     * 获取url参数
     * @parm  string name 参数名
     *
     * @return string
     * */

    function getUrlParamFun(name,url) {
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
    }

    /**
     * 设置url参数
     * @param name
     * @param value
     * @returns {string}
     */
    function setUrlParamFun(name,value,url){
        var _search,_url;
        var _u = url.split('?');
        _search = typeof _u[1] !=='undefined'?_u[1]:'';
        _url = url;

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
    }
</script>
<?php $this->endBlock();?>
