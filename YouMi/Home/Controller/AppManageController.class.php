<?php
/**
 * 应用设置控制器
 * @author 付敏平
 */
namespace Home\Controller;
class AppManageController extends HomeController {
	/**
	 * 应用列表
	 */
    public function AppList(){
    	$name = trim( $_POST['name'] );
    	$type = intval( $_POST['app_type'] );
    	$throw_type = intval( $_POST['throw_type'] );
    	$all_time   =  trim( $_POST['reservation'] );
    	$end_time = trim($_GET['end_time']);
		$start_time = trim($_GET['start_time']);
		$stauts = intval($_POST['stauts']);
		$throwtype = intval( $_GET['throw_type'] );
		$getstauts = intval($_GET['stauts']);

		if($all_time){
            $times = explode(" - ",$all_time);
            $end_time = strtotime($times[1]." 23:59:59");
            $start_time = strtotime($times[0]);
            $_GET['end_time'] = $end_time;
            $_GET['start_time'] = $start_time;
        }else if(empty($end_time) || empty($start_time)){
            $end_time = time();
            $start_time  = strtotime(date("Y-m-d",time()-7*24*60*60)." 00:00:00");
        }

    	$where =  "did=".$this->did;

		if($type>0){
			$where.= " and app_type=".$type;
			$_GET['app_type'] = $type;
		}else{
			$type = intval( $_GET['app_type'] );
			if($type>0){
				$where.= " and app_type=".$type;
			}
		}

		if($throw_type > 0){
			$where.= " and is_throw=".$throw_type;
			$_GET['throw_type'] = $throw_type;
		}else if($throwtype>0){
			$where.= " and is_throw=".$throwtype;
		}

		if($stauts > 0 ){
			$where.= " and stauts=".$stauts;
			$_GET['stauts'] = $stauts;
		}else if($getstauts > 0){
			$where.= " and stauts=".$getstauts;
		}

    	if($name){ 
    		$where.= " AND app_name like '%".$name."%'";
    	}

    	if($start_time != $end_time){
    		$where.= " and add_time between " .$start_time." and ".$end_time;
    	}
    	
		$app = M("app");
		$n = 5;
		$counts = $app->where($where)->count();
		$Page  = new \Think\Page($counts,$n); // 实例化分页类 传入总记录数和每页显示的记录数
		$Page->setConfig ( 'prev', '上一页' );
		$Page->setConfig ( 'next', '下一页' );
		$app_list = $app->field("id,app_name,app_type,app_cover,app_size,app_downloadnum,stauts,add_time,is_throw,is_delete,expend_money")->where($where)->order("add_time desc")->limit($Page->firstRow.','.$Page->listRows)->select();
		$app_lists = $app->count();
		$is_stauts =  $app->where('stauts=1 and did='.$this->did." and add_time between " .$start_time." and ".$end_time)->count();
		$no_stauts =  $app->where('stauts=2 and did='.$this->did." and add_time between " .$start_time." and ".$end_time)->count();
		$is_throw =  $app->where('is_throw=2 and did='.$this->did." and add_time between " .$start_time." and ".$end_time)->count();
		$no_throw =  $app->where('is_throw=1 and did='.$this->did." and add_time between " .$start_time." and ".$end_time)->count();
		$P = $Page->firstRow+1;
		if($P== 0){
			$P = 1; 
		}
		foreach ($app_list as $k => $v) {
			$app_list[$k]['cid'] = $P;
			$P++;
		}
		
		$this->app_name = $name;
		$this->throw_type = $throw_type;
		$this->app_type = $type;
		$this->end_time = $end_time;
		$this->start_time = $start_time;
		$this->app_counts  = $counts;
		$this->is_stauts  = $is_stauts;
		$this->no_stauts  = $no_stauts; 
		$this->app_list  = $app_list;
		$this->no_throw  = $no_throw;
		$this->is_throw  = $is_throw;
		$this->stauts  = $stauts;
		$this->Page  = $Page->show();
		$this->display();
    }

    /**
     * 应用操作编辑或添加
     */
    public function AppDo($edit_id = ''){
		if (IS_POST) {
			$app = M("app");
			$map ['app_name'] = trim ( $_REQUEST ['app_name'] );			
			$map ['did'] = $this->did;
			$map ['app_explain'] 	 = trim ( $_REQUEST ['app_explain'] );
			$map ['app_intro'] 		 = trim ( $_REQUEST ['app_intro'] );
			$map ['app_downloadnum'] = trim ( $_REQUEST ['app_downloadnum'] );
			$map ['ios_url'] 		 = trim ( $_REQUEST ['ios_url'] );	
			$map ['remarks'] 		 = trim ( $_REQUEST ['remarks'] );	
			$map ['app_type'] 		 = intval ( $_REQUEST ['type'] );	
			
			if ($map ['app_name'] == '') {
				$this->error ( "应用名不能为空！" );
			}
			if($map ['app_explain']==''){
				$this->error ( "应用说明不能为空！" );
			}		
			if(empty($_FILES)){
   				$this->error('请选择上传文件');
  			}

			$arr = array();
			foreach ($_FILES as $k => $v) {
				if('android_url' == $k){//上传spk
					if($_FILES['android_url']['size'] > 0 ){
						$appPath = './Public/Uploads/App/'.$this->did;
						$this->MakeDir($appPath);
						$appth =  'Uploads/App/'.$this->did.'/'; 
						$upload = new \Think\Upload();// 实例化上传类    
						$upload->maxSize = 0;
						$upload->savePath = $appth;
						$upload->saveName = array();
						$upload->exts     = array('apk');
						$upload->autoSub  = true;
						$upload->subName  = '';
						$info   =   $upload->uploadOne($_FILES[$k]);    
						if(!$info) {// 上传错误提示错误信息        
							$this->error($upload->getError());    
						}else{// 上传成功 获取上传文件信息         
							$arr[$k] =  '/Public/'.$info['savepath'].$info['savename'];  
							unlink(substr($_POST['android_url1'], 1));  
						}
					}else{
						$arr['android_url'] = $_POST['android_url1'];
					}
				}

				if('app_cover' == $k ){//上传图片
					if($_FILES['app_cover']['size'] > 0  ){
						$imgPath = './Public/Uploads/images/'.$this->did;
						$this->MakeDir($imgPath);
						$imgth =  'Uploads/images/'.$this->did.'/'; 
						$upload = new \Think\Upload();// 实例化上传类    
						$upload->maxSize = 3145728;
						$upload->savePath = $imgth;
						$upload->saveName = array('uniqid','');
						$upload->exts     = array('jpg', 'gif', 'png', 'jpeg');
						$upload->autoSub  = true;
						$upload->subName  = array('date','Ymd');
						$info   =   $upload->uploadOne($_FILES[$k]);    
						if(!$info) {// 上传错误提示错误信息        
							$this->error($upload->getError());    
						}else{// 上传成功 获取上传文件信息         
							$arr[$k] =  '/Public/'.$info['savepath'].$info['savename'];  
							unlink(substr($_POST['app_cover1'], 1));
						}
					}else{
						$arr['app_cover'] = $_POST['app_cover1'];
					}					
				}
				if('app_images' == $k){//上传图片
					if( $_FILES['app_images']['size'] > 0 ){
						$imgPath = './Public/Uploads/images/'.$this->did;
						$this->MakeDir($imgPath);
						$imgth =  'Uploads/images/'.$this->did.'/'; 
						$upload = new \Think\Upload();// 实例化上传类    
						$upload->maxSize = 3145728;
						$upload->savePath = $imgth;
						$upload->saveName = array('uniqid','');
						$upload->exts     = array('jpg', 'gif', 'png', 'jpeg');
						$upload->autoSub  = true;
						$upload->subName  = array('date','Ymd');
						$info   =   $upload->uploadOne($_FILES[$k]);    
						if(!$info) {// 上传错误提示错误信息        
							$this->error($upload->getError());    
						}else{// 上传成功 获取上传文件信息         
							$arr[$k] =  '/Public/'.$info['savepath'].$info['savename'];    
							unlink(substr($_POST['app_images1'], 1));
						}
					}else{
						$arr['app_images'] = $_POST['app_images1'];
					}
				}
			}				
			$map ['app_cover']   = $arr['app_cover'];
			$map ['app_images']  = $arr['app_images'];			
			$map ['android_url'] = $arr['android_url'];
			$map ['app_size'] 	 = $this->getBagSize($_SERVER['DOCUMENT_ROOT'].$arr['android_url']);
			$edit_id = intval($edit_id);
			if($edit_id > 0){
				$map ['edit_time'] 		 = time();			
				$res  = $app->where("did=".$this->did." and id=".$edit_id)->save( $map );
				$mess = "应用修改成功!";
			}else{
				$map ['add_time'] 		 = time();			
				$flag = $app->where ( "app_name='" . $map ['app_name']."' and did=".$this->did )->getField ( 'app_name' );
				if ($flag == $map ['app_name']) {
					$this->error ( "应用名重复！" );
				}
				$res  = $app->add ( $map );
				$mess = "应用添加成功!";
			}

			if($res){
				$this->success ( $mess, U ( 'AppManage/AppList' ) );
			}else{
				$this->error ( "发生错误:" . mysql_error (), U ( 'AppManage/AppList' ) );
			}
		}else{
			if($edit_id > 0){
				$app = M("app");
				$this->appInfo = $app->where('id='.$edit_id)->find();
			}
			
			$this->display();
		}
    }

    /**
     * 删除应用
     */
    public function appDel(){
    	$del_id = $_POST['orderid'];

    	if($del_id){
	    	if(is_array($del_id)){
				$cids = implode ( ',', $del_id ); 

				$map ['id'] = array (
					'in',
					$cids 
				);

				$apps ['app_id'] = array (
					'in',
					$cids 
				);
			}else{
				$map ['id'] = $del_id;
				$apps ['app_id'] = $del_id;
			}

			$app = M("app");
			$data['is_delete'] = 2;
			$res = $app->where($map)->save($data);
			$arr = array();

			$app = M("throw");
    		$applist = $app->where($apps)->count();

			if($applist){
				$arr['ret'] = 4;
				$arr['message'] = "数据不能删除，已经参与投放了";		
			}else if($res){
			 	$arr['ret'] = 3;
			 	$arr['message'] = "终止成功";	
			}else{
	     		$arr['ret'] = -1;
			 	$arr['message'] = "终止失败";	
	     	}
	     	$this->ajaxReturn($arr);
    	}

	}

	
    //获取sdk包的大小
    public function getBagSize($fileName){
    	$f = fopen($fileName,'r');
    	fseek($f,0,SEEK_END);
    	$size = ftell($f); 
    	fclose($f);
    	return $this->chSize($size);
    }

    //字节转换
    public function chSize($size){
    	if($size){
    		if(((1024*1024) > $size && $size >= 1024)){
    			return sprintf("%.2f KB", ($size/1024) );
    		}elseif((1024*1024*1024) > $size && $size >= (1024*1024)){
    			return sprintf("%.2f MB", ($size/(1024*1024)) );
    		}
    	}else{
    		return 0;
    	}
    }
    
}