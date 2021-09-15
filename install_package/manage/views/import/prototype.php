<?php
/**
 * @var $model
 * @var $categoryList
 */

use common\helpers\HtmlHelper;
use common\helpers\UrlHelper;
use manage\assets\FormAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use common\widgets\ActiveForm;

$this->title = '数据批量导入';
FormAsset::register($this);
\manage\assets\ZtreeAsset::register($this);
$this->registerJsFile('@web/js/plugins/uploadUeditor.js',['depends' => [\manage\assets\UeditorAsset::className()]]);
$this->registerJs("formApp.init();commonApp.formYiiAjax($('#j_form'));uploadUeditor.init({serverUrl:'".Url::to(['/files/index'])."'});", View::POS_READY);
$this->registerCss('
    .crumbs span{color: #666;}
    .crumbs span:after{  margin: 0 10px;  content:"/";  }
    .crumbs .active{  color: #c7254e;  }
    .crumbs span.active:after{  display: none;  }
    .btn-primary-info{margin-right: 10px;}
    .main-aside{position: static!important;width: auto!important;background-color: transparent;}
    .main-aside nav{width: auto;height: auto;}
    .main-aside .tree-icon{background-image: url("images/icon_tree.png") ;}
    .main-aside nav li, .main-aside nav a{color: #333;}
    .main-aside nav a:hover, .main-aside nav a:after{background-color:#eee;}
    .main-aside .active > a, .main-aside .active > a:hover{background-color:#333;color:#fff;}
    .main-aside .active > a .tree-icon, .main-aside .active > a:hover .tree-icon{background-image: url("images/icon_tree-yellow.png");}
');
?>
<!-- 表单开始 -->
<div class="panel panel-default form-data">
    <div class="panel-body">
        <?php $form = ActiveForm::begin([
            'id'=>'j_form',
            'options'=>['class' => 'form-horizontal'],
            'fieldConfig'=>['template'=>'{label}<div class="col-sm-17">{input}{error}{hint}</div>', 'labelOptions'=>['class'=>'col-sm-4 control-label']]
        ]); ?>
        <!-- 表单控件开始 -->
        <div class="form-group">
            <label class="control-label col-sm-4"><?=$model->getAttributeLabel('categoryId')?></label>
            <div class="col-sm-17">
                <?=\common\helpers\HtmlHelper::activeHiddenInput($model,'categoryId',['id'=>'select-category'])?>
                <p class="form-control-static crumbs hide" id="select-crumbs"><span>产品中心</span><span>国内产品</span><span class="active">vbsx</span></p>
                <a class="btn btn-primary-info" id="select-category-model-btn" href="#select-category-model" data-toggle="modal">选择栏目</a>
                <a class="btn btn-default disabled" id="js-download-tpl">下载数据模板</a>
            </div>
        </div>

        <?= $form->field($model, "excelFile",[
    'template'=> '{label}<div class="col-sm-17"><div class="list-file clearfix j_upload_single_file">{input}<ul class="upload_list"></ul><a class="upload btn btn-default upload_btn" href="javascript:;">文件上传</a></div>{error}{hint}</div>'])->hiddenInput(['class'=>'upload_input'])->hint('注意：文件格式必须为<code>xlsx</code>或<code>xls</code>，且必须为该栏目<code>指定的数据模板</code>。富文本编辑器工具 <a style="color:#318ec1;" href="'.UrlHelper::toRoute(['import/ueditor'],true).'" target="_blank">点击使用</a>。');?>

        <?=$form->field($model, "attachment",[
            'template'=> '{label}<div class="col-sm-17"><div class="list-file clearfix j_upload_multiple_file">{input}<ul class="upload_list"></ul><a class="upload btn btn-default upload_btn" href="javascript:;">附件上传</a></div>{error}{hint}</div>'])->hiddenInput(['class'=>'upload_input'])->hint('Excel中的<code>图片和附件文件请打包成<b>zip</b></code>格式文件，在此处上传。打包zip文件时，<code>不要包含附件根目录</code>， <a href="#zip-example" data-toggle="modal" style="color:#318ec1;">查看示例</a>。');?>

        <!-- 表单控件结束 -->
        <div class="form-data-footer">
            <div class="form-group">
                <div class="col-sm-offset-4 col-sm-14">
                    <?php if(extension_loaded('zlib')){?>
                        <?= Html::submitButton(Yii::t('common','Submit'), ['class' => 'btn btn-primary']) ?>
                    <?php }else{ ?>
                        <?= Html::submitButton(Yii::t('common','Submit'), ['class' => 'btn btn-info disabled']) ?>
                        <span>php未开启“zlib”扩展，无法使用此功能，请联系运维人员。</span>
                    <?php }?>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<div class="modal fade" id="select-category-model" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><strong>选择栏目</strong></h4>
            </div>
            <div class="modal-body main-aside">
                <nav>
                    <ul class="nav-aside">
                    <?php
                    /**
                     * tree数组输出html
                     * @param $data
                     * @param int $pid
                     * @return string
                     */
                    function navHtml($data, $pid = 0, $count = 0){
                        $_html = '';
                        foreach($data as $key=>$value){

                            // class类名
                            $_class = '';
                            $childHasNode = false;
                            if(!empty($value['child'])){
                                $childHasNode = childHasNode($value['child']);
                            }

                            switch($value['type']){
                                case 0:
                                    $_url = '#model'.$value['model_id'];
                                    break;
                                default:
                                    $_url = 'javascript:;';
                                    break;
                            }

                            if((empty($value['child']) || (!empty($value['child']) && !$childHasNode)  ) &&  $value['type'] > 0){
                                continue;
                            }elseif(empty($value['child']) || !$childHasNode){
                                $_class = 'class="tree-nch"';
                            }

                            // 生成li
                            $_html .= '<li '.$_class.' data-id="'.$value['id'].'"><a href="'.$_url.'"><span class="tree-icon"></span>'.$value['title'].'</a>';
                            if($value['pid'] == $pid){
                                $_html .= navHtml($value['child'],$value['id'],$count+1);
                            }

                            $_html .='</li>';
                        }

                        return $_html?($pid==0?$_html:'<ul>'.$_html.'</ul>'):'';
                    }
                    function childHasNode($child){
                        $return = false;
                        foreach($child as $item){
                            if($item['type'] < 1){
                                $return = true;
                            }elseif(!empty($item['child'])){
                                $return = childHasNode($item['child']);
                            }
                            if($return) break;
                        }
                        return $return;
                    }
                    echo navHtml($categoryList);
                    ?>
                </ul>
                </nav>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                <button id="select-category-confirm" type="button" class="btn btn-primary">确定</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="zip-example" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><strong>附件压缩示例</strong></h4>
            </div>
            <div class="modal-body">
                <h4>1、现在有以下数据：</h4>
                <p><?=HtmlHelper::img('@web/images/import-demo-1.png',['class'=>'img-responsive'])?></p>
                <h4>2、压缩后的结果：</h4>
                <p><?=HtmlHelper::img('@web/images/import-demo-2.png',['class'=>'img-responsive'])?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">确定</button>
            </div>
        </div>
    </div>
</div>

<!-- 表单结束 -->
<?php $this->beginBlock('endBlock');?>
<script>
    $(function () {
        var $model = $('#select-category-model');
        $model.find('li:not(.tree-nch)').each(function(){
            var $this = $(this),
                $child = $this.children(),
                $a = $child.filter('a'),
                $icon = $a.find('.tree-icon');

            if($icon.data('bindclick')) return;
            $icon.data('bindclick',true);
            var $ul = $child.filter('ul'),
                accordionFun = function(){
                    if($this.hasClass('tree-open')){
                        $this.removeClass('tree-open');
                        $ul.slideUp();
                    }else{
                        $this.addClass('tree-open');
                        $ul.slideDown();
                    }
                };

            // 绑定展开和折叠事件
            $icon.click(function(e){
                accordionFun();
                e.stopPropagation();
                e.preventDefault();
            });

            //绑定空链接
            $a.click(function(){
                if($a.attr('href') === 'javascript:;'){
                    accordionFun();
                }
            });

        });
        $model.find('li').on('click','a',function () {
            var $this = $(this),
                _href = $this.attr('href');

            if(_href !== 'javascript:;' || (_href === 'javascript:;' && $this.siblings().length<1)){
                $model.find('li.active').removeClass('active');
                $this.parent().addClass('active');
            }
        });

        // 选择栏目
        var $jsDownloadTpl = $('#js-download-tpl'),
            $selectCategoryModelBtn = $('#select-category-model-btn'),
            $selectCrumbs = $('#select-crumbs'),
            $selectCategory = $('#select-category');

        $('#select-category-confirm').click(function () {
            var $active = $model.find('.active'),
                _id = $active.data('id');
            $model.modal('hide');
            if(!_id) return false;
            $jsDownloadTpl.removeClass('disabled').data('id',_id);
            $selectCategory.val(_id);
            $selectCategoryModelBtn.text('重新选择');

            $selectCrumbs.removeClass('hide').html(getCrumbs($active.children('a'),true));

        });

        function getCrumbs($a,isActive) {
            var _html = '<span'+(isActive?' class="active"':'')+'>'+$a.text()+'</span>',
                $ul = $a.parent().parent('ul');
            if($ul.length > 0){
                var $prev = $ul.prev();
                if($prev.length > 0) _html = getCrumbs($prev,false)+_html;
            }
            return _html;
        }

        $jsDownloadTpl.click(function () {
            var _id = $(this).data('id');
            if(_id){
                window.open("<?=\common\helpers\UrlHelper::current()?>&category_id="+_id);
            }
        });
    });
</script>
<?php $this->endBlock();?>
