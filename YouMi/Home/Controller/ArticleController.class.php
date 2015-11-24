<?php 
/**
 * name 公告显示控制器
 * @author danielchan
 */
namespace Home\Controller;
class ArticleController extends HomeController {

	/*
	 * name 公告内容详情显示
	 * @param  int id
	 *
	 */
	public function detail($id='0'){
		$Art = M('article');
		$this->artInfo = $Art->where('id='.$id)->find();

		$this->display();
	}

}

?>