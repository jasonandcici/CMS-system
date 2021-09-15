<?php
/**
 * @copyright
 * @link 
 * @create Created on 2016/12/2
 */

namespace common\components\home;

use common\helpers\ArrayHelper;
use Yii;
use yii\base\Action;

/**
 * 自由页
 *
 * @author 
 * @since 1.0
 */
class FreeAction extends Action
{
    public $categoryInfo;
    public $parentCategoryList;

    /**
     * @return string
     */
    public function run()
    {
        $this->categoryInfo = $this->controller->categoryInfo;
        $this->parentCategoryList = $this->controller->parentCategoryList;
        return $this->controller->render($this->findNodeListView());
    }

    /**
     * 获取node List视图
     * @param string|null $defaultView
     * @return string
     */
    public function findNodeListView($defaultView = 'index'){

        switch($this->categoryInfo->type){
            case 1:
                $view = '/'.'page/'.$this->findNodeViewPropagation(0,$defaultView);
                break;
            case 2:
                $view = $this->findNodeViewPropagation(0,$defaultView);
                break;
            default:
                $view = '/'.$this->categoryInfo->model->name.'/'.$this->findNodeViewPropagation(0,$defaultView);
                break;
        }
        return $view;
    }

    /**
     * 以向上冒泡的方式获取视图文件
     * @param $type 0:获取template字段，1：获取template_content字段
     * @param string $default 默认视图
     * @return array|string
     */
    private function findNodeViewPropagation($type = 0,$default = null){
        $default = empty($default)?'index':$default;
        $viewName = '';
        foreach(array_reverse($this->parentCategoryList,false) as $item){
            if($type === 0 && !empty($item['template'])){
                $viewName = $item['template'];
                break;
            }elseif($type === 1 && !empty($item['template_content'])){
                $viewName = $item['template_content'];
            }
        }
        return empty($viewName)?$default:$viewName;
    }
}