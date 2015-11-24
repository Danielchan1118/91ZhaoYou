<?php
/**
 * 数据统计控制器
 * @author 付敏平
 */
namespace Home\Controller;
class DataCountController extends HomeController {
    /**
     *  分日统计
     */
    public function DownloadNum(){
        date_default_timezone_set('prc');
        $all_time   = trim( $_POST['reservation'] );
		$down = M("download_record");
        if($all_time){
            $times = explode(" - ",$all_time);
            $end_time = strtotime($times[1]." 23:59:59");
            $start_time = strtotime($times[0]);
			$num = ($end_time - $start_time)/(24*60*60);
        }else{
            $start_time = time()-7*24*60*60;
            $end_time = time();
            $num = 7;
        }

		$usercount = array();
		$user_count = array();
		$activatcount = array();
		$activeusercount = array();
        $activeuser = 0;
		$datacount = array();
		for($i = 0; $i<=$num; $i++ ){
            $times = date('Y-m-d',$end_time - $i*24*60*60);
            $starttime = strtotime($times);
            $endtime = strtotime($times." 23:59:59");
            
            $where = "did=".$this->did." and add_time between ".$starttime." and ".$endtime;
            $datacount[$i]['user_count'][] = $down->field('count(*) as num')->where($where." and nomove=1")->find();//下载量
            $datacount[$i]['usercount'][] = $down->field("count(*) as num,username")->where($where." and is_today_sign=1")->group("username")->find();
            $datacount[$i]['activatcount'][] = $down->field("SUM(app_money) AS money")->where($where." and app_money>0")->find();
            $datacount[$i]['activecount'][] = $down->field("count(*) as num")->where($where." and move = 1")->find();
            $datacount[$i]['activeusercount'][] = $down->field("count(*) as num")->where($where." and move = 1")->group("username")->find();
            
            if(!$datacount[$i]['activeusercount'][0]['num']){
                $datacount[$i]['activeusercount'][0]['num'] = 0; 
            }
			if(!$datacount[$i]['user_count'][0]['num']){
                $datacount[$i]['user_count'][0]['num'] = 0; 
            }
            if(!$datacount[$i]['usercount'][0]['num']){
                $datacount[$i]['usercount'][0]['num'] = 0; 
            }
            if(!$datacount[$i]['activatcount'][0]['money']){
                $datacount[$i]['activatcount'][0]['money'] = 0; 
            }
            $datacount[$i]['times'][] = $times;

        }
        if($all_time){
            $this->ajaxReturn($datacount);
        }else{
            $this->assign("datacount",$datacount);
            $this->assign('start_time',$start_time);
            $this->assign('end_time',$end_time);
            $this->display();
        }
    }

	/**
	 * 手机分辨率统计
	 */
	public function Index(){
        date_default_timezone_set('prc');
        $down = M("download_record");
        $all_time = trim($_POST['reservation']);

       if($all_time){
            $times = explode(" - ",$all_time);
            $end_time = strtotime($times[1]." 23:59:59");
            $start_time = strtotime($times[0]);
        }else{
            $start_time  = strtotime(date("Y-m-d",time()-7*24*60*60)." 00:00:00");
            $end_time = time();
        }

        $where = "did=".$this->did;

        $where.=" and add_time between ".$start_time." and ".$end_time;

        $downtype = trim($_POST['downtype']);

        switch ($downtype) {
            case 'evt_2':
                $field = "count(*) as num,mobilewidth,mobileheight";
                $group = "mobileheight,mobilewidth";
                break;
            case 'evt_3':
                $field = "count(*) as num,Network";
                $group = "Network";
                break;
            case 'evt_4':
                $field = "count(*) as num,ProvidersName";
                $group = "ProvidersName";
                break;
            case 'evt_5':
                $field = "count(*) as num,Mphonemodels";
                $group = "Mphonemodels";
                break;
            default:
                $field = "count(*) as num,Mpverinfor";
                $group = "Mpverinfor";
                $downtype = 'evt_1';
                break;
        }

        $todaynum = $down->field($field)->where($where)->group($group)->select();
		
        $ResolList = array();
        $downarray = array();

        foreach($todaynum as $v){
            switch ($downtype) {
                case 'evt_2':
                    $ResolList[] = $v['mobilewidth']."*".$v['mobileheight'];
                    break;
                case 'evt_3':
                   $ResolList[] = $v['Network'];
                    break;
                case 'evt_4':
                    $ResolList[] = $v['ProvidersName'];
                    break;
                case 'evt_5':
                    $ResolList[] = $v['Mphonemodels'];
                    break;
                default:
                    $ResolList[] = "'".$v['Mpverinfor']."'";
                    break;
            }

            $downarray[] = intval($v['num']);
        }

        if($_POST['downtype']){
            $arr['categories'] = $ResolList;
            $arr['downarray'] = $downarray;
            $this->ajaxReturn($arr);
        }else{
            $this->categories = "[".implode(",",$ResolList)."]";
            $this->downarray = "[".implode(",",$downarray)."]";
            $this->downtype = $downtype;
            $this->end_time = $end_time;
            $this->start_time = $start_time;
            $this->display();
        }
        
    }

    /**
     * 应用统计
     */
    public function AppData(){
        date_default_timezone_set('prc');
        $posttime = $_POST['reservation'];
        $appname = trim($_POST['app_name']);
        if($posttime){
            $times = explode(" - ",$posttime);
        }
       
        $down = M("download_record");
        $downcount = array();
        $appdata = array(); 

        if($appname){
            $app_name = " and a.app_name like '%".$appname."%'";
            $this->appname = $appname;
        }


        if(is_array($times)){
            $end_time = strtotime($times[1]);
            $start_time = strtotime($times[0]);
            $num = ($end_time - $start_time)/(24*60*60);
        }else{
            $start_time = time()-7*24*60*60;
            $end_time = time();
            $num = 7;
        }
		
		$where = "d.did=".$this->did;

        $moves = 0;
        $nomove = 0;
        $Activation = 0;
        $signs = 0;
        $app_money = 0;

        for($i = 0; $i<=$num; $i++ ){
            $times = date('Y-m-d',$end_time - $i*24*60*60);
            $starttime = strtotime($times);
            $endtime = strtotime($times." 23:59:59");

            $downcount =  $down->table('sw_download_record AS d')
                    ->join('sw_app AS a on d.APP_KEY = a.APP_KEY')
                    ->field('d.move, d.nomove, d.app_money, a.app_name,is_today_sign,app_money')
                    ->where($where." and d.add_time between ".$starttime." and ".$endtime.$app_name)
                    ->order("d.add_time desc")
                    ->select();
            foreach($downcount as $k=>$v ){
                if($v['move'] == 1){
                    $appdata[$v['app_name']]['move']+= 1;
                    $moves+= 1;
                    $this->moves = $moves;
                }
                if($v['nomove']==1){
                    $appdata[$v['app_name']]['nomove']+= 1;
                    $nomove+=1;
                    $this->nomove = $nomove;
                }

                if($v['is_today_sign'] == 1){
                    $appdata[$v['app_name']]['sign'] += intval($v['is_today_sign']);   
                    $sign += 1;
                    $this->sign = $sign;
                }
                if($v['app_money'] > 0){
                    $appdata[$v['app_name']]['Activation'] +=1;
                    $appdata[$v['app_name']]['app_money']+= intval($v['app_money']);  
                    $this->app_money += intval($v['app_money']);  
                    $Activation += 1;     
                    $this->Activation = $Activation;           
                }
                $appdata[$v['app_name']]['app_name'] = $v['app_name'];
            }

        }
        
        $this->end_time = $end_time;
        $this->start_time = $start_time;
        $this->AppData = $appdata;
        $this->display();
    }

    //导出CSV文件
    public function export(){
        $down = M("download_record");
        $downcount = array();
        $appdata = array(); 
        $end_time = trim($_POST['end_time']);
        $start_time = trim($_POST['start_time']);
        $type = trim($_POST['type']);
        $where = "d.did=".$this->did;
        if($end_time && $start_time){
            $num = ($end_time - $start_time)/(24*60*60);
        }else{
            $start_time = time()-7*24*60*60;
            $end_time = time();
            $num = 7;
        }
        if($type == 'appdata'){
            for($i = 0; $i<=$num; $i++ ){
                $times = date('Y-m-d',$end_time - $i*24*60*60);
                $starttime = strtotime($times);
                $endtime = strtotime($times." 23:59:59");

                $downcount =  $down->table('sw_download_record AS d')
                        ->join('sw_app AS a on d.APP_KEY = a.APP_KEY')
                        ->field('d.move, d.nomove, d.app_money, a.app_name,is_today_sign,app_money')
                        ->where($where." and d.add_time between ".$starttime." and ".$endtime.$app_name)
                        ->order("d.add_time desc")
                        ->select();

                foreach($downcount as $k=>$v ){
                    if($v['move'] == 1){
                        $appdata[$v['app_name']]['move']+= 1;
                    }
                    if($v['nomove']==1){
                        $appdata[$v['app_name']]['nomove']+= 1;
                    }
                    if($v['is_today_sign'] == 1){
                        $appdata[$v['app_name']]['sign']+= intval($v['is_today_sign']);    
                    }
                    if($v['app_money'] > 0){
                        $appdata[$v['app_name']]['Activation'] +=1;
                        $appdata[$v['app_name']]['app_money']+= intval($v['app_money']);    
                    }
                    $appdata[$v['app_name']]['app_name'] = $v['app_name'];
                }
            }

            $str = "应用名,运行量,下载量,激活量,签到量,应用消耗金额\n";   
            $str = iconv('utf-8','gb2312',$str);   
            foreach($appdata as $v){
                $app_name = iconv('utf-8','gb2312',$v['app_name']); //中文转码   
                $move = iconv('utf-8','gb2312',$v['move']); 
                $nomove = iconv('utf-8','gb2312',$v['nomove']);
                $Activation = iconv('utf-8','gb2312',$v['Activation']);
                $sign = iconv('utf-8','gb2312',$v['sign']);
                $app_money = iconv('utf-8','gb2312',$v['app_money']);  
                $str .= $app_name.",".$move.",".$nomove.",".$Activation.",".$sign.",".$app_money."\n"; //用引文逗号分开   
            } 

            $file_name = "应用统计";  
        }else if($type == 'downnum'){
            $all_time   = trim( $_POST['times'] );
            if($all_time){
                $times = explode(" - ",$all_time);
                $end_time = strtotime($times[1]." 23:59:59");
                $start_time = strtotime($times[0]);
                $num = ($end_time - $start_time)/(24*60*60);
            }else{
                $start_time = time()-7*24*60*60;
                $end_time = time();
                $num = 7;
            }

            $datacount = array();
            for($i = 0; $i<=$num; $i++ ){
                $times = date('Y-m-d',$end_time - $i*24*60*60);
                $starttime = strtotime($times);
                $endtime = strtotime($times." 23:59:59");
                
                $where = "did=".$this->did." and add_time between ".$starttime." and ".$endtime;
                
                $datacount[$i]['usercount'][] = $down->field("count(*) as num,username")->where($where." and is_today_sign=1")->group("username")->find();
                if(!$datacount[$i]['usercount'][0]['num']){
                    $datacount[$i]['usercount'][0]['num'] = 0; 
                }
                $datacount[$i]['activatcount'][] = $down->field("SUM(app_money) AS money")->where($where." and app_money>0")->find();
                $datacount[$i]['activecount'][] = $down->field("count(*) as num")->where($where." and move = 1")->find();
                if(!$datacount[$i]['activecount'][0]['num']){
                    $datacount[$i]['activecount'][0]['num'] = 0; 
                }
                $datacount[$i]['activeusercount'][] = $down->field("count(*) as num")->where($where." and move = 1")->group("username")->find();
                if(!$datacount[$i]['activeusercount'][0]['num']){
                    $datacount[$i]['activeusercount'][0]['num'] = 0; 
                }
                $datacount[$i]['times'] = $times;
            }

            $str = "日期,激活量,活跃用户,安装消耗金币,签到用户\n";   
            $str = iconv('utf-8','gb2312',$str);   
            foreach($datacount as $v){
                $times = iconv('utf-8','gb2312',$v['times']); //中文转码   
                $activecount = iconv('utf-8','gb2312',$v['activecount'][0]['num']); 
                $activeusercount = iconv('utf-8','gb2312',$v['activeusercount'][0]['num']);
                $activatcount = iconv('utf-8','gb2312',$v['activatcount'][0]['money']);
                $usercount = iconv('utf-8','gb2312',$v['usercount'][0]['num']);
                $str .= $times.",".$activecount.",".$activeusercount.",".$activatcount.",".$usercount."\n"; //用引文逗号分开   
            }   
            $file_name = "分日统计";
        }
        $filename = $file_name."_".date('Ymd').'.csv'; //设置文件名   
        $this->export_csv($filename,$str); //导出   

    }

    public function export_csv($filename,$data){   
        header("Content-type:text/csv");   
        header("Content-Disposition:attachment;filename=".$filename);   
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');   
        header('Expires:0');   
        header('Pragma:public');   
        echo $data;   
    }  

}

?>