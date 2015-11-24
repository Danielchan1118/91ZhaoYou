<?php
/**
 * 商家信息控制器
 * @author huyuming
 */
namespace Home\Controller;
class ShopInfoController extends HomeController{

	/**
 	 * 修改商户信息
 	 */
	public function ShopUser(){
		$C = M('cooperation_user');
        if($_POST){
            $username = trim(htmlspecialchars($_POST['user']));     
            $telphone= trim($_POST['m_phone']);
            $phone = trim($_POST['phone']);
            $qq = trim($_POST['qq']);
            $email = trim(htmlspecialchars($_POST['email']) ) ;
            if(!preg_match( "/^[a-zA-Z][a-zA-Z0-9]{6,22}$/",$username)){
                $this->error ( '您提交的数据有误:用户名非法,请检查您的输入!',U('ShopInfo/ShopUser') );
            }
            
            if (!preg_match("/^13[0-9]{1}[0-9]{8}$|15[0189]{1}[0-9]{8}$|189[0-9]{8}$/",$telphone)) {
                $this->error ( '您提交的数据有误:电话号码的格式是由11位数字组成',U('ShopInfo/ShopUser') );
            }

            if (! preg_match("/^((0\d{2,3})-)?(\d{7,8})(-(\d{3,}))?$/",$phone)) {
                $this->error ( '您提交的数据有误:电话号码的格式是由11或是0725-7856787位数字组成',U('ShopInfo/ShopUser') );
            }

            if ( !preg_match("/^\d{5,10}$/",$qq)) {
                $this->error ( '您提交的数据有误:qq号码的格式不正确',U('ShopInfo/ShopUser') );
            }
            if (!eregi("^[_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,4}$",$email)) {
                $this->error ( '您提交的数据有误:邮箱格式不正确',U('ShopInfo/ShopUser') );
            }
            $edit_user = $C->where('id='.$this->did." and username='".$username."'" )->find();
            $edit_email = $C->where('id='.$this->did." and email='".$email."'" )->find();

            //检验用户名
            if(!$edit_user){
                $edit_users = $C->where( "username='".$username."'")->find();
                if($edit_users){
                    $this->error('用户名已存在');
                }
            }

            //检验邮箱
            if(!$edit_email){
                $edit_emails = $C->where( "email='".$email."'")->find();               
                if($edit_emails){
                    $this->error('邮箱已存在');
                }
            }

            $arr['username']  = $username;
            $arr['mobilephone']  = $telphone;
            $arr['phone']  = $phone;
            $arr['qq']  = $qq;
            $arr['email']  = $email;
            $res = $C->where('id='.$this->did)->save($arr);               
            if($res){
                $this->success('修改商家信息成功',U('ShopInfo/ShopUser'));
            }else{
                $this->error('修改商家信息失败',U('ShopInfo/ShopUser'));
 		    }
            
        }else{
            $user = $C->where('id='.$this->did)->find();
			$this->user=$user;
			$this->display();
        }
	}

    /**
     * 商家信息的修改密码
     */
    public function ShopPass(){
        $user = M('cooperation_user');

        if($_POST){
            $password = trim($_POST['password']);
            $password1 = trim($_POST['password1']);
            $password2 = trim($_POST['password2']);
            if($password){
                $password = md5 ( 'y' . md5 ( $password ) . 'm' );  
                $verify = $user->where("password='".$password."' and id=".$this->did)->count();

                if($verify > 0){
                    echo 1;
                }else{
                    echo 2;
                }    
                exit;        
            }else{
                if($password1 && $password2){                    
                    if($password1 == $password2){
                        $data['password'] = md5 ( 'y' . md5 ( $password2 ) . 'm' ); 
                        $res = $user->where('id='.$this->did)->save($data);
                        if($res){
                                $this->success('修改成功',U('ShopInfo/ShopUser'));
                        }else{ 
                            if($res==0){
                                $this->error('新密码和旧密码不能一样',U('ShopInfo/ShopUser'));
                            }else{
                                $this->error('修改失败');
                            }
                        }
                    }else{
                        $this->error('两次的密码不一致，请从新输入');
                    }
                
                }
            }
        }
    }

}
?>