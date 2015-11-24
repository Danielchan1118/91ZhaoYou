<?php
/**
 * 会员管理控制器
 * @author daneichan
 */
namespace Home\Controller;

class UsersController extends HomeController{

	/**
 	 * 商户个人信息展示与修改
 	 */
	public function comUserInfo($id){
		$C = M('cooperation_user');
		if($_POST){
			$data['mobilephone'] = I('mobilephone');
			$data['phone'] 		 = I('phone');
			$data['qq'] 		 = I('qq');
			$data['email'] 		 = I('email');
			if($C-> where('id='.$id)->save($data)){
				$this->success('修改成功!');
			}else{
				$this->error('修改失败!');
			}	
		}else{			
			$this->userInfo = $C->where('id='.$id.' AND status=1')->find();		
			//$this->display();
		}
		
	}

	/**
 	 * 商户个人信息密码修改
 	 * 
 	 */
	public function comUserEdit($id){
		$C = M('cooperation_user');
		if(IS_POST){
			$userPwd = $C->field('password')->where('id='.$id.' AND status=1')->find();
			$oldPwd  = I('oldPwd');
			$newPwd  = I('newPwd');
			$newPwd1 = I('newPwd1');
			if($userPwd['password'] == $oldPwd){
				if($newPwd == $newPwd1){
					if($C-> where('id='.$id)->setField('password',$newPwd)){
						$this->success('修改成功!');
					}else{
						$this->error('修改失败!');
					}
				}else{
					$this->error('新密码不一致,请重新输入!');
				}
			}else{
				$this->error('现密码不正确,请确认密码!');
			}
		}else{
			$this->display();
		}

	}

}
?>