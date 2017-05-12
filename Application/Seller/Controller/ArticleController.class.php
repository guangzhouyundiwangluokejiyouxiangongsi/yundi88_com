<?php
/**
 * tpshop
 * ============================================================================
 * 版权所有 2015-2027 深圳搜豹网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tp-shop.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * Author: 当燃      
 * Date: 2015-09-09
 */
namespace Seller\Controller;

class ArticleController extends BaseController {
    public function _initialize()
    {
        $this->store_id = session('store_id');
        if(!(session('seller_id') > 0 && session('user') && session('store_id') > 0 && session('seller'))){
            echo "<script>top.location.href='http://".$_SERVER['HTTP_HOST']."/User/login.html'</script>";
        }
    }

    public function store_link(){
    	$act = I('GET.act','add');
    	$this->assign('act',$act);
    	$id = I('GET.id');
    	$link_info = array();
    	if($id){
    		$link_info = M('store_friend_link')->where(array('id'=>$id))->find();
    		$this->assign('info',$link_info);
    	}
    	$this->display();
    }
    
    public function store_linkList(){
        $Ad = M('store_friend_link');
        $count      = $Ad->where(array('store_id'=>$this->store_id))->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出
    	$res = $Ad->where(array('store_id'=>$this->store_id))->order('orderby')->limit($Page->firstRow.','.$Page->listRows)->select();
    	if($res){
    		foreach ($res as $val){
                $val['target'] = $val['target']>0 ? '开启' : '关闭';
    			$val['is_show'] = $val['is_show']>0 ? '显示' : '隐藏';
    			$list[] = $val;
    		}
    	}
    	$this->assign('list',$list);// 赋值数据集
    	$this->assign('page',$show);// 赋值分页输出
    	$this->display();
    }
    
    public function store_linkHandle(){
        $data = I('post.');
        $data['store_id'] = $this->store_id;
    	if($data['act'] == 'add'){
            if (!$data['link_name']){
                $this->error('链接名称不能为空');
            }
            if (!$data['link_url']){
                $this->error('链接地址不能为空');
            }
    		$r = M('store_friend_link')->add($data);
    	}
    	if($data['act'] == 'edit'){
    		$r = M('store_friend_link')->where(array('id'=>$data['id']))->save($data);
    	}
    	if($data['act'] == 'del'){
    		$r = M('store_friend_link')->where(array('id'=>$data['id']))->delete();
    		if($r) exit(json_encode(1));
    	}
    	if($r){
    		$this->success("操作成功",U('Seller/Article/store_linkList'));
    	}else{
    		$this->error("操作失败",U('Seller/Article/store_linkList'));
    	}
    }
}