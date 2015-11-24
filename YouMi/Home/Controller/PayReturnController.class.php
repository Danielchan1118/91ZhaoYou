<?php
/**
 *接收充值中心接口返回数据处理控制器
 * @author daneichan
 */
namespace Home\Controller;
use Think\Controller;
class PayReturnController extends Controller{
	//alipay后台处理
	public function aliPayAdmin(){		
		$dir = $_SERVER ['DOCUMENT_ROOT'];
		require_once $dir . '/YouMi/Home/Pay/alipay/alipay.config.php';
		require_once $dir . '/YouMi/Home/Pay/alipay/lib/alipay_notify.class.php';

		//计算得出通知验证结果
		$alipayNotify = new\AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyNotify();
		
		if($verify_result) {//验证成功

			$out_trade_no = $_POST['out_trade_no'];//商户订单号
			$trade_status = $_POST['trade_status'];//交易状态
			$total_fee 	  = $_POST['total_fee'];//充值金额

		    if($_POST['trade_status'] == 'TRADE_FINISHED') {
		    	$pay = M ( "pay_ok" );
				$payInfo = $pay->where("orderid='".$out_trade_no."'")->find();
				if("2,2,2" == $payInfo['order_status']){ exit();}

				//查询出费率
				$pay_type = M('pay_type');
				$fee  = $pay_type->where("tag ='".$payInfo['pay_tag']."'")->getField('fee');
				if($fee !=''){
					$arr['pay_really_money'] = $total_fee*$fee/100;
				}else{
					$arr['pay_really_money'] = $total_fee;
				}
				
				$arr['success_time'] 	 = time();
				$arr['order_status'] 	 = '2,2,2';
				$arr['remark'] 			 = '充值成功,充值金额:'.$total_fee.'元';
				$pay->where("orderid='".$out_trade_no."'")->save($arr);

				//充值成功，商家打钱
				$user = M('cooperation_user');
				$user->where('id='.$payInfo['did'])->setInc('money',$total_fee);

		    }else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
		    	$pay = M ( "pay_ok" );
				$payInfo = $pay->where("orderid='".$out_trade_no."'")->find();
				if("2,2,2" == $payInfo['order_status']){ exit();}

				//查询出费率
				$pay_type = M('pay_type');
				$fee  = $pay_type->where("tag ='".$payInfo['pay_tag']."'")->getField('fee');
				if($fee !=''){
					$arr['pay_really_money'] = $total_fee*$fee/100;
				}else{
					$arr['pay_really_money'] = $total_fee;
				}
				
				$arr['success_time'] 	 = time();
				$arr['order_status'] 	 = '2,2,2';
				$arr['remark'] 			 = '充值成功,充值金额:'.$total_fee.'元';
				$pay->where("orderid='".$out_trade_no."'")->save($arr);

				//充值成功，商家打钱
				$user = M('cooperation_user');
				$user->where('id='.$payInfo['did'])->setInc('money',$total_fee);
		    }      
			echo "success";		//请不要修改或删除	
		}
		
	}

	//alipay前台处理
	public function aliPayHome(){
		$dir = $_SERVER ['DOCUMENT_ROOT'];
		require_once $dir . '/YouMi/Home/Pay/alipay/alipay.config.php';
		require_once $dir . '/YouMi/Home/Pay/alipay/lib/alipay_notify.class.php';
		//计算得出通知验证结果
		$alipayNotify = new\AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyReturn();		
		
		if($verify_result) {//验证成功
			//交易状态
		    if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
				$this->success('支付成功!',U('Home/Pay/index'));//跳转到充值成功提示页面！
		    }
		}
	}
		
	//yeepay后台处理
	public function yeePayAdmin(){

		$dir = $_SERVER ['DOCUMENT_ROOT'];
		require $dir . '/YouMi/Home/Pay/yeepay/yeepayCommon.php';
		$get = $_REQUEST;			
		#	只有支付成功时易宝支付才会通知商户.
		##支付成功回调有两次，都会通知到在线支付请求参数中的p8_Url上：浏览器重定向;服务器点对点通讯.

		#	解析返回参数.
		$return = getCallBackValue($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$hmac);

		#	判断返回签名是否正确（True/False）
		$bRet = CheckHmac($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$hmac);
		#	以上代码和变量不需要修改.
			 	
		#	校验码正确.
		if($bRet){
			if($r1_Code=="1"){
				
			#	需要比较返回的金额与商家数据库中订单的金额是否相等，只有相等的情况下才认为是交易成功.
			#	并且需要对返回的处理进行事务控制，进行记录的排它性处理，在接收到支付结果通知后，判断是否进行过业务逻辑处理，不要重复进行业务逻辑处理，防止对同一条交易重复发货的情况发生.      	  	
				$pay = M('pay_ok');
				$payInfo = $pay->where("orderid='".$r6_Order."'")->find();
				if($r9_BType=="1"){//yeepay前台处理
					if($payInfo['pay_money'] == intval($r3_Amt)){
						$this->success('充值成功!',U('Home/Pay/index'));//跳转到充值成功提示页面！
					}
				}elseif($r9_BType=="2"){//yeepay后台处理
					echo "success";
					if($payInfo['pay_money'] == intval($r3_Amt)){
						if('2,2,2' == $payInfo['order_status']){ exit();}
						//查询出费率
						$pay_type = M('pay_type');
						$fee  = $pay_type->where("tag ='".$payInfo['pay_tag']."'")->getField('fee');
						if($fee !=''){
							$arr['pay_really_money'] = $r3_Amt*$fee/100;
						}else{
							$arr['pay_really_money'] = $r3_Amt;
						}
						
						$arr['success_time'] 	 = time();
						$arr['order_status'] 	 = '2,2,2';
						$arr['remark'] 			 = '充值成功,充值金额:'.$total_fee.'元';
						$pay->where("orderid='".$r6_Order."'")->save($arr);

						//充值成功，商家打钱
						$user = M('cooperation_user');
						$user->where('id='.$payInfo['did'])->setInc('money',$total_fee);
					} 
				}
			}	
		}else{
			echo "交易信息被篡改";
		}
	}



}








?>