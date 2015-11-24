<?php
/**
 * 商家管理
 * @author 付敏平
 */
namespace Admin\Controller;
class CooperationUserController extends AdminController {
	/**
	 * 商家列表
	 */
	public function CooperationList(){
		$app = M("app");
		$throw = M("throw");
		$M = M("cooperation_user");
		if(trim($_POST['username'])){
			$username = trim($_POST['username']);
			$this->assign("username",$username);
		}
		$where = '1=1';
		if($username){
			$where.= " and username like '%".$username."%'";
		}

		$n = 20;
		$count = $M->where($where)->count();
		$Page = new \Think\Page($count,$n);
		$coo_list = $M->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
		$coo_count = $M->select();
		$moneys = 0;
		$app_counts = 0;
		$throw_counts = 0;
		foreach ($coo_count as $k => $v) {
			$moneys +=$v['money']; 
			$app_counts += $app->where("did=".$v['id'])->count();; 
			$throw_counts += $throw->where("did=".$v['id'])->count(); 
		}
		$P = $Page->firstRow+1;
		$coo_info = array();
		foreach($coo_list as $k=>$v){
			$coo_info[$k]['id'] = $v['id'];
			$coo_info[$k]['status'] = $v['status'];
			$coo_info[$k]['username'] = $v['username'];
			$coo_info[$k]['money'] = $v['money'];
			$coo_info[$k]['app_count'] = $app->where("did=".$v['id'])->count();
			$coo_info[$k]['throw_count'] = $throw->where("did=".$v['id'])->count();
			$coo_info[$k]['cid'] = $P;
			$P++;
		}
		$this->Page =$Page->show();		
		$this->assign("coo_list",$coo_info);
		$this->assign("moneys",$moneys);
		$this->assign("app_counts",$app_counts);
		$this->assign("throw_counts",$throw_counts);
		$this->display();

	}

	/**
	 * 操作用户详情信息
	 */
	public function CooperationInfo(){
		$M = M("cooperation_user");
		$data['username'] = trim($_POST['username']);
		$edit_id = intval($_POST['edit_id']);
		$editid  = intval($_POST['editid']);
		$lockid  = intval($_GET['lockid']);
		$Unlock  = intval($_GET['Unlock']);
		$money = $M->where('id='.$edit_id)->getField('money');
		if($editid > 0  ){
			$data['mobilephone'] = trim($_POST['mobilephone']);
			$data['password'] = md5 ( 'y' . md5 ( $password ) . 'm' );
			$data['phone'] = trim($_POST['phone']);
			$data['qq'] = trim($_POST['qq']);
			$data['email'] = trim($_POST['email']);
			$data['money'] = intval($_POST['money']);
			if($data['username'] == '' || $data['email'] == ''){
				$this->error("数据不能为空!");
			}
			$res = $M->where('id='.$editid)->save($data);
			if($res){
				$log_str = '商家修改,原积分'.$money.',  现在积分'.$data['money'];
				$this->admin_log($log_str); 	
				$this->success('修改成功',U ( 'Admin/CooperationUser/CooperationList' ));
			}else{
				$this->error("操作失败!",U ( 'Admin/CooperationUser/CooperationList' ));
			}
		}else if($edit_id > 0){
			$edit_coop = $M ->where('id='.$edit_id)->find();
			$this->ajaxReturn($edit_coop);
		}else if($lockid >= 0){
			if($Unlock ==1){
				$date['status'] = 0;
				$mess = '解锁成功';
			}else if($lockid > 0){

				$date['status'] = 3;
				$mess = '锁定成功';
			}
			$res = $M->where('id='.$lockid)->save($date);
			if($res){
				$this->success($mess,U ( 'Admin/CooperationUser/CooperationList' ));
			}else{
				$this->error("操作失败!",U ( 'Admin/CooperationUser/CooperationList' ));
			}
		}
		
	}

	/**
	 * 删除商家
	 */
	public function DelCooperationUser(){
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


			$arr = array();
			$M = M("cooperation_user");
			$res = $M->where($map)->delete();
			if($res){
			 	$arr['ret'] = 1;
			 	$arr['message'] = "删除成功";	
			}else{
	     		$arr['ret'] = -1;
			 	$arr['message'] = "删除失败";	
	     	}
	     	$this->ajaxReturn($arr);
    	}
	}
}

?>