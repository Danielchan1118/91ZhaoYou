<?php 
/**
 * name 资讯管理控制器
 * @author danielchan
 */

namespace Admin\Controller;
class ArticleController extends AdminController {
	/**
	 * name 公告列表
	 */
	public function articleList(){
		$Art = M('article');
		$n = 20;
		$counts = $Art->count();
		$Page  = new \Think\Page($counts,$n);// 实例化分页类 传入总记录数和每页显示的记录数
		$articleList = $Art->order("sort ASC,add_time DESC")->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->articleList = $articleList;
		$this->Page  = $Page->show();
		$this->display();
	}

	/**
	 * name 公告add & edit
	 */
	public function articleEdit($id=0){
		$Art = M('article');
		
		if(IS_POST){
			$data['title']   = trim( $_POST['title'] );
			$data['link']    = trim( $_POST['web_link'] );
			$data['status']  = $_POST['is_on'];
			$data['author']  = trim( $_POST['author'] );
			$data['sort']    = trim( $_POST['sort'] );
			$data['content'] = stripcslashes($_POST['content']);

			if(empty($data['title'])){ $this->error('标题不能为空！',U('Article/articleEdit') ); }
			if(empty($data['author'])){ $this->error('作者不能为空！',U('Article/articleEdit') ); }
			if(empty($data['content'])){ $this->error('内容不能为空！',U('Article/articleEdit') ); }
			if($id){
				$str = $Art->where('id='.$id)->getField('content');
				$this->imgDel($str,$data['content']);
				$data['modify_time'] = time();
				$res = $Art->where('id='.$id)->save($data);
			}else{
				$data['add_time'] = time();
				$data['modify_time'] = time();
				$res = $Art->add($data);
			}
			if($res){
				$this->success('操作成功！',U('Article/articleList'));
			}else{
				$this->error('操作失败！');
			}
		}else{
			$this->artinfo= $Art->where('id='.$id)->find();
			$this->display();
		}
	}

	/**
	 * name 公告删除
	 */
	/*public function articleDel($id){
		$Art = M('article');
		$str = $Art->where('id='.$id)->getField('content');
		$this->imgDel($str);
		if($Art->delete($id)){
				$this->success('删除成功！',U('Article/articleList'));
			}else{
				$this->error('删除失败！');
		}
	}
 */

	public function allDel(){
		/*
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
		*/
    	$del_id = $_POST['orderid'];
    	if($del_id){
	    	if(is_array($del_id)){
				$cids = implode ( ',', $del_id ); 
				$map ['id'] = array ('in',$del_id);
			}else{ 
				$map ['id'] = $del_id;
			}
    	}
		$Art = M ('article');
		$flag = $Art->where($map)->delete();
		
		if($flag){
			$arr['ret'] = 1;
			$arr['message'] = "删除成功";
		}else{
			$arr['ret'] = -1;
			$arr['message'] = "删除失败";
		}
     	$this->ajaxReturn($arr);
	}


	/**
	 * name 删除百度编辑器图片与数据库对比
	 */
	public function imgArr($str){
		
		$list = array();    //这里存放结果map  
		$c1 = preg_match_all('/<img\s.*?>/', $str, $m1);  //先取出所有img标签文本 
		for($i=0; $i<$c1; $i++) {    //对所有的img标签进行取属性  
		    $c2 = preg_match_all('/(\w+)\s*=\s*(?:(?:(["\'])(.*?)(?=\2))|([^\/\s]*))/', $m1[0][$i], $m2);   //匹配出所有的属性  
		    for($j=0; $j<$c2; $j++) {    //将匹配完的结果进行结构重组  
		        $list[$i][$m2[1][$j]] = !empty($m2[4][$j]) ? $m2[4][$j] : $m2[3][$j];  
		    }  
		}
		$arr = array();
		foreach($list as $v){
			$arr[] = $v['src'];
		} 
		return $arr;
	}

	/**
	 * name 删除百度编辑器图片
	 */
	public function imgDel($database,$change){//$str数据库$str1更改后
		$res = $this->imgArr($database);
		if($change){
			$res1 = $this->imgArr($change);
			foreach ($res as $val) {
				if(!in_array($val,$res1)){ 
					unlink(substr($val, 1));
				}
			}
		}else{
			foreach ($res as $val) {
				unlink(substr($val, 1));
			}
		} 
		
		


	}


}










































?>