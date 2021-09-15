<?php
/**
 * @var $model
 */

use common\helpers\ArrayHelper;
use common\helpers\HtmlHelper;
use common\widgets\ActiveForm;
use yii\web\View;

$this->params['active'] = 'profile';

// 通过自定义get参数，可以实现视图分隔
$view = Yii::$app->getRequest()->get('view','profile');
?>
<ul class="nav nav-tabs">
    <?php foreach(['profile'=>'修改资料','avatar'=>'上传头像'] as $k=>$v){?>
        <li<?=$view == $k?' class="active"':''?>>
            <a href="<?=$this->generateUserUrl('profile',($k == 'profile'?[]:['params'=>['view'=>$k]]))?>"><?=$v?></a>
        </li>
    <?php }?>
</ul>
<br>

<!-- 登录表单 -->
<?php $form = ActiveForm::begin(['id' => 'js-form','validateOnBlur'=>false, 'validateOnSubmit'=>true]); ?>

    <?php if($view == 'avatar'){
        echo '<!-- 头像上传 -->';
        echo HtmlHelper::activeHiddenInput($model,'nickname');
        echo HtmlHelper::activeHiddenInput($model,'avatar');
        $this->registerCssFile('@web/js/cropperjs/cropper.min.css');
        $this->registerJsFile('@web/js/cropperjs/cropper.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
    ?>

    <div class="dk-cropper clearfix">
        <div class="dk-cropper-container">
            <?=empty($model->avatar)?HtmlHelper::img('@web/images/avatar.png',['id'=>'js-cropper-image']):HtmlHelper::getImgHtml($model->avatar,['id'=>'js-cropper-image'])?>
        </div>

        <div class="dk-cropper-preview-wrapper">
            <div class="dk-cropper-preview-lg">
                <div class="dk-cropper-preview"></div>
            </div>
            <div class="dk-cropper-preview-md">
                <div class="dk-cropper-preview"></div>
            </div>
            <div class="dk-cropper-preview-sm">
                <div class="dk-cropper-preview"></div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="btn-group mr-1 js-cropper-buttons">
            <button type="button" class="btn btn-default" data-method="zoom" data-option="0.1" title="放大">放大</button>
            <button type="button" class="btn btn-default" data-method="zoom" data-option="-0.1" title="缩小">缩小</button>
            <button type="button" class="btn btn-default" data-method="move" data-option="-10" data-second-option="0" title="左移">左移</button>
            <button type="button" class="btn btn-default" data-method="move" data-option="10" data-second-option="0" title="右移">右移</button>
            <button type="button" class="btn btn-default" data-method="move" data-option="0" data-second-option="-10" title="上移">上移</button>
            <button type="button" class="btn btn-default" data-method="move" data-option="0" data-second-option="10" title="下移">下移</button>
            <button type="button" class="btn btn-default" data-method="rotate" data-option="90" title="旋转">旋转</button>
            <button type="button" class="btn btn-default" data-method="reset" title="重设">重置</button>
        </div>
        <label class="btn btn-default" for="select-Image" title="上传头像">
            <input type="file" id="select-Image" class="sr-only" accept="image/jpg,image/jpeg,image/png,image/gif">✚ 选择图片
        </label>
        <input type="hidden" id="fine-input">
    </div>

    <?php }else{ ?>
        <!-- 修改资料 -->
        <?= $form->field($model, 'nickname')->textInput()?>
        <?= $form->field($model, 'gender')->radioList(['male'=>'男','female'=>'女','secrecy'=>'保密'],['itemOptions'=>['labelOptions'=>['class'=>'radio-inline']]])?>
        <?= $form->field($model, 'birthday')->textInput(['placeholder'=>'格式：'.date('Y-m-d'),])?>
        <?= $form->field($model, 'blood')->dropDownList(ArrayHelper::unifyKeyValue(['A','B','AB','O']))?>

        <div class="form-group">
            <?=HtmlHelper::activeHiddenInput($model,'country',['value'=>'中国'])?>
            <?php
            $this->registerJsFile('@web/js/city/jquery.cityselect.js',['depends' => [\yii\web\JqueryAsset::className()]]);
            $this->registerJs("
                $('#js-city-change').citySelect({
                    url:'".Yii::getAlias('@web')."/js/city/city.min.js',
                    prov:'".$model->province."',
                    city:'".$model->city."',
                    dist:'".$model->area."',
                });
                ", View::POS_READY);
            ?>
            <label class="control-label">地址</label>
            <div class="row" id="js-city-change">
                <?=$form->field($model, 'province',[
                    'options'=>['class'=>'col-sm-4'],
                    'template'=>'{input}',
                ])->dropDownList([],['class'=>'form-control prov']); ?>
                <?=$form->field($model, 'city',[
                    'options'=>['class'=>'col-sm-4'],
                    'template'=>'{input}',
                ])->dropDownList([],['class'=>'form-control city']); ?>

                <?=$form->field($model, 'area',[
                    'options'=>['class'=>'col-sm-4'],
                    'template'=>'{input}',
                ])->dropDownList([],['class'=>'form-control dist']); ?>
            </div>
        </div>

        <?= $form->field($model, 'street')->textInput()?>

        <?= $form->field($model, 'signature')->textarea()?>
    <?php } ?>
    <?= HtmlHelper::submitButton('保存',['class'=>'btn btn-primary','data-loading-text'=>'提交中...']) ?>
<?php ActiveForm::end(); ?>

<?php $this->beginBlock('endBody');?>
    <script>
        $(function () {
            var $inputImage = $('#select-Image');
            <?php if($view == 'avatar'){?>
            // 头像上传
            var $previews = $('.dk-cropper-preview'),
                $image = $('#js-cropper-image');

            var $fileInput = $('#fine-input');

            $image.cropper({
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 1,
                restore: false,
                highlight: false,
                cropBoxMovable: false,
                cropBoxResizable: false,
                aspectRatio: 1,
                ready: function (e) {
                    var $clone = $(this).clone().removeClass('cropper-hidden');

                    $clone.css({
                        display: 'block',
                        width: '100%',
                        minWidth: 0,
                        minHeight: 0,
                        maxWidth: 'none',
                        maxHeight: 'none'
                    });

                    $previews.css({
                        width: '100%',
                        overflow: 'hidden'
                    }).html($clone);
                },
                crop: function (e) {
                    var imageData = $(this).cropper('getImageData');
                    var previewAspectRatio = e.width / e.height;

                    $previews.each(function () {
                        var $preview = $(this);
                        var previewWidth = $preview.width();
                        var previewHeight = previewWidth / previewAspectRatio;
                        var imageScaledRatio = e.width / previewWidth;

                        $preview.height(previewHeight).find('img').css({
                            width: imageData.naturalWidth / imageScaledRatio,
                            height: imageData.naturalHeight / imageScaledRatio,
                            marginLeft: -e.x / imageScaledRatio,
                            marginTop: -e.y / imageScaledRatio
                        });
                    });

                    $fileInput.val($(this).cropper('getCroppedCanvas',{width:300,height:300}).toDataURL("image/png"));
                }
            });

            // 功能按钮
            $('.js-cropper-buttons').on('click', '[data-method]', function () {
                var $this = $(this);
                var data = $this.data();
                var $target;
                var result;

                if ($this.prop('disabled') || $this.hasClass('disabled')) {
                    return;
                }

                if ($image.data('cropper') && data.method) {
                    data = $.extend({}, data); // Clone a new one
                    if (typeof data.target !== 'undefined') {
                        $target = $(data.target);
                        if (typeof data.option === 'undefined') {
                            try {
                                data.option = JSON.parse($target.val());
                            } catch (e) {
                                console.log(e.message);
                            }
                        }
                    }
                    result = $image.cropper(data.method, data.option, data.secondOption);
                    if ($.isPlainObject(result) && $target) {
                        try {
                            $target.val(JSON.stringify(result));
                        } catch (e) {
                            console.log(e.message);
                        }
                    }
                }
                return false;
            });

            // 选择图片
            var URL = window.URL || window.webkitURL;
            var blobURL;

            if (URL) {
                $inputImage.change(function () {
                    var files = this.files;
                    var file;
                    if (!$image.data('cropper')) {
                        return;
                    }
                    if (files && files.length) {
                        file = files[0];
                        if (/^image\/\w+$/.test(file.type)) {
                            $inputImage.data('new',true);
                            blobURL = URL.createObjectURL(file);
                            $image.one('built.cropper', function () {
                                // Revoke when load complete
                                URL.revokeObjectURL(blobURL);
                            }).cropper('reset').cropper('replace', blobURL);
                        } else {
                            window.alert('Please choose an image file.');
                        }
                    }
                    $('.btn-group button').removeAttr('disabled');
                });
            } else {
                $inputImage.prop('disabled', true).parents('.btn').addClass('disabled');
            }
            <?php } ?>

            // 表单提交
            $('#js-form').on('beforeSubmit', function (e) {
                var $form = $(this);

                <?php if($view == 'avatar'){?>
                if(!$inputImage.data('new')) return false;
                <?php }?>

                var $submit = $form.find('[type="submit"]');
                $submit.button('loading');

                <?php if($view == 'avatar'){?>
                //上传头像
                $.post('<?=$this->generateFormUrl('upload',['params'=>['mode'=>'base64']])?>',{'UploadForm[file]':$fileInput.val()},function (response) {
                    if(typeof response === 'string') response = JSON.parse(response);
                    if(response.status){
                        $('#profilefrom-avatar').val('[{"alt":"<?=$model->nickname?>","file":"'+response.files[0].file+'"}]');
                        submitForm($form,$submit);
                    }else{
                        $submit.button('reset');
                        alert(response.message);
                    }
                });
                <?php }else{ ?>
                submitForm($form,$submit);
                <?php }?>
            }).on('submit', function (e) {
                e.preventDefault();
            });

            function submitForm($form,$submitBtn) {
                $.post($form.attr('action'),$form.serialize(),function (response) {
                    if(typeof response === 'string') response = JSON.parse(response);
                    $submitBtn.button('reset');
                    if(response.status){
                        alert('操作成功。');
                    }else{
                        alert(response.message);
                    }
                });
            }
        });
    </script>
<?php $this->endBlock();?>