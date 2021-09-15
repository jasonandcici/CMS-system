<?php
/**
 * @copyright
 * @link 
 * @create Created on 2018/11/8
 */

namespace manage\modules\doc\controllers;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\Url;


/**
 * DefaultController
 *
 * @author 
 * @since 1.0
 */
class DefaultController extends BaseDocController {

	protected $apiModule = '/api/';
	protected $suffix = '.html';

	/**
	 * @return string
	 */
	public function actionIndex(){
		$apiModule = $this->apiModule;
		$suffix = $this->suffix;

		$apiRules = [];
		foreach (require(Yii::$app->getBasePath().'/../common/config/api.php') as $i=>$item){
			if(is_array($item)){
				foreach ($item['controller'] as $v){
					$k = str_replace('api/','',$v);
					$ks = Inflector::pluralize($k);
					$apiRules[$k] = [
						[
							'type'=>'GET',
							'api'=>$apiModule.$ks.$suffix,
							'title'=>Yii::t('doc',$k).'列表',
							'url'=>Url::to(['/doc/view/'.$k,'action'=>'list']),
							'action'=>'list',
						],
						[
							'type'=>'GET',
							'api'=>$apiModule.$ks.'/{'.ucfirst($k).'Id}'.$suffix,
							'title'=>Yii::t('doc',$k).'详情',
							'url'=>Url::to(['/doc/view/'.$k,'action'=>'view']),
							'action'=>'view',
						]
					];

					$m = str_replace('-','_',$k).'Stand';
					if(method_exists($this,$m)){
						$apiRules[$k] = $this->$m($apiRules[$k],$k,$ks);
					}
				}
			}else{
				$v = explode(' ',$i);
				$type = $v[0];
				$api = $v[1];
				$v = $v[1];
				$v = explode('/',$v);
				$rs = $v[1];
				$r = Inflector::singularize($rs);

				$type = explode(',',$type);
				$newType = [];
				foreach ($type as $t){
					$t = ucwords($t);
					if($t == 'PUT' || $t == 'PATCH'){
						$newType['PUT,PATCH'][] = $t;
					}else{
						$newType[] = [$t];
					}
				}
				$type = [];
				foreach ($newType as $t){
					$type[] = implode(',',$t);
				}
				unset($newType);

				$tmp = [];
				foreach ($type as $ti=>$t){
					if($r == 'html5'){
						$tmp[] = [
							'type'=>$t,
							'api'=>$apiModule.$r.'/index'.$suffix,
							'title'=>'单网页'.Yii::t('doc',$r).'页面',
							'url'=>Url::to(['/doc/view/'.$r,'action'=>'page']),
							'action'=>'page',
						];
						$tmp[] = [
							'type'=>$t,
							'api'=>$apiModule.$r.'/view'.$suffix,
							'title'=>Yii::t('doc','node').Yii::t('doc',$r).'页面',
							'url'=>Url::to(['/doc/view/'.$r,'action'=>'view']),
							'action'=>'view',
						];
					}else{
						$action = array_key_exists(2,$v)?$v[2]:'list';
						if($ti>0) $action .= '_'.strtolower(str_replace(',','',$t));
						$tmp[] = [
							'type'=>$t,
							'api'=>'/'.$api.$suffix,
							'title'=>Yii::t('doc',$r).(array_key_exists(2,$v)?Yii::t('doc',$v[2]):'列表'),
							'url'=>Url::to(['/doc/view/'.$r,'action'=>$action]),
							'action'=>$action,
						];
					}
				}

				$mn = str_replace('-','_',$r);
				if(method_exists($this,$mn)){
					$tmp = $this->$mn($tmp,$r,$rs);
				}
				foreach ($tmp as $v){
					$apiRules[$r][] = $v;
				}
			}
		}

		$apiRules['uploads'] = [
			[
				'type'=>'GET',
				'api'=>'/uploads/{ImageFile}?thumb={Thumb}&token={Token}',
				'title'=>'缩略图生成',
				'url'=>Url::to(['/doc/view/uploads','action'=>'image']),
				'action'=>'image',
			],
			[
				'type'=>'GET',
				'api'=>'/uploads/{File}?token={Token}',
				'title'=>'文件访问',
				'url'=>Url::to(['/doc/view/uploads','action'=>'file']),
				'action'=>'file',
			],
		];

		return $this->render('index',['dataList'=>$apiRules]);
	}

	/**
	 * @param $data
	 * @param $k
	 * @param $ks
	 *
	 * @return mixed
	 */
	protected function formStand($data,$k,$ks){
		$data[0] = [
			'type'=>'POST',
			'api'=>$this->apiModule.$ks.$this->suffix.'?sid={SiteId}&mid={ModelId}',
			'title'=>'提交表单',
			'url'=>Url::to(['/doc/view/'.$k,'action'=>'create']),
			'action'=>'create',
		];
		unset($data[1]);
		return $data;
	}

	/**
	 * @param $data
	 * @param $k
	 * @param $ks
	 *
	 * @return mixed
	 */
	protected function form($data,$k,$ks){
		foreach ($data as $i=>$item){
			if($item['action'] == 'send-sms'){
				$item['api'] .= '?mode={Mode}';
			}elseif($item['action'] == 'upload'){
				$item['api'] .= '?type={Type}&mode={Mode}';
			}
			$data[$i] = $item;
		}
		return $data;
	}

	/**
	 * @param $data
	 * @param $k
	 * @param $ks
	 *
	 * @return array
	 */
	protected function commentStand($data,$k,$ks){
		foreach ($data as $i=>$item){
			$item['api'] .= '?expand={Expand}';
			$data[$i] = $item;
		}
		$data[] = [
			'type'=>'POST',
			'api'=>$this->apiModule.$ks.$this->suffix,
			'title'=>'发布'.Yii::t('doc',$k),
			'url'=>Url::to(['/doc/view/'.$k,'action'=>'create']),
			'action'=>'create',
		];
		$data[] = [
			'type'=>'DELETE',
			'api'=>$this->apiModule.$ks.'/{'.ucfirst($k).'Id}'.$this->suffix,
			'title'=>'删除'.Yii::t('doc',$k),
			'url'=>Url::to(['/doc/view/'.$k,'action'=>'delete']),
			'action'=>'delete',
		];

		return $data;
	}

	/**
	 * @param $data
	 * @param $k
	 * @param $ks
	 *
	 * @return mixed
	 */
	protected function comment($data,$k,$ks){
		foreach ($data as $i=>$item){
			if($item['action'] == 'relation'){
				$item['api'] .= '?slug={Slug}';
			}
			$data[$i] = $item;
		}
		return $data;
	}

	/**
	 * @param $data
	 * @param $k
	 * @param $ks
	 *
	 * @return mixed
	 */
	protected function nodeStand($data,$k,$ks){
		foreach ($data as $i=>$item){
			$item['api'] .= '?sid={SiteId}&cid={CategoryId}&mid={ModelId}&user-relations={UserRelations}&expand={Expand}';
			$data[$i] = $item;
		}
		return $data;
	}

	/**
	 * @param $data
	 * @param $k
	 * @param $ks
	 *
	 * @return mixed
	 */
	protected function fragmentStand($data,$k,$ks){
		foreach ($data as $i=>$item){
			$item['api'] .= '?sid={SiteId}&slug={Slug}';
			$data[$i] = $item;
		}
		return $data;
	}

	/**
	 * @param $data
	 * @param $k
	 * @param $ks
	 *
	 * @return mixed
	 */
	protected function category($data,$k,$ks){
		foreach ($data as $i=>$item){
			if($item['action'] == 'list'){
				$item['api'] .= '?sid={SiteId}';
			}
			$data[$i] = $item;
		}
		return $data;
	}

	/**
	 * @param $data
	 * @param $k
	 * @param $ks
	 *
	 * @return mixed
	 */
	protected function search($data,$k,$ks){
		foreach ($data as $i=>$item){
			if($item['action'] == 'list'){
				$item['api'] .= '?mid={ModelId}';
			}
			$data[$i] = $item;
		}
		return $data;
	}

	/**
	 * @param $data
	 * @param $k
	 * @param $ks
	 *
	 * @return mixed
	 */
	protected function tag_search($data,$k,$ks){
		foreach ($data as $i=>$item){
			if($item['action'] == 'list'){
				$item['api'] .= '?mid={ModelId}&tag={Tag}';
			}
			$data[$i] = $item;
		}
		return $data;
	}

	/**
	 * @param $data
	 * @param $k
	 * @param $ks
	 *
	 * @return mixed
	 */
	protected function config($data,$k,$ks){
		foreach ($data as $i=>$item){
			if($item['action'] == 'list'){
				$item['api'] .= '?slug={Slug}';
			}
			$data[$i] = $item;
		}
		return $data;
	}

	/**
	 * @param $data
	 * @param $k
	 * @param $ks
	 *
	 * @return mixed
	 */
	protected function passport($data,$k,$ks){
		foreach ($data as $i=>$item){
			if(in_array($item['action'],["login","register","find-password"])){
				$item['api'] .= '?mode={Mode}';
			}
			$data[$i] = $item;
		}
		return $data;
	}

	/**
	 * @param $data
	 * @param $k
	 * @param $ks
	 *
	 * @return mixed
	 */
	protected function user($data,$k,$ks){
		foreach ($data as $i=>$item){
			if(in_array($item['action'],["reset-password","bind"])){
				$item['api'] .= '?mode={Mode}';
			}elseif ($item['action'] == 'comment'){
				$item['api'] .= '?expand={Expand}';
			}
			$data[$i] = $item;
		}
		return $data;
	}

	/**
	 * @param $data
	 * @param $k
	 * @param $ks
	 *
	 * @return mixed
	 */
	protected function relation($data,$k,$ks){
		foreach ($data as $i=>$item){
			$item['api'] .= '?slug={Slug}';
			$data[$i] = $item;
		}
		return $data;
	}

	/**
	 * @param $data
	 * @param $k
	 * @param $ks
	 *
	 * @return mixed
	 */
	protected function html5($data,$k,$ks){
		foreach ($data as $i=>$item){
			if($item['action'] == 'page'){
				$item['api'] .= '?sid={SiteId}&category_id={CategoryId}';
			}elseif ($item['action'] == 'view'){
				$item['api'] .= '?sid={SiteId}&category_id={CategoryId}&id={Id}';
			}

			$data[$i] = $item;
		}
		return $data;
	}
}