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
 * $Author: IT宇宙人 2015-08-10 $
 */ 
namespace Home\Controller;

class CartController extends BaseController {
    
    public $cartLogic; // 购物车逻辑操作类
    public $user_id = 0;
    public $user = array();    
    /**
     * 初始化函数
     */
    public function _initialize() {       
        parent::_initialize();
        $this->cartLogic = new \Home\Logic\CartLogic();
        
        if(session('?user'))
        {
        	$user = session('user');
            $user = M('users')->where("user_id = {$user['user_id']}")->find();
            session('user',$user);  //覆盖session 中的 user
        	$this->user = $user;
        	$this->user_id = $user['user_id'];
        	$this->assign('user',$user); //存储用户信息                
        }                        
    }


    public function cart(){ 
        $user_id = session('user')['user_id'];
        $this->assign('user_id',$user_id);
        $this->display();
    }
    
    public function index(){
        $user_id = session('user')['user_id'];
        $this->assign('user_id',$user_id);
    	$this->display('cart');

    }


    //判断是否为测试用户 自己的
    public function iscs($goodsid)
    {

        $store_id = M('goods')->where(array('goods_id'=>$goodsid))->field('store_id')->find();
        return M('shouji')->where(array('userid'=>$store_id['store_id']))->find();
    }

    /**
     * ajax 将商品加入购物车
     */
    function ajaxAddCart()
    {
        $goods_id = I("goods_id"); // 商品id
        if($this->iscs($goods_id)){
            $this->ajaxReturn(999);
            
        }
        $goods_num = I("goods_num");// 商品数量
        $goods_spec = I("goods_spec"); // 商品规格      

        
        $result = $this->cartLogic->addCart($goods_id, $goods_num, $goods_spec,$this->session_id,$this->user_id); // 将商品加入购物车                     
        exit(json_encode($result));
    }
    
    /**
     * ajax 删除购物车的商品
     */
    public function ajaxDelCart()
    {       
        $ids = I("ids"); // 商品 ids        
        $result = M("Cart")->where(" id in ($ids)")->delete(); // 删除id为5的用户数据
        $return_arr = array('status'=>1,'msg'=>'删除成功','result'=>''); // 返回结果状态       
        exit(json_encode($return_arr));
    }
    
    
    /*
     * ajax 请求获取购物车列表
     */
    public function ajaxCartList()
    {    
        $post_goods_num = I("goods_num"); // goods_num 购物车商品数量
        $post_cart_select = I("cart_select"); // 购物车选中状态                               
        $where = " session_id = '$this->session_id' "; // 默认按照 session_id 查询
        $this->user_id && $where = " user_id = ".$this->user_id; // 如果这个用户已经等了则按照用户id查询
        
        $cartList = M('Cart')->where($where)->getField("id,goods_num,selected,prom_type,prom_id"); 
        
        if($post_goods_num)
        {
            // 修改购物车数量 和勾选状态
            foreach($post_goods_num as $key => $val)
            {   
                $data['goods_num'] = $val < 1 ? 1 : $val;
                
                if($cartList[$key]['prom_type'] == 1) //限时抢购 不能超过购买数量
                {
                    $flash_sale = M('flash_sale')->where("id = {$cartList[$key]['prom_id']}")->find();
                    $data['goods_num'] = $data['goods_num'] > $flash_sale['buy_limit'] ? $flash_sale['buy_limit'] : $data['goods_num'];
                }
                
                $data['selected'] = $post_cart_select[$key] ? 1 : 0 ;                               
                if(($cartList[$key]['goods_num'] != $data['goods_num']) || ($cartList[$key]['selected'] != $data['selected'])) 
                    M('Cart')->where("id = $key")->save($data);
            }
            $this->assign('select_all', $_POST['select_all']); // 全选框
        }
                
        $result = $this->cartLogic->cartList($this->user, $this->session_id,1,1); // 选中的商品        
        if(empty($result['total_price']))
            $result['total_price'] = Array( 'total_fee' =>0, 'cut_fee' =>0, 'num' => 0);
        
        $this->assign('cartList', $result['cartList']); // 购物车的商品                
        $this->assign('total_price', $result['total_price']); // 总计
        $this->display('ajax_cart_list');
    }
    /**
     * 购物车第二步确定页面
     */
    public function cart2()
    {   

        if($this->user_id == 0)
            $this->error('请先登陆',U('/User/login'));
        
        if($this->cartLogic->cart_count($this->user_id,1) == 0 ) 
            $this->error ('你的购物车没有选中商品',U('/Cart/cart'));
        
        $result = $this->cartLogic->cartList($this->user, $this->session_id,1,1); // 获取购物车商品        
               
        $store_id_arr = M('cart')->where("user_id = {$this->user_id} and selected = 1")->getField('store_id',true); // 获取所有店铺id        
        $storeList = M('store')->where("store_id in (".implode(',', $store_id_arr).")")->select(); // 找出所有商品对应的店铺id
                
        $shippingList = M('shipping_area')->where(" store_id in (".implode(',', $store_id_arr).") and is_default = 1 and is_close = 1")->group("store_id,shipping_code")->getField('shipping_area_id,shipping_code,store_id');// 物流公司
        $shippingList2 = M('plugin')->where("type = 'shipping'")->getField('code,name'); // 查找物流插件
        foreach($shippingList as $k => $v)
            $shippingList[$k]['name']  = $shippingList2[$v['shipping_code']];
        // 找出这个用户的优惠券 没过期的  并且 订单金额达到 condition 优惠券指定标准的
        $sql = "select c1.name,c1.money,c1.condition, c2.* from __PREFIX__coupon as c1 inner join __PREFIX__coupon_list as c2  on c2.cid = c1.id and c1.type in(0,1,2,3) and order_id = 0
            where c2.uid = {$this->user_id}  and ".time()." < c1.use_end_time and c2.store_id in (".implode(',', $store_id_arr).")";

        $couponList = M()->query($sql);
        if($couponList && $result['cartList']){
        	foreach ($result['cartList'] as $val){
        		$coupon[$val['store_id']] += $val['member_goods_price']*$val['goods_num'];//每个商家的商品价格之和
        	}
        	
        	foreach ($couponList as $k=>$v){
        		if($v['condition'] > $coupon[$v['store_id']])
        			unset($couponList[$k]);//不满足使用额度的优惠券过滤掉
        	}
        }

        $this->assign('storeList', $storeList); // 店铺列表
        $this->assign('couponList', $couponList); // 优惠券列表        
        $this->assign('shippingList', $shippingList); // 物流公司        
        $this->assign('cartList', $result['cartList']); // 购物车的商品
        $this->assign('total_price', $result['total_price']); // 总计
        $this->display();
    }
   
    /*
     * ajax 获取用户收货地址 用于购物车确认订单页面
     */
    public function ajaxAddress(){                               
        $address_list = M('UserAddress')->where("user_id = {$this->user_id}")->select();
        if($address_list){
        	$area_id = array();
        	foreach ($address_list as $val){
        		$area_id[] = $val['province'];
                $area_id[] = $val['city'];
                $area_id[] = $val['district'];
                $area_id[] = $val['twon'];
        	}    
            $area_id = array_filter($area_id);
        	$area_id = implode(',', $area_id);
        	$regionList = M('region')->where("id in ($area_id)")->getField('id,name');
        	$this->assign('regionList', $regionList);
        }
        $c = M('UserAddress')->where("user_id = {$this->user_id} and is_default = 1")->count(); // 看看有没默认收货地址        
        if((count($address_list) > 0) && ($c == 0)) // 如果没有设置默认收货地址, 则第一条设置为默认收货地址
            $address_list[0]['is_default'] = 1;
                     
        $this->assign('address_list', $address_list);
        $this->display('ajax_address');
    }
    
	
    /**
     * ajax 获取订单商品价格 或者提交 订单
     */
    public function cart3(){
        
        if($this->user_id == 0)
            exit(json_encode(array('status'=>-100,'msg'=>"登录超时请重新登录!",'result'=>null))); // 返回结果状态
        
        $address_id = I("address_id"); //  收货地址id
        $shipping_code =  I("shipping_code"); //  物流编号                
        $user_note = I('user_note'); // 给卖家留言
        $couponTypeSelect =  I("couponTypeSelect"); //  优惠券类型  1 下拉框选择优惠券 2 输入框输入优惠券代码        
        $coupon_id =  I("coupon_id"); //  优惠券id
        $couponCode =  I("couponCode"); //  优惠券代码
        $invoice_title = I('invoice_title'); // 发票
        $pay_points =  I("pay_points",0); //  使用积分
        $user_money =  I("user_money",0); //  使用余额        
        $user_money = $user_money ? $user_money : 0;
                
        if($this->cartLogic->cart_count($this->user_id,1) == 0 ) exit(json_encode(array('status'=>-2,'msg'=>'你的购物车没有选中商品','result'=>null))); // 返回结果状态
        if(!$address_id) exit(json_encode(array('status'=>-3,'msg'=>'请先填写收货人信息','result'=>null))); // 返回结果状态
        if(!$shipping_code) exit(json_encode(array('status'=>-4,'msg'=>'请选择物流信息','result'=>null))); // 返回结果状态
		                
		$address = M('UserAddress')->where("address_id = $address_id")->find();
		$order_goods = M('cart')->where("user_id = {$this->user_id} and selected = 1")->select();
        $result = calculate_price($this->user_id,$order_goods,$shipping_code,0,$address[province],$address[city],$address[district],$pay_points,$user_money,$coupon_id,$couponCode);
                
        if($result['status'] < 0)
             exit(json_encode($result));
                       
        $car_price = array(
            'postFee'      => $result['result']['shipping_price'], // 物流费
            'couponFee'    => $result['result']['coupon_price'], // 优惠券            
            'balance'      => $result['result']['user_money'], // 使用用户余额
            'pointsFee'    => $result['result']['integral_money'], // 积分支付            
            'payables'     => array_sum($result['result']['store_order_amount']), // 订单总额 减去 积分 减去余额
            'goodsFee'     => $result['result']['goods_price'],// 总商品价格
            'order_prom_amount' => array_sum($result['result']['store_order_prom_amount']), // 总订单优惠活动优惠了多少钱
                                    
            'store_order_prom_id'=> $result['result']['store_order_prom_id'], // 每个商家订单优惠活动的id号
            'store_order_prom_amount'=> $result['result']['store_order_prom_amount'], // 每个商家订单活动优惠了多少钱
            'store_order_amount' => $result['result']['store_order_amount'], // 每个商家订单优惠后多少钱, -- 应付金额
            'store_shipping_price'=>$result['result']['store_shipping_price'],  //每个商家的物流费
            'store_coupon_price'=>$result['result']['store_coupon_price'],  //每个商家的优惠券抵消金额
            'store_point_count' => $result['result']['store_point_count'], // 每个商家平摊使用了多少积分            
            'store_balance'=>$result['result']['store_balance'], // 每个商家平摊用了多少余额
            'store_goods_price'=>$result['result']['store_goods_price'], // 每个商家的商品总价
        );   
        // 提交订单        
        if($_REQUEST['act'] == 'submit_order')
        {  
            if(empty($coupon_id) && !empty($couponCode))
            {
                foreach($couponCode as $k => $v)
                $coupon_id[$k] = M('CouponList')->where("`code`='$v' and store_id = $k")->getField('id');
            }                
            $result = $this->cartLogic->addOrder($this->user_id,$address_id,$shipping_code,$invoice_title,$coupon_id,$car_price,$user_note); // 添加订单                        
            exit(json_encode($result));            
        }
        $return_arr = array('status'=>1,'msg'=>'计算成功','result'=>$car_price); // 返回结果状态
        exit(json_encode($return_arr));           
    }	
    /**
     * ajax 获取订单商品价格 或者提交 订单
	 * 已经用心方法 这个方法 cart9  准备作废
     */
   
    /*
     * 订单支付页面
     */
    public function cart4(){
        $order_id = I('order_id',0); 
        
        // 如果是主订单号过来的, 说明可能是合并付款的
        $master_order_sn = I('master_order_sn','');        
        if($master_order_sn)
        {                       
            $sum_order_amount = M('order')->where("master_order_sn = '$master_order_sn'")->sum('order_amount');
            if($sum_order_amount == 0){                
                $order_order_list = U("/User/order_list");
                header("Location: $order_order_list");
            }            
        }
        else
        {
            $order = M('Order')->where("order_id = $order_id")->find();
            // 如果已经支付过的订单直接到订单详情页面. 不再进入支付页面
            if($order['pay_status'] == 1){
                $order_detail_url = U("/User/order_detail",array('id'=>$order_id));
                header("Location: $order_detail_url");
            }
        }
         
        $paymentList = M('Plugin')->where("`type`='payment' and status = 1 and  scene in(0,2)")->select();                        
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
        $payment = M('Plugin')->where("`type`='payment' and status = 1")->select();        
        $this->assign('paymentList',$paymentList);        
        $this->assign('bank_img',$bank_img);
        $this->assign('master_order_sn', $master_order_sn); // 主订单号
        $this->assign('sum_order_amount', $sum_order_amount); // 所有订单应付金额        
        $this->assign('order',$order);
        $this->assign('bankCodeList',$bankCodeList);        
        $this->assign('pay_date',date('Y-m-d', strtotime("+1 day")));
        $this->display();                   
    }
 
    
    //ajax 请求购物车列表
    public function header_cart_list()
    {
    	$cart_result = $this->cartLogic->cartList($this->user, $this->session_id,0,1);
    	if(empty($cart_result['total_price']))
    		$cart_result['total_price'] = Array( 'total_fee' =>0, 'cut_fee' =>0, 'num' => 0);
    	
    	$this->assign('cartList', $cart_result['cartList']); // 购物车的商品
    	$this->assign('cart_total_price', $cart_result['total_price']); // 总计
        $template = I('template','header_cart_list');    	 
        $this->display($template);		 
    }
}
