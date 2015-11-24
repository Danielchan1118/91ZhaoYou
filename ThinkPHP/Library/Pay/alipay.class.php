<?php
/**
 * @name 支付宝支付接口--即时到账
 *
 * @author danielchan 
 */
class alipay {
	public function __construct() {
	}

	/**
	 *
	 * @name 开始支付
	 * @param array $arr        	
	 * @return string
	 */
	public function beginPay($arr) {
		require LIB_PATH .'Pay/alipay/alipay.config.php';
		require LIB_PATH .'Pay/alipay/lib/alipay_submit.class.php';

		/**************************请求参数**************************/
		//支付类型
		$payment_type = "1";
		//必填，不能修改
		
		//卖家支付宝帐户
		$seller_email = '2080782236@qq.com';
		//必填
		//商户订单号
		$out_trade_no = $arr ['order_id'];
		//商户网站订单系统中唯一订单号，必填
		//订单名称
		$subject =  $arr ['pay_to_account']; 
		//必填
		//付款金额
		$total_fee = $arr ['pay_money']; 
		//$total_fee = '0.1';
		//必填
		//订单描述
		$body = $arr ['pay_to_account']; 
		//商品展示地址
		$show_url = '';//可直接进入游戏
		//需以http://开头的完整路径，例如：http://www.xxx.com/myorder.html
		//防钓鱼时间戳
		$anti_phishing_key = "";
		//若要使用请调用类文件submit中的query_timestamp函数
		//客户端的IP地址
		$exter_invoke_ip = get_client_ip ();
		//非局域网的外网IP地址，如：221.0.0.1
				
		
		//构造要请求的参数数组，无需改动
		$parameter = array(
				"service" 			=> "create_direct_pay_by_user",
				"partner" 			=> trim($alipay_config['partner']),
				"payment_type"		=> $payment_type,
				"notify_url"		=> $notify_url,
				"return_url"		=> $return_url,
				"seller_email"		=> $seller_email,
				"out_trade_no"		=> $out_trade_no,
				"subject"			=> $subject,
				"total_fee"			=> $total_fee,
				"body"				=> $body,
				"show_url"			=> $show_url,
				"anti_phishing_key"	=> $anti_phishing_key,
				"exter_invoke_ip"	=> $exter_invoke_ip,
				"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
		);
			
		//建立请求
		//$alipaySubmit = new AlipaySubmit($alipay_config);
		//$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
		//return $html_text;


		//建立请求
		$alipaySubmit = new AlipaySubmit($alipay_config);
		$urlparam = $this->buidRequestParams($alipaySubmit,$parameter);
		Header("Location: ".$urlparam);

	}
	
	public function buidRequestParams($objSubmit,$para_temp){
		$para = $objSubmit->buildRequestPara($para_temp);
		$sHtml= $objSubmit->alipay_gateway_new;
		while (list ($key, $val) = each ($para)) {
            $sHtml.= $key."=".$val."&";
        }
		return $sHtml;
	}
	
	
}


?>

