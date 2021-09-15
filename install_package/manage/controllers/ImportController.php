<?php
/**
 * @copyright
 * @link 
 * @create Created on 2017/9/15
 */

namespace manage\controllers;

use common\components\manage\ManageController;
use common\entity\models\PrototypeCategoryModel;
use common\helpers\ArrayHelper;
use manage\models\ImportPrototypeForm;
use Yii;
use yii\web\NotFoundHttpException;


/**
 * 批量导入
 *
 * @author 
 * @since 1.0
 */
class ImportController extends ManageController
{

    //php init时文件夹权限问题

	/**
	 * 原型数据导入
	 *
	 * @param bool $category_id 获取模板
	 *
	 * @return string
	 * @throws NotFoundHttpException
	 * @throws \PHPExcel_Exception
	 * @throws \PHPExcel_Reader_Exception
	 * @throws \PHPExcel_Writer_Exception
	 * @throws \yii\db\Exception
	 */
    public function actionPrototype($category_id = false){
        $model = new ImportPrototypeForm();

        // 返回栏目模板
        if($category_id){
            $model->getTpl($category_id);
        }

        // 页面
        else{
            if(Yii::$app->request->isPost){
                if ($model->load(Yii::$app->request->post()) && $model->save()) {
                    $this->success([Yii::t('common','Operation successful')]);
                }
                $this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrors()]);
            }

            return $this->render($this->action->id,[
                'model'=>$model,
                'categoryList'=>ArrayHelper::tree($this->getCategory(false))
            ]);
        }
    }


    /**
     * 获取菜单列
     * @param bool $titleHandle 是否对标题进行处理
     * @return array
     * @throws NotFoundHttpException
     */
    protected function getCategory($titleHandle = true){
        $category = Yii::$app->cache->get('category'.$this->siteInfo->id);
        if($category == null){
            $category = PrototypeCategoryModel::find()->where(['site_id'=>$this->siteInfo->id])->indexBy('id')->with(['model'])->orderBy(['sort'=>SORT_ASC,'id'=>SORT_ASC])->asArray()->all();
            Yii::$app->cache->set('category'.$this->siteInfo->id,$category);
        }

        // 权限控制栏目显示
        $userPermissionList = $this->isSuperAdmin?[]:Yii::$app->getAuthManager()->getPermissionsByUser(Yii::$app->getUser()->getId());
        $categoryList = [];
        foreach($category as $i=>$item){
            if($this->isSuperAdmin || ($item['type'] == 0 && array_key_exists('prototype/node/index?category_id='.$item['id'],$userPermissionList)) || ($item['type'] == 1 && array_key_exists('prototype/node/page?category_id='.$item['id'],$userPermissionList)) || $item['type'] == 2 || $item['type'] == 3){
                $categoryList[$item['id']] = $item;
            }
        }
        unset($category);

        $categoryList =  ArrayHelper::linear($categoryList,' ├ ');
        if($titleHandle){
            foreach($categoryList as $i=>$item){
                $categoryList[$i]['title'] = $item['str'].$item['title'];
            }
        }
        return $categoryList;
    }

    /**
     * 富文本编辑器
     */
    public function actionUeditor(){
        $this->layout = 'base';
        return $this->render($this->action->id);
    }


    /**
     * 用户导入
     */
    public function actionMember(){

    }
}