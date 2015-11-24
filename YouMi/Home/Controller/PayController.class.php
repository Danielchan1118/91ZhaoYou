<?php
/**
 *充值中心接口控制器
 * @author huyuming
 */
namespace Home\Controller;
class PayController extends HomeController{

	/**
 	 * 充值中心支付首页
 	 */
	public function index(){
		$M = M('pay_ok');
		if(IS_POST){
			$arr['orderid'] = ($_POST['order_id']);
			$arr['pay_to_account'] = trim(htmlspecialchars($_POST['member_name']));
			$arr['pay_tag'] = $_POST['pay_tag'];//标签
			$arr['did'] = $this->did;//标签
			$arr['pay_port'] = $_POST['payname'];//支付方式		
			$arr['pay_money'] = intval($_POST['pay_money']);//充值金额
			$arr['pay_ip'] = $_SERVER["REMOTE_ADDR"];
			$arr['pay_time'] = time();
			$arr['order_status'] = "1,1,1";//订单状态
			$arr['remark'] = "下单未付款";//备注
			if($_POST['payname'] ==''){
				$this->error('支付方式未选择，请选择');
			}
			if( $_POST['number'] != $_POST['number1'] ){
				$this->error('两次账号不一样，请从新输入/账号不能为空');
			}
			if($arr['pay_money'] ==''){
				$this->error('金钱不能为空');
			}
			if($M->where("orderid='".$arr['orderid']."'")->find()){
				$this->error('订单号已经被使用，请重新输入');
			}
			if('支付宝' == $_POST['payname'] ){ $arr['pay_bank'] = '';}else{ $arr['pay_bank'] = $_POST['pay_bank'];}
			$addpay = $M->add($arr);
			if($addpay){
				$import = import ( "@.Pay." . strtolower ( $arr ['pay_tag'] ) . "" );
				if (! $import) {
					$this->assign ( 'jumpUrl', '/' );
					$this->error ( "充值渠道接口加载失败！请联系客服解决." );
				}
				$beginpay = new $arr['pay_tag']();
				if (! method_exists ( $beginpay, 'BeginPay' )) {
					$this->assign ( 'jumpUrl', '/' );
					$this->error ( "游戏充值渠道操作方法不存在！请联系客服解决." );
				}
				// 模板赋值
				$data = $beginpay->BeginPay ( $arr );
				$this->assign ( 'data', $data );
				$this->display ('pay/pay/' . $arr ['pay_tag']);
			}else{
				$this->error('下单失败');
			}
		}else{
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
		
			// ##获取充值方式###
			$pay_type = M ( 'pay_type' );
			$arr_paytype['status'] ="1";
			$arr_paytype['isdisplay'] ="1";
			$pay_type_list = $pay_type->where( $arr_paytype )->order ( 'sort asc' )->select ();
			$this->assign ( 'pay_type_list', $pay_type_list );
			//生成订单
			import ( "Org.Util.String" );
			$String = new \Org\Util\String();
			$this->order_id = date ( 'Ymd' ) . $String::randString ( 1, 5 ) . date ( 'His' ) . $String::randString ( 1, 5 );

			$this->display("pay");
		}
	}

	/**
	 *查询接收ajax数据
	 */
	public function selectid($name = ''){
		if($name){
			$M = M('pay_type');
			$data = $M->field('tag,payname,content')->where('id='.$name)->find();
			if($data){	
				$this->ajaxReturn($data);
			}else{
				$this->error('错误');
			}
		}
	}

}

?>