<?php
/**
 * @var $maxFileSize
 */

use manage\assets\FormAsset;

$this->registerJsFile('@web/js/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js',['depends' => [FormAsset::className()]]);
$this->registerCssFile('@web/js/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css');
?>

<?php $this->beginBlock('endBlock'); ?>
<script>
    $(function () {
        var $watermarkType = $('#systemconfigmodel-28-value'),
            watermarkType = $watermarkType.find(':checked').val(),
            watermarkTypeChange = function (value) {
                if(parseInt(value)){
                    $('.field-systemconfigmodel-31-value,.field-systemconfigmodel-32-value,.field-systemconfigmodel-33-value').hide();
                    $('.field-systemconfigmodel-29-value,.field-systemconfigmodel-30-value').show();
                }else{
                    $('.field-systemconfigmodel-31-value,.field-systemconfigmodel-32-value,.field-systemconfigmodel-33-value').show();
                    $('.field-systemconfigmodel-29-value,.field-systemconfigmodel-30-value').hide();
                }
            };
        watermarkTypeChange(watermarkType);
        $watermarkType.find('input:radio').change(function () {
            watermarkTypeChange($(this).val());
        });

        var $maxUploadSize = parseInt($('#max-upload-size').text());
        var $number = $('#systemconfigmodel-30-value');
        $number = $number.add($('#systemconfigmodel-32-value'));
        $number = $number.add($('#systemconfigmodel-34-value'));
        $number = $number.add($('#systemconfigmodel-37-value'));
        $number = $number.add($('#systemconfigmodel-38-value'));
        $number = $number.add($('#systemconfigmodel-40-value'));
        $number.each(function (i,n) {
           var $this = $(n),
             _tVal = parseInt($this.val());
            $this.attr('type','number');
            if($this.attr('id') === 'systemconfigmodel-30-value'){
                if(_tVal > 100 ||  _tVal < 1) $this.siblings('.hint-block').append(' <span class="upload-error text-danger">值只能在0~100之间</span>');
                $this.change(function () {
                    var _val = parseInt($(this).val());
                    if(_val< 1){
                        $(this).val(1);
                    }else if(_val > 100){
                        $(this).val(100);
                    }
                    $this.parent().find('.upload-error').remove();
                });
            }else{
                if($this.attr('id') === 'systemconfigmodel-37-value' || $this.attr('id') === 'systemconfigmodel-32-value') return;
                if(_tVal > $maxUploadSize || _tVal < 0)  $this.siblings('.hint-block').append(' <span class="upload-error text-danger">超过最大服务器限制'+$maxUploadSize+'M</span>');
                $this.change(function () {
                    var _val = parseInt($(this).val());
                    if(_val > $maxUploadSize){
                        $(this).val($maxUploadSize);
                    }
                    $this.parent().find('.upload-error').remove();
                });
            }
        });

        var $hr = $('.field-systemconfigmodel-28-value');
        $hr = $hr.add('.field-systemconfigmodel-34-value');
        $hr = $hr.add('.field-systemconfigmodel-35-value');
        $hr = $hr.add('.field-systemconfigmodel-40-value');
        var titles = ['水印','图片','附件','视频'];
        $hr.each(function (i,n) {
            $(n).before('<div class="form-group"><div class="col-sm-17 col-sm-offset-4"><h4 style="font-weight: bold;border-bottom: 1px solid #ddd;padding-bottom: 8.5px;margin-bottom: 0;margin-top: 0;">'+titles[i]+'</h4></div></div>');
        });

        $('#systemconfigmodel-33-value').colorpicker({
            colorSelectors: {
                'black': '#000000',
                'white': '#ffffff',
                'red': '#FF0000',
                'default': '#777777',
                'primary': '#337ab7',
                'success': '#5cb85c',
                'info': '#5bc0de',
                'warning': '#f0ad4e',
                'danger': '#d9534f'
            }
        });
    });
</script>
<?php $this->endBlock(); ?>