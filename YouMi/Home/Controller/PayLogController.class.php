<?php
/**
 * 充值记录控制器
 *@ danielchan
 */
namespace Home\Controller;
class PayLogController extends HomeController{

	/**
	 * 充值订单列表
	 */
	public function orderList(){
		$all_time   = trim( $_POST['reservation'] );
		$order_status = trim($_POST['order_status']);
		$order_id   = trim($_POST['order_id']);
		$end_time = trim($_GET['end_time']);
		$start_time = trim($_GET['start_time']);

        if($all_time){
            $times = explode(" - ",$all_time);
            $end_time = strtotime($times[1]);
            $start_time = strtotime($times[0]);
            $_GET['end_time'] = $end_time;
            $_GET['start_time'] = $start_time;
        }else if(empty($end_time) || empty($start_time)){
            $end_time = time();
            $start_time  = strtotime(date("Y-m-d",time()-7*24*60*60)." 00:00:00");
        }


		$where = "did=".$this->did." AND paydelete=1";

		if($order_id){
			$where.= " AND orderid='".$order_id."'";
		}
		if($order_status){
			$where.= " AND order_status='".$order_status."'";
		}

		$where.= " AND pay_time BETWEEN ".$start_time." AND ".$end_time;

		$pay = M("pay_ok");
		$n = 5;
		$counts = $pay->where($where)->count();
		$Page  = new \Think\Page($counts,$n);// 实例化分页类 传入总记录数和每页显示的记录数
		$Page->setConfig ( 'prev', '上一页' );
		$Page->setConfig ( 'header', '款游戏' );
		$Page->setConfig ( 'first', '首 页' );
		$Page->setConfig ( 'last', '末 页' );
		$Page->setConfig ( 'next', '下一页' );
		$this->pay_list = $pay->table('sw_pay_ok pk')
							->field('pk.*,pt.payname')
							->join('sw_pay_type as pt ON pk.pay_tag=pt.tag')
							->where($where)
							->order("pay_time desc")
							->limit($Page->firstRow.','.$Page->listRows)
							->select();
		$this->Page  = $Page->show();
		$this->assign('start_time',$start_time);
		$this->assign('end_time',$end_time);
		$this->order_id = $order_id;
		$this->order_status = $order_status;
		$this->display();
	}

	/**
	 * 平台消耗记录
	 */
	public function ExpendRecords(){

		$arrR = $_REQUEST; 
        $strUrl = '/ExpendRecords/operation/report';
        if(!empty($arrR)){          
            foreach($arrR as $k => $v){
                if('_URL_' != $k){
                    $strUrl .= "/".$k."/".$v;
                }
            }
            $this->strUrl = $strUrl;
        }else{
            $this->strUrl = $strUrl."/search/搜索/p/1.html";
        }

		$down = M("download_record");
		$where = "r.did=".$this->did." and move = 1 and app_money > 0 ";
		$all_time   =  trim( $_REQUEST['reservation'] );
		$app_name   = trim($_POST['app_name']);
		$end_time = trim($_GET['end_time']);
		$start_time = trim($_GET['start_time']);
		
    	if($all_time){
            $times = explode(" - ",$all_time);
            $end_time = strtotime($times[1]);
            $start_time = strtotime($times[0]);
            $_GET['end_time'] = $end_time;
            $_GET['start_time'] = $start_time;
        }else if(empty($end_time) || empty($start_time)){
            $end_time = time();
            $start_time  = strtotime(date("Y-m-d",time()-7*24*60*60)." 00:00:00");
        }
		
		$where.=" and r.add_time BETWEEN ".$start_time." and ".$end_time;

		if($app_name){
			$where.=" and a.app_name like '%".$app_name."%'";
		}
		$n = 20;
		$count = $down->table('sw_download_record r')
					  ->join('sw_app as a on r.APP_KEY = a.APP_KEY')
					  ->where($where)
					  ->count();
		$Page  = new \Think\Page($count,$n);// 实例化分页类 传入总记录数和每页显示的记录数
		$Page->setConfig ( 'prev', '上一页' );
		$Page->setConfig ( 'header', '款游戏' );
		$Page->setConfig ( 'first', '首 页' );
		$Page->setConfig ( 'last', '末 页' );
		$Page->setConfig ( 'next', '下一页' );
		$expend_list = $down->table('sw_download_record r')
							->field('r.id, r.username, a.app_name, r.app_money, r.add_time')
							->join('sw_app as a on r.APP_KEY = a.APP_KEY')
							->where($where)
							->order("add_time desc")
							->limit($Page->firstRow.','.$Page->listRows)
							->select();
						
		$counts = $count;
		$moneys = 0;
		foreach ($expend_list as $k => $v) {
			$moneys += $v['app_money'];
		}	

		$res['expend_list'] = $expend_list;
		$res['counts'] = $counts;
		$res['moneys'] = $moneys;

		//Excel调用导出方法 
        if( $_REQUEST['operation']){ 
            if( 'report' == $_REQUEST['operation'] ){ $operation = 'report'; }     
            $this->dcOrDrExcel($res,$operation,'ExpendRecords');
        }

		$this->assign("Page",$Page->show());
		$this->assign("app_name",$app_name);
		$this->assign("counts",$counts);
		$this->assign("moneys",$moneys);
		$this->assign("expend_list",$expend_list);
		$this->assign("end_time",$end_time);
		$this->assign("start_time",$start_time);
		$this->display();
	}


	//批量删除充值记录
	public function orderDelAll(){
		$orderid = $_POST['orderid'];

		if(is_array($orderid)){
			$cids = implode ( ',', $orderid ); 
			$map ['id'] = array (
				'in',
				$orderid 
			);
		}else{
			$map ['id'] = $orderid;
		}
		
		$pay_ok = M ( "pay_ok" ); // 实例化card对象
		$flag = $pay_ok->where($map)->setField('paydelete',0);

		if($flag){
			$arr['ret'] = 1;
		 	$arr['message'] = "删除成功";
		}else{
			$arr['ret'] = -1;
		 	$arr['message'] = "删除失败";
		}
		$this->ajaxReturn($arr);
	}

	/*
	 * @name 导出,入Excel
	 * $pargam array $datas 二维数组
	 * $pargam string $operation 判断导出或导入
	 *
	 */
    public function dcOrDrExcel($datas,$operation,$function_name){

        switch ( $operation ) { 
            case 'report': 
                $author = $this->admin_user;
                //路径按自己项目实际路径修改，文件请到PHPExcel官网下载  
                include_once LIB_PATH."ORG/Excel/PHPExcel.php";
                include_once LIB_PATH."ORG/Excel/PHPExcel/Writer/Excel2007.php"; 
                //或者include 'PHPExcel/Writer/Excel5.php'; 用于输出.xls的  
                //创建一个excel  
                $objPHPExcel = new\PHPExcel(); 
                //保存excel—2007格式  
                $objWriter = new\PHPExcel_Writer_Excel2007( $objPHPExcel ); 
                //或者$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel); 非2007格式  

				if( "ExpendRecords" == $function_name ){
                    //设置excel的属性：
                    $title    = '金额消耗';
                    $fileName = '金额消耗_'.date('Y-m-d-H_i_s').'.xlsx';  
                    //创建人  
                    $objPHPExcel->getProperties()->setCreator( $author ); 
                    //最后修改人  
                    $objPHPExcel->getProperties()->setLastModifiedBy( $author ); 
                    //标题  
                    $objPHPExcel->getProperties()->setTitle( $title ); 
                    //题目  
                    $objPHPExcel->getProperties()->setSubject( $title ); 
                    //描述  
                    $objPHPExcel->getProperties()->setDescription( $title ); 
                    //种类  
                    $objPHPExcel->getProperties()->setCategory( "file" ); 
                    //  
                    //设置当前的sheet  
                    $objPHPExcel->setActiveSheetIndex( 0 ); 
                    //设置sheet的name  
                    $objPHPExcel->getActiveSheet()->setTitle( '金额消耗导出表' ); 
                    //设置单元格的值  

                    $subTitle = array( '用户', '应用名', '金额', '消耗时间' ); 
                    $colspan = range( 'A', 'D' ); 
                    $count = count( $subTitle ); 
                    // 标题输出  
                    for ( $index = 0; $index < $count; $index++ ) { 
                        $col = $colspan[$index]; 
                        $objPHPExcel->getActiveSheet()->setCellValue( $col . '1', $subTitle[$index] ); 
                        //设置font  
                        $objPHPExcel->getActiveSheet()->getStyle( $col . '1' )->getFont()->setName( 'Candara' ); 
                        $objPHPExcel->getActiveSheet()->getStyle( $col . '1' )->getFont()->setSize( 15 ); 
                        $objPHPExcel->getActiveSheet()->getStyle( $col . '1' )->getFont()->setBold( true ); 
                        $objPHPExcel->getActiveSheet()->getStyle( $col . '1' )->getFont()->getColor()->setARGB( '#FFFFFF' ); 
              
                        //设置填充色彩    
                        $objPHPExcel->getActiveSheet()->getStyle( $col . '1' )->getFill()->getStartColor()->setARGB( 'FF808080' ); 
                        
                        //设置宽度
                        $objPHPExcel->getActiveSheet()->getColumnDimension( $col )->setWidth( 20 ); 
/*                        if( $subTitle[$index] == '序号'){
                            $objPHPExcel->getActiveSheet()->getColumnDimension( $col )->setWidth( 8 ); 
                        }else{
                            $objPHPExcel->getActiveSheet()->getColumnDimension( $col )->setWidth( 18 ); 
                        }
*/                    } 
                    // 内容输出 
                    $i = 1;           
                    foreach ( $datas['expend_list'] as $key => $value ) {
                    	$add_time = date("Y-m-d H:i:s",$value['add_time']); 
                        $i += 1;
                        $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $value['username'] );
                        $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $value['app_name'] );
                        $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $value['app_money'] );
                        $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $add_time );
                    } 

                    $i += 1;
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, '' );
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, '' );
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, '' );
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, '' );

                    $i += 1;
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, '总条数:' );
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $datas['counts'] );
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, '消费总金额:' );
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $datas['moneys'] );
                }
   
			//在默认sheet后，创建一个worksheet               
			$objPHPExcel->createSheet();                
			$objWriter->save( $fileName ); 
			$this->download( $fileName, true ); 
			break;             
        } 
    }
    
    //下载文档
    public function download( $fileName, $delDesFile = false, $isExit = true ) { 
        if ( file_exists( $fileName ) ) { 
            header( 'Content-Description: File Transfer' ); 
            header( 'Content-Type: application/octet-stream' ); 
            header( 'Content-Disposition: attachment;filename = ' . $fileName ); 
            header( 'Content-Transfer-Encoding: binary' ); 
            header( 'Expires: 0' ); 
            header( 'Cache-Control: must-revalidate, post-check = 0, pre-check = 0' ); 
            header( 'Pragma: public' ); 
            header( 'Content-Length: ' . filesize( $fileName ) ); 
            ob_clean(); 
            flush(); 
            readfile( $fileName ); 
            if ( $delDesFile ) { 
                unlink( $fileName ); 
            } 
            if ( $isExit ) { 
                exit; 
            } 
        } 
    }












}

?>