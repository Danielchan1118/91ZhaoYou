<?php
/**
 * Home 公共模块公用控制器
 * @author danielchan
 */
namespace Home\Controller;
use Think\Controller;
class HomeController extends Controller{

	public function _initialize(){
		//登陆后获取用户资料
		
		if (session('did')) {
			$this->did = session('did');
			$this->username = session('username');
		}else{
			$this->CheckUsers();
		}
	}

	/**
	 * 检查是否需要用户登录操作
	 */
	public function CheckUsers(){
		$noUserAct = array('UserLogin','UserReg', 'Verify','CheckVerify','check_username','email_check','edit_user','close');
		if(!in_array(ACTION_NAME, $noUserAct)){
			$this->redirect('Index/UserLogin@s.91zhaoyou.com');
		}
	}

	/**
	 * 功能：IP地址获取真实地址函数
	 * 参数：$ip - IP地址
	 */	
	public function convertip($ip) {
	    //IP数据文件路径
	    $dir = $_SERVER ['DOCUMENT_ROOT'].'/Public/IpData/qqwry.dat';
	    $dat_path = $dir;

	    //检查IP地址
	   if(!preg_match("/^(\\d{1,3}\\.){3}\\d{1,3}$/", $ip)) {
	        return 'IP Address Error';
	    }
	    //打开IP数据文件
	    if(!$fd = @fopen($dat_path, 'rb')){
	        return 'IP date file not exists or access denied';
	    }

	    //分解IP进行运算，得出整形数
	    $ip = explode('.', $ip);
	    $ipNum = $ip[0] * 16777216 + $ip[1] * 65536 + $ip[2] * 256 + $ip[3];

	    //获取IP数据索引开始和结束位置
	    $DataBegin = fread($fd, 4);
	    $DataEnd = fread($fd, 4);
	    $ipbegin = implode('', unpack('L', $DataBegin));
	    if($ipbegin < 0) $ipbegin += pow(2, 32);
	    $ipend = implode('', unpack('L', $DataEnd));
	    if($ipend < 0) $ipend += pow(2, 32);
	    $ipAllNum = ($ipend - $ipbegin) / 7 + 1;
	   
	    $BeginNum = 0;
	    $EndNum = $ipAllNum;

	    //使用二分查找法从索引记录中搜索匹配的IP记录
	    while($ip1num>$ipNum || $ip2num<$ipNum) {
	        $Middle= intval(($EndNum + $BeginNum) / 2);

	        //偏移指针到索引位置读取4个字节
	        fseek($fd, $ipbegin + 7 * $Middle);
	        $ipData1 = fread($fd, 4);
	        if(strlen($ipData1) < 4) {
	            fclose($fd);
	            return 'System Error';
	        }
	        //提取出来的数据转换成长整形，如果数据是负数则加上2的32次幂
	        $ip1num = implode('', unpack('L', $ipData1));
	        if($ip1num < 0) $ip1num += pow(2, 32);
	       
	        //提取的长整型数大于我们IP地址则修改结束位置进行下一次循环
	        if($ip1num > $ipNum) {
	            $EndNum = $Middle;
	            continue;
	        }
	       
	        //取完上一个索引后取下一个索引
	        $DataSeek = fread($fd, 3);
	        if(strlen($DataSeek) < 3) {
	            fclose($fd);
	            return 'System Error';
	        }
	        $DataSeek = implode('', unpack('L', $DataSeek.chr(0)));
	        fseek($fd, $DataSeek);
	        $ipData2 = fread($fd, 4);
	        if(strlen($ipData2) < 4) {
	            fclose($fd);
	            return 'System Error';
	        }
	        $ip2num = implode('', unpack('L', $ipData2));
	        if($ip2num < 0) $ip2num += pow(2, 32);

	        //没找到提示未知
	        if($ip2num < $ipNum) {
	            if($Middle == $BeginNum) {
	                fclose($fd);
	                return 'Unknown';
	            }
	            $BeginNum = $Middle;
	        }
	    }

	    //下面的代码读晕了，没读明白，有兴趣的慢慢读
	    $ipFlag = fread($fd, 1);
	    if($ipFlag == chr(1)) {
	        $ipSeek = fread($fd, 3);
	        if(strlen($ipSeek) < 3) {
	            fclose($fd);
	            return 'System Error';
	        }
	        $ipSeek = implode('', unpack('L', $ipSeek.chr(0)));
	        fseek($fd, $ipSeek);
	        $ipFlag = fread($fd, 1);
	    }

	    if($ipFlag == chr(2)) {
	        $AddrSeek = fread($fd, 3);
	        if(strlen($AddrSeek) < 3) {
	            fclose($fd);
	            return 'System Error';
	        }
	        $ipFlag = fread($fd, 1);
	        if($ipFlag == chr(2)) {
	            $AddrSeek2 = fread($fd, 3);
	            if(strlen($AddrSeek2) < 3) {
	                fclose($fd);
	                return 'System Error';
	            }
	            $AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
	            fseek($fd, $AddrSeek2);
	        } else {
	            fseek($fd, -1, SEEK_CUR);
	        }

	        while(($char = fread($fd, 1)) != chr(0))
	            $ipAddr2 .= $char;

	        $AddrSeek = implode('', unpack('L', $AddrSeek.chr(0)));
	        fseek($fd, $AddrSeek);

	        while(($char = fread($fd, 1)) != chr(0))
	            $ipAddr1 .= $char;
	    } else {
	        fseek($fd, -1, SEEK_CUR);
	        while(($char = fread($fd, 1)) != chr(0))
	            $ipAddr1 .= $char;

	        $ipFlag = fread($fd, 1);
	        if($ipFlag == chr(2)) {
	            $AddrSeek2 = fread($fd, 3);
	            if(strlen($AddrSeek2) < 3) {
	                fclose($fd);
	                return 'System Error';
	            }
	            $AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
	            fseek($fd, $AddrSeek2);
	        } else {
	            fseek($fd, -1, SEEK_CUR);
	        }
	        while(($char = fread($fd, 1)) != chr(0)){
	            $ipAddr2 .= $char;
	        }
	    }
	    fclose($fd);

	    //最后做相应的替换操作后返回结果
	    if(preg_match('/http/i', $ipAddr2)) {
	        $ipAddr2 = '';
	    }
	    $ipaddr = "$ipAddr1 $ipAddr2";
	    $ipaddr = preg_replace('/CZ88.Net/is', '', $ipaddr);
	    $ipaddr = preg_replace('/^s*/is', '', $ipaddr);
	    $ipaddr = preg_replace('/s*$/is', '', $ipaddr);
	    if(preg_match('/http/i', $ipaddr) || $ipaddr == '') {
	        $ipaddr = 'Unknown';
	    }

	    return  iconv('GB2312', 'UTF-8', $ipaddr);
	}

	/**
	 * 上传数据
	 * @param array $files	//上传文件信息
	 * @param array $config	//上传文件配置
	 */
	public function Uploads($files, $config){
		$upload = new \Think\Upload($config);// 实例化上传类
		$info   =   $upload->upload($files);
		if(!$info) {	// 上传错误提示错误信息    
			return $upload->getError();
		}else{
			return $info;
		}
	}

	/**
	 * 验证码图片
	 */
	public function Verify($id = ''){
		$_vc = new \Think\ValidateCode();  //实例化一个对象
		$_vc->doimg();  
		$_SESSION['authnum_session'] = $_vc->getCode();//验证码保存到SESSION中
	}

	/**
	 * 检测输入的验证码是否正确
	 * @param string $code 用户输入的验证码字符串
	 * @param int $id
	 * @return boolean
	 */
	function CheckVerify($code, $id = ''){    
		if($code){
			if($code == $_SESSION['authnum_session']){
				return true;
			}else{
				return false;
			}
		}
	}

	/**
	 * 上传APK文件包回调
	 */
	public function uploadify(){
		$targetFolder = './Public/Uploads/App/'.session('did');
		$this->MakeDir($targetFolder); 
		$folderDir = '/Public/Uploads/App/'.session('did').'/';//设置上传目录
		if(!is_dir($targetFolder)){
			mkdir($targetFolder);
		}
		if (!empty($_FILES)) {
			$tempFile = $_FILES['file_upload']['tmp_name'];

			$extend = explode("." , $_FILES['file_upload']['name']);
			$va=count($extend)-1;

			$path = $_FILES['file_upload']['name'];
			$targetFile =$targetFolder. '/' . $path; //$_FILES['Filedata']['name'];//上传后的图片路径
			// Validate the file type
			$fileTypes = array('apk'); //允许的文件后缀
			//$fileParts = pathinfo($_FILES['file_upload']['name'],PATHINFO_EXTENSION);

			if (in_array($extend[$va],$fileTypes)) {
				move_uploaded_file($tempFile,iconv("gb2312","UTF-8", $targetFile));
				$data['originalname'] = $_FILES['file_upload']['name'];
				//$data['type'] = $_GET['id'];
				$data['newname'] = $path;
				exit($folderDir.$path);//上传成功后返回给前端的数据
			} else {
				echo '不支持的文件类型';
			}
		}
	}

	/**
	 * 递归目录函数
	 */
	public function MakeDir($dir){
		if(!is_dir($dir)&& !is_file($dir)){
			if(strlen($dir)<=1){
				return;
			}
			$dirs = explode('/',$dir);
			$curdir = $dirs[count($dirs)-1];
			$curnamelength = strlen($curdir);
			$parentdir = substr($dir,0,strlen($dir)-$curnamelength-1);
			if(!is_dir($parentdir)&& !is_file($dir))	{
				$this->MakeDir($parentdir);
			}
			mkdir($dir);
		}
	}

	/**
	 * 验证注册/修改用户名
	 */
	public function check_username(){
		$M = M('cooperation_user');
		$user = htmlspecialchars(I('username'));
		if($user){
			if (! preg_match ( "/^[a-zA-Z][a-zA-Z0-9]{6,22}$/", $user )) {
			echo 3;
			}else{
			if($M->where("username='".$user."'")->find()){
				echo 2;
			}else{
				echo 1;
				}
			}
		}
	}

	/**
	 * 验证注册邮箱
	 */
	public function email_check(){
		$M = M('cooperation_user');
		$email = I('email');
		$info = $M->where("email='".$email."'")->find();
		if($info){
			echo 2;
		}else{
			echo 1;
		}
	}

	/**
	 *修改用户名
	 */
	public function edit_user(){
		$M = M('cooperation_user');
		$edit_user = htmlspecialchars(I('edit_user'));
		$edit_email = htmlspecialchars(I('edit_email'));
		if($edit_user){
			$query_user = $M->where('id='.$this->did." and username='".$edit_user."'" )->find();
			if($query_user){
				echo 4;
			}else{
				$query_users = $M->where("username='".$edit_user."'")->find();
				if($query_users){
					echo 5;
				}else{
					echo 6;
				}
			}

		}else if($edit_email){
			$query_email = $M->where('id='.$this->did." and email='".$edit_email."'" )->find();
			if($query_email){
				echo 7;
			}else{
				$query_emails = $M->where("email='".$edit_email."'")->find();
				if($query_emails){
					echo 8;
				}else{
					echo 9;
				}
			}
		}
	}
	
}



?>