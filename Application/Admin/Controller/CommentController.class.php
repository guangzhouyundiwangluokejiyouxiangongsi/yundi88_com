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

namespace Admin\Controller;
use Think\AjaxPage;
use Think\Page;

class CommentController extends BaseController {
    public function index(){      
        $this->display();
    }

    public function detail(){
        $id = I('get.id');
        $res = M('comment')->where(array('comment_id'=>$id))->find();
        if(!$res){
            exit($this->error('不存在该评论'));
        }
        if(IS_POST){
            $add['parent_id'] = $id;
            $add['content'] = I('post.content');
            $add['goods_id'] = $res['goods_id'];
            $add['add_time'] = time();
            $add['username'] = '平台';
            $add['is_show'] = 1;
            //$add['seller_id'] = session('seller_id');
            $row =  M('comment')->add($add);
            if($row){
                $this->success('添加成功');exit;
            }else{
                $this->error('添加失败');
            }
        }
        $reply = M('comment')->where(array('parent_id'=>$id))->select(); // 评论回复列表         
        $this->assign('comment',$res);
        $this->assign('reply',$reply);
        $this->display();
    }


    public function del(){
        $id = I('get.id');
        $row = M('comment')->where(array('comment_id'=>$id))->delete();
        if($row){
            $this->success('删除成功');exit;
        }else{
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
            $where .= " OR parent_id IN ({$selected_id})";
            $row = M('comment')->where($where)->delete(); //删除回复
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
        $username = I('nickname');
        $content = I('content');
        $where['c.parent_id'] = 0;
        if($username){
			$where['u.nickname'] = $username;
        }
        if($content){
            $where['c.content'] = array('like','%'.$content.'%');
        }        
        $count = $model->table(C('DB_PREFIX').'comment c')->join('LEFT JOIN __USERS__ u ON u.user_id = c.user_id')->where($where)->count();
        $Page  = new AjaxPage($count,16);
        $show = $Page->show();
                
        $comment_list = $model->field('c.*,u.nickname as nickname')->table(C('DB_PREFIX').'comment c')->join('LEFT JOIN __USERS__ u ON u.user_id = c.user_id')->where($where)->order('add_time DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		if(!empty($comment_list))
        {
            $goods_id_arr = get_arr_column($comment_list, 'goods_id');
            $goods_list = M('Goods')->where("goods_id in (".  implode(',', $goods_id_arr).")")->getField("goods_id,goods_name");
        }
        $this->assign('goods_list',$goods_list);
        $this->assign('comment_list',$comment_list);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();
    }
    
    public function ask_list(){
    	$this->display();//咨询列表
    }
    
    public function ajax_ask_list(){
    	$model = M('goods_consult');
    	$username = I('nickname');
    	$content = I('content');
    	$where = '';
    	if($username){
    		$where = " AND username='$username'";
    	}
    	if($content){
    		$where = " AND content like '%{$content}%'";
    	}
    	$sql = "SELECT COUNT(1) as total_count FROM __PREFIX__goods_consult WHERE parent_id=0".$where;
    	$count = M()->query($sql);
    	$Page  = new AjaxPage($count[0]['total_count'],15);
    	$show = $Page->show();
    	
    	$sql = "SELECT * FROM __PREFIX__goods_consult WHERE parent_id=0".$where.' ORDER BY add_time DESC LIMIT '.$Page->firstRow.','.$Page->listRows;
    	$comment_list =  M()->query($sql);     
    	if(!empty($comment_list))
    	{
    		$goods_id_arr = get_arr_column($comment_list, 'goods_id');
    		$goods_list = M('Goods')->where("goods_id in (".  implode(',', $goods_id_arr).")")->getField("goods_id,goods_name");
    	}
    	$consult_type = array(0=>'默认咨询',1=>'商品咨询',2=>'支付咨询',3=>'配送',4=>'售后');
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
    		exit($this->error('不存在该评论'));
    	}
    	if(IS_POST){
    		$add['parent_id'] = $id;
    		$add['content'] = I('post.content');
    		$add['goods_id'] = $res['goods_id'];
    		$add['add_time'] = time();
    		$add['username'] = 'admin';   	
    		$add['is_show'] = 1;   	
    		$row =  M('comment')->add($add);
    		if($row){
    			$this->success('添加成功');
    			exit;
    		}else{
    			$this->error('添加失败');
    		}    	
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
    		$this->error('非法操作');
    	$where = "id IN ({$selected_id})";
    	if($type == 'del'){
    		//删除咨询
    		$where .= " OR parent_id IN ({$selected_id})";
    		$row = M('goods_consult')->where($where)->delete();
    	}
    	if($type == 'show'){
    		$row = M('goods_consult')->where($where)->save(array('is_show'=>1));
    	}
    	if($type == 'hide'){
    		$row = M('goods_consult')->where($where)->save(array('is_show'=>0));
    	}
    	if(!$row)
    		$this->error('操作失败');
    	$this->success('操作成功');
    }
    
    public function complain_list(){
    	$timegap = I('timegap');
    	$nickname = I('accuser_name');
    	$map = array();
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
    
    public function subject_list(){
    	$count = M('complain_subject')->where(array('subject_state'=>1))->count();
    	$page = new Page($count);
    	$lists  = M('complain_subject')->where(array('subject_state'=>1))->limit($page->firstRow.','.$page->listRows)->select();
    	$this->assign('page',$page->show());
    	$this->assign('lists',$lists);
    	$this->display();
    }
    
    public function subject_del(){
    	$subject_id = I('del_id');
    	if($subject_id>0){
    		if(M('complain_subject')->where(array('subject_id'=>$subject_id))->save(array('subject_state'=>2))){
    			respose(1);
    		}else{
    			respose('删除失败');
    		}
    	}
    }
    
    public function subject_info(){
    	if(IS_POST){
    		$data = I('post.');
    		$data['subject_state'] = 1;
    		if(M('complain_subject')->add($data)){
    			$this->success('添加成功',U('Comment/subject_list'));exit;
    		}else{
    			$this->error('添加失败,',U('Comment/subject_list'));
    		}
    	}
    	$this->display();
    }
    
}