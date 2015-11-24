<?php
/**
 * 兑换中心
 */
 class AppConvert{
	/**
	 * 兑换列表
	 */
	public function ConvertList($datas = array()){
		$pid = intval($datas['pid']);
		$where = "is_on = 1";
		if($pid>0){
			$where.= ' and pid='.$pid;
		}else{
			$where.= ' and pid=0';
		}
		$M = M('convert');
		$convert_list = $M->where($where)->field('id,convert_name,tag,image,gold')->order("order_id asc")->select();
	
		if($convert_list){
			$arr = $convert_list;
		}else{
			$arr['ret'] = -1;
		} 
		return json_encode($arr);
	}
	
	/**
	 * 兑换提交
	 */
	public function ConvertSubmit($datas = array()){
		$username = trim($datas['username']);
		$cid = intval($datas['cid']);
		$data1 = trim($datas['data1']);
		$data2 = trim($datas['data2']);

		if($username && $cid && $data1){
			$user = M("users");
			$goldcount = $user->where("username='".$username."'")->getfield("goldcount");
		
			$convert = M("convert");
			$cinfo = $convert->field("gold,tag,convert_name")->where("id=".$cid)->find();
			$cinter = intval($cinfo['gold'])*10000;
			$count = intval($goldcount) - $cinter;
			if($count >= 0){
				$res = $user->where("username='".$username."'")->setDec( 'goldcount',$cinter); 
				if($res){
					$data['username'] = $username;
					$data['convert_id'] = $cid;
					$data['userdata'] = $data1;
					$data['expend_coin'] = $cinter;
					$data['convert_get'] = $cinfo['convert_name'];
					$data['status'] = 2;
					$data['tag'] = $cinfo['tag'];
					$data['add_time'] = time();
					$log = M("convertrecords");
					$res = $log->add($data);
					if($res){
						$arr['ret'] = 1;
					}else{
						$arr['ret'] = -1;
					}			
				}else{
					$arr['ret'] = -3;
				}
			}else{
				$arr['ret'] = 2;
			}
		}else{
			$arr['ret'] = -2;
		}
		return json_encode($arr);
	}
	
 }
 ?>