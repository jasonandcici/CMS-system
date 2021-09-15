<?php
// +----------------------------------------------------------------------
// | SimplePig
// +----------------------------------------------------------------------
// | Copyright (c) 2016-+ http://www.zhuyanjun.cn.
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | Created on 2016/3/13 21:49.
// +----------------------------------------------------------------------

/**
 * 控制器curd操作规范接口
 */

namespace common\components;


interface CurdInterface
{
    /**
     * 列表页
     * @return mixed
     */
    public function actionIndex();

    /**
     * 新增数据
     * @return mixed
     */
    public function actionCreate();

    /**
     * 更新数据
     * @param $id int 数据id
     * @return mixed
     */
    public function actionUpdate($id);

    /**
     * 删除数据
     * @param $id int|string 数据id，string类型必须有分隔符‘,’,例如“2,4,5,6”
     * @return mixed
     */
    public function actionDelete($id);
}