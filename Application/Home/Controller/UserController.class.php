<?php
/**
 * tpshop
 * ============================================================================
 * * 版权所有 2015-2027 深圳搜豹网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tp-shop.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * 2015-11-21
 */
namespace Home\Controller; 
use Home\Logic\OrderGoodsLogic;
use Home\Logic\StoreLogic;
use Home\Logic\UsersLogic;
use Think\Page; 
use Think\Verify;

class UserController extends BaseController {

	public $user_id = 0;
	public $user = array();
	
    public function _initialize() {      
        parent::_initialize();
        if(session('?user'))
        {
        	$user = session('user');
            $user = M('users')->where("user_id = {$user['user_id']}")->find();
            session('user',$user);  //覆盖session 中的 user               
        	$this->user = $user;
        	$this->user_id = $user['user_id'];
        	$this->assign('user',$user); //存储用户信息
        	$this->assign('user_id',$this->user_id);
        }else{
        	$nologin = array(
        			'login','pop_login','do_login','logout','verify','set_pwd','finished',
        			'verifyHandle','reg','send_sms_reg_code','identity','check_validate_code',
        			'forget_pwd','check_captcha','check_username','send_validate_code','regstore',
                    'ajax_seller','ajax_mobile','ajax_store','regstore2','login3','registered','ajax_jump','putcode','is_beautiful_username','test'
        	);
        	if(!in_array(ACTION_NAME,$nologin)){
        		header("location:".U('/User/login'));
        		exit;
        	}
        }

        
        //用户中心面包屑导航
        $navigate_user = navigate_user();
        $this->assign('navigate_user',$navigate_user);        
    }

    public function regstore2(){
        $this->display();
    }
    /*
     * 用户中心首页
     */
    public function index(){
        $logic = new UsersLogic();
        $level = M('user_level')->select();
        $level = convert_arr_key($level,'level_id');
        $this->assign('level',$level);
        $this->display();
    }


    public function ajax_head_pic()
    {   
        $data['head_pic'] = I('head_pic');
        $res = M('users')->where(array('user_id'=>$this->user_id))->save($data);
        if ($res){
            $this->success('修改成功');
        } else {
            $this->error('修改失败');
        }
    }
    
    public function logout(){
    	session(null);
        cookie('uname',null);
        cookie('cn',null);
        cookie('user_id',null);
        cookie('referurl',null);
        redirect(U('User/login'));
    }

    /*
     * 账户资金
     */
    public function account(){
        $user = session('user');
        //获取账户资金记录
        $logic = new UsersLogic();
        $data = $logic->get_account_log($this->user_id,I('get.type'));
        $account_log = $data['result'];
        $this->assign('user',$user);
        $this->assign('account_log',$account_log);
        $this->assign('page',$data['show']);
        $this->assign('active','account');
        $this->display();
    }
    /*
     * 优惠券列表
     */
    public function coupon(){
        $logic = new UsersLogic();
        $data = $logic->get_coupon($this->user_id,$_REQUEST['type']);
        $coupon_list = $data['result'];
        $this->assign('coupon_list',$coupon_list);
        $this->assign('page',$data['show']);
        $this->assign('active','coupon');
        $this->display();
    }
    /**
     *  登录
     */
    public function login(){
        if($this->user_id > 0 && session('store_id') > 0 && session('seller') != ''){
        	header("Location: ".U('seller/Index/index'));
        }           
        // dump($_COOKIE['referurl']);
        if ($_COOKIE['referurl']){
            $login = M('automatic_login')->where(array('rand_id'=>$_COOKIE['referurl']))->find();
            if ($login['addtime'] - time() > 0){
                $user_id = $login['user_id'];
                $user = M('users')->where(array('user_id'=>$user_id))->find();
                $seller = M('seller')->where(array('user_id'=>$user_id))->find();
                if (!$user['nickname']) {
                $user['nickname'] = $seller['seller_name'];
                }
                session('seller',$seller);
                session('seller_id',$seller['seller_id']);
                session('store_id',$seller['store_id']);
                session('user',$user);
                setcookie('user_id',$user['user_id'],null,'/');
                setcookie('is_distribut',$user['is_distribut'],null,'/');
                setcookie('uname',urlencode($user['nickname']),null,'/');
                setcookie('cn',0,time()-3600,'/');
                header("Location: ".U('seller/Index/index'));
                exit;
            }
        }
        $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U("/User/index");
        $copyright = M('config')->where(array('id'=>80))->getField('value');
        $record_no = M('config')->where(array('id'=>2))->getField('value');
        $phone = M('config')->where(array('id'=>9))->getField('value');
        $this->assign('phone',$phone);
        $this->assign('copyright',$copyright);
        $this->assign('record_no',$record_no);
        $this->assign('referurl',$referurl);
        $this->display();
    }

    public function login3(){
        $this->display();
    }

    public function pop_login(){
    	if($this->user_id > 0){
    		header("Location: ".U('seller/Index/index'));
    	}
        $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U("seller/Index/index");
        $this->assign('referurl',$referurl);
    	$this->display();
    }
    
    public function do_login(){
    	$username = I('post.username','');
    	$password = I('post.password','');
        $username = trim($username);
        $password = trim($password); 
        $referurl = I('post.referurl','');       
    	$verify_code = I('post.verify_code');
     
        $verify = new Verify();
        if (!$verify->check($verify_code,'user_login'))
        {
             $res = array('status'=>0,'msg'=>'验证码错误');
             exit(json_encode($res));
        }
    	         
    	$logic = new UsersLogic();
    	$res = $logic->login($username,$password);         
        
    	if($res['status'] == 1){
    		$res['result']['nickname'] = empty($res['result']['nickname']) ? $username : $res['result']['nickname'];
            $seller = M('seller')->where(array('user_id'=>$res['result']['user_id']))->find();
            if ($referurl){
                $rand_id = date('YmdHis',time()).rand(111111,999999).$res['result']['user_id'];
                setcookie('referurl',$rand_id,time()+3600*24*14,'/');
                $data['user_id'] = $res['result']['user_id'];
                $data['rand_id'] = $rand_id;
                $data['addtime'] = time()+3600*24*14;
                M('automatic_login')->add($data);
            }
            if($seller['group_id'] > 0){
                 $group = M('seller_group')->where(array('group_id'=>$seller['group_id']))->find();
                 $seller['act_limits'] = $group['act_limits'];
                 $seller['smt_limits'] = $group['smt_limits'];
            }
            session('seller',$seller);
            session('seller_id',$seller['seller_id']);
            session('store_id',$seller['store_id']);
            session('user',$res['result']);
    		setcookie('user_id',$res['result']['user_id'],null,'/');
    		setcookie('is_distribut',$res['result']['is_distribut'],null,'/');
            setcookie('uname',urlencode($res['result']['nickname']),null,'/');
            setcookie('cn',0,time()-3600,'/');
    		$cartLogic = new \Home\Logic\CartLogic();
    		$cartLogic->login_cart_handle($this->session_id,$res['result']['user_id']);  //用户登录后 需要对购物车 一些操作
            M('seller')->where(array('seller_id'=>$seller['seller_id']))->save(array('last_login_time'=>time()));
            // sellerLog('商家管理中心登录',__ACTION__);
            // $url = session('from_url') ? session('from_url') : U('Index/index');
    	}
    	exit(json_encode($res));
    }

    /**
     *  个人注册
     */
    public function reg(){
    	if($this->user_id > 0) header("Location: ".U('/seller/Index/index'));
        $copyright = M('config')->where(array('id'=>80))->getField('value');
        $record_no = M('config')->where(array('id'=>2))->getField('value');
        $phone = M('config')->where(array('id'=>9))->getField('value');
        $this->assign('phone',$phone);
        $this->assign('copyright',$copyright);
        $this->assign('record_no',$record_no);
        $this->assign('regis_sms_enable',tpCache('sms.regis_sms_enable')); // 注册启用短信：
        $this->assign('regis_smtp_enable',tpCache('smtp.regis_smtp_enable')); // 注册启用邮箱：
        $sms_time_out = tpCache('sms.sms_time_out')>0 ? tpCache('sms.sms_time_out') : 60;
        $this->assign('sms_time_out', $sms_time_out); // 手机短信超时时间
        $this->display('regstore');
    }

    // 注册
    public function regstore(){
        // dump(I());exit;
        if($this->user_id > 0) header("Location: ".U('/seller/Index/index'));
        $copyright = M('config')->where(array('id'=>80))->getField('value');
        $record_no = M('config')->where(array('id'=>2))->getField('value');
        $phone = M('config')->where(array('id'=>9))->getField('value');
        $this->assign('phone',$phone);
        $this->assign('copyright',$copyright);
        $this->assign('record_no',$record_no);

        if (IS_POST){
            $logic = new UsersLogic(); 
            $user_name = I('username');      // 手机号
            $store_name = I('storename'); // 店铺名称
            $seller_name = I('sellername');    // 登陆账号
            $sex = I('sex',0);
            $nickname = I('nickname');
            $code = I('post.code','');
            if (!I('password')){
                $this->error('密码不能为空');
            }
            if (!$store_name) {
                $store_name = $user_name;
            }

            if(!$code){
                $this->error('请输入短信验证码');
            }

            $check_code = $logic->sms_code_verify($user_name,$code,$this->session_id);

            if($check_code['status'] != 1){
                $this->error($check_code['msg']);
            }

            if(M('users')->where(array('mobile'=>$user_name))->find())
                $this->error('手机号已被注册');
            if(M('store')->where("store_name='$store_name'")->count()>0){
                $this->error("店铺名称已存在");
            }
            if(!preg_match('/^[a-zA-Z][a-zA-Z0-9_]{5,16}/', $seller_name)){
                $this->error("账号不合法！");
            }
            if(M('seller')->where("seller_name='$seller_name'")->count()>0){
                $this->error("账号已被注册");
            }
            if (!$this->is_beautiful_username($seller_name)){
                $this->error("账号已被注册");
            }
            $user_id = M('users')->where("email='$user_name' or mobile='$user_name'")->getField('user_id');
            if($user_id){
                if(M('store')->where(array('user_id'=>$user_id))->count()>0){
                    $this->error("手机号已被使用");
                }
                if(M('seller')->where(array('user_id'=>$user_id))->count()>0){
                    $this->error("手机号已被使用");
                }
            }
            $store = array('store_name'=>$store_name,'user_name'=>$user_name,'store_state'=>1,
                    'seller_name'=>$seller_name,'password'=>I('password'),
                    'store_time'=>time(),'is_own_shop'=>0,'sex'=>$sex,'nickname'=>$nickname
            );
            $storeLogic = new StoreLogic();
            if($storeLogic->addStore($store)){
                $seller = M('seller')->where(array('seller_name'=>$seller_name))->find();

                // $user  = M('users')->where(array('mobile'=>$user_name ||'email'=>$user_name))->find();
                // dump();
                if($seller['group_id'] > 0){
                     $group = M('seller_group')->where(array('group_id'=>$seller['group_id']))->find();
                     $seller['act_limits'] = $group['act_limits'];
                     $seller['smt_limits'] = $group['smt_limits'];
                }
                session('seller',$seller);
                session('seller_id',$seller['seller_id']);
                session('store_id',$seller['store_id']);
                $this->success('注册成功',U('seller/index/index'));
            }else{
                $this->error("注册失败");
            }
        } else {
            $this->assign('regis_sms_enable',tpCache('sms.regis_sms_enable')); // 注册启用短信：
            $this->assign('regis_smtp_enable',tpCache('smtp.regis_smtp_enable')); // 注册启用邮箱：
            $sms_time_out = tpCache('sms.sms_time_out')>0 ? tpCache('sms.sms_time_out') : 60;
            $this->assign('sms_time_out', $sms_time_out); // 手机短信超时时间
            $this->display();
        }
    }


    /*
     * 订单列表
     */
    public function order_list(){
        header('location:/seller/index/index?nav=order');
        $where = ' user_id='.$this->user_id;
        //条件搜索
       if(I('get.type')){
           $where .= C(strtoupper(I('get.type')));
       }
       // 搜索订单 根据商品名称 或者 订单编号
       $search_key = trim(I('search_key'));       
       if($search_key)
       {
          $where .= " and (order_sn like '%$search_key%' or order_id in (select order_id from `".C('DB_PREFIX')."order_goods` where goods_name like '%$search_key%') ) ";
       }
       
        $count = M('order')->where($where)->count();
        $Page       = new Page($count,5);

        $show = $Page->show();
        $order_str = "order_id DESC";
        $order_list = M('order')->order($order_str)->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
        //获取订单商品
        $model = new UsersLogic();
        foreach($order_list as $k=>$v)
        {
            $order_list[$k] = set_btn_order_status($v);  // 添加属性  包括按钮显示属性 和 订单状态显示属性
            //$order_list[$k]['total_fee'] = $v['goods_amount'] + $v['shipping_fee'] - $v['integral_money'] -$v['bonus'] - $v['discount']; //订单总额
            $data = $model->get_order_goods($v['order_id']);
            $order_list[$k]['goods_list'] = $data['result'];            
        }
        $store_id_list = get_arr_column($order_list, 'store_id');
        if(!empty($store_id_list))
            $store_list = M('store')->where("store_id in (".  implode(',', $store_id_list).")")->getField('store_id,store_name,store_qq');
        
        $this->assign('store_list',$store_list);
        $this->assign('order_status',C('ORDER_STATUS'));
        $this->assign('shipping_status',C('SHIPPING_STATUS'));
        $this->assign('pay_status',C('PAY_STATUS'));
        $this->assign('page',$show);
        $this->assign('lists',$order_list);
        $this->assign('active','order_list');
        $this->assign('active_status',I('get.type'));
        $this->display();
    }

    /*
     * 订单详情
     */
    public function order_detail(){
        
        $id = I('get.id');
        $map['order_id'] = $id;
        $map['user_id'] = $this->user_id;
        $order_info = M('order')->where($map)->find();
        $order_info = set_btn_order_status($order_info);  // 添加属性  包括按钮显示属性 和 订单状态显示属性
        
        if(!$order_info){
            $this->error('没有获取到订单信息');
            exit;
        }
        //获取订单商品
        $model = new UsersLogic();
        $data = $model->get_order_goods($order_info['order_id']);
        $order_info['goods_list'] = $data['result'];
        //$order_info['total_fee'] = $order_info['goods_price'] + $order_info['shipping_price'] - $order_info['integral_money'] -$order_info['coupon_price'] - $order_info['discount'];
        //获取订单进度条
        $sql = "SELECT action_id,log_time,status_desc,order_status FROM ((SELECT * FROM __PREFIX__order_action WHERE order_id = $id AND status_desc <>'' ORDER BY action_id) AS a) GROUP BY status_desc ORDER BY action_id";
        $items = M()->query($sql);
        $items_count = count($items);
        // $region_list = get_region_list();
        
        $invoice_no = M('DeliveryDoc')->where("order_id = $id")->getField('invoice_no',true);
        $order_info[invoice_no] = implode(' , ', $invoice_no);                
        $store = M('store')->where("store_id = {$order_info['store_id']}")->find(); // 找出这个商家
        // 店铺地址id
        $ids[] = $store['province_id'];
        $ids[] = $store['city_id'];
        $ids[] = $store['district'];
        
        $ids[] = $order_info['province'];
        $ids[] = $order_info['city'];
        $ids[] = $order_info['district'];        
        if(!empty($ids))        
            $regionLits = M('region')->where("id in (".  implode(',', $ids).")")->getField("id,name");        
        
        //获取订单操作记录
        $order_action = M('order_action')->where(array('order_id'=>$id))->select();
        if($order_info['shipping_status'] == 1){
        	$express = queryExpress($order_info['shipping_code'],$order_info['invoice_no']);
        	$this->assign('express',$express);
        }
        $this->assign('store',$store);
        $this->assign('regionLits',$regionLits);        
        $this->assign('order_status',C('ORDER_STATUS'));
        $this->assign('shipping_status',C('SHIPPING_STATUS'));
        $this->assign('pay_status',C('PAY_STATUS'));
        // $this->assign('region_list',$region_list);
        $this->assign('regionLits',$regionLits);
        $this->assign('order_info',$order_info);
        $this->assign('order_action',$order_action);
        $this->assign('active','order_list');
        $this->display();
    }

    /*
     * 取消订单
     */
    public function cancel_order(){
        $id = I('get.id');
        //检查是否有积分，余额支付
        $logic = new UsersLogic();
        $data = $logic->cancel_order($this->user_id,$id);
        if($data['status'] < 0){
            $this->error($data['msg']);
        }
        $this->success($data['msg']);
    }

    /*
     * 用户地址列表
     */
    public function address_list(){
        $address_lists = get_user_address_list($this->user_id);
        $region_list = get_region_list();
        $this->assign('region_list',$region_list);
        $this->assign('lists',$address_lists);
        $this->assign('active','address_list');

        $this->display();
    }
    /*
     * 添加地址
     */
    public function add_address(){
        header("Content-type:text/html;charset=utf-8");
        if(IS_POST){
            $logic = new UsersLogic();
            $data = $logic->add_address($this->user_id,0,I('post.'));
            if($data['status'] != 1)
                exit('<script>alert("'.$data['msg'].'");history.go(-1);</script>');
            $call_back = $_REQUEST['call_back'];
            echo "<script>parent.{$call_back}('success');</script>";
            exit(); // 成功 回调closeWindow方法 并返回新增的id
        }
        $p = M('region')->where(array('parent_id'=>0,'level'=> 1))->select();
        $this->assign('province',$p);
        $this->display('edit_address');

    }

    /*
     * 地址编辑
     */
    public function edit_address(){
        header("Content-type:text/html;charset=utf-8");
        $id = I('get.id');
        $address = M('user_address')->where(array('address_id'=>$id,'user_id'=> $this->user_id))->find();
        if(IS_POST){
            $logic = new UsersLogic();
            $data = $logic->add_address($this->user_id,$id,I('post.'));
            if($data['status'] != 1)
                exit('<script>alert("'.$data['msg'].'");history.go(-1);</script>');

            $call_back = $_REQUEST['call_back'];
            echo "<script>parent.{$call_back}('success');</script>";
            exit(); // 成功 回调closeWindow方法 并返回新增的id
        }
        //获取省份
        $p = M('region')->where(array('parent_id'=>0,'level'=> 1))->select();
        $c = M('region')->where(array('parent_id'=>$address['province'],'level'=> 2))->select();
        $d = M('region')->where(array('parent_id'=>$address['city'],'level'=> 3))->select();
        if($address['twon']){
        	$e = M('region')->where(array('parent_id'=>$address['district'],'level'=>4))->select();
        	$this->assign('twon',$e);
        }

        $this->assign('province',$p);
        $this->assign('city',$c);
        $this->assign('district',$d);
        $this->assign('address',$address);
        $this->display();
    }

    /*
     * 设置默认收货地址
     */
    public function set_default(){
        $id = I('get.id');
        M('user_address')->where(array('user_id'=>$this->user_id))->save(array('is_default'=>0));
        $row = M('user_address')->where(array('user_id'=>$this->user_id,'address_id'=>$id))->save(array('is_default'=>1));
        if(!$row)
            $this->error('操作失败');
        $this->success("操作成功");
    }
    
    /*
     * 地址删除
     */
    public function del_address(){
        $id = I('get.id');
        
        $address = M('user_address')->where("address_id = $id")->find();
        $row = M('user_address')->where(array('user_id'=>$this->user_id,'address_id'=>$id))->delete();                
        // 如果删除的是默认收货地址 则要把第一个地址设置为默认收货地址
        if($address['is_default'] == 1)
        {
            $address2 = M('user_address')->where("user_id = {$this->user_id}")->find();            
            $address2 && M('user_address')->where("address_id = {$address2['address_id']}")->save(array('is_default'=>1));
        }        
        if(!$row)
            $this->error('操作失败',U('User/address_list'));
        else
            $this->success("操作成功",U('User/address_list'));
    } 
        
    /*
     * 评论晒单
     */
    public function comment(){
        $user_id = $this->user_id;
        $status = I('get.status',-1);
        $logic = new UsersLogic();
        $data = $logic->get_comment($user_id,$status); //获取评论列表
        $this->assign('page',$data['show']);// 赋值分页输出
        $this->assign('comment_list',$data['result']);
        $this->assign('active','comment');
        $this->display();
    }

    /*
     *添加评论
     */
    public function add_comment()
    {          
            $user_info = session('user');
            $comment_img = serialize(I('comment_img')); // 上传的图片文件            
            $add['goods_id'] = I('goods_id');
            $add['email'] = $user_info['email'];
            //$add['nick'] = $user_info['nickname'];
            $add['username'] = $user_info['nickname'];
            $add['order_id'] = I('order_id');
            $add['service_rank'] = I('service_rank');
            $add['deliver_rank'] = I('deliver_rank');
            $add['goods_rank'] = I('goods_rank');
            //$add['content'] = htmlspecialchars(I('post.content'));
            $add['content'] = I('content');
            $add['img'] = $comment_img;
            $add['add_time'] = time();
            $add['ip_address'] = $_SERVER['REMOTE_ADDR'];
            $add['user_id'] = $this->user_id;
            $logic = new UsersLogic();
            //添加评论
            $row = $logic->add_comment($add);            
            exit(json_encode($row));        
    }

    /**
     * @time 2016/8/5
     * @author dyr
     * 订单评价列表
     */
    public function comment_list()
    {
        $order_id = I('get.order_id');
        $store_id = I('get.store_id');
        $part_finish = I('get.part_finish', 0);
   
        if (empty($order_id) || empty($store_id)) {
            $this->error("参数错误");
        } else {
            //查找店铺信息
            $store_where['store_id'] = $store_id;
            $store_info = M('store')->field('store_id,store_name,store_phone,store_address,store_logo')->where($store_where)->find();
            if (empty($store_info)) {
                $this->error("该商家不存在");
            }
            //查找订单是否已经被用户评价
            $order_comment_where['order_id'] = $order_id;
            $order_comment_where['deleted'] = 0;
            $order_info = M('order')->field('order_id,order_sn,is_comment,add_time')->where($order_comment_where)->find();
            //查找订单下的所有未评价的商品
            $order_goods_logic = new OrderGoodsLogic();
            $no_comment_goods_list = $order_goods_logic->get_no_comment_goods_list($order_id);
            $goods_id_list = array();
            foreach ($no_comment_goods_list as $key => $value) {
                array_push($goods_id_list, $value['goods_id']);
            }
            $this->assign('goods_id_list', $goods_id_list);
            $this->assign('store_info', $store_info);
            $this->assign('order_info', $order_info);
            $this->assign('no_comment_goods_list', $no_comment_goods_list);
            $this->assign('no_comment_goods_list_count',count($no_comment_goods_list));
            $this->assign('part_finish', $part_finish);
            $this->display();
        }
    }

    /**
     * @time 2016/8/5
     * @author dyr
     *  添加评论
     */
    public function conmment_add()
    {
        $remark = I("post.remark");
        $anonymous = I('post.anonymous');
        $store_score['describe_score'] = I('post.store_packge_hidden');
        $store_score['seller_score'] = I('post.store_speed_hidden');
        $store_score['logistics_score'] = I('post.store_sever_hidden');
        $order_id = $store_score['order_id'] = $store_score_where['order_id'] = I('post.order_id');
        $store_score['user_id'] = $store_score_where['user_id'] = $this->user_id;
        $store_score_where['deleted'] = 0;
        $store_id = M('order')->where(array('order_id' => $store_score_where['order_id']))->getField('store_id');
        $store_score['store_id'] = $store_id;
        //处理订单评价
        if (!empty($store_score['describe_score']) && !empty($store_score['seller_score']) && !empty($store_score['logistics_score'])) {
            $order_comment = M('order_comment')->where($store_score_where)->find();
            if ($order_comment) {
                M('order_comment')->where($store_score_where)->save($store_score);
                M('order')->where(array('order_id' => $order_id))->save(array('is_comment' => 1));
            } else {
                M('order_comment')->add($store_score);//订单打分
                M('order')->where(array('order_id' => $order_id))->save(array('is_comment' => 1));
            }
            //订单打分后更新店铺评分
            $store_logic = new StoreLogic();
            $store_logic->updateStoreScore($store_id);
        }
        //处理商品评价
        if (is_array($remark)) {
            foreach ($remark as $key => $value) {
                if (!empty($value['rank']) && !empty($value['content'])) {
                    $comment['goods_id'] = $key;
                    $comment['order_id'] = $store_score['order_id'];
                    $comment['store_id'] = $store_id;
                    $comment['user_id'] = $this->user_id;
                    $comment['content'] = $value['content'];
                    $comment['ip_address'] = get_client_ip();
                    $comment['spec_key_name'] = $value['spec_key_name'];
                    $comment['goods_rank'] = $value['rank'];
                    $comment['img'] = (empty($value['commment_img'][0])) ? '' : serialize($value['commment_img']);
                    $comment['impression'] = (empty($value['tag'][0])) ? '' : implode(',', $value['tag']);
                    $comment['is_anonymous'] = empty($anonymous) ? 1 : 0;
                    $comment['add_time'] = time();
                    M('comment')->add($comment);//想评论表插入数据
                    M('order_goods')->where(array('order_id' => $store_score['order_id'], 'goods_id' => $key))->save(array('is_comment' => 1));
                    M('goods')->where(array('goods_id' => $key))->setInc('comment_count', 1);
                    unset($comment);
                }
            }
        }
        //查找订单下是否有没有评价的商品
        $order_goods_logic = new OrderGoodsLogic();
        $no_comment_goods_list = $order_goods_logic->get_no_comment_goods_list($order_id);
        $no_comment_goods_count = count($no_comment_goods_list);
        if ($no_comment_goods_count > 0) {
            redirect(U('User/comment_list', array('part_finish' => 1, 'order_id' => $order_id, 'store_id' => $store_id)));
        } else {
            redirect(U('User/comment_list', array('order_id' => $order_id, 'store_id' => $store_id)));
        }
    }

    /**
     *  点赞
     *  @author dyr
     */
    public function ajaxZan(){
        $comment_id = I('post.comment_id');
        $user_id = $this->user_id;
        $comment_info = M('comment')->where(array('comment_id'=>$comment_id))->find();
        $comment_user_id_array = explode(',', $comment_info['zan_userid']);
        if (in_array($user_id, $comment_user_id_array)) {
            $result['success'] = 0;
        }else{
            array_push($comment_user_id_array,$user_id);
            $comment_user_id_string = implode(',',$comment_user_id_array);
            $comment_data['zan_num'] = $comment_info['zan_num'] + 1;
            $comment_data['zan_userid'] = $comment_user_id_string;
            M('comment')->where(array('comment_id'=>$comment_id))->save($comment_data);
            $result['success'] = 1;
        }
        exit(json_encode($result));
    }

    /**
     * 添加回复
     * @author dyr
     */
    public function reply_add()
    {
        $comment_id = I('post.comment_id');
        $reply_id = I('post.reply_id', 0);
        $content = I('post.content');
        $to_name = I('post.to_name', '');
        $goods_id = I('post.goods_id');
        $reply_data = array(
            'comment_id' => $comment_id,
            'parent_id' => $reply_id,
            'content' => $content,
            'user_name' => $this->user['nickname'],
            'to_name' => $to_name,
            'reply_time' => time()
        );
        $db_prefix = C('DB_PREFIX');
        $table_array = array($db_prefix.'order'=>'o');
        $where = array('o.user_id' => $this->user_id, 'og.goods_id' => $goods_id, 'o.pay_status' => 1);
        $user_goods_count = M()
            ->table($table_array)
            ->join('left join __ORDER_GOODS__ AS og ON o.order_id = og.order_id')
            ->where($where)
            ->count();
        if($user_goods_count > 0){
            M('reply')->add($reply_data);
            M('comment')->where(array('comment_id'=>$comment_id))->setInc('reply_num');
            $json['success'] = true;
        }else{
            $json['success'] = false;
            $json['msg'] = '只有购买过该商品才能进行评价';
        }
        $this->ajaxReturn($json);
    }

    /**
     * 个人信息
     */
    public function info(){
        header('location:/seller/index/index.html?nav=userinfo');
        $userLogic = new UsersLogic();
        $user_info = $userLogic->get_info($this->user_id); // 获取用户信息
        $user_info = $user_info['result'];
        if(IS_POST){
            I('post.nickname') ? $post['nickname'] = I('post.nickname') : false; //昵称
            I('post.qq') ? $post['qq'] = I('post.qq') : false;  //QQ号码
            I('post.head_pic') ? $post['head_pic'] = I('post.head_pic') : false; //头像地址
            I('post.sex') ? $post['sex'] = I('post.sex') : false;  // 性别
            I('post.birthday') ? $post['birthday'] = strtotime(I('post.birthday')) : false;  // 生日
            I('post.province') ? $post['province'] = I('post.province') : false;  //省份
            I('post.city') ? $post['city'] = I('post.city') : false;  // 城市
            I('post.district') ? $post['district'] = I('post.district') : false;  //地区
            if(!$userLogic->update_info($this->user_id,$post)){
                $this->error("保存失败");
            }else{
                if (I('post.nickname')){
                    setcookie('uname',urlencode(I('post.nickname')),null,'/');
                }
                $this->success("操作成功");
            }
            exit;
        }
        //  获取省份
        $province = M('region')->where(array('parent_id'=>0,'level'=>1))->select();
        //  获取订单城市
        $city =  M('region')->where(array('parent_id'=>$user_info['province'],'level'=>2))->select();
        //获取订单地区
        $area =  M('region')->where(array('parent_id'=>$user_info['city'],'level'=>3))->select();

        $this->assign('province',$province);
        $this->assign('city',$city);
        $this->assign('area',$area);
        $this->assign('user',$user_info);
        $this->assign('sex',C('SEX'));
        $this->assign('active','info');
        $this->display();
    }

    /*
     * 邮箱验证
     */
   public function email_validate(){
        $userLogic = new UsersLogic();
        $user_info = $userLogic->get_info($this->user_id); // 获取用户信息
        $user_info = $user_info['result'];
        $step = I('get.step',1);
        $data['email'] = '';
        if(IS_POST){
            $email = I('post.email');
            // $old_email = I('post.old_email'); //旧邮箱
            $code = I('post.code');
            $info = session('validate_code');
            if(!$info)
                $this->error('非法操作');
            if($info['time']<time()){
                session('validate_code',null);
                $this->error('验证超时，请重新验证');
            }
            //检查原邮箱是否正确
            // if($user_info['email_validated'] == 1 && $old_email != $user_info['email'])
            //     $this->error('原邮箱匹配错误');
            //验证邮箱和验证码
            if($info['sender'] == $email && $info['code'] == $code){
                session('validate_code',null);
                if($user_info['email'] != ''){
                    if(M('users')->where(array('user_id'=>$this->user_id))->save($data))
                        $this->success('解绑成功',U('User/safety_settings'));
                    return fasle;
                }
                if(!$userLogic->update_email_mobile($email,$this->user_id))
                    $this->error('邮箱已存在');
                $this->success('绑定成功',U('User/safety_settings'));
                exit;
            }
            $this->error('邮箱验证码不匹配');
        }
        $this->assign('step',$step);
        $this->assign('user_info',$user_info);
        $this->display();
    }


    /*
    * 手机验证
    */
    public function mobile_validate(){
        $userLogic = new UsersLogic();
        $user_info = $userLogic->get_info($this->user_id); //获取用户信息
        $user_info = $user_info['result'];
        $config = F('sms','',TEMP_PATH);
        $sms_time_out = $config['sms_time_out'];
        $step = I('get.step',1);
        $data['mobile'] = '';
        if(IS_POST){
            $mobile = I('post.mobile');
            $old_mobile = I('post.old_mobile');
            $code = I('post.code');
            $info = session('validate_code');
            if(!$info)
                $this->error('非法操作');
            if($info['time']<time()){
                session('validate_code',null);
                $this->error('验证超时，请重新验证');
            }
            //检查原手机是否正确
            // if($user_info['mobile_validated'] == 1 && $old_mobile != $user_info['mobile'])
            //     $this->error('原手机号码错误');
            //验证手机和验证码
            if($info['sender'] == $mobile && $info['code'] == $code){
                session('validate_code',null);
                //验证有效期
                if($info['time'] < time())
                    $this->error('验证码已失效');
                if($user_info['mobile']) {
                    if(M('users')->where(array('user_id'=>$this->user_id))->save($data))
                        $this->success('解绑成功',U('User/safety_settings'));
                    return false;
                }
                $data2['mobile'] = $mobile;
                if (M('users')->where(array('mobile'=>$mobile))->find()) $this->error('该手机号码已被绑定');
                $seller = M('seller')->where(array('user_id'=>$this->user_id))->find();
                $seller2 = M('seller')->where(array('seller_name'=>$mobile))->find();
                if ($seller2 && $seller['seller_name'] != $mobile) $this->error('该手机号码已被使用');
                if (M('users')->where(array('user_id'=>$this->user_id))->save($data2)){
                    $this->success('绑定成功',U('User/safety_settings'));
                } else {
                    $this->error('绑定失败');
                }
                
                exit;
            }
            $this->error('手机验证码不匹配');
        }
        $this->assign('time',$sms_time_out);
        $this->assign('step',$step);
        $this->assign('user_info',$user_info);
        $this->display();
    }
    
    /**
     * 发送手机注册验证码
     */
    public function send_sms_reg_code(){
        $mobile = I('mobile');
        $userLogic = new UsersLogic();
        if(!check_mobile($mobile))
            exit(json_encode(array('status'=>-1,'msg'=>'手机号码格式有误')));
        $code =  rand(100000,999999);
        $send = $userLogic->sms_log($mobile,$code,$this->session_id);
        if($send['status'] != 1)
            exit(json_encode(array('status'=>-1,'msg'=>$send['msg'])));
        exit(json_encode(array('status'=>1,'msg'=>'验证码已发送，请注意查收')));
    }
    /**
     *我的收藏
     */
    public function goods_collect()
    {
        $type = I('get.type', 1);
        if ($type == 1) {
            //商品收藏
            $userLogic = new UsersLogic();
            $data = $userLogic->get_goods_collect($this->user_id);
            $this->assign('page', $data['show']);// 赋值分页输出
            $this->assign('lists', $data['result']);
            $this->assign('active', 'goods_collect');
            $this->display();
        } else {
            //店铺收藏
            $sc_id = I('get.sc_id');
            $store_class = M('store_class')->field('sc_id,sc_name')->where('')->select();
            $storeLogic = new StoreLogic();
            $store_collect_list = $storeLogic->getCollectStore($this->user_id, $sc_id);
            dump($store_collect_list);
            $this->assign('page', $store_collect_list['show']);// 赋值分页输出
            $this->assign('store_collect_list', $store_collect_list['result']);
            $this->assign('store_class', $store_class);//店铺分类
            $this->display('bookmark');
        }
    }

    /*
     * 删除一个收藏商品
     */
    public function del_goods_collect(){
        $id = I('get.id');
        if(!$id)
            $this->error("缺少ID参数");
        $row = M('goods_collect')->where(array('collect_id'=>$id,'user_id'=>$this->user_id))->delete();
        if(!$row)
            $this->error("删除失败");
        $this->success('删除成功');
    }

    /**
     *  删除一个收藏店铺
     */
    public function del_store_collect(){
        $id = I('get.log_id');
        if(!$id)
            $this->error("缺少ID参数");
        $store_id = M('store_collect')->where(array('log_id'=>$id,'user_id'=>$this->user_id))->getField('store_id');
        $row = M('store_collect')->where(array('log_id'=>$id,'user_id'=>$this->user_id))->delete();
        M('store')->where(array('store_id' => $store_id))->setDec('store_collect');
        if(!$row)
            $this->error("删除失败");
        $this->success('删除成功');
    }

    /*
     * 密码修改
     */
    public function password(){
        //检查是否第三方登录用户
        $logic = new UsersLogic();
        $data = $logic->get_info($this->user_id);
        $user = $data['result'];
        if($user['mobile'] == ''&& $user['email'] == '')
            $this->error('请先绑定手机或邮箱',U('/User/info'));
        if(IS_POST){
            $userLogic = new UsersLogic();
            $data = $userLogic->password($this->user_id,I('post.old_password'),I('post.new_password'),I('post.confirm_password')); // 获取用户信息
            if($data['status'] == -1)
                $this->error($data['msg']);
            $this->success($data['msg']);
            session(null);
            setcookie('uname','',time()-3600,'/');
            setcookie('cn','',time()-3600,'/');
            setcookie('user_id','',time()-3600,'/');
            setcookie('referurl','',time()-3600,'/');
            exit;
        }
        $this->display();
    }

    public function bind_remove()
    {
		
    }
    
    public function forget_pwd(){
    	session(null);
        cookie('referurl',null);
    	if(IS_POST){
    		$logic = new UsersLogic();
    		$username = I('post.username');
    		$code = I('post.code');
    		$new_password = I('post.new_password');
    		$confirm_password = I('post.confirm_password');
    		$pass = false;
    	
    		//检查是否手机找回
    		if(check_mobile($username)){
    			if(!$user = get_user_info($username,2))
    				$this->error('账号不存在');
    			$check_code = $logic->sms_code_verify($username,$code,$this->session_id);
    			if($check_code['status'] != 1)
    				$this->error($check_code['msg']);
    			$pass = true;
    		}
    		//检查是否邮箱
    		if(check_email($username)){
    			if(!$user = get_user_info($username,1))
    				$this->error('账号不存在');
    			$check = session('forget_code');
    			if(empty($check))
    				$this->error('非法操作');
    			if(!$username || !$code || $check['email'] != $username || $check['code'] != $code)
    				$this->error('邮箱验证码不匹配');
    			$pass = true;
    		}
    		if($user['user_id'] > 0 && $pass)
    			$data = $logic->password($user['user_id'],'',$new_password,$confirm_password,false); // 获取用户信息
    		if($data['status'] != 1)
    			$this->error($data['msg'] ? $data['msg'] :  '操作失败');
    		$this->success($data['msg'],U('/User/login'));
    		exit;
    	}
        $this->display();
    }
    
    public function set_pwd(){
    	
    	$check = session('validate_code');
    	if(empty($check)){
    		header("Location:".U('/User/forget_pwd'));
    	}elseif($check['is_check']==0){
    		$this->error('验证码还未验证通过',U('/User/forget_pwd'));
    	}    	
    	if(IS_POST){
    		$password = I('post.password');
    		$password2 = I('post.password2');
    		if($password2 != $password){
    			$this->error('两次密码不一致',U('/User/forget_pwd'));
    		}  		
    		if($check['is_check']==1){
    			//$user = get_user_info($check['sender'],1);
                        $user = M('users')->where("mobile = '{$check['sender']}' or email = '{$check['sender']}'")->find();

    			M('users')->where("user_id=".$user['user_id'])->save(array('password'=>encrypt($password)));
    			session('validate_code',null);
    			header("Location:".U('/User/finished'));
    		}else{
    			$this->error('验证码还未验证通过',U('/User/forget_pwd'));
    		}
    	}
    	$this->display();
    }
    
    public function finished(){

    	session(null);
        setcookie('uname','',time()-3600,'/');
        setcookie('cn','',time()-3600,'/');
        setcookie('user_id','',time()-3600,'/');
        setcookie('referurl','',time()-3600,'/');
    	$this->display();
    }   
    
    public function check_captcha(){
    	$verify = new Verify();
    	$type = I('post.type','user_login');
    	if (!$verify->check(I('post.verify_code'), $type)) {
    		exit(json_encode(0));
    	}else{
    		exit(json_encode(1));
    	}
    }
    
    public function check_username(){
    	$username = I('post.username');
    	if(!empty($username)){
    		$count = M('users')->where("email='$username' or mobile='$username'")->count();
            if ($count == 0){
                $count = M('seller')->where(array('seller_name'=>$username))->count();
            }
    		exit(json_encode(intval($count)));
    	}else{
    		exit(json_encode(0));
    	}  	
    }
    
    public function identity(){
    	$username = I('post.username');
    	$userinfo = array();
    	if($username){
    		$userinfo = M('users')->where("email='$username' or mobile='$username'")->find();
            if (empty($userinfo)){
                $seller = M('seller')->where(array('seller_name'=>$username))->find();
                $userinfo = M('users')->where(array('user_id'=>$seller['user_id']))->find();
            }
    		$userinfo['username'] = $username;
            // dump($userinfo['mobile']);die;
            if (empty($userinfo['mobile']) && empty($userinfo['email'])) $this->error('您未绑定手机和邮箱,请联系客服帮忙找回密码');
    		session('userinfo',$userinfo);
    	}else{
    		$this->error('参数有误！！！');
    	} 	
    	if(empty($userinfo)){
    		$this->error('非法请求！！！');
    	}
    	unset($user_info['password']);
    	$this->assign('userinfo',$userinfo);
    	$this->display();
    }
    
    //发送验证码
    public function send_validate_code(){
    	$type = I('type');
    	$send = I('send');
    	$logic = new UsersLogic();
        $res = $logic->send_validate_code2($send, $type);
        $this->ajaxReturn($res);
    }
    
    public function check_validate_code(){
    	$code = I('post.code');
    	$send = I('send');
    	$logic = new UsersLogic();
        $res = $logic->check_validate_code($code, $send);
        $this->ajaxReturn($res);
    }
    
    /**
     * 验证码验证
     * $id 验证码标示
     */
    private function verifyHandle($id)
    {
        $verify = new Verify();
        if (!$verify->check(I('post.verify_code'), $id ? $id : 'user_login')) {
            $this->error("验证码错误");
        }
    }

    /**
     * 验证码获取
     */
    public function verify()
    {
        //验证码类型
        $type = I('get.type') ? I('get.type') : 'user_login';
        $config = array(
            'fontSize' => 40,
            'length' => 4,
            'useCurve' => true,
            'useNoise' => false,
        );
        $Verify = new Verify($config);
        $Verify->entry($type);
    }

    public function order_confirm(){
        $id = I('get.id',0);
        $data = confirm_order($id,$this->user_id);
        if(!$data['status'])
            $this->error($data['msg']);
		else	
	        $this->success($data['msg']);
    }
    /**
     * 申请退货
     */
    public function return_goods()
    {
        $order_id = I('order_id',0);
        $order_sn = I('order_sn',0);
        $goods_id = I('goods_id',0);        
	$spec_key = I('spec_key');    

        $c = M('order')->where("order_id = $order_id and user_id = {$this->user_id}")->count();
        if($c == 0)
        {
            $this->error('非法操作');
            exit;
        }        
        
        $return_goods = M('return_goods')->where("order_id = $order_id and goods_id = $goods_id  and spec_key = '$spec_key'")->find();            
        if(!empty($return_goods))
        {
            $this->success('已经提交过退货申请!',U('/User/return_goods_info',array('id'=>$return_goods['id'])));
            exit;
        }       
        if(IS_POST)
        {
            $data['order_id'] = $order_id; 
            $data['order_sn'] = $order_sn; 
            $data['goods_id'] = $goods_id; 
            $data['addtime'] = time(); 
            $data['user_id'] = $this->user_id;            
            $data['type'] = I('type'); // 服务类型  退货 或者 换货
            $data['reason'] = I('reason'); // 问题描述
            $data['imgs'] = I('imgs'); // 用户拍照的相片
            $data['spec_key'] = I('spec_key'); // 商品规格
            $data['store_id'] = M('order')->where("order_id = $order_id")->getField('store_id'); // 店铺id
            M('return_goods')->add($data);            
            $this->success('申请成功,客服第一时间会帮你处理',U('/User/order_list'));
            exit;
        }
               
        $goods = M('goods')->where("goods_id = $goods_id")->find();        
        $this->assign('goods',$goods);
        $this->assign('order_id',$order_id);
        $this->assign('order_sn',$order_sn);
        $this->assign('goods_id',$goods_id);
        $this->display();
    }
    
    /**
     * 退换货列表
     */
    public function return_goods_list()
    {        
        $count = M('return_goods')->where("user_id = {$this->user_id}")->count();
        $page = new Page($count,10);
        $list = M('return_goods')->where("user_id = {$this->user_id}")->order("id desc")->limit("{$page->firstRow},{$page->listRows}")->select();
        $goods_id_arr = get_arr_column($list, 'goods_id');
        if(!empty($goods_id_arr))
            $goodsList = M('goods')->where("goods_id in (".  implode(',',$goods_id_arr).")")->getField('goods_id,goods_name');        
        $this->assign('goodsList', $goodsList);
        $this->assign('list', $list);
        $this->assign('page', $page->show());// 赋值分页输出
        $this->display();
    }
    
    /**
     *  退货详情
     */
    public function return_goods_info()
    {
        $id = I('id',0);
        $return_goods = M('return_goods')->where("id = $id")->find();
        if($return_goods['imgs'])
            $return_goods['imgs'] = explode(',', $return_goods['imgs']);        
        $goods = M('goods')->where("goods_id = {$return_goods['goods_id']} ")->find();                
        $this->assign('goods',$goods);
        $this->assign('return_goods',$return_goods);
        $this->display();
    }
    
    /**
     * 安全设置
     */
    public function safety_settings()
    {
        $userLogic = new UsersLogic();
        $user_info = $userLogic->get_info($this->user_id); // 获取用户信息
        $user_info = $user_info['result'];        
        $this->assign('user',$user_info);
        $this->display();      
    }
    
    /**
     * 申请提现记录
     */
    public function withdrawals(){       
    	//C('TOKEN_ON',true);
    	if(IS_POST)
    	{
                $this->verifyHandle('withdrawals');                
    		$data = I('post.');
    		$data['user_id'] = $this->user_id;    		    		
    		$data['create_time'] = time();                
                $distribut_min = tpCache('distribut.min'); // 最少提现额度
                if($data['money'] < $distribut_min)
                {
                        $this->error('每次最少提现额度'.$distribut_min);
                        exit;
                }
                if($data['money'] > $this->user['user_money'])
                {
                        $this->error("你最多可提现{$this->user['user_money']}账户余额.");
                        exit;
                }     
                 
    		if(M('withdrawals')->add($data)){
    			$this->success("已提交申请");
                        exit;
    		}else{
    			$this->error('提交失败,联系客服!');
                        exit;
    		}
    	}
        
        $where = " user_id = {$this->user_id}";        
        $count = M('withdrawals')->where($where)->count();
        $page = new Page($count,16);
        $show = $page->show();
        $list = M('withdrawals')->where($where)->order("id desc")->limit("{$page->firstRow},{$page->listRows}")->select();   
        $this->assign('show',$show);// 赋值分页输出
        $this->assign('list',$list); // 下线      
        $this->display(); 
    }  


    public  function recharge(){
    	if(IS_POST){
   			$user = session('user');
   			$data['user_id'] = $this->user_id;
   			$data['nickname'] = $user['nickname'];
   			$data['account'] = I('account');
   			$data['order_sn'] = 'recharge'.get_rand_str(10,0,1);
   			$data['ctime'] = time();
    		$order_id = M('recharge')->add($data);
    		if($order_id){
    			$url = U('/Payment/getPay',array('pay_radio'=>$_REQUEST['pay_radio'],'order_id'=>$order_id));
    			redirect($url);
    		}else{
    			$this->error('提交失败,参数有误!');
    		}
    	}
	   	$paymentList = M('Plugin')->where("`type`='payment' and code!='cod' and status = 1 and scene in(0,2)")->select();
	   	$paymentList = convert_arr_key($paymentList, 'code');	   	
	   	foreach($paymentList as $key => $val)
	   	{
	   		$val['config_value'] = unserialize($val['config_value']);
	   		if($val['config_value']['is_bank'] == 2)
	   		{
	   			$bankCodeList[$val['code']] = unserialize($val['bank_code']);
	   		}
	   	}
	   	$bank_img = include 'Application/Home/Conf/bank.php'; // 银行对应图片
	   	$this->assign('paymentList',$paymentList);
	   	$this->assign('bank_img',$bank_img);
	   	$this->assign('bankCodeList',$bankCodeList);
	   	
	   	$count = M('recharge')->where(array('user_id'=>$this->user_id))->count();
	   	$Page = new Page($count,10);
	   	$show = $Page->show();
	   	$recharge_list = M('recharge')->where(array('user_id'=>$this->user_id))->limit($Page->firstRow.','.$Page->listRows)->select();
	   	$this->assign('page',$show);
	   	$this->assign('recharge_list',$recharge_list);//充值记录
	   	
	   	$count2 = M('account_log')->where(array('user_id'=>$this->user_id,'user_money'=>array('neq',0)))->count();
	   	$Page2 = new Page($count2,10);
	   	$consume_list = M('account_log')->where(array('user_id'=>$this->user_id,'user_money'=>array('neq',0)))->limit($Page2->firstRow.','.$Page2->listRows)->select();
	   	$this->assign('consume_list',$consume_list);//消费记录
	   	$this->assign('page2',$Page2->show());
   		$this->display();
    }

    /**
     *  用户消息通知
     * @author dyr
     * @time 2016/09/01
     */
    public function message_notice()
    {
        $this->display();
    }
    /**
     * ajax用户消息通知请求
     * @author dyr
     * @time 2016/09/01
     */
    public function ajax_message_notice()
    {
        $type = I('type',0);
        $user_logic = new UsersLogic();
        if ($type == 1) {
            //系统消息
            $user_sys_message = D('Message')->getUserMessageNotice();
            $user_logic->setSysMessageForRead();
        } else if ($type == 2) {
            //活动消息：后续开发
            $user_sys_message = array();
        } else {
            //全部消息：后续完善
            $user_sys_message = D('Message')->getUserMessageNotice();
        }
        $this->assign('messages', $user_sys_message);
        $this->display();
    }

    // ajax验证商户名是否重复
    public function ajax_seller()
    {   
        $seller_name = I('sellername','');
        $data['mobile'] = $seller_name;
        $data['email'] = $seller_name;
        $data['_logic'] = 'or';
        $obj = M('seller')->where(array('seller_name'=>$seller_name))->find();
        $res = M('users')->where(array($data))->find();
        if($obj){
            $this->ajaxReturn(true);
        } else {
            $this->ajaxReturn(false);
        }
    }

    // ajax验证手机号码是否可以用
    public function ajax_mobile()
    {
        $mobile = I('mobile','');
        $obj = M('users')->where(array('mobile'=>$mobile))->find();
        // $res = M('seller')->where(array('seller_name'=>$mobile))->find();
        if($obj){
            $this->ajaxReturn(true);
        } else {
            $this->ajaxReturn(false);
        }
    }

    public function ajax_jump(){
        if (session('seller_id') > 0 && session('user') != '' && session('store_id') > 0 && session('seller') !=''){
            $this->ajaxReturn(1);
        }else{
            $this->ajaxReturn(0);
        }
    }

    // ajax验证店铺名字是否存在
    public function ajax_store()
    {	
    	$store_name = I('storename','');
    	$obj = M('store')->where(array('store_name'=>$store_name))->find();
    	if ($obj) {
    		$this->ajaxReturn(true);
    	} else {
    		$this->ajaxReturn(false);
    	}
    	
    }

    //将另一台服务器的邮箱验证码写入到session
    public function putcode()
    {   
        $send = I('send','');
        if (!$send) $this->ajaxReturn(0);
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, "http://template.yundi88.com/index.php?m=home&c=User&a=send_validate_code&step=1&type=email&send=".$send);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        //显示获得的数据 
        // print_r($data);
        $data = explode(',', $data);
        $info['code'] = $data[2];
        $info['sender'] = $data[1];
        $info['time'] = time() + 120;
        session('code',$data);
        session('validate_code',$info);
        $this->ajaxReturn($data[0]);
    }

    public function is_beautiful_username($username){
        $res = 1;
        $arr = str_split($username);
        if (count($arr) == 6){
            if  ($arr[0] == $arr[1] && $arr[1] == $arr[2] &&  $arr[1] == $arr[3] && $arr[1] ==  $arr[4] && $arr[1] == $arr[5]){
                $res = '';
            }

            if (is_numeric($arr[1]) && is_numeric($arr[2]) && is_numeric($arr[3]) && is_numeric($arr[4]) && is_numeric($arr[5])){

                if ($arr[1]+1 == $arr[2] && $arr[2]+1 == $arr[3] && $arr[3]+1 == $arr[4] && $arr[4]+1 == $arr[5]){
                    $res = '';
                }

                if ($arr[1]-1 == $arr[2] && $arr[2]-1 == $arr[3] && $arr[3]-1 == $arr[4] && $arr[4]-1 == $arr[5]){
                    $res = '';
                }

                if  ($arr[1] == $arr[2] &&  $arr[1] == $arr[3] && $arr[1] ==  $arr[4] && $arr[1] == $arr[5]){
                    $res = '';
                }
            }
        }
        return $res;
    }

    public function test(){
        // $storeLogic = new StoreLogic();
        // $storeLogic->add_user('aa206414','206414','13640618075','aa208349@163.com','');
        // dump(session());
        dump(cookie());
        dump(session());
    }
}
