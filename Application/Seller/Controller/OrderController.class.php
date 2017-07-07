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
use Seller\Logic\OrderLogic;
use Home\Logic\UsersLogic;
use Home\Logic\OrderGoodsLogic;
use Home\Logic\StoreLogic;
use Think\Page;
use Think\Verify;
use Think\AjaxPage;

class OrderController extends BaseController {
    public  $order_status;
    public  $shipping_status;
    public  $pay_status;
    /*
     * 初始化操作
     */
    public function _initialize() {
        parent::_initialize();
        C('TOKEN_ON',false); // 关闭表单令牌验证
        // 订单 支付 发货状态
        $this->order_status = C('ORDER_STATUS');
        $this->pay_status = C('PAY_STATUS');
        $this->shipping_status = C('SHIPPING_STATUS');
        $this->assign('order_status',$this->order_status);
        $this->assign('pay_status',$this->pay_status);
        $this->assign('shipping_status',$this->shipping_status);
        $this->user_id = session('user')['user_id'];
    }

    /*
     *订单首页
     */
    public function index(){
    	$begin = date('Y/m/d',(time()-30*60*60*24));//30天前
    	$end = date('Y/m/d',strtotime('+1 days')); 
    	$this->assign('timegap',$begin.'-'.$end);
        $this->display();
    }

    /*
     *订单首页 买
     */
     public function indexbuy(){
        $begin = date('Y/m/d',(time()-30*60*60*24));//30天前
        $end = date('Y/m/d',strtotime('+1 days')); 
        $this->assign('timegap',$begin.'-'.$end);
         $where = ' user_id='.session('user.user_id');
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
     *Ajax首页
     *jinjun 有改动 买订单
     */

    public function ajaxindex(){
        $orderLogic = new OrderLogic();       
        $timegap = I('timegap');
        if($timegap){
        	$gap = explode('-', $timegap);
        	$begin = strtotime($gap[0]);
        	$end = strtotime($gap[1]);
        }
        // 搜索条件 参数buy存在那么条件为user_id 反之STORE_ID
        $condition = array( 'store_id' => STORE_ID ); // 商家id 

        I('consignee') ? $condition['consignee'] = trim(I('consignee')) : false;
        if($begin && $end){
        	$condition['add_time'] = array('between',"$begin,$end");
        }
        I('order_sn') ? $condition['order_sn'] = trim(I('order_sn')) : false;
        I('order_status') != '' ? $condition['order_status'] = I('order_status') : false;
        I('pay_status') != '' ? $condition['pay_status'] = I('pay_status') : false;
        I('pay_code') != '' ? $condition['pay_code'] = I('pay_code') : false;
        I('shipping_status') != '' ? $condition['shipping_status'] = I('shipping_status') : false;
        I('is_comment') != '' ? $condition['is_comment'] = I('is_comment') : false;
        
        $sort_order = I('order_by','DisESC').' '.I('sort');
        $count = M('order')->where($condition)->count();
        $Page  = new AjaxPage($count,10);
        $show = $Page->show();
    
        //获取订单列表
        $orderList = $orderLogic->getOrderList($condition,$sort_order,$Page->firstRow,$Page->listRows);
        $this->assign('orderList',$orderList);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();
    }

      /*
     * 订单详情
     */
    public function order_detail(){
        $id = I('get.id');
        $map['order_id'] = $id;
        $map['user_id'] = session('user.user_id');
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
//###################################################
    /*
     * 取消订单
     */
    public function cancel_order(){
        $id = I('get.id');
        //检查是否有积分，余额支付
        $logic = new UsersLogic();
        $data = $logic->cancel_order(session('user.user_id'),$id);
        if($data['status'] < 0){
            $this->error($data['msg']);
        }
        $this->success($data['msg']);
    }

    //####################################################

    /*
     * ajax 发货订单列表
    */
    public function ajaxdelivery(){
    	$orderLogic = new OrderLogic();
    	$condition = array('store_id'=>STORE_ID);
    	I('consignee') ? $condition['consignee'] = trim(I('consignee')) : false;
    	I('order_sn') != '' ? $condition['order_sn'] = trim(I('order_sn')) : false;
    	$shipping_status = I('shipping_status');
    	$condition['shipping_status'] = empty($shipping_status) ? array('neq',1) : $shipping_status;
		$condition['order_status'] = array('in','1,2,4');
    	$count = M('order')->where($condition)->count();
    	$Page  = new AjaxPage($count,10);
    	$show = $Page->show();
    	$orderList = M('order')->where($condition)->limit($Page->firstRow.','.$Page->listRows)->order('add_time DESC')->select();
    	$this->assign('orderList',$orderList);
    	$this->assign('page',$show);// 赋值分页输出
    	$this->display();
    }

    /**
     * 订单详情卖出
     * @param int $id 订单id
     */
    public function detail($order_id){
        $orderLogic = new OrderLogic();
        $order = $orderLogic->getOrderInfo($order_id);
        //判断是否是自己买的还是卖的
        if($order['user_id'] != session('user.user_id')){
             if ($order['store_id'] != STORE_ID) {
                $this->error('该订单不存在',U('Seller/Order/index'));
            }
        }
       
        $orderGoods = $orderLogic->getOrderGoods($order_id);
        $button = $orderLogic->getOrderButton($order);
        // 获取操作记录
        $action_log = M('order_action')->where(array('order_id'=>$order_id))->order('log_time desc')->select();
        $this->assign('order',$order);
        $this->assign('action_log',$action_log);
        $this->assign('orderGoods',$orderGoods);
        $split = count($orderGoods) >1 ? 1 : 0;
        foreach ($orderGoods as $val){
            if($val['goods_num']>1){
                $split = 1;
            }
        }
        $this->assign('split',$split);
        $this->assign('button',$button);
        $this->display();
    }
    
   
//#######################################
    /**
      * 确认收货
      */ 
    public function order_confirm(){
        $id = I('get.id',0);
        $data = confirm_order($id,session('user.user_id'));
        if(!$data['status'])
            $this->error($data['msg']);
        else    
            $this->success($data['msg']);
    }
//##################################################
/**
 * 退货
 */
public function return_goods()
    {
        $order_id = I('order_id',0);
        $order_sn = I('order_sn',0);
        $goods_id = I('goods_id',0);        
        $spec_key = I('spec_key');      
        $user_id = session('user.user_id');
        $c = M('order')->where("order_id = $order_id and user_id = $user_id")->count();
        if($c == 0)
        {
            $this->error('非法操作');
            exit;
        }        
        
        $return_goods = M('return_goods')->where("order_id = $order_id and goods_id = $goods_id  and spec_key = '$spec_key'")->find();            
        if(!empty($return_goods))
        {
            $this->success('已经提交过退货申请!',U('/Seller/order/return_goods_info',array('id'=>$return_goods['id'])));
            exit;
        }       
        if(IS_POST)
        {
            $data['order_id'] = $order_id; 
            $data['order_sn'] = $order_sn; 
            $data['goods_id'] = $goods_id; 
            $data['addtime'] = time(); 
            $data['user_id'] = $user_id;  
            $data['type'] = I('type'); // 服务类型  退货 或者 换货
            $data['reason'] = I('reason'); // 问题描述
            $data['imgs'] = I('imgs'); // 用户拍照的相片
            $data['spec_key'] = I('spec_key'); // 商品规格
            $data['store_id'] = M('order')->where("order_id = $order_id")->getField('store_id'); // 店铺id
            M('return_goods')->add($data);            
            $this->success('申请成功,客服第一时间会帮你处理',U('/Seller/order/indexbuy'));
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
        $user_id = session('user.user_id');
        $count = M('return_goods')->where("user_id = $user_id")->count();
        $page = new Page($count,10);
        $list = M('return_goods')->where("user_id = $user_id")->order("id desc")->limit("{$page->firstRow},{$page->listRows}")->select();
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
    //#########################################

    /*
     * 评论晒单
     */
    public function comment(){
        $user_id = session('user.user_id');
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
     * 评价添加
     */
     public function comment_list(){
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
            redirect(U('Seller/order/comment_list', array('part_finish' => 1, 'order_id' => $order_id, 'store_id' => $store_id)));
        } else {
            redirect(U('Seller/order/comment_list', array('order_id' => $order_id, 'store_id' => $store_id)));
        }
    }
//###################################################
     /**
     *我的收藏
     */
    public function goods_collect()
    {
        $type = I('get.type', 1);
        if ($type == 1) {
            //商品收藏
            $userLogic = new UsersLogic();
            $data = $userLogic->get_goods_collect(session('user.user_id'));
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
//#########################################################
    /**
     * 订单编辑
     * @param int $id 订单id
     */
    public function edit_order(){
        $order_id = I('order_id');
        $orderLogic = new OrderLogic();
        $order = $orderLogic->getOrderInfo($order_id);
        if ($order['store_id'] != STORE_ID) {
            $this->error('该订单不存在',U('Seller/Order/index'));
        }
        if($order['shipping_status'] != 0){
            $this->error('已发货订单不允许编辑');
            exit;
        } 
    
        $orderGoods = $orderLogic->getOrderGoods($order_id);
                
       	if(IS_POST)
        {
            $order['consignee'] = I('consignee');// 收货人
            $order['province'] = I('province'); // 省份
            $order['city'] = I('city'); // 城市
            $order['district'] = I('district'); // 县
            $order['address'] = I('address'); // 收货地址
            $order['mobile'] = I('mobile'); // 手机           
            $order['invoice_title'] = I('invoice_title');// 发票
            $order['admin_note'] = I('admin_note'); // 管理员备注
            $order['admin_note'] = I('admin_note'); //                  
            $order['shipping_code'] = I('shipping');// 物流方式
            $order['shipping_name'] = M('plugin')->where(array('status'=>1,'type'=>'shipping','code'=>I('shipping')))->getField('name');            
            $order['pay_code'] = I('payment');// 支付方式            
            $order['pay_name'] = M('plugin')->where(array('status'=>1,'type'=>'payment','code'=>I('payment')))->getField('name');                            
            $goods_id_arr = I("goods_id");
            $new_goods = $old_goods_arr = array();
            //################################订单添加商品
            if($goods_id_arr){
            	$new_goods = $orderLogic->get_spec_goods($goods_id_arr);
            	foreach($new_goods as $key => $val)
            	{
            		$val['order_id'] = $order_id;
                        $val['store_id'] = STORE_ID;
            		$rec_id = M('order_goods')->add($val);//订单添加商品
            		if(!$rec_id)
            			$this->error('添加失败');
            	}
            }
            
            //################################订单修改删除商品
            $old_goods = I('old_goods');
            foreach ($orderGoods as $val){
            	if(empty($old_goods[$val['rec_id']])){
            		M('order_goods')->where("rec_id=".$val['rec_id'])->delete();//删除商品
            	}else{
            		//修改商品数量
            		if($old_goods[$val['rec_id']] != $val['goods_num']){
            			$val['goods_num'] = $old_goods[$val['rec_id']];
            			M('order_goods')->where("rec_id=".$val['rec_id'])->save(array('goods_num'=>$val['goods_num']));
            		}
            		$old_goods_arr[] = $val;
            	}
            }
            
            $goodsArr = array_merge($old_goods_arr,$new_goods);
            $result = calculate_price($order['user_id'],$goodsArr,$order['shipping_code'],0,$order['province'],$order['city'],$order['district'],0,0,0,0);
            if($result['status'] < 0)
            {
            	$this->error($result['msg']);
            }
       
            //################################修改订单费用
            $order['goods_price']    = $result['result']['goods_price']; // 商品总价
            $order['shipping_price'] = $result['result']['shipping_price'];//物流费
            $order['order_amount']   = $result['result']['order_amount']; // 应付金额
            $order['total_amount']   = $result['result']['total_amount']; // 订单总价           
            $o = M('order')->where("order_id = $order_id and store_id =".STORE_ID)->save($order);
            
			$seller_id = session('seller_id');
            $l = $orderLogic->orderActionLog($order,'edit','修改订单',$seller_id,1);//操作日志
            if($o && $l){
            	$this->success('修改成功',U('Order/editprice',array('order_id'=>$order_id)));
            }else{
            	$this->success('修改失败',U('Order/detail',array('order_id'=>$order_id)));
            }
            exit;
        }
        // 获取省份
        $province = M('region')->where(array('parent_id'=>0,'level'=>1))->select();
        //获取订单城市
        $city =  M('region')->where(array('parent_id'=>$order['province'],'level'=>2))->select();
        //获取订单地区
        $area =  M('region')->where(array('parent_id'=>$order['city'],'level'=>3))->select();
        //获取支付方式
        $payment_list = M('plugin')->where(array('status'=>1,'type'=>'payment'))->select();
        //获取配送方式
        $shipping_list = M('plugin')->where(array('status'=>1,'type'=>'shipping'))->select();
        
        $this->assign('order',$order);
        $this->assign('province',$province);
        $this->assign('city',$city);
        $this->assign('area',$area);
        $this->assign('orderGoods',$orderGoods);
        $this->assign('shipping_list',$shipping_list);
        $this->assign('payment_list',$payment_list);
        $this->display();
    }
    
    /*
     * 拆分订单
     */
    public function split_order(){
    	$order_id = I('order_id');
    	$orderLogic = new OrderLogic();
    	$order = $orderLogic->getOrderInfo($order_id);
        if ($order['store_id'] != STORE_ID) {
            $this->error('该订单不存在',U('Seller/Order/index'));
        }
    	if($order['shipping_status'] != 0){
    		$this->error('已发货订单不允许编辑');
    		exit;
    	}
    	$orderGoods = $orderLogic->getOrderGoods($order_id);
    	if(IS_POST){
    		$data = I('post.');
    		//################################先处理原单剩余商品和原订单信息
    		$old_goods = I('old_goods');
    		foreach ($orderGoods as $val){
    			if(empty($old_goods[$val['rec_id']])){
    				M('order_goods')->where("rec_id=".$val['rec_id'])->delete();//删除商品
    			}else{
    				//修改商品数量
    				if($old_goods[$val['rec_id']] != $val['goods_num']){
    					$val['goods_num'] = $old_goods[$val['rec_id']];
    					M('order_goods')->where("rec_id=".$val['rec_id'])->save(array('goods_num'=>$val['goods_num']));
    				}
    				$oldArr[] = $val;//剩余商品
    			}
    			$all_goods[$val['rec_id']] = $val;//所有商品信息
    		}
    		$result = calculate_price($order['user_id'],$oldArr,array(STORE_ID => $order['shipping_code']),0,$order['province'],$order['city'],$order['district'],0,0,0,0);
    		if($result['status'] < 0)
    		{
    			$this->error($result['msg']);
    		}
    		//修改订单费用
    		$res['goods_price']    = $result['result']['goods_price']; // 商品总价
    		$res['order_amount']   = $result['result']['order_amount']; // 应付金额
    		$res['total_amount']   = $result['result']['total_amount']; // 订单总价
    		M('order')->where("order_id=".$order_id." and store_id =".STORE_ID)->save($res);
			//################################原单处理结束

    		//################################新单处理
    		for($i=1;$i<20;$i++){
    			if(!empty($_POST[$i.'_old_goods'])){
    				$split_goods[] = $_POST[$i.'_old_goods'];//新订单商品
    			}
    		}
    		foreach ($split_goods as $key=>$vrr){
    			foreach ($vrr as $k=>$v){
    				$all_goods[$k]['goods_num'] = $v;
    				$brr[$key][] = $all_goods[$k];
    			}
    		}

    		foreach($brr as $goods){
    			$result = calculate_price($order['user_id'],$goods,array(STORE_ID => $order['shipping_code']),0,$order['province'],$order['city'],$order['district'],0,0,0,0);
    			if($result['status'] < 0)
    			{
    				$this->error($result['msg']);
    			}
    			$new_order = $order;
    			$new_order['order_sn'] = date('YmdHis').mt_rand(1000,9999);
    			$new_order['parent_sn'] = $order['order_sn'];
    			//修改订单费用
    			$new_order['goods_price']    = $result['result']['goods_price']; // 商品总价
    			$new_order['order_amount']   = $result['result']['order_amount']; // 应付金额
    			$new_order['total_amount']   = $result['result']['total_amount']; // 订单总价
    			$new_order['add_time'] = time();
    			unset($new_order['order_id']);
    			$new_order_id = M('order')->add($new_order);//插入订单表
    			foreach ($goods as $vv){
    				$vv['order_id'] = $new_order_id;//新订单order_id
    				unset($vv['rec_id']);
                    $vv['store_id'] = STORE_ID;
    				$nid = M('order_goods')->add($vv);//插入订单商品表
    			}
    		}
    		//################################新单处理结束
    		$this->success('操作成功',U('Order/detail',array('order_id'=>$order_id)));
            exit;
    	}
    	
    	foreach ($orderGoods as $val){
    		$brr[$val['rec_id']] = array('goods_num'=>$val['goods_num'],'goods_name'=>getSubstr($val['goods_name'], 0, 35).$val['spec_key_name']);
    	}
    	$this->assign('order',$order);
    	$this->assign('goods_num_arr',json_encode($brr));
    	$this->assign('orderGoods',$orderGoods);
    	$this->display();
    }
    
    /*
     * 价钱修改
     */
    public function editprice($order_id){
        $orderLogic = new OrderLogic();
        $order = $orderLogic->getOrderInfo($order_id);
        if ($order['store_id'] != STORE_ID) {
            $this->error('该订单不存在',U('Seller/Order/index'));
        }
        $this->editable($order);
        if(IS_POST){        	
            $update['discount'] = I('post.discount');
            $update['shipping_price'] = I('post.shipping_price');
	    $update['order_amount'] = $order['goods_price'] + $update['shipping_price'] - $update['discount'] - $order['user_money'] - $order['integral_money'] - $order['coupon_price'];
            $row = M('order')->where(array('order_id'=>$order_id,'store_id'=>STORE_ID))->save($update);
            if(!$row){
                $this->success('没有更新数据',U('Order/editprice',array('order_id'=>$order_id)));
            }else{
                $this->success('操作成功',U('Order/detail',array('order_id'=>$order_id)));
            }
            exit;
        }
        $this->assign('order',$order);
        $this->display();
    }

    /**
     * 订单删除
     * @param int $id 订单id
     *  store_id   
     */
    public function delete_order($order_id){
    	$orderLogic = new OrderLogic();
    	$del = $orderLogic->delOrder($order_id,STORE_ID);
        if($del){
            $this->success('删除订单成功');
        }else{
        	$this->error('订单删除失败');
        }
    }
    
    /**
     * 订单取消付款
     */
    public function pay_cancel($order_id){
    	if(I('remark')){
    		$data = I('post.');
            $orderLogic = new OrderLogic();
            $order = $orderLogic->getOrderInfo($data['order_id']);
            if ($order['store_id'] != STORE_ID) {
                $this->error('该订单不存在',U('Seller/Order/index'));
            }
    		$note = array('退款到用户余额','已通过其他方式退款','不处理，误操作项');
    		if($data['refundType'] == 0 && $data['amount']>0){
    			accountLog($data['user_id'], $data['amount'], 0,  '退款到用户余额');
    		}
            $orderLogic->orderProcessHandle($data['order_id'],'pay_cancel');
			$seller_id = session('seller_id');
    		$d = $orderLogic->orderActionLog($order,'取消付款',$data['remark'].':'.$note[$data['refundType']],$seller_id,1);
    		if($d){
    			exit("<script>window.parent.pay_callback(1);</script>");
    		}else{
    			exit("<script>window.parent.pay_callback(0);</script>");
    		}
    	}else{
    		$order = M('order')->where("order_id=$order_id")->find();
    		$this->assign('order',$order);
    		$this->display();
    	}
    }

    /**
     * 订单打印
     * @param int $id 订单id
     */
    public function order_print(){
    	$order_id = I('order_id');
        $orderLogic = new OrderLogic();
        $order = $orderLogic->getOrderInfo($order_id);

        if( I('get.buy') ){
            if($order['user_id'] != session('user.user_id')){
                     $this->error('该订单不存在',U('Seller/Order/index'));
            }
        }else{
            if ($order['store_id'] != STORE_ID) {
                $this->error('该订单不存在',U('Seller/Order/index'));
            }
        }
        
        $order['province'] = getRegionName($order['province']);
        $order['city'] = getRegionName($order['city']);
        $order['district'] = getRegionName($order['district']);
        $order['full_address'] = $order['province'].' '.$order['city'].' '.$order['district'].' '. $order['address'];
        $orderGoods = $orderLogic->getOrderGoods($order_id);
        $shop = tpCache('shop_info');
        $this->assign('order',$order);
        $this->assign('shop',$shop);
        $this->assign('orderGoods',$orderGoods);
        $template = I('template','print');
        $this->display($template);
    }

    /**
     * 快递单打印
     */
    public function shipping_print(){
        $order_id = I('get.order_id');
        $orderLogic = new OrderLogic();
        $order = $orderLogic->getOrderInfo($order_id);
        if ($order['store_id'] != STORE_ID) {
            $this->error('该订单不存在',U('Seller/Order/index'));
        }
        //查询是否存在订单及物流
        $shipping = M('plugin')->where(array('code'=>$order['shipping_code'],'type'=>'shipping'))->find();        
        if(!$shipping){
        	$this->error('物流插件不存在');
        }
		if(empty($shipping['config_value'])){
			$this->error('请联系平台管理员设置'.$shipping['name'].'打印模板');
		}
        $shop = M('store')->where(array('store_id'=>STORE_ID))->find();
        $shop['province'] = empty($shop['province_id']) ? '' : getRegionName($shop['province_id']);
        $shop['city'] = empty($shop['city_id']) ? '' : getRegionName($shop['city_id']);
        $shop['district'] = empty($shop['district']) ? '' : getRegionName($shop['district']);

        $order['province'] = getRegionName($order['province']);
        $order['city'] = getRegionName($order['city']);
        $order['district'] = getRegionName($order['district']);
        if(empty($shipping['config'])){
        	$config = array('width'=>840,'height'=>480,'offset_x'=>0,'offset_y'=>0);
        	$this->assign('config',$config);
        }else{
        	$this->assign('config',unserialize($shipping['config']));
        }
        $template_var = array("发货点-名称", "发货点-联系人", "发货点-电话", "发货点-省份", "发货点-城市",
        		 "发货点-区县", "发货点-手机", "发货点-详细地址", "收件人-姓名", "收件人-手机", "收件人-电话", 
        		"收件人-省份", "收件人-城市", "收件人-区县", "收件人-邮编", "收件人-详细地址", "时间-年", "时间-月", 
        		"时间-日","时间-当前日期","订单-订单号", "订单-备注","订单-配送费用");
        $content_var = array($shop['store_name'],$shop['seller_name'],$shop['store_phone'],$shop['province'],$shop['city'],
        	$shop['district'],$shop['store_phone'],$shop['store_address'],$order['consignee'],$order['mobile'],$order['phone'],
        	$order['province'],$order['city'],$order['district'],$order['zipcode'],$order['address'],date('Y'),date('M'),
        	date('d'),date('Y-m-d'),$order['order_sn'],$order['admin_note'],$order['shipping_price'],
        );
        $shipping['config_value'] = str_replace($template_var,$content_var, $shipping['config_value']);
        $this->assign('shipping',$shipping);
        $this->display("Plugin/print_express");
    }

    /**
     * 生成发货单
     */
    public function deliveryHandle(){
        $orderLogic = new OrderLogic();
		$data = I('post.');
		$res = $orderLogic->deliveryHandle($data,STORE_ID);
		if($res){
			$this->success('操作成功',U('Order/delivery_info',array('order_id'=>$data['order_id'])));
		}else{
			$this->success('操作失败',U('Order/delivery_info',array('order_id'=>$data['order_id'])));
		}
    }

    
    public function delivery_info(){
    	$order_id = I('order_id');
    	$orderLogic = new OrderLogic();
    	$order = $orderLogic->getOrderInfo($order_id);
        if ($order['store_id'] != STORE_ID) {
            $this->error('该订单不存在',U('Seller/Order/index'));
        }
    	$orderGoods = $orderLogic->getOrderGoods($order_id);
		$delivery_record = M('delivery_doc')->join('LEFT JOIN __SELLER__ ON __SELLER__.seller_id = __DELIVERY_DOC__.admin_id')->where('order_id='.$order_id)->select();
		if($delivery_record){
			$order['invoice_no'] = $delivery_record[count($delivery_record)-1]['invoice_no'];
		}
		$this->assign('order',$order);
		$this->assign('orderGoods',$orderGoods);
		$this->assign('delivery_record',$delivery_record);//发货记录
    	$this->display();
    }
    
    /**
     * 发货单列表
     */
    public function delivery_list(){
        $this->display();
    }
    /**
     * 退货单列表
     */
    public function return_list(){
        $this->display();
    }	
    /*
     * ajax 退货订单列表
     */
    public function ajax_return_list(){
        // 搜索条件        
        $order_sn =  trim(I('order_sn'));
        $order_by = I('order_by') ? I('order_by') : 'id';
        $sort_order = I('sort_order') ? I('sort_order') : 'desc';
        $status =  I('status','');
        
        $where = " store_id = ".STORE_ID;
        $order_sn && $where.= " and order_sn like '%$order_sn%' ";
        if($status !== '') 
            $where.= " and status = '$status' ";
        $count = M('return_goods')->where($where)->count();
        $Page  = new AjaxPage($count,20);
        $show = $Page->show();
        $list = M('return_goods')->where($where)->order("$order_by $sort_order")->limit("{$Page->firstRow},{$Page->listRows}")->select();        
        $goods_id_arr = get_arr_column($list, 'goods_id');
        if(!empty($goods_id_arr))
            $goods_list = M('goods')->where("goods_id in (".implode(',', $goods_id_arr).")")->getField('goods_id,goods_name');
        $this->assign('goods_list',$goods_list);
        $this->assign('list',$list);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();
    }
    
    /**
     * 删除某个退换货申请
     */
    public function return_del(){
        $id = I('get.id');
        M('return_goods')->where("id = $id and store_id = ".STORE_ID)->delete(); 
        $this->success('成功删除!');
    }
    /**
     * 退换货操作
     */
    public function return_info()
    {
        $id = I('id');
        $return_goods = M('return_goods')->where("id= $id  and store_id = ".STORE_ID)->find();
        empty($return_goods) && $this->error("参数有误");
        
        if($return_goods['imgs'])            
             $return_goods['imgs'] = explode(',', $return_goods['imgs']);
        $user = M('users')->where("user_id = {$return_goods[user_id]}")->find();
        $goods = M('goods')->where("goods_id = {$return_goods[goods_id]}")->find();
        $order_goods = M('order_goods')->where("order_id ={$return_goods['order_id']} and goods_id = {$return_goods['goods_id']} and spec_key = '{$return_goods['spec_key']}'")->find();
        $type_msg = array('退换','换货');
        $status_msg = array('未处理','处理中','已完成');
        if(IS_POST)
        {            
            $data['status'] = I('status');
            $data['remark'] = I('remark');                                    
            $note ="退换货:{$type_msg[$return_goods['type']]}, 状态:{$status_msg[$data['status']]},处理备注：{$data['remark']}";
            $result = M('return_goods')->where("id= $id  and store_id = ".STORE_ID)->save($data);    
            if($result)
            {                    	
                // 如果是换货
                if($return_goods['type'] == 1)
                {
                    $where = " order_id = ".$return_goods['order_id']." and goods_id=".$return_goods['goods_id'];
                    M('order_goods')->where($where)->save(array('is_send'=>2));//更改商品状态                           
                }
                $orderLogic = new OrderLogic();
                $seller_id = session('seller_id');
                $order = $orderLogic->getOrderInfo($return_goods['order_id']);
                $log = $orderLogic->orderActionLog($order,'订单退货',$note,$seller_id,1);
                $this->success('修改成功!');            
                exit;
            }  
        }        
        
        $this->assign('id',$id); // 用户
        $this->assign('user',$user); // 用户
        $this->assign('goods',$goods);// 商品        
        $this->assign('order_goods',$order_goods);// 商品列表
        $this->assign('return_goods',$return_goods);// 退换货               
        $this->display();
    }
    
    /**
     * 管理员生成申请退货单
     */
    public function add_return_goods()
   {                
            $order_id = I('order_id'); 
            $goods_id = I('goods_id');
                
            $return_goods = M('return_goods')->where("order_id = $order_id and goods_id = $goods_id")->find();
            if(!empty($return_goods))
            {
                $this->error('已经提交过退货申请!',U('Admin/Order/return_list'));
                exit;
            }
            $order = M('order')->where("order_id = $order_id")->find();
            
            $data['order_id'] = $order_id; 
            $data['order_sn'] = $order['order_sn']; 
            $data['goods_id'] = $goods_id; 
            $data['addtime'] = time(); 
            $data['user_id'] = $order[user_id];            
            $data['remark'] = '管理员申请退换货'; // 问题描述            
            M('return_goods')->add($data);            
            $this->success('申请成功,现在去处理退货',U('Admin/Order/return_list'));
            exit;
    }

    /**
     * 订单操作
     * @param $id
     */
    public function order_action(){    	
        $orderLogic = new OrderLogic();
        $action = I('get.type');
        $order_id = I('get.order_id');
        if($action && $order_id){
        	 $a = $orderLogic->orderProcessHandle($order_id,$action,STORE_ID);
			 $seller_id = session('seller_id');
			 $order = $orderLogic->getOrderInfo($order_id);			 
        	 $res = $orderLogic->orderActionLog($order,$action,I('note'),$seller_id,1);
        	 if($res && $a){
        	 	exit(json_encode(array('status' => 1,'msg' => '操作成功')));
        	 }else{
        	 	exit(json_encode(array('status' => 0,'msg' => '操作失败')));
        	 }
        }else{
        	$this->error('参数错误',U('Admin/Order/detail',array('order_id'=>$order_id)));
        }
    }
    
    public function order_log(){
    	$timegap = I('timegap');
    	if($timegap){
    		$gap = explode('-', $timegap);
    		$begin = strtotime($gap[0]);
    		$end = strtotime($gap[1]);
    	}
    	$condition = array();
    	$log =  M('order_action');
    	if($begin && $end){
    		$condition['log_time'] = array('between',"$begin,$end");
    	}
    	$admin_id = I('admin_id');
		if($admin_id >0 ){
			$condition['action_user'] = $admin_id;
		}
		$condition['store_id'] = STORE_ID;
    	$count = $log->where($condition)->count();
    	$Page = new \Think\Page($count,20);
    	foreach($condition as $key=>$val) {
    		$Page->parameter[$key] = urlencode($val);
    	}
    	$show = $Page->show();
    	$list = $log->where($condition)->order('action_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
    	$this->assign('list',$list);
    	$this->assign('page',$show);   	
    	$seller = M('seller')->getField('seller_id,seller_name');
    	$this->assign('admin',$seller);    	
    	$this->display();
    }

    /**
     * 检测订单是否可以编辑
     * @param $order
     */
    private function editable($order){
        if($order['shipping_status'] != 0){
            $this->error('已发货订单不允许编辑');
            exit;
        }
        return;
    }

    /**
     * 数据查询处理 jinjun 有改动 
     */
    public function export_order()
    {
    	//搜索条件 
                    $where = "where store_id =".STORE_ID." ";
                     
		
		$consignee = I('consignee');
		if($consignee){
			$where .= " AND consignee like '%$consignee%' ";
		}
		$order_sn =  I('order_sn');
		if($order_sn){
			$where .= " AND order_sn = '$order_sn' ";
		}
		if(I('order_status')){
			$where .= " AND order_status = ".I('order_status');
		}
		$timegap = I('timegap');
		if($timegap){
			$gap = explode('-', $timegap);
			$begin = strtotime($gap[0]);
			$end = strtotime($gap[1]);
			$where .= " AND add_time>$begin and add_time<$end";
		}
	$region	= M('region')->getField('id,name');
                
	$sql = "select *,FROM_UNIXTIME(add_time,'%Y-%m-%d') as create_time from __PREFIX__order $where order by order_id";
    	$orderList = D()->query($sql);   
    	$strTable ='<table width="500" border="1">';
    	$strTable .= '<tr>';
    	$strTable .= '<td style="text-align:center;font-size:12px;width:120px;">订单编号</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="100">日期</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">收货人</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">收货地址</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">电话</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">订单金额</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">实际支付</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">支付方式</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">支付状态</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">发货状态</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">商品信息</td>';
    	$strTable .= '</tr>';
    	
    	foreach($orderList as $k=>$val){
    		$strTable .= '<tr>';
    		$strTable .= '<td style="text-align:center;font-size:12px;">&nbsp;'.$val['order_sn'].'</td>';
    		$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['create_time'].' </td>';
    		$strTable .= '<td style="text-align:left;font-size:12px;">'."{$region[$val['province']]},{$region[$val['city']]},{$region[$val['district']]},{$region[$val['twon']]}{$val['consignee']}".' </td>';
    		$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['address'].'</td>';
    		$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['mobile'].'</td>';
    		$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['goods_price'].'</td>';
    		$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['order_amount'].'</td>';
    		$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['pay_name'].'</td>';
    		$strTable .= '<td style="text-align:left;font-size:12px;">'.$this->pay_status[$val['pay_status']].'</td>';
    		$strTable .= '<td style="text-align:left;font-size:12px;">'.$this->shipping_status[$val['shipping_status']].'</td>'; 
    		$orderGoods = D('order_goods')->where('order_id='.$val['order_id'])->select();
    		$strGoods="";
    		foreach($orderGoods as $goods){
    			$strGoods .= "商品编号：".$goods['goods_sn']." 商品名称：".$goods['goods_name'];
    			if ($goods['spec_key_name'] != '') $strGoods .= " 规格：".$goods['spec_key_name'];
    			$strGoods .= "<br />";
    		}
    		unset($orderGoods);
    		$strTable .= '<td style="text-align:left;font-size:12px;">'.$strGoods.' </td>';
    		$strTable .= '</tr>';
    	}
    	$strTable .='</table>';
    	unset($orderList);
    	downloadExcel($strTable,'order');
    	exit();
    }
   
    /**
     * 添加一笔订单
     */
    public function add_order()
    {
        $order = array('store_id'=>STORE_ID);
        //  获取省份
        $province = M('region')->where(array('parent_id'=>0,'level'=>1))->select();
        //  获取订单城市
        $city =  M('region')->where(array('parent_id'=>$order['province'],'level'=>2))->select();
        //  获取订单地区
        $area =  M('region')->where(array('parent_id'=>$order['city'],'level'=>3))->select();
        //  获取配送方式
        $shipping_list = M('plugin')->where(array('status'=>1,'type'=>'shipping'))->select();
        //  获取支付方式
        $payment_list = M('plugin')->where(array('status'=>1,'type'=>'payment'))->select();
        if(IS_POST)
        {
            $order['user_id'] = I('user_id');// 用户id 可以为空
            $order['consignee'] = I('consignee');// 收货人
            $order['province'] = I('province'); // 省份
            $order['city'] = I('city'); // 城市
            $order['district'] = I('district'); // 县
            $order['address'] = I('address'); // 收货地址
            $order['mobile'] = I('mobile'); // 手机           
            $order['invoice_title'] = I('invoice_title');// 发票
            $order['admin_note'] = I('admin_note'); // 管理员备注            
            $order['order_sn'] = date('YmdHis').mt_rand(1000,9999); // 订单编号;
            $order['admin_note'] = I('admin_note'); // 
            $order['add_time'] = time(); //                    
            $order['shipping_code'] = I('shipping');// 物流方式
            $order['shipping_name'] = M('plugin')->where(array('status'=>1,'type'=>'shipping','code'=>I('shipping')))->getField('name');            
            $order['pay_code'] = I('payment');// 支付方式            
            $order['pay_name'] = M('plugin')->where(array('status'=>1,'type'=>'payment','code'=>I('payment')))->getField('name');            
                            
            $goods_id_arr = I("goods_id");
            $orderLogic = new OrderLogic();
            $order_goods = $orderLogic->get_spec_goods($goods_id_arr);          
            $result = calculate_price($order['user_id'],$order_goods, array(STORE_ID => $order['shipping_code']), 0,$order[province],$order[city],$order[district],0,0,0,0);      
            if($result['status'] < 0)	
            {
                 $this->error($result['msg']);      
            } 
           
           $order['goods_price']    = $result['result']['goods_price']; // 商品总价
           $order['shipping_price'] = $result['result']['store_shipping_price'][STORE_ID]; //物流费
           $order['order_amount']   = $result['result']['order_amount']; // 应付金额
           $order['total_amount']   = $result['result']['total_amount']; // 订单总价
           
            // 添加订单
            $order_id = M('order')->add($order);
            if($order_id)
            {
                foreach($order_goods as $key => $val)
                {
                    $val['order_id'] = $order_id;
                    $val['store_id'] = STORE_ID;
                    $rec_id = M('order_goods')->add($val);
                    if(!$rec_id)                 
                        $this->error('添加失败');                                  
                }
                $this->success('添加商品成功',U("Order/detail",array('order_id'=>$order_id)));
                exit();
            }
            else{
                $this->error('添加失败');
            }                
        }     
        $this->assign('shipping_list',$shipping_list);
        $this->assign('payment_list',$payment_list);
        $this->assign('province',$province);
        $this->assign('city',$city);
        $this->assign('area',$area);        
        $this->display();
    }
    
    /**
     * 选择搜索商品
     */
    public function search_goods()
    {
    	$brandList =  M("brand")->select();
    	$categoryList =  M("goods_category")->select();
    	$this->assign('categoryList',$categoryList);
    	$this->assign('brandList',$brandList);   	
    	$where = ' store_id = '.STORE_ID.' and  is_on_sale = 1 ';//搜索条件
    	I('intro')  && $where = "$where and ".I('intro')." = 1";
        
        $cat_id = I('cat_id');
    	if($cat_id){    	    
            $goods_category = M('goods_category')->where("id = $cat_id")->find();
            $where = " $where  and cat_id{$goods_category['level']} = $cat_id "; // 初始化搜索条件
            $this->assign('cat_id',$cat_id);
    	}
        if(I('brand_id')){
            $this->assign('brand_id',I('brand_id'));
            $where = "$where and brand_id = ".I('brand_id');
        }
    	if(!empty($_REQUEST['keywords']))
    	{
    		$this->assign('keywords',I('keywords'));
    		$where = "$where and (goods_name like '%".I('keywords')."%' or keywords like '%".I('keywords')."%')" ;
    	}  	
    	$goodsList = M('goods')->where($where)->order('goods_id DESC')->limit(10)->select();
                
        foreach($goodsList as $key => $val)
        {
            $spec_goods = M('spec_goods_price')->where("goods_id = {$val['goods_id']}")->select();
            $goodsList[$key]['spec_goods'] = $spec_goods;            
        }
        
        $store_bind_class = M('store_bind_class')->where("store_id =".STORE_ID)->select();
        $cat_id1 = get_arr_column($store_bind_class, 'class_1');
        $cat_id2 = get_arr_column($store_bind_class, 'class_2');
        $cat_id3 = get_arr_column($store_bind_class, 'class_3');
        $cat_id0 = array_merge($cat_id1,$cat_id2,$cat_id3);
        
        $this->assign('cat_id0',$cat_id0);
    	$this->assign('goodsList',$goodsList);
    	$this->display();        
    }
    
    public function ajaxOrderNotice(){
        $order_amount = M('order')->where(array('order_status'=>0))->count();
        echo $order_amount;
    }
}
