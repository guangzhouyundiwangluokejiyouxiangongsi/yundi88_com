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
 * 评论咨询投诉管理
 * @author soubao 当燃
 * @Date: 2016-06-20
 */

namespace Seller\Controller;
use Think\AjaxPage;
use Think\Page;

class CommentController extends BaseController {
    public function index(){  
        checkIsBack();
        $this->display();
    }

    public function detail()
    {
        $id = I('get.id');
        $res = M('comment')->where(array('comment_id' => $id, 'store_id' => STORE_ID))->find();
        if (!$res) {
            exit($this->error('不存在该评论'));
        }
        if (IS_POST) {
            $add['parent_id'] = $id;
            $add['content'] = I('post.content');
            $add['goods_id'] = $res['goods_id'];
            $add['add_time'] = time();
            $add['username'] = '卖家';
            $add['is_show'] = 1;
            $add['store_id'] = STORE_ID;
            $row = M('comment')->add($add);
            if ($row) {
                $this->success('添加成功');
            } else {
                $this->error('添加失败');
            }
        }
        $reply = M('comment')->where(array('parent_id' => $id))->select(); // 评论回复列表

        $this->assign('comment', $res);
        $this->assign('reply', $reply);
        $this->display();
    }

    public function del()
    {
        $id = I('get.id');
        $row = M('comment')->where(array('comment_id' => $id, 'store_id' => STORE_ID))->delete();
        if ($row) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    public function op(){
        $type = I('post.type');
        $selected_id = I('post.selected');
        if(!in_array($type,array('del','show','hide')) || !$selected_id)
            $this->error('非法操作');
        $where = "comment_id IN ({$selected_id})";
        if($type == 'del'){
            //删除回复
            $where .= " OR parent_id IN ({$selected_id})";
            $row = M('comment')->where($where)->delete();
        }
        if($type == 'show'){
            $row = M('comment')->where($where)->save(array('is_show'=>1));
        }
        if($type == 'hide'){
            $row = M('comment')->where($where)->save(array('is_show'=>0));
        }
        if(!$row)
            $this->error('操作失败');
        $this->success('操作成功');

    }

    public function ajaxindex(){
        $model = M('');
        $username = I('nickname','','trim');
        $content = I('content','','trim');
        $where['c.parent_id'] = 0;
        $where['c.store_id'] = STORE_ID;
        if($username){
            $where['u.nickname'] = $username;
        }
        if($content){
            $where['c.content'] = array('like','%'.$content.'%');
        }
        $count = $model->table(C('DB_PREFIX').'comment c')->join('LEFT JOIN __USERS__ u ON u.user_id = c.user_id')->where($where)->count();
        $Page  = new AjaxPage($count,16);
        
        
        //是否从缓存中读取Page
        if(session('is_back')==1){
            $Page = getPageFromCache();
            delIsBack();
        }
                
        $comment_list = $model->field('c.*,u.nickname as nickname')->table(C('DB_PREFIX').'comment c')->join('LEFT JOIN __USERS__ u ON u.user_id = c.user_id')->where($where)->order('add_time DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
        if(!empty($comment_list))
        {
            $goods_id_arr = get_arr_column($comment_list, 'goods_id');
            $goods_list = M('Goods')->where("goods_id in (".  implode(',', $goods_id_arr).")")->getField("goods_id,goods_name");
        }
        
        cachePage($Page);
        $show = $Page->show();
        
        $this->assign('goods_list',$goods_list);
        $this->assign('comment_list',$comment_list);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();
    }
    
    public function ask_list(){
        checkIsBack();
    	$this->display();
    }
    
    public function ajax_ask_list(){
    	$model = M('goods_consult');
    	$username = I('nickname','','trim');
    	$content = I('content','','trim');
    	$where=' parent_id = 0 and store_id = '.STORE_ID;
    	if($username){
    		$where .= " AND username='$username'";
    	}
    	if($content){
    		$where .= " AND content like '%{$content}%'";
    	}
        $count = $model->where($where)->count();        
        $Page  = new AjaxPage($count,16);
        //是否从缓存中读取Page
        if(session('is_back')==1){
            $Page = getPageFromCache();
            //重置获取条件
            delIsBack();
        }        	
    	
        $comment_list = $model->where($where)->order('add_time DESC')->limit($Page->firstRow.','.$Page->listRows)->select(); 
    	if(!empty($comment_list))
    	{
    		$goods_id_arr = get_arr_column($comment_list, 'goods_id');
    		$goods_list = M('Goods')->where("goods_id in (".  implode(',', $goods_id_arr).")")->getField("goods_id,goods_name");
    	}
    	$consult_type = array(0=>'默认咨询',1=>'商品咨询',2=>'支付咨询',3=>'配送',4=>'售后');
    	
    	
    	cachePage($Page);
    	$show = $Page->show();
    	
    	$this->assign('consult_type',$consult_type);
    	$this->assign('goods_list',$goods_list);
    	$this->assign('comment_list',$comment_list);
    	$this->assign('page',$show);// 赋值分页输出
    	$this->display();
    }
    
    public function consult_info(){
    	$id = I('get.id');
    	$res = M('goods_consult')->where(array('id'=>$id))->find();
    	if(!$res){
    		exit($this->error('不存在该咨询'));
    	}
    	if(IS_POST){
    		$add['parent_id'] = $id;
    		$add['content'] = I('post.content');
    		$add['goods_id'] = $res['goods_id'];
                $add['consult_type'] = $res['consult_type'];
    		$add['add_time'] = time();
    		$add['store_id'] = STORE_ID;   	
    		$add['is_show'] = 1;   	
    		$row =  M('goods_consult')->add($add);
    		if($row){
    			$this->success('添加成功');
    		}else{
    			$this->error('添加失败');
    		}
    		exit;   	
    	}
    	$reply = M('goods_consult')->where(array('parent_id'=>$id))->select(); // 咨询回复列表   	 
    	$this->assign('comment',$res);
    	$this->assign('reply',$reply);
    	$this->display();
    }
    
    public function ask_handle(){
    	$type = I('post.type');
    	$selected_id = I('post.selected');        
    	if(!in_array($type,array('del','show','hide')) || !$selected_id)
    		$this->error('操作完成');
    
        $selected_id = implode(',',$selected_id);
    	if($type == 'del'){
    		//删除咨询
    		$where .= "( id IN ({$selected_id}) OR parent_id IN ({$selected_id})) and store_id = ".STORE_ID;
    		$row = M('goods_consult')->where($where)->delete();
    	}
    	if($type == 'show'){
    		$row = M('goods_consult')->where("id IN ({$selected_id}) and store_id = ".STORE_ID)->save(array('is_show'=>1));
    	}
    	if($type == 'hide'){
    		$row = M('goods_consult')->where("id IN ({$selected_id}) and store_id = ".STORE_ID)->save(array('is_show'=>0));
    	}    		
    	$this->success('操作完成');
    }
    
    public function complain_list(){
    	$timegap = I('timegap');
    	$nickname = I('accuser_name');
    	$map = array();
    	$map['accused_id'] = STORE_ID;
    	$map['complain_active'] = 2;
    	if($timegap){
    		$gap = explode(' - ', $timegap);
    		$begin = $gap[0];
    		$end = $gap[1];
    		$map['appeal_datetime'] = array('between',array(strtotime($begin),strtotime($end)));
    	}
    	if($nickname){
    		$map['accuser_name'] = $nickname;
    	}
    	$count = M('complain')->where($map)->count();
    	$page = new Page($count);
    	$lists  = M('complain')->where($map)->order('appeal_datetime desc')->limit($page->firstRow.','.$page->listRows)->select();
    	$this->assign('page',$page->show());
    	$this->assign('lists',$lists);
    	$this->display();
    }
}