<?php

class yeepay{

	public function __construct(){

	}

	public function beginPay($arr){
		require_once LIB_PATH .'Pay/yeepay/yeepayCommon.php';
		require_once LIB_PATH .'Pay/yeepay/HttpClient.class.php';

		$data ['p0_Cmd'] 		  = "Buy"; 
		$data ['p1_MerId'] 		  = '10001126856';//$p1_MerId; 
		$data ['p2_Order'] 		  = $arr ['order_id'];
		$data ['p3_Amt'] 		  = 0.01;//$arr ['pay_money']; 
		$data ['p4_Cur'] 		  = "CNY"; 
		$data ['p5_Pid'] 		  = $arr ['pay_to_account'];
		$data ['p6_Pcat'] 		  = $arr ['pay_to_account']; 
		$data ['p7_Pdesc'] 		  = $arr ['pay_to_account'];  
		$data ['p8_Url'] 		  = 'http://' . $_SERVER ['HTTP_HOST']."/PayReturn/yeePayAdmin";
		$data ['pa_MP'] 		  = $arr ['pay_to_account']; 
		$data ['pd_FrpId'] 		  = $arr ['pay_bank']; 
		$data ['pr_NeedResponse'] = "1";
		$data ['p9_SAF'] 		  = "0";
		$hmac = getReqHmacString($data ['p2_Order'],$data ['p3_Amt'],$data ['p4_Cur'],$data ['p5_Pid'],$data ['p6_Pcat'],$data ['p7_Pdesc'],$data ['p8_Url'],$data ['pa_MP'],$data ['pd_FrpId'],$data ['pr_NeedResponse']);
		$data ['hmac'] = $hmac;

		$res = "<html>
					<head>
					<title>To YeePay Page</title>
					</head>
					<body onLoad='document.yeepay.submit();'>
					<form name='yeepay' action='".$reqURL_onLine."' method='post'>
					<input type='hidden' name='p0_Cmd'					value='".$data ['p0_Cmd']."'>
					<input type='hidden' name='p1_MerId'				value='".$data ['p1_MerId']."'>
					<input type='hidden' name='p2_Order'				value='".$data ['p2_Order']."'>
					<input type='hidden' name='p3_Amt'					value='".$data ['p3_Amt']."'>
					<input type='hidden' name='p4_Cur'					value='".$data ['p4_Cur']."'>
					<input type='hidden' name='p5_Pid'					value='".$data ['p5_Pid']."'>
					<input type='hidden' name='p6_Pcat'					value='".$data ['p6_Pcat']."'>
					<input type='hidden' name='p7_Pdesc'				value='".$data ['p7_Pdesc']."'>
					<input type='hidden' name='p8_Url'					value='".$data ['p8_Url']."'>
					<input type='hidden' name='p9_SAF'					value='".$data ['p9_SAF']."'>
					<input type='hidden' name='pa_MP'					value='".$data ['pa_MP']."'>
					<input type='hidden' name='pd_FrpId'				value='".$data ['pd_FrpId']."'>
					<input type='hidden' name='pr_NeedResponse'	value='".$data ['pr_NeedResponse']."'>
					<input type='hidden' name='hmac'			value='".$data ['hmac']."'>
					</form>
					</body>
					</html>";
		echo $res;



	}




}














?>