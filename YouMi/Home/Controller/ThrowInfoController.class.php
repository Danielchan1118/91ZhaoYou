<?php
/**
 * 投放设置
 *@ author 付敏平
 */
namespace Home\Controller;
use Think\Controller;

/*
投放方式
*/
define("TROW_TYPE_NO", 0);
define("TROW_TYPE_DISTRICT",1);

class ThrowInfoController extends HomeController{
	/**
	 * 应用投放列表
	 */
	public function ThrowList(){
		$end_time = trim($_GET['end_time']);
		$start_time = trim($_GET['start_time']);
		$throw = M("throw");
		$all_time   = trim( $_POST['reservation'] );
		if($all_time){
            $times = explode(" - ",$all_time);
            $end_time = strtotime($times[1]);
            $start_time = strtotime($times[0]);
            $_GET['end_time'] = $end_time;
            $_GET['start_time'] = $start_time;
        }else if(empty($end_time) || empty($start_time)){
            $end_time = time();
            $start_time  = time();
        }
		
		if(trim($_POST['app_name'])){
			$app_name = trim($_POST['app_name']);
			$this->assign("app_name",$app_name);
		}
		
		$where = "t.did=".$this->did;
		if($app_name){
			$where.= " and a.app_name like '%".$app_name."%'";
		}

		if($all_time && $start_time != $end_time ){
			$where.= " and t.throw_starttime between ".$start_time.' and '.$end_time;
		}
		
		
		$count = $throw->table('sw_throw AS t')
        	        ->join('sw_app AS a on t.app_id=a.id')
        	        ->where($where)
        	        ->count();
        $n = 10;
	    $Page = new \Think\Page($count,$n);
	    $Page->setConfig ( 'prev', '上一页' );
		$Page->setConfig ( 'header', '款游戏' );
		$Page->setConfig ( 'first', '首 页' );
		$Page->setConfig ( 'last', '末 页' );
		$Page->setConfig ( 'next', '下一页' );
		$throw_list =  $throw->table('sw_throw AS t')
        	        ->join('sw_app AS a on t.app_id=a.id')
        	        ->field('t.id,t.throw_type,t.remarks,t.throw_endtime,t.add_time,a.app_name,a.expend_money,t.throw_content')
        	        ->where($where)
        	        ->order("t.add_time desc")
        	        ->limit($Page->firstRow.','.$Page->listRows)
        	        ->select();
      
		$this->assign("start_time",$start_time);
		$this->assign("end_time",$end_time);
		$this->Page  = $Page->show();
		$this->assign("throw_list", $throw_list);
		$this->assign("throw_count", $count);
		$this->display();
	}
	
	/**
	 * 投放设置
	 */
	public function ThrowSet($edit_id = ''){
		$app = M('app');
		if($edit_id > 0){
			$throw = M("throw");
			$throws = $throw->where("did=".$this->did." and id=".$edit_id)->find();
			$throws['throw_starttime'] = date("Y-m-d H:i:s",$throws['throw_starttime']);
			$throws['throw_endtime'] = date("Y-m-d H:i:s",$throws['throw_endtime']);
			
			//查询已审核的应用
			$app_name = $app->where('id='.$throws['app_id'])->field('id,app_name')->select();
			$this->app_name = $app_name;
		}else{
			$throws['throw_endtime'] = $throws['throw_starttime'] = date("Y-m-d H:i:s",time());
			$throws['throw_type'] = TROW_TYPE_DISTRICT; //默认按地区投放
			$throws['throw_content'] = '[]';
			
			//查询已审核的应用
			$app_name = $app->where('stauts=1 and is_delete = 1 and is_throw = 1 and did='.$this->did." and package_name != ''")->field('id,app_name')->select();
			$num = count($app_name);
			$this->num = $num;
			$this->app_name = $app_name;
		}

		//查中国行政区
		$mpros = M("pros");
		$pros = $mpros->order('sorts')->select();
		$mcitys = M("citys");
		$discts = array();
		$info = array();
		foreach($pros as $kp => $vp){
			$info["id"] = 1000+$vp["pid"];
			$info["pId"] = 0;
			$info["name"] = $vp["pro_name"];
			$info["isParent"] = true;
			$info["checked"] = true;
			$discts[] = $info;
			$citys = $mcitys->order('sorts')->where('pid='.$vp["pid"])->select();
			foreach($citys as $kc => $vc){
				$info["id"] = $vc["cid"];
				$info["pId"] = $vc["pid"]+1000;
				$info["name"] = $vc["city_name"];
				$info["checked"] = true;
				$info["isParent"] = false;
				$discts[] = $info;
			}
		}
		
		$this->assign("edit_id", $edit_id);
		$this->assign("throws", $throws);
		$this->assign("throws_json", json_encode($throws));
		$this->assign("city_tree", json_encode($discts));
		$this->display("throw_info");
	}
	
	/**
	 * 在citys中反向添加 throw_id 以便安卓查询
	 */
	 private function RebindCity($throwid){
		$ct = M('citys');
		$map['cid'] = array('in',implode(",",$_POST ['district']));
		$ctdatas = $ct->where($map)->select();
		foreach($ctdatas as $k=>$ctdata){
			//$ctdata = $ct->where('cid='.trim($v))->find();
			$arrThrowidStr = $ctdata['invisible_throwid'];
			$arrThrowids = explode(',',$arrThrowidStr);
			$arrThrowids[] = $throwid;
			$arrThrowids = array_unique($arrThrowids);
			$data['invisible_throwid'] = implode(",",$arrThrowids);
			$ct->where('cid='.$ctdata['cid'])->save($data);
		}
	}
	/**
	 * 投放操作
	 */
	public function ThrowDo(){
		$user = M("cooperation_user");
		$money = $user->where("id=".$this->did)->getfield("money");
		if($money>10){
			if ($_POST) {
				$times   =  trim( $_POST['reservation'] );
	    		list($start_time,$end_time)   =  explode(" - ",$times);

				$throw = M("throw");
				$edit_id = intval($_POST['edit_id']);
				$map ['did'] = $this->did;
				$map ['app_id'] = intval($_POST['appid']);
				$map ['throw_type'] = intval ( $_POST['throw_type'] );
				
				switch($map ['throw_type']){
					case TROW_TYPE_DISTRICT:
						$map ['throw_content'] = json_encode($_POST ['district']);
						break;
						
					default:
						$map ['throw_content'] = trim ( $_POST ['throw_content'] );
				}

				$map ['throw_starttime'] = strtotime($start_time);
				$map ['throw_endtime'] = strtotime($end_time);
				$map ['add_time'] = time();

				if(!$map ['app_id'] || !$map ['throw_starttime'] || !$map ['throw_endtime']){
					$this->error('数据不能为空，请重输');
				}

				if($edit_id){
					$res = $throw->where("id=".$edit_id." and did=".$this->did)->save($map);
					$mess = "修改成功!";
				}else{
					$res = $throw->add($map);
					$edit_id = $res;
					$mess = "添加成功!";
				}

				$app = M("app");
				$data['is_throw'] = 2;
				$app->where("id=".$map ['app_id'])->save($data);

				if($res){
					$this->RebindCity($edit_id);
					$this->success ( $mess, U ( 'ThrowInfo/ThrowList' ) );
				}else{
					$this->error ( "发生错误:" . mysql_error (), U ( 'ThrowInfo/ThrowList' ) );
				}

			}
		}else{
			$this->error ( '您的余额不足，请充值！', U ( 'ThrowInfo/ThrowList' ) );
		}
	}

	/*
	 *同步删除citys表
	 */
	private function SyncCitysDel($del_ids){
		if(is_array($del_id)){
			$cids = implode ( ',', $del_id ); 
		}else{
			$cids = $del_ids;
		}
		$throw = M("throw");
		$city = M("citys");
		$map['id'] = array('in',$cids);
		$rows = $throw ->where($map)->select();
		$citys = array();
		
		foreach($rows as $row){
			$js = $row['throw_content'];
			$jsobj = json_decode($js);
			foreach($jsobj as $cityid){
				if(!isset($citys[$cityid])){
					$ct = $city->where('cid='.$cityid)->find();
					$citys[$cityid] = explode(',',$ct["invisible_throwid"]);
				}
				$ret = array_search($row['id'],$citys[$cityid]);
				if($ret){
					//unset($citys[$cityid][$ret]);
					array_splice($citys[$cityid], $ret, 1); 
				}
			}
		}
		foreach($citys as $cityid=>$arr){
			$data['invisible_throwid'] = implode(',',array_unique($arr));
			$city->where('cid='.$cityid)->save($data);
		}
	}
	/**
	 * 删除投放数据
	 */
	public function ThrowDel(){
		$del_id = $_POST['orderid'];
		if($del_id){
	    	if(is_array($del_id)){
				$cids = implode ( ',', $del_id ); 

				$map ['id'] = array (
					'in',
					$cids 
				);
			}else{
				$map ['id'] = $del_id;
			}

    		$throw = M("throw");

    		$app = M("app");
			$data['is_throw'] = 1;
			if(count($del_id)>1){
				$app_ids = $throw ->where($map)->getfield("app_id",true);
				$res = $app->where(array("id"=>array('in',implode ( ',', $app_ids ))))->save($data);
			}else{
				$app_id = $throw->where("id=".$map ['id'])->getfield("app_id");
				$res = $app->where("id=".$app_id)->save($data);
			}

			if($res){
				$this->SyncCitysDel($del_id);
				$re = $throw ->where($map)->delete();
				if($re){
					$del = M("delapp");
					if(is_array($del_id)){
						foreach ($del_id as $v) {
							$datas['app_id'] = $v;
							$datas['delete_time'] = time();
							$del->add($datas);
						}
					}else{
						$datas['app_id'] = $del_id;
						$datas['delete_time'] = time();
						$del->add($datas);
					}

					$arr['ret'] = 1;
				 	$arr['message'] = "删除成功";
				}else{
					$arr['ret'] = -1;
				 	$arr['message'] = "删除失败";
				}
			}else{
				$arr['ret'] = -1;
			 	$arr['message'] = "删除失败!";
			}
			$this->ajaxReturn($arr);
    	}
	}

	/**
	 * 查询市区
	 */
	public function QueryCity(){
		$pid = intval($_POST['id']);
		$edit_id = intval($_POST['edit_id']);

		if($pid>0){
			$zone = M("zone");
			$zone_sub = $zone->field("zone_id, zone_name")->where("father_id=".$pid)->select();
			if($edit_id>0){
				$throw = M("throw");
				$throws = $throw->where("did=".$this->did." and id=".$edit_id)->find();
				if($throws['throw_type'] == 1){
					$zones = json_decode($throws['throw_content'],true);
				}

				foreach(zone_sub as $key=>$value){
					if($zones){
						if(!in_array($v['zone_id'],$zones)){
							$zone_sub [$k][$key]['nocheck'] = 1;
						}
					}
				} 
			}
			
			$this->ajaxReturn($zone_sub);
		}
	}

}

?>