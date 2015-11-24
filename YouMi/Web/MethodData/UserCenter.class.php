<?php 
/**
 * 汇总管理接口
 * 汇总
 *任务记录
 */
 class UserCenter{
	/**
	 * 汇总
	 */
	public function Summary($datas = array()){
		$username = trim($datas['username']);
		
		if($username){
			$M = M('users');
			$task = M('taskrecords');         //任务记录
			$convert = M('convertrecords');    //兑换记录
			$Invited = M('invitedrecords');      //邀请记录
			$arr = '';
			if($username){
				$num = $M->where("username='".$username."'")->field('id,username,level,taskgold,email,goldcount,usablegold')->find();	
				$arr['id'] = $num['id'];
				$arr['username'] = $num['username'];
				
				$grade = M("grade");
				$gradelist = $grade->order("grade asc")->select();
				$arr['level']  = intval($num['level']);
				if($num['level'] == count($gradelist)){
					$arr['phaseintegral'] = "当前为最高等级";
				}else{
					$arr['phaseintegral'] = "离下一等级还有".($gradelist[$arr['level']-1]['integral'] - $num['usablegold']);
				}
				
				$arr['taskgold'] = ($num['taskgold']/10000)."万";
				$arr['email'] = $num['email'];
				$arr['goldcount'] = ($num['goldcount']/10000)."万";
				$arr['tasks'] = $task->where("username='".$username."'")->count();
				$convertinfo = $convert->field("SUM(expend_coin) as glob,count(*) as num")->where("username='".$username."'")->find();
				
				$arr['convertnum'] = $convertinfo['num'];
				if($convertinfo['glob']){
					$arr['convertgold'] = $convertinfo['glob'];
				}else{
					$arr['convertgold'] = 0;
				}
				
				$arr['Inviteds'] = $Invited->where("username='".$username."'")->count();
				$arr['levelurl'] = "/web/ArticleDetil/infos/aid/20";
			}
			$res = $arr;
		}else{
			$res['res'] = -1;
			$res['error'] = '数据出错';
		}

		return json_encode($res);
	}

	/**
	 * 任务记录
	 */
	public function Records($datas = array()){
		$username= trim($datas['username']);
		$task_type = trim($datas['task_type']);

		if($username && $task_type){
			if(!isset($datas['cid'])){
				$p = 1;
				$n = 100;
			}else{
				$pages = intval($datas['cid']);
				$p = $pages;
				$pages = $pages + 1;
				$n = 10;
			}
		
			switch($task_type){
				case 'task':
					$M = M('taskrecords');
					$record = $M->table('sw_taskrecords u')
							  ->join('sw_activity_taskrule as w on u.atr_id = w.atr_id')
							  ->field('u.id,u.username,u.earn_coin,u.add_time,w.atr_name')
							  ->where("u.username='".$username."'")
							  ->order("u.add_time desc")
							  ->limit($p." , ".$n)
							  ->select();
				break;
				case 'convert':
					$M = M('convertrecords');
					$record = $M->table('sw_convertrecords u')
							  ->join('sw_convert as w on w.id = u.convert_id')
							  ->field('u.id,u.username,u.add_time,u.status,w.convert_name')
							  ->where("u.username='".$username."'")
							  ->order("u.add_time desc")
							  ->limit($p." , ".$n)
							  ->select();
				break;
				case 'invite':
					$user = M("users");
					$down = M("download_record");
					$task = M('taskrecords');
					$M = M('invitedrecords');
					$invitinfo = $M->field('id,invited_id,add_time,reward_coin')->where("username='".$username."'")->order("add_time desc")->limit($p." , ".$n)->select();
					$subinvit = array();
					foreach($invitinfo as $k=>$v){
						$invitinfo[$k]['invitlevel'] = "(一级邀请)";
						$glodcount = $down->field("SUM(glod) as glod")->where("username='".$v['invited_id']."'")->find();
						$invitinfo[$k]['goldcount'] = intval($glodcount['glod']);
						$coin = $task->field("SUM(earn_coin) as coin")->where("atr_id = 5 and winning='".$v['invited_id']."' and username='".$username."'")->find();
						$invitinfo[$k]['reward_coin'] = $coin['coin'];
						$subinfo = $M->field('id,invited_id,add_time,reward_coin')->where("username='".$v['invited_id']."'")->order("add_time desc")->select();
						if($subinfo){
							$subinvit[$k] = $subinfo;
						}
					}
		
					$inivtsub = array();
					if($subinvit){
						foreach($subinvit as $k=>$v){
							foreach($v as $key=>$value){
								$subglodcount = $down->field("SUM(glod) as glod")->where("username='".$value['invited_id']."'")->find();
								$inivtsub[$value['id']]['goldcount'] = intval($subglodcount['glod']);
								$inivtsub[$value['id']]['invitlevel'] = "(二级邀请)";	
								$inivtsub[$value['id']]['id'] = $value['id'];
								$inivtsub[$value['id']]['invited_id'] = $value['invited_id'];
								$inivtsub[$value['id']]['add_time'] = $value['add_time'];
								$subcoin = $task->field("SUM(earn_coin) as coin")->where("atr_id = 5 and winning='".$value['invited_id']."' and username='".$username."'")->find();
								$inivtsub[$value['id']]['reward_coin'] = $subcoin['coin'];
							}
						}
						
						$record = array_merge($invitinfo,$inivtsub);
					}else{
						$record = $invitinfo;
					}
				break;
			}

			if($record){
				if($pages > 0){
					foreach ($record as $k => $v) {
						$record[$k]['cid'] = $pages;
						$pages++;
					}
				}
		
				$arr = $record;
			}else{
				$arr['res'] = -2;
				$arr['error'] = '数据为空';
			}
			
		}else{
			$arr['res'] = -1;
			$arr['error'] = '数据出错';
		}
		return json_encode($arr);
	}
	/**
	 * VIP进度条
	 */
	public function VipProgres($datas = array()){
		$username = $datas['username'];
		$user = M("users");
		$userinfo = $user->field("usablegold,level")->where("username='".$username."'")->find();

		$grade = M("grade");
		$grade_list = $grade->field("integral,grade")->order("grade asc")->select();
		$grades = array();
		foreach($grade_list as $k=>$v){
			if($v['grade'] == $userinfo['level']){
				$num = $userinfo['usablegold']/$v['integral'];
				$grades['chance'] = $num*100;
				$grades['usablegold'] = $userinfo['usablegold'];
				$grades['level'] = $userinfo['level'];
			}
		}

		return json_encode($grades);
	}
 
 }
 
 ?>