<?php
/**
 * 前台登陆接口
 */
namespace Home\Controller;
define("HAVE_APP",4);
class IndexController extends HomeController {
	/**
	 * 数据分析
	 */
	public function index(){
		if($this->did > 0){
			$user = M("cooperation_user");
			$status = $user->where("id=".$this->did)->getfield("status"); 

			$menu = M("menu_sj");
			$menu_list = $menu->field("id,name,model,controller,action")->where("pid = 0 and is_show = 1")->order("order_id asc")->select();
			foreach($menu_list as $k=>$v){
				$menu_list[$k]['sub'] = $menu->field("id,name,model,controller,action")->where("pid = ".$v['id']." and is_show = 1")->order("order_id asc")->select();
			}

			$tree =  "[";
				foreach ($menu_list as $v) {
					$tree.="{text:'".$v['name']."',";	
						if($v['sub']){
							$tree.="items:[";      
							foreach ($v['sub'] as $value) {
								$tree.="{text : '".$value['name']."',";
								$url = U($value['model']."/".$value['controller']."/".$value['action']);
								$tree.='href:"'.$url.'"},';
							}
							$tree.="]";
						}
						
					$tree.="},";
				} 
					
			$tree.=  "]"; 
			$this->assign("tree",$tree);
				
			if($status != HAVE_APP){
				$this->isapp = "/Home/Index/create_app.html";
			}
			
			$this->display();
			
		}else{
			$this->success ( '请先登录', U('Index/UserLogin'));
		}
		
	}

	/**
	 * 首页
	 */
	public function index_info(){
		//应用排行榜
		$M =  M('app');
		$Ranking = $M->where("did=".$this->did)->field('app_name,app_downloadnum,app_runnum,installcount')->order('installcount desc')->limit('0,10')->select();

		//公告
		$Art = M('article');
		$this->articleList = $Art->field('id,title,modify_time')->order("add_time desc")->limit('5')->select();

		$down = M("download_record");		
		//昨日下载应用量,昨日用户运行量
		$yestime = date("Y-m-d",time()-24*60*60); 
		$startyes = strtotime($yestime);
		$endyes = strtotime($yestime." 23:59:59");
		$yesdata = $down->field("count(move) as move,count(nomove) as nomove,SUM(app_money) as app_money")->where("did=".$this->did." and add_time between ".$startyes." and ".$endyes)->find();
		
		//今日安装量曲线图
		date_default_timezone_set('prc');
		$starttime = strtotime(date("Y-m-d",time()).' 00:00:00');
		$todaynum = $down->where("did=".$this->did." and add_time between ".$starttime." and ".time()." and move > 0")->select();
		
		$newarray = array();
        $downarray = array();
        $usercount = 0;

		for($i = 0; $i < 24; $i++ ){
			$times = date('Y-m-d', mktime(0,0,0,date("m"),date('d'),date("Y")))." ".$i.":00:00";  
            $bigtimes = date('Y-m-d', mktime(0,0,0,date("m"),date('d'),date("Y")))." ".$i.":59:59";

            foreach($todaynum as $v){
                if(strtotime($bigtimes) > $v['add_time'] && $v['add_time'] > strtotime($times)){
                    $downarray[$i] += 1;  
                    $usercount++;
                }
            }
            
            if($downarray[$i] <= 0){
            	 $downarray[$i] = 0;
            }
           
            $newarray[$i] = "'".$i.":00'";
		}

		$this->title_data = "今日安装统计";
		$this->data_titles = "安装统计";
        $this->categories = "[".implode(",",$newarray)."]";
        $this->downarray = "[".implode(",",$downarray)."]";
		$this->ranking = $Ranking;
		$this->yesdata = $yesdata;
		$this->display();
	}

	/**
	 * 平台登陆
	 */
    public function UserLogin(){
    	if($this->did > 0){
    		$this->redirect ( 'Index/index' );	
    	}

		if($_POST){
			$User = M ( 'cooperation_user' );
			if ($this->CheckVerify ( $_POST ['Code'] )) {
				$_POST ['password'] = md5 ( 'y' . md5 ( $_POST ['password'] ) . 'm' );
				$uinfo = $User->where ( "username = '$_POST[username]' and password = '$_POST[password]'" )->find ();
				if ($uinfo) {
					session ( 'did', $uinfo ['id']);
					session ( 'username', $uinfo ['username']);
					cookie ( 'did', $uinfo ['id']);
					cookie ( 'username', $uinfo ['username']);
					$this->success ( '登录91找游商家后台成功。',U('Index/index'));
					
				} else {
					$this->error ( '用户名或密码错误。' );
				}
			} else { 
				$this->error ( '验证码错误。' );
			}
		}else{
			$this->time = time();
			$this->display();
		}
    }


    /**
	 *平台用户退出
	 *@param int $type 1为退出，2为注销
	 */
	public function UserQuit($type= 2){
		if($this->did){
			session(null);
			cookie(null);
			$this->did = session('did');
			
			if(!$this->did){
				if($type == 1){
					$this->success('退出成功。', U('Home/Home/close'));
				}else{
					$this->success('注销成功。', U('Home/Index/UserLogin'));
				}
			}
		}else{
			$this->error('你还没有登录。');
		}
	}


	/**
	 * 用户注册	
	 */
	public function UserReg(){
		$M = M('cooperation_user');				
		if($_POST){
			$username = trim(htmlspecialchars($_POST['username']));
			$password = trim(htmlspecialchars($_POST['password']));
			$password2 = trim(htmlspecialchars($_POST['password2']));
			$email = trim(htmlspecialchars($_POST['email']));

			if(!preg_match( "/^[a-zA-Z][a-zA-Z0-9]{6,22}$/",$username)){
				$this->error ( '您提交的数据有误:用户名非法,请检查您的输入!' );
			}

			if (strlen ( $password ) < 6 || strlen ( $password ) > 22) {
				$this->error ( '您提交的数据有误:密码长度为6到22位的字符,请检查您的输入!' );
			}
			if (strlen ( $password2 ) < 6 || strlen ( $password2 ) > 22 || $password2 == "" || $password2 !== $password) {
				$this->error ( '您提交的数据有误:密码长度为6到22位的字符,请检查您的输入!或者两次密码不对' );
			}
			
			if (!eregi("^[_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,4}$",$email)) {
				$this->error ( '您提交的数据有误:邮箱格式不正确' );
			}
			$map['username'] = $username;
			$map['password'] = md5 ( 'y' . md5 ( $password ) . 'm' );
			$map['email'] = $email;
			if (!$this->CheckVerify ( $_POST ['Code'] )) {
				$this->error('验证码错误！！');
			}
			if($_POST['password']=='' && $_POST['username']==''){
				$this->error('用户名和密码不能为空');
			}else{
				$user = $M->where("username='".$_POST['username']."'")->find();
				$email = $M->where("email='".$_POST['email']."'")->find();
				if($user){
					$this->error('用户名已存在! ');
				}
				if($email){
					$this->error('邮箱已注册! ');
				}
				$res = $M->add($map);
				if($res){
					session ( 'did', $res);
					session ( 'username', $username);
					cookie ( 'did', $res);
					cookie ( 'username', $username);
					$this->success('用户注册成功',U('Home/Index/index'));
				}else{
					$this->error('用户注册失败，请检查错误');
				}
			}
		}else{
			$this->display();
		}
	}

	/**
	 * 检测应用是否重名
	 */
	public function is_AppName($appname = ''){
		$appname = trim($appname);
		if($appname){
			$APP = M("app");
			$res = $APP->where("app_name='".$appname."'")->count();
			if($res){
				echo 1;
			}else{
				echo 2;
			}
		}

	}
	
	public function test(){
		echo phpinfo();
	}

}
