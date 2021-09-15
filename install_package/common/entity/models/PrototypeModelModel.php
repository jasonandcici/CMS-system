<?php

namespace common\entity\models;

use common\entity\domains\PrototypeFieldDomain;
use common\entity\domains\PrototypeModelDomain;
use common\helpers\ArrayHelper;
use common\helpers\FileHelper;
use Yii;
use yii\gii\CodeFile;

/**
 * This is the model class for table "{{%prototype_model}}".
 */
class PrototypeModelModel extends PrototypeModelDomain
{
	/**
	 * 查询模型列表
	 * @param null $modelId
	 * @return array|mixed|\yii\db\ActiveRecord[]
	 */
	static public function findModel($modelId = null){
		$model = Yii::$app->cache->get('model');
		if(!$model){
			$model = self::find()->with(['fields'])->select(self::querySelectExclude(new self(),['extend_code']))
			             ->indexBy('id')->orderBy(['id'=>SORT_ASC])->asArray()->all();
			Yii::$app->cache->set('model',$model);
		}

		if($modelId !== null){
			if(array_key_exists($modelId,$model)){
				return ArrayHelper::convertToObject($model[$modelId]);
			}else{
				return null;
			}
		}

		return $model;
	}

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFields(){
        return $this->hasMany(PrototypeFieldModel::className(),['model_id'=>'id'])->orderBy(['sort'=>SORT_ASC]);
    }

    /**
     * 生成模型
     */
    public function generate(){
        $db = Yii::$app->getDb();
        $this->name = strtolower($this->name);

        $basePath = Yii::$app->getBasePath().'/..';
        if(!is_writable($basePath.'/common/entity/nodes') || !is_writable(Yii::$app->getBasePath().'/modules/prototype/views/form') || !is_writable(Yii::$app->getBasePath().'/modules/prototype/views/node')){
            $this->addError('id','没有权限操作此文件夹。');
            return false;
        }

        $oldFields = $db->getTableSchema($db->tablePrefix.'node_'.$this->name);
        $isNews = true;
        if(!empty($oldFields)){
            $oldFields = ArrayHelper::getColumn($oldFields->columns,'name');
            if($this->type === 0){
                unset($oldFields['id'],$oldFields['model_id'],$oldFields['site_id'],$oldFields['category_id'],$oldFields['title'],$oldFields['sort'],$oldFields['status'],$oldFields['template_content'],$oldFields['is_push'],$oldFields['is_comment'],$oldFields['views'],$oldFields['create_time'],$oldFields['update_time'],$oldFields['seo_title'],$oldFields['seo_keywords'],$oldFields['seo_description'],$oldFields['jump_link'],$oldFields['is_login'],$oldFields['layouts'],$oldFields['count_user_relations']);
            }else{
                unset($oldFields['id'],$oldFields['model_id'],$oldFields['site_id'],$oldFields['status'],$oldFields['create_time']);
            }

            $newOldFields = [];
            foreach ($oldFields as $item){
                $newOldFields[] = $item;
            }

            $oldFields = $newOldFields;
            unset($newOldFields);
            $isNews = false;
        }

        $fields = $this->fields;
        foreach ($fields as $i=>$item){
            $verificationRules =  $item->custom_verification_rules? json_decode($item->custom_verification_rules, true) : [];
            $setting =  $item->setting? json_decode($item->setting, true) : [];
            $fields[$i]->options =  self::optionResolve($item->options);

            if($item->type == 'relation_data'){
                $setting['relationType'] = intval($setting['relationType']);
                $setting['isNodeModel'] = intval($setting['isNodeModel']);
                if(!$setting['relationType']) $verificationRules['unsigned'] = true;
            }
            $fields[$i]->setting = $setting;
            $fields[$i]->custom_verification_rules = $verificationRules;
        }

        // 生成生成sql
        $relationSql = '';
        $modelSetting = empty($this->setting)?[]:json_decode($this->setting,true);
        if($isNews){
            $sql = 'CREATE TABLE `'.$db->tablePrefix.'node_'.$this->name.'` (';
            $sql .= "`id` int(10) unsigned NOT NULL AUTO_INCREMENT,";
            $sql .= "`site_id` smallint(5) unsigned NOT NULL,";
            $sql .= "`model_id` smallint(5) unsigned NOT NULL COMMENT '栏目所属模型id',";
            if($this->type === 0){
                $sql .= "`category_id` smallint(5) unsigned NOT NULL,";
                $sql .= "`title` varchar(255) NOT NULL COMMENT '标题',";
            }

            $foreingKey = [];
            foreach ($fields as $item){
                if($item->type == 'relation_data'){
                    if($item->setting['relationType'] === 1){
                        $relationSql .=self::generateRelationTableSql($item,$this);
                    }else{
                        $sql .=self::generateSql($item);
                        $foreingKey[$item->setting['modelName']] = "CONSTRAINT `".$db->tablePrefix."node_".$this->name."_ibfk_$$$$` FOREIGN KEY (`".$item->name."`) REFERENCES `".$db->tablePrefix.($item->setting['isNodeModel']?'node_':'').$item->setting['modelName']."` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION";
                    }
                }else{
                    $sql .=self::generateSql($item);
                }
            }

            if($this->type === 0) {
                $sql .= "`sort` int(10) unsigned DEFAULT NULL COMMENT '排序',";
            }
            $sql .= "`status` tinyint(1) unsigned DEFAULT '".($this->type===0?1:0)."',";
            if($this->type === 0){
                $sql .= "`template_content` char(50) DEFAULT NULL COMMENT '内容模板',";
                $sql .= "`is_push` tinyint(1) unsigned DEFAULT '0' COMMENT '是否推荐',";
                $sql .= "`is_comment` tinyint(1) DEFAULT '1' COMMENT '是否允许评论',";
                $sql .= "`views` int(10) unsigned DEFAULT '0' COMMENT '浏览数',";
                $sql .= "`jump_link` varchar(255) DEFAULT NULL COMMENT '跳转链接',";
                $sql .= "`is_login` tinyint(1) unsigned DEFAULT '0' COMMENT '访问是否需登录',";
                $sql .= "`layouts` char(50) DEFAULT NULL COMMENT '页面布局',";
	            $sql .= "`count_user_relations` text DEFAULT NULL,";
            }
            $sql .= "`create_time` int(10) unsigned DEFAULT NULL,";
            if($this->type === 0){
                $sql .= "`update_time` int(10) DEFAULT NULL,";
                $sql .= "`seo_title` varchar(255) DEFAULT NULL,";
                $sql .= "`seo_keywords` varchar(255) DEFAULT NULL,";
                $sql .= "`seo_description` varchar(255) DEFAULT NULL,";
            }
            $sql .= "PRIMARY KEY (`id`),KEY `site_id` (`site_id`),";
            if($this->type === 0){
                $sql .= "KEY `category_id` (`category_id`),";
            }

            $foreingKey['site'] = "CONSTRAINT `".$db->tablePrefix."node_".$this->name."_ibfk_$$$$` FOREIGN KEY (`site_id`) REFERENCES `".$db->tablePrefix."site` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION";
            if($this->type === 0){
                $foreingKey['category'] = "CONSTRAINT `".$db->tablePrefix."node_".$this->name."_ibfk_$$$$` FOREIGN KEY (`category_id`) REFERENCES `".$db->tablePrefix."prototype_category` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION";
            }
            foreach ($foreingKey as $fk=>$fv){
                $foreingKey[$fk] = str_replace('$$$$',$fk,$fv);
            }
            $sql .= implode(',',$foreingKey);

            $sql .= ") ENGINE=InnoDB AUTO_INCREMENT=1024 DEFAULT CHARSET=utf8 COMMENT='".$this->title."';";
        }
        else{
            $sql = '';
            // 修改
            $commonFields = [];
            foreach ($fields as $item){
                if(empty($item->updated_target)) $item->updated_target = $item->name;

                $res = self::updateRelation($item,$this);
                $sql .= $res['sql'];
                if($res['continue']) continue;

                if(in_array($item->updated_target,$oldFields)){
                    $sql .= self::generateSql($item,'ALTER',$this);
                    $commonFields[] = $item->updated_target;
                }else{
                    $sql .= self::generateSql($item,'ADD',$this);
                }

            }

            // 删除
            if(array_key_exists('delRelationField',$modelSetting)){
                $file = new FileHelper();
                foreach ($modelSetting['delRelationField'] as $item){
                    if($item['relationType']){
                        $sql .= "DROP TABLE IF EXISTS `".$db->tablePrefix."node_".$this->name."_".$item['modelName']."_relation`;";
	                    FileHelper::unlink(Yii::$app->getBasePath().'/../common/entity/nodes'.'/'.ucwords($this->name).ucwords($item['modelName']).'RelationModel.php');
                    }else{
                        $sql .= "ALTER TABLE `".$db->tablePrefix."node_".$this->name."` DROP FOREIGN KEY `".$db->tablePrefix."node_".$this->name."_ibfk_".$item['modelName']."`;";
                    }
                }
                unset($file,$modelSetting['delRelationField']);
            }

            $delFields = [];
            foreach ($oldFields as $item){
                if(!in_array($item,$commonFields)){
                    $delFields[] = $item;
                }
            }

            if(!empty($delFields)){
                $sql .= "ALTER TABLE `".$db->tablePrefix."node_".$this->name."`";
                foreach ($delFields as $i=>$item){
                    $sql .= ($i==0?'':',')." DROP COLUMN `".$item."`";
                }
                $sql .= ';';
            }
            unset($delFields,$commonFields);
        }
        $sql .= $db->createCommand()->update(PrototypeFieldModel::tableName(),['updated_target'=>null,'is_updated'=>0],['model_id'=>$this->id])->rawSql.';';

        $db->createCommand($sql)->execute();
        $db->createCommand()->update(self::tableName(),['is_generate'=>1,'setting'=>(empty($modelSetting)?'':json_encode($modelSetting))],['id'=>$this->id])->execute();
        unset($sql,$modelSetting,$oldFields);


        foreach ($fields as $item){
            $item = ArrayHelper::toArray($item);
            unset($item['history']);
            $relationSql .= $db->createCommand()->update(PrototypeFieldDomain::tableName(),['is_generate'=>1,'history'=>json_encode($item)],['id'=>$item['id']])->rawSql.';';
        }
        $db->createCommand($relationSql)->execute();
        unset($relationSql);

        // 生成模型
        self::generateFile($this,$fields);

        return true;
    }

    /**
     * @param $field
     * @param $operate null|string 可选的值ALTER|ADD
     * @param null $model null|obj 当operate参数不为null时，必传
     * @return string
     */
    static private function generateSql($field, $operate = null,$model = null)
    {
        if($field->type == 'captcha') return '';

        $verificationRules = $field->custom_verification_rules;

        if ($operate) {

            $sql = "ALTER TABLE `".Yii::$app->getDb()->tablePrefix."node_" . strtolower($model->name) . "`";

            if($operate == 'ALTER'){
                if($field->updated_target !== $field->name){
                    $sql .= ' CHANGE `'.$field->updated_target.'`';
                }else{
                    $sql .= ' MODIFY';
                }
            }else{
                $sql .= ' ADD';
            }
            $sql .= ' `'.$field->name.'`';

        } else {
            $sql = "`" . strtolower($field->name) . "`";
        }

        // 生成语句
        if ($field->field_type == 'int') {
            $sql .= " int(10)";

            if (ArrayHelper::getValue($verificationRules, 'unsigned', false)) {
                $sql .= " unsigned";
            }

        } elseif ($field->field_type == 'decimal') {
            $sql .= " decimal(10," . $field->field_decimal_place . ")";

            if (ArrayHelper::getValue($verificationRules, 'unsigned', false)) {
                $sql .= " unsigned";
            }
        } elseif ($field->field_type == 'text' || $field->field_type == 'longtext') {
            $sql .= ' ' . $field->field_type . ' COLLATE utf8_unicode_ci';
        } elseif ($field->field_type == 'enum') {
            $options = $field->options;
            if(empty($options['list'])){
                $sql .= " enum('')";
            }else{
                $sql .= " enum(";
                foreach ($options['list'] as $k=>$v){
                    $sql .= ($k==0?'':',')."'".$v['value']."'";
                }
                $sql .= ")";
            }

            $sql .= ' COLLATE utf8_unicode_ci';
        } elseif ($field->field_type == 'date') {
            $sql .= ' date';
        } elseif ($field->field_type == 'datetime') {
            //$sql .= ' datetime ON UPDATE CURRENT_TIMESTAMP';
            $sql .= ' datetime';
        } else {
            $sql .= ' varchar('.($field->field_length?:255).')';
            $sql .= ' COLLATE utf8_unicode_ci';
        }

        if ($field->field_type == 'text' || $field->field_type == 'longtext'){
            if($field->is_required){
                $sql .= " NOT NULL";
            }
        }elseif ($field->field_type == 'enum'){
            if($field->is_required && empty($options['default'])){
                $sql .= " NOT NULL";
            }else{
                $sql .= " DEFAULT ".(empty($options['default'])?'NULL':"'".$options['default'][0]."'");
            }
        }else{
            if($field->is_required && empty($field->default_value)){
                $sql .= " NOT NULL";
            }else{
                $sql .= " DEFAULT " . (empty($field->default_value) ? 'NULL' : "'" . $field->default_value . "'");
            }
        }

        $sql .= " COMMENT '" . $field->title . "'".($operate?';':',');

        return $sql;
    }

    /**
     * 生成关联表
     * @param $field
     * @param $model
     * @return string
     */
    static private function generateRelationTableSql($field,$model){
        $tablePrefix = Yii::$app->getDb()->tablePrefix;
        $modelName = ArrayHelper::getValue($field->setting,'modelName');

        $tableName = $tablePrefix.'node_'.$model->name.'_'.$modelName."_relation";
        $sql = "CREATE TABLE `".$tableName."` (";
        $sql .= "`parent_id` int(10) unsigned NOT NULL,";
        $sql .= "`relation_id` int(10) unsigned NOT NULL COMMENT '关联数据ID',";
        $sql .= "KEY `parent_id` (`parent_id`),KEY `relation_id` (`relation_id`),";
        $sql .= "CONSTRAINT `".$tableName."_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `".$tablePrefix."node_".$model->name."` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,";

        $sql .= "CONSTRAINT `".$tableName."_ibfk_2` FOREIGN KEY (`relation_id`) REFERENCES `";
        $sql .= $tablePrefix.($field->setting['isNodeModel']?'node_':'').($modelName == 'category'?'prototype_'.$modelName:$modelName);
        $sql .= "` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION";
        $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

        return $sql;
    }

    /**
     * 关联修改
     * @param $field
     * @param $model
     * @return array
     */
    static private function updateRelation($field,$model){
        $db = Yii::$app->getDb();
        $res = ['continue'=>false,'sql'=>''];
        $tableName = $db->tablePrefix."node_".$model->name;

        $modelPath = Yii::$app->getBasePath().'/../common/entity/nodes';

        $history = empty($field->history)?null:json_decode($field->history);

        // 字段类型“非关联类型”改为“关联类型”
        if($field->type == 'relation_data' && (!$history || $history->type != 'relation_data')){
            if($field->setting['relationType'] === 1){
                $res['sql'] .=self::generateRelationTableSql($field,$model);
            }else{
                $res['sql'] .= self::generateSql($field,'ADD',$model);
                $res['sql'] .= "ALTER TABLE `".$tableName."` ADD CONSTRAINT `".$tableName."_ibfk_".$field->setting['modelName']."` FOREIGN KEY(`".$field->name."`) REFERENCES `".$db->tablePrefix.($field->setting['isNodeModel']?'node_':'').$field->setting['modelName']."` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;";
            }
            $res['continue'] = true;
        }
        // 字段类型从“关联类型”改为“非关联类型”
        elseif($field->type != 'relation_data' && ($history && $history->type == 'relation_data')){
            if($history->setting->relationType === 1){
                $res['sql'] .= "DROP TABLE IF EXISTS `".$tableName."_".$history->setting->modelName."_relation`;";
	            FileHelper::unlink($modelPath.'/'.ucwords($model->name).ucwords($history->setting->modelName).'RelationModel.php');
                $res['sql'] .= self::generateSql($field,'ADD',$model);
            }else{
                $res['sql'] .= "ALTER TABLE `".$tableName."` DROP FOREIGN KEY `".$tableName."_ibfk_".$history->setting->modelName."`;";
                $res['sql'] .= self::generateSql($field,'ALTER',$model);
            }
            $res['continue'] = true;
        }
        // 字段为“关联类型”，类型不变
        elseif($field->type == 'relation_data' && ($history && $history->type == 'relation_data')){
            // 关联类型不变
            if($field->setting['relationType'] == $history->setting->relationType){
                if($field->setting['relationType'] === 1 && $field->setting['modelName'] != $history->setting->modelName){
                    $res['sql'] .= "DROP TABLE IF EXISTS `".$tableName."_".$history->setting->modelName."_relation`;";
	                FileHelper::unlink($modelPath.'/'.ucwords($model->name).ucwords($history->setting->modelName).'RelationModel.php');
                    $res['sql'] .= self::generateRelationTableSql($field,$model);
                }elseif($field->setting['relationType'] && $field->setting['modelName'] != $history->setting->modelName){
                    $res['sql'] .= "ALTER TABLE `".$tableName."` DROP FOREIGN KEY `".$tableName."_ibfk_".$history->setting->modelName."`;";
                    $res['sql'] .= self::generateSql($field,'ALTER',$model);
                    $res['sql'] .= "ALTER TABLE `".$tableName."` ADD CONSTRAINT `".$tableName."_ibfk_".$field->setting['modelName']."` FOREIGN KEY(`".$field->name."`) REFERENCES `".$db->tablePrefix.($field->setting['isNodeModel']?'node_':'').$field->setting['modelName']."` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;";
                }
            }else{
                if($field->setting['relationType'] === 1){
                    $res['sql'] .= "ALTER TABLE `".$tableName."` DROP FOREIGN KEY `".$tableName."_ibfk_".$history->setting->modelName."`;";
                    $res['sql'] .= "ALTER TABLE `".$tableName."` DROP COLUMN `".$history->setting->modelName."`;";
                    $res['sql'] .= self::generateRelationTableSql($field,$model);
                }else{
                    $res['sql'] .= self::generateSql($field,'ADD',$model);
                    $res['sql'] .= "ALTER TABLE `".$tableName."` ADD CONSTRAINT `".$tableName."_ibfk_".$field->setting['modelName']."` FOREIGN KEY(`".$field->name."`) REFERENCES `".$db->tablePrefix.($field->setting['isNodeModel']?'node_':'').$field->setting['modelName']."` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;";
                    $res['sql'] .= "DROP TABLE IF EXISTS `".$tableName."_".$history->setting->modelName."_relation`;";
	                FileHelper::unlink($modelPath.'/'.ucwords($model->name).ucwords($history->setting->modelName).'RelationModel.php');
                }
            }
            $res['continue'] = true;
        }


        return $res;
    }

    /**
     * 生成文件
     * @param $model
     * @param $fields
     */
    static private function generateFile($model,$fields){
        $basePath = Yii::$app->getBasePath().'/..';
        $modelPath = $basePath.'/common/entity/nodes';
        $formPath = Yii::$app->getBasePath().'/modules/prototype/views/form';
        $nodePath = Yii::$app->getBasePath().'/modules/prototype/views/node';

        // 生成模型
        $file = new FileHelper();
        $file->put($modelPath.'/'.ucwords($model->name).'Model.php',Yii::$app->getView()->renderPhpFile($basePath.'/common/assets/node/model_'.$model->type.'.php',['model'=>$model,'fields'=>$fields]));

        $file->put($modelPath.'/'.ucwords($model->name).'Search.php', Yii::$app->getView()->renderPhpFile($basePath.'/common/assets/node/search_'.$model->type.'.php',['model'=>$model,'fields'=>$fields]));


        // 生成表单
        if($model->type === 0){
            $file->put($nodePath.'/_form_'.$model->name.'.php', Yii::$app->getView()->renderPhpFile($basePath.'/common/assets/node/node_form.php',['model'=>$model,'fields'=>$fields]));
            $file->put($nodePath.'/_list_'.$model->name.'.php', Yii::$app->getView()->renderPhpFile($basePath.'/common/assets/node/node_list.php',['model'=>$model,'fields'=>$fields]));
        }else{
            $file->put($formPath.'/index_'.$model->name.'.php', Yii::$app->getView()->renderPhpFile($basePath.'/common/assets/node/form_index.php',['model'=>$model,'fields'=>$fields]));
            $file->put($formPath.'/view_'.$model->name.'.php', Yii::$app->getView()->renderPhpFile($basePath.'/common/assets/node/form_view.php',['model'=>$model,'fields'=>$fields]));
        }

        // 生成关联模型和扩展
        foreach ($fields as $item){
            if($item->type == 'relation_data'){
                $relationModelName = ucwords($item->setting['modelName']);
                if($item->setting['relationType'] === 1){
                    $file->put($modelPath.'/'.ucwords($model->name).$relationModelName.'RelationModel.php', Yii::$app->getView()->renderPhpFile($basePath.'/common/assets/node/node_relation.php',['model'=>$model,'field'=>$item]));
                }
            }
        }
    }

    /**
     * 解析选项
     * @param $options
     * @return array
     */
    static public function optionResolve($options){
        $res = [
            'list'=>[],
            'default'=>[]
        ];

        if(empty($options)) return $res;
        $options = str_replace(array("\r\n", "\r", "\n"),'$_break_tag_$',$options);
        foreach (explode('$_break_tag_$',$options) as $item){
            $tmp = explode('=>',$item);
            $res['list'][] = ['value'=>$tmp[0],'title'=>ArrayHelper::getValue($tmp,1,$tmp[0])];
            if(array_key_exists(2,$tmp)) $res['default'][] =  $tmp[2];
        }

        return $res;
    }

    /**
     * 选项地图
     * @param $options
     * @return string
     */
    static public function optionsMap($options){
        $optionsHtml = [];
        foreach ($options['list'] as $item){
            $optionsHtml[] = '"'.$item['value'].'"=>"'.$item['title'].'"';
        }

        return implode(',',$optionsHtml);
    }
}
