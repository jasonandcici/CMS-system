/**
 * Created by chris on 2015-11-13.
 */
(function($) {
    // 插件的定义
    $.fn.dataSelector = function(options,intifun,callback) {

        var opts = $.extend({}, $.fn.dataSelector.defaults, options);
        return this.each(function() {
            var $this = $(this);
            var o = $.meta ? $.extend({}, opts, $this.data()) : opts;

            if(opts.selectedIds.length == 0 && opts.dataSourceInput){
                var _val = opts.dataSourceInput.val();
                opts.selectedIds = _val?_val.split(','):[];
            }
            $this.data('options',opts);
            if(intifun && typeof intifun == 'function') intifun();

            $this.click(function(){
                var currPpts = $this.data('options');
                if($this.hasClass('disabled')) return false;
                commonApp.dialog.iframe($this.text(),$this.attr('href'),{
                    cancel:function(){
                        if(currPpts.dataSourceInput){
                            var _val = currPpts.dataSourceInput.val();
                            currPpts.selectedIds = _val?_val.split(','):[];
                        }
                        $this.data('options',currPpts);
                    },
                    confirm:function(){
                        var  selectedIds  =  window.frames['dialog-iframe'].getData();
                        currPpts.selectedIds = selectedIds;
                        $this.data('options',currPpts);
                        if(callback && typeof callback == 'function') callback(selectedIds,$this);
                    },size:'large',dialogIframeHeight:500,dialogIframeName:'dialog-iframe'
                });
                $("#dialog-iframe").data("selectedIds",currPpts.selectedIds);
                return false;
            });
        });
    };

    // 插件的defaults
    $.fn.dataSelector.defaults = {
        dataSourceInput:null, // 存放数据源的input（jquery对象）
        selectedIds:[],
        skipIds:[],
        selectorId:""
    };
// 闭包结束
})(jQuery);