<?php
/**
*	@author 金龙
*	2016/11/30
*	文章管理
*/
namespace Seller\Controller;
class NewsController extends BaseController {

	public function newslist()
	{	
		$id = (!empty($_GET['type']))?$_GET['type']:0;

		$_GET['p'] = (empty($_GET['p']))?0:$_GET['p'];

		$where = 'sn_id = '.$id.' and store = '.STORE_ID;

		if($id==0)$where = 'store = '.STORE_ID;


		$list= M('store_art')->where($where)->page($_GET['p'].',10')->order('timer desc')->select();

		$nav = M('store_navigation')->where(" sn_is_list = 1 and sn_store_id=".STORE_ID)->getfield('sn_id,sn_title');

		foreach ($list as $key => $val) {
			$list[$key]['is_show'] = $val['is_show']>0 ? '显示' : '隐藏';
			// $list[$key]['sn_id'] = (empty($nav[$val['sn_id']]))?'所有':$nav[$val['sn_id']];
			$list[$key]['sn_ids'] = (empty($nav[$val['sn_id']]))?'所有':$nav[$val['sn_id']];

		}

		$count = M('store_art')->where($where)->count();

		$Page = new \Think\Page($count,10);
		$show = $Page->show();
		$this->assign('page',$show);


		$this->assign('nav',$nav);
		$this->assign('list',$list);
		$this->display();
	}

	public function addNews()
	{
		$nav = M('store_navigation')->where("sn_is_list = 1 and sn_store_id=".STORE_ID)->select();
		$this->initEditor();//2016/12/01
		$this->assign('nav',$nav);
		$this->assign('all',0);
		$this->display();
	}
	public function uddNews()
	{
		$nav = M('store_navigation')->where("sn_is_list = 1 and sn_store_id=".STORE_ID)->select();

		$info = M('store_art')->where('id = '.$_GET['id'].' and store = '.STORE_ID)->select();

		$this->initEditor();//2016/12/01
		$this->assign('info',$info[0]);
		$this->assign('nav',$nav);
		$this->display();
	}
	public function newsHandle(){
		$data = I('post.');
		$data['timer'] = time();
		if($data['act'] == 'del'){
			$r = M('store_art')->where('id='.$data['id'])->delete();
			if($r) exit(json_encode(1));
		}
		if(empty($data['id'])){
			$data['store'] = STORE_ID;
			$r = M('store_art')->add($data);
		}else{
			$r = M('store_art')->where('id='.$data['id'])->save($data);
		}
		if($r){
			$this->success("操作成功",U('News/newslist'));
		}else{
			$this->error("操作失败",U('News/newslist'));
		}
	}
	/**
	*	2016/12/1
	*	富文本框参数
	*/
	private function initEditor()
	{
		$this->assign("URL_upload", U('Admin/Ueditor/imageUp',array('savepath'=>'decoration')));
		$this->assign("URL_fileUp", U('Admin/Ueditor/fileUp',array('savepath'=>'decoration')));
		$this->assign("URL_scrawlUp", U('Admin/Ueditor/scrawlUp',array('savepath'=>'decoration')));
		$this->assign("URL_getRemoteImage", U('Admin/Ueditor/getRemoteImage',array('savepath'=>'decoration')));
		$this->assign("URL_imageManager", U('Admin/Ueditor/imageManager',array('savepath'=>'decoration')));
		$this->assign("URL_imageUp", U('Admin/Ueditor/imageUp',array('savepath'=>'decoration')));
		$this->assign("URL_getMovie", U('Admin/Ueditor/getMovie',array('savepath'=>'decoration')));
		$this->assign("URL_Home", "");
	}

	public function ajax_re_news()
	{
		$news_id = I('news_id');
		if (!$news_id){
			$this->ajaxReturn();
		}

		$time = time()-86400;

		$where['time'] = array('gt',$time);
		$where['news_id'] = $news_id;

		$data['time'] = time();
		$data['news_id'] = $news_id;
		$data3['timer'] = time();
		$res2 = M('rest_news')->where($where)->find();
		if (!$res2){
			$res = M('rest_news')->add($data);
			M('store_art')->where(array('id'=>$news_id))->save($data3);
			$this->ajaxReturn(true);
		}
		if ($res2['num'] == 5){
			$this->ajaxReturn();
		} 

		$data2['num'] = $res2['num']+1;
		$res = M('rest_news')->where(array('id'=>$res2['id']))->save($data2);
		if ($res){
			M('store_art')->where(array('id'=>$news_id))->save($data3);
			$this->ajaxReturn(true);
		}
		$this->ajaxReturn();
	}
}