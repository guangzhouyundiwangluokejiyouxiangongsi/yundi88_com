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

namespace Admin\Controller;

use Admin\Logic\UsersLogic;
use Think\AjaxPage;
use Think\Page;

class UserController extends BaseController {

    public function index(){
        $this->display();
    }

    /**
     * 会员列表
     */
    public function ajaxindex(){
        // 搜索条件
        $condition = array();
        I('mobile') ? $condition['mobile'] = I('mobile') : false;
        I('email') ? $condition['email'] = I('email') : false;
        $sort_order = I('order_by').' '.I('sort');
               
        $model = M('users');
        $count = $model->where($condition)->count();
        $Page  = new AjaxPage($count,10);
        //  搜索条件下 分页赋值
        foreach($condition as $key=>$val) {
            $Page->parameter[$key]   =   urlencode($val);
        }
        
        $userList = $model->where($condition)->order($sort_order)->limit($Page->firstRow.','.$Page->listRows)->select();
                
        $user_id_arr = get_arr_column($userList, 'user_id');
        if(!empty($user_id_arr))
        {
            $first_leader = M('users')->query("select first_leader,count(1) as count  from __PREFIX__users where first_leader in(".  implode(',', $user_id_arr).")  group by first_leader");
            $first_leader = convert_arr_key($first_leader,'first_leader');
            
            $second_leader = M('users')->query("select second_leader,count(1) as count  from __PREFIX__users where second_leader in(".  implode(',', $user_id_arr).")  group by second_leader");
            $second_leader = convert_arr_key($second_leader,'second_leader');            
            
            $third_leader = M('users')->query("select third_leader,count(1) as count  from __PREFIX__users where third_leader in(".  implode(',', $user_id_arr).")  group by third_leader");
            $third_leader = convert_arr_key($third_leader,'third_leader');            
        }
        $this->assign('first_leader',$first_leader);
        $this->assign('second_leader',$second_leader);
        $this->assign('third_leader',$third_leader);
                                
        $show = $Page->show();
        $this->assign('userList',$userList);
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('level',M('user_level')->getField('level_id,level_name'));
        $this->display();
    }

    /**
     * 会员详细信息查看
     */
    public function detail(){
        $uid = I('get.id');
        $user = D('users')->where(array('user_id'=>$uid))->find();
        if(!$user)
            exit($this->error('会员不存在'));
        if(IS_POST){
            //  会员信息编辑
            $password = I('post.password');
            $password2 = I('post.password2');
            if($password != '' && $password != $password2){
                exit($this->error('两次输入密码不同'));
            }
            if($password == '' && $password2 == ''){
                unset($_POST['password']);
            }else{
                $_POST['password'] = encrypt($_POST['password']);
            }

            $row = M('users')->where(array('user_id'=>$uid))->save($_POST);
            if($row)
                exit($this->success('修改成功'));
            exit($this->error('未作内容修改或修改失败'));
        }
        
        $user['first_lower'] = M('users')->where("first_leader = {$user['user_id']}")->count();
        $user['second_lower'] = M('users')->where("second_leader = {$user['user_id']}")->count();
        $user['third_lower'] = M('users')->where("third_leader = {$user['user_id']}")->count();
 
        $this->assign('user',$user);
        $this->display();
    }
    
    
    public function add_user(){
    	if(IS_POST){
    		$data = I('post.');
    		$user_obj = new UsersLogic();
    		$res = $user_obj->addUser($data);
    		if($res['status'] == 1){
    			$this->success('添加成功',U('User/index'));exit;
    		}else{
    			$this->error('添加失败,'.$res['msg'],U('User/index'));
    		}
    	}
    	$this->display();
    }

    /**
     * 用户收货地址查看
     */
    public function address(){
        $uid = I('get.id');
        $lists = D('user_address')->where(array('user_id'=>$uid))->select();
        $regionList = M('Region')->getField('id,name');
        $this->assign('regionList',$regionList);
        $this->assign('lists',$lists);
        $this->display();
    }

    /**
     * 删除会员
     */
    public function delete(){
        $uid = I('get.id');
        $row = M('users')->where(array('user_id'=>$uid))->delete();
        if($row){
            $this->success('成功删除会员');
        }else{
            $this->error('操作失败');
        }
    }

    /**
     * 账户资金记录
     */
    public function account_log(){
        $user_id = I('get.id');
        //获取类型
        $type = I('get.type');
        //获取记录总数
        $count = M('account_log')->where(array('user_id'=>$user_id))->count();
        $page = new Page($count);
        $lists  = M('account_log')->where(array('user_id'=>$user_id))->order('change_time desc')->limit($page->firstRow.','.$page->listRows)->select();

        $this->assign('user_id',$user_id);
        $this->assign('page',$page->show());
        $this->assign('lists',$lists);
        $this->display();
    }

    /**
     * 账户资金调节
     */
    public function account_edit(){
        $user_id = I('get.id');
        if(!$user_id > 0)
            $this->error("参数有误");
        if(IS_POST){
            //获取操作类型
            $m_op_type = I('post.money_act_type');
            $user_money = I('post.user_money');
            $user_money =  $m_op_type ? $user_money : 0-$user_money;

            $p_op_type = I('post.point_act_type');
            $pay_points = I('post.pay_points');
            $pay_points =  $p_op_type ? $pay_points : 0-$pay_points;

            $f_op_type = I('post.frozen_act_type');
            $frozen_money = I('post.frozen_money');
            $frozen_money =  $f_op_type ? $frozen_money : 0-$frozen_money;

            $desc = I('post.desc');
            if(!$desc)
                $this->error("请填写操作说明");
            if(accountLog($user_id,$user_money,$pay_points,$desc)){
                $this->success("操作成功",U("Admin/User/account_log",array('id'=>$user_id)));
            }else{
                $this->error("操作失败");
            }
            exit;
        }
        $this->assign('user_id',$user_id);
        $this->display();
    }
    
    public function recharge(){
        $timegap = I('timegap');
    	$nickname = I('nickname');
    	$map = array();
    	if($timegap){
    		$gap = explode(' - ', $timegap);
    		$begin = $gap[0];
    		$end = $gap[1];
    		$map['ctime'] = array('between',array(strtotime($begin),strtotime($end)));
    	}
    	if($nickname){
    		$map['nickname'] = array('like',"%$nickname%");
    	}  
    	$count = M('recharge')->where()->count();
    	$page = new Page($count);
    	$lists  = M('recharge')->where()->order('ctime desc')->limit($page->firstRow.','.$page->listRows)->select();
    	$this->assign('page',$page->show());
    	$this->assign('lists',$lists);
    	$this->display();
    }
    
    public function level(){
    	$act = I('GET.act','add');
    	$this->assign('act',$act);
    	$level_id = I('GET.level_id');
    	$level_info = array();
    	if($level_id){
    		$level_info = D('user_level')->where('level_id='.$level_id)->find();
    		$this->assign('info',$level_info);
    	}
    	$this->display();
    }
    
    public function levelList(){
    	$Ad =  M('user_level');
    	$res = $Ad->where('1=1')->order('level_id')->page($_GET['p'].',10')->select();
    	if($res){
    		foreach ($res as $val){
    			$list[] = $val;
    		}
    	}
    	$this->assign('list',$list);
    	$count = $Ad->where('1=1')->count();
    	$Page = new \Think\Page($count,10);
    	$show = $Page->show();
    	$this->assign('page',$show);
    	$this->display();
    }
    
    public function levelHandle(){
    	$data = I('post.');
    	if($data['act'] == 'add'){
    		$r = D('user_level')->add($data);
    	}
    	if($data['act'] == 'edit'){
    		$r = D('user_level')->where('level_id='.$data['level_id'])->save($data);
    	}
    	 
    	if($data['act'] == 'del'){
    		$r = D('user_level')->where('level_id='.$data['level_id'])->delete();
    		if($r) exit(json_encode(1));
    	}
    	 
    	if($r){
    		$this->success("操作成功",U('Admin/User/levelList'));
    	}else{
    		$this->error("操作失败",U('Admin/User/levelList'));
    	}
    }

    /**
     * 搜索用户名
     */
    public function search_user()
    {
        $search_key = trim(I('search_key'));        
        if(strstr($search_key,'@'))    
        {
            $list = M('users')->where(" email like '%$search_key%' ")->select();        
            foreach($list as $key => $val)
            {
                echo "<option value='{$val['user_id']}'>{$val['email']}</option>";
            }                        
        }
        else
        {
            $list = M('users')->where(" mobile like '%$search_key%' ")->select();        
            foreach($list as $key => $val)
            {
                echo "<option value='{$val['user_id']}'>{$val['mobile']}</option>";
            }            
        } 
        exit;
    }
    
    /**
     * 分销树状关系
     */
    public function ajax_distribut_tree()
    {
          $list = M('users')->where("first_leader = 1")->select();
          $this->display();
    }

    /**
     *
     * @time 2016/08/31
     * @author dyr
     * 发送站内信
     */
    public function sendMessage()
    {
        $user_id_array = I('get.user_id_array');
        $users = array();
        if (!empty($user_id_array)) {
            $users = M('users')->field('user_id,nickname')->where(array('user_id' => array('IN', $user_id_array)))->select();
        }
        $this->assign('users', $users);
        $this->display();
    }

    /**
     * 发送系统消息
     * @author dyr
     * @time  2016/09/01
     */
    public function doSendMessage()
    {
        $call_back = I('call_back');//回调方法
        $message = I('post.text');//内容
        $type = I('post.type', 0);//个体or全体
        $admin_id = session('admin_id');
        $users = I('post.user');//个体id
        $message = array(
            'admin_id' => $admin_id,
            'message' => $message,
            'category' => 0,
            'send_time' => time()
        );
        if ($type == 1) {
            //全体用户系统消息
            $message['type'] = 1;
            M('Message')->data($message)->add();
        } else {
            //个体消息
            $message['type'] = 0;
            if (!empty($users)) {
                $create_message_id = M('Message')->data($message)->add();
                foreach ($users as $key) {
                    M('user_message')->data(array('user_id' => $key, 'message_id' => $create_message_id, 'status' => 0, 'category' => 0))->add();
                }
            }
        }
        echo "<script>parent.{$call_back}(1);</script>";
        exit();
    }

    /**
     *
     * @time 2016/09/03
     * @author dyr
     * 发送邮件
     */
    public function sendMail()
    {
        $user_id_array = I('get.user_id_array');
        $users = array();
        if (!empty($user_id_array)) {
            $user_where = array(
                'user_id' => array('IN', $user_id_array),
                'email'=> array('neq','')
            );
            $users = M('users')->field('user_id,nickname,email')->where($user_where)->select();
        }
        $this->assign('smtp',tpCache('smtp'));
        $this->assign('users',$users);
        $this->display();
    }

    /**
     * 发送邮箱
     * @author dyr
     * @time  2016/09/03
     */
    public function doSendMail()
    {
        $call_back = I('call_back');//回调方法
        $message = I('post.text');//内容
        $title = I('post.title');//标题
        $users = I('post.user');
        if (!empty($users)) {
            $user_id_array = implode(',', $users);
            $users = M('users')->field('email')->where(array('user_id' => array('IN', $user_id_array)))->select();
            $to = array();
            foreach ($users as $user) {
                if (check_email($user['email'])) {
                    $to[] = $user['email'];
                }
            }
            $res = send_email($to, $title, $message);
            echo "<script>parent.{$call_back}({$res});</script>";
            exit();
        }
    }

    // 用户体验
    public function experience()
    {   
        $count = M('experience')->count();
        $page = new Page($count,10);
        $show = $page->show();
        $experience = M('experience')->order('addtime')->page($_GET['p'],10)->select();
        $beautiful = M('experience')->Avg('beautiful');
        $operation = M('experience')->Avg('operation');
        $trading  = M('experience')->Avg('trading');
        $this->assign('beautiful',$beautiful);
        $this->assign('operation',$operation);
        $this->assign('trading',$trading);
        $this->assign('experience',$experience);
        $this->assign('show',$show);
        $this->display();
    }

    public function delete_all()
    {
        $arr = array();
        $arr = I('choose');
        foreach ($arr as $k => $v) {
            M('experience')->where(array('id'=>$v))->delete();
        }
        $this->ajaxReturn(true);
    }

    // 用户留言
    public function message()
    {   
        $count      = M('messageboard')->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出
        $message = M('messageboard')->order('addtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('page',$show);
        $this->assign('message',$message);
        $this->display();
    }

    // 删除用户体验
    public function delete_exp()
    {   
        $id = I('id');
        if (empty($id)) $this->ajaxReturn();
        $res = M('experience')->where(array('id'=>$id))->delete();
        if ($res){
            $this->ajaxReturn(true);
        } else {
            $this->ajaxReturn();
        }
    }



    // 删除留言
    public function delete_mes()
    {
        $id = I('id');
        $res = M('messageboard')->where(array('id'=>$id))->delete();
        $this->ajaxReturn($res);
    }

     // 删除留言
    public function delete_h5()
    {
        $id = I('id');
        $res = M('user_contact')->where(array('id'=>$id))->delete();
        $this->ajaxReturn($res);
    }

    public function delete_h5all()
    {   
        $arr = I('choose');
        foreach($arr as $vv){
            if ($vv){
                M('user_contact')->where(array('id'=>$vv))->delete();
            }
        }
        redirect('/Admin/User/h5message.html');
        // header('location:http://www.yundi88.com/Admin/User/h5message.html');
    }
    public function delete_all2()
    {   
        $arr = I('choose');
        foreach($arr as $vv){
            if ($vv){
                M('messageboard')->where(array('id'=>$vv))->delete();
            }
        }
        redirect('/Admin/User/message.html');
        // header('location:http://www.yundi88.com/Admin/User/message.html');
    }

    // 用户体验轮播图管理
    public function tiyan_lunbo()
    {   
        $tiyan_lunbo2 = M('lunbo')->Field('tiyan_lunbo,tiyan_jump')->find();
        $tiyan_lunbo = explode(',', $tiyan_lunbo2['tiyan_lunbo']);
        $tiyan_jump = explode(',', $tiyan_lunbo2['tiyan_jump']);
        if (IS_POST){
            $lunbo = I('tiyan_lunbo');
            $jump = I('tiyan_jump');
            $data['tiyan_lunbo'] = implode(',', $lunbo);
            $data['tiyan_jump'] = implode(',', $jump);
            $res = M('lunbo')->where(array('id'=>'1'))->save($data);
            if ($res){
                $this->success('操作成功');
                return;
            } else {
                $this->error('操作失败');
                return;
            }
        }
        $this->assign('jump',$tiyan_jump);
        $this->assign('lunbo',$tiyan_lunbo);
        $this->display();
    }

    // 认证页面轮播图管理
    public function renzheng_lunbo()
    {   
        $tiyan_lunbo2 = M('lunbo')->Field('renzheng_lunbo,renzheng_jump')->find();
        $tiyan_lunbo = explode(',', $tiyan_lunbo2['renzheng_lunbo']);
        $tiyan_jump = explode(',', $tiyan_lunbo2['renzheng_jump']);
        if (IS_POST){
            $lunbo = I('renzheng_lunbo');
            $jump = I('jump');
            $data['renzheng_lunbo'] = implode(',', $lunbo);
            $data['renzheng_jump'] = implode(',', $jump);
            $res = M('lunbo')->where(array('id'=>'1'))->save($data);
            if ($res){
                $this->success('操作成功');
                return;
            } else {
                $this->error('操作失败');
                return;
            }
        }
        $this->assign('lunbo',$tiyan_lunbo);
        $this->assign('jump',$tiyan_jump);
        $this->display();
    }

    // 用户体验轮播图管理
    public function store_lunbo()
    {   
        $tiyan_lunbo = M('lunbo')->getField('store_lunbo');
        $tiyan_lunbo = explode(',', $tiyan_lunbo);
        if (IS_POST){
            $lunbo = I('store_lunbo');
            $data['store_lunbo'] = implode(',', $lunbo);
            $res = M('lunbo')->where(array('id'=>'1'))->save($data);
            if ($res){
                $this->success('操作成功');
                return;
            } else {
                $this->error('操作失败');
                return;
            }
        }
        $this->assign('lunbo',$tiyan_lunbo);
        $this->display();
    }

    // 用户留言
    public function h5message()
    {   
        $count      = M('user_contact')->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(15)
        $show       = $Page->show();// 分页显示输出
        $message = M('user_contact')->order('addtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('page',$show);
        $this->assign('message',$message);
        $this->display();
    }


}