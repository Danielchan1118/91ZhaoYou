<?php
/**
 *充值中心接口控制器
 * @author huyuming
 */
namespace Home\Controller;
class MenuListController extends HomeController{
		
		/**
		 *菜单列表
		 */
		public function  MenuList(){
			$Menu = M('menu_sj');
			$list = $Menu->where('pid=0') ->select();
			if($list){
				$arr = array();
				foreach ($list as $k => $v) {
					$arr[$k]['id']     = $v['id'];
					$arr[$k]['name']   = $v['name'];
					$arr[$k]['pid']    = $v['pid'];
					$arr[$k]['model']  = $v['model'];
					$arr[$k]['controller']  = $v['controller'];
					$arr[$k]['action']  = $v['action'];
					$arr[$k]['order_id']  = $v['order_id'];
					$arr[$k]['is_show']  = $v['is_show'];
					$listtwo = $Menu->where('pid='.$v['id']) ->select();
					$arr[$k]['listtwo'] = $listtwo;
				}
			}
			

			$this->MenuList= $arr;
			$this ->list =$list;
			$this->display();
		}





}