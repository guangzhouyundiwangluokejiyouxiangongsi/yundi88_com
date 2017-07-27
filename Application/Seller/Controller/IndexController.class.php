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
 * Date: 2016-06-09
 */
namespace Seller\Controller;
use Think\Page;

class IndexController extends BaseController
{
    public function _initialize()
    {
       
        define('STORE_ID',session('store_id')); //将当前的session_id保存为常量，供其它方法调用

         //判断有没有创建官网
         $store_status = M('store')->where(array('store_id'=>session('store_id')))->getField('status');
        $this->assign('status',$store_status);
         // dump($store_status);exit;
         if($store_status != 2){
                    //判断条件给默认数据
                    $seller = M('seller')->where(array('store_id'=>session('store_id')))->getField('num');
                    if(!$seller){
                        $shanghui = I('shanghui',0);
                        $this->redirect('Seller/Store/initdata',array('shanghui'=>$shanghui));
                    }
         }
                   


    }


    public function index(){

        $this->pushVersion();
        $seller = session('seller');
        $menu_list = $this->getSellerMenuList($seller['is_admin'],$seller['act_limits']);
        $this->assign('menu_list',$menu_list['seller_menu']);
        $this->assign('seller',$seller);
        $this->display();
    }

    public function welcome(){
    	// $seller = session('seller');
    	// $count['handle_order'] = M('order')->where("store_id = ".STORE_ID.C('WAITSEND'))->count();//待处理订单
    	// $order_list =  M('order')->where("store_id = ".STORE_ID." and add_time>".strtotime("-7 day"))->select();//最近7天订单统计
    	// $count['wait_shipping'] = $count['wait_pay'] = $count['wait_confirm'] = $count['refund_pay'] = 0;
    	// $count['refund_goods'] = $count['part_shipping'] = $count['order_sum'] = 0;
    	// $count['refund_pay'] = M('return_goods')->where("store_id = ".STORE_ID." and type=0")->count();
    	// $count['refund_goods'] = M('return_goods')->where("store_id = ".STORE_ID." and type=1")->count();
    	// if($order_list){
    	// 	$count['order_sum'] = count($order_list);
    	// 	foreach ($order_list as $v){
    	// 		if($v['order_status']== 1 && $v['shipping_status']== 0){
    	// 			$count['wait_shipping']++;
    	// 		}else if($v['pay_status'] ==  0){
    	// 			$count['wait_pay']++;
    	// 		}else if($v['order_status'] == 0){
    	// 			$count['wait_confirm']++;
    	// 		}else if($v['shipping_status'] == 1){
    	// 			$count['part_shipping']++;
    	// 		}
    	// 	}
    	// }

    	// $store_goods = M('goods')->where(array('store_id'=>STORE_ID))->select();
    	// $count['goods_sum'] = $count['pass_goods'] = $count['warning_goods'] = $count['new_goods'] = 0;
    	// $count['prom_goods'] = $count['off_sale_goods']  = $count['below_goods'] = $count['verify_goods'] = 0;
    	// if($store_goods){
    	// 	$count['goods_sum'] =  count($store_goods);//商品总数
    	// 	foreach ($store_goods as $k=>$val){
    	// 		if($val['goods_state'] == 0 ){
    	// 			$count['verify_goods']++;
    	// 		}else if($val['goods_state'] == 1){
    	// 			$count['pass_goods']++;
    	// 		}else if($val['goods_state'] == 2){
    	// 			$count['below_goods']++;
    	// 		}else if($val['is_on_sale'] == 0){
    	// 			$count['off_sale_goods']++;
    	// 		}else if($val['prom_id'] >0){
    	// 			$count['prom_goods']++;
    	// 		}else if($val['store_count']<10){
    	// 			$count['warning_goods']++;
    	// 		}else if($val['is_new'] == 1){
    	// 			$count['new_goods']++;
    	// 		}
    	// 	}
    	// }

    	// $count['article'] =  M('article')->where(array('store_id'=>STORE_ID))->count();//文章总数
    	// $users = M('users')->where(array('user_id'=>$seller['user_id']))->find();
    	// $seller['user_name'] = empty($users['email']) ? $users['mobile'] : $users['email'];
    	// //今天销售总额
    	// $today = strtotime("-1 day");
    	// $count['total_amount'] = M('order')->where("store_id = ".STORE_ID." and add_time>$today and pay_status=1")->sum('order_amount');
    	// $count['comment'] = M('comment')->where("is_show=0 and store_id=".STORE_ID)->count();//最新评论
    	// $count['consult'] = M('goods_consult')->where("is_show=0 and store_id=".STORE_ID)->count();//最新咨询
    	// $this->assign('count',$count);
    	// $store = M('store')->where(array('store_id'=>STORE_ID))->find();
    	// $this->assign('store',$store);
    	// $this->assign('seller',$seller);
        
        $lunbo = M('lunbo')->where(array('id'=>1))->getField('store_lunbo');
        $lunbo = explode(',', $lunbo);
        $this->assign('lunbo',$lunbo);
        $this->display();
    }

    /**
     * 商家查看消息
     */
    public function store_msg(){
        $where = "store_id=".STORE_ID;
        $count = M('store_msg')->where($where)->count();
        $Page  = new Page($count,10);
        $show = $Page->show();

    	$msg_list = M('store_msg')->where($where)->order('sm_id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
    	$this->assign('msg_list',$msg_list);
        $this->assign('page',$show);// 赋值分页输出
    	$this->display();
    }
    /**
     * 删除操作
     */
    public function del_store_msg()
    {
        $sm_id = I('sm_id',0);
        $where = "sm_id in($sm_id) and store_id=".STORE_ID;
        M('store_msg')->where($where)->delete();
        $this->success('操作成功!');
    }
    /**
     * 消息批量操作
     */
    public function store_msg_batch()
    {
        $action = I('action',0);
        $sm_id = I('sm_id');

        // 如果是标记已读
        if($action == 'del' && !empty($sm_id))
        {
            $where = "sm_id in(".  implode(',',$sm_id).") and store_id=".STORE_ID;
            M('store_msg')->where($where)->delete();
        }
        // 如果是标记已读
        if($action == 'open' && !empty($sm_id))
        {
            $where = "sm_id in(".  implode(',',$sm_id).") and store_id=".STORE_ID;
            M('store_msg')->where($where)->save(array('open'=>1));
        }
        $this->success('操作成功!');
    }

    /**
     *  添加修改客服
     */
    public function store_service(){

        // post提交
        if(IS_POST)
        {
            $pre = I('pre');
            $after = I('after');
            $working_time = I('working_time');
            foreach ($pre['name'] as $key => $v1) {
                $arr[$key]['name'] = $v1;

            }
            foreach ($pre['type'] as $key => $v2) {
                $arr[$key]['type'] = $v2;

            }
            foreach ($pre['account'] as $key => $v3) {
                $arr[$key]['account'] = $v3;
            }
            foreach ($arr as $kk => $vv) {
               foreach ($vv as $ko => $vo) {
                   if ($vo['name'] == '' || $vo['account'] == '') unset($arr[$kk]);
               }
            }
            $data = array(
                'store_presales'=>serialize($arr),
                'store_aftersales'=>serialize($after),
                'store_workingtime'=>$working_time,
            );

            M('store')->where(array('store_id'=>STORE_ID))->save($data);
            $this->success('修改成功');
            exit();
        }
        //
        $store = M('store')->where("store_id = ".STORE_ID)->find();
        $store['store_presales']    = unserialize($store['store_presales']);
        $store['store_aftersales']  = unserialize($store['store_aftersales']);
        $this->assign('store',$store);
    	$this->display();
    }

    public function pushVersion()
    {
        if(!empty($_SESSION['isset_push']))
            return false;
        $_SESSION['isset_push'] = 1;
        error_reporting(0);//关闭所有错误报告
        $app_path = dirname($_SERVER['SCRIPT_FILENAME']).'/';
        $version_txt_path = $app_path.'/Application/Admin/Conf/version.txt';
        $curent_version = file_get_contents($version_txt_path);

        $vaules = array(
                'domain'=>$_SERVER['SERVER_NAME'],
                'last_domain'=>$_SERVER['SERVER_NAME'],
                'key_num'=>$curent_version,
                'install_time'=>INSTALL_DATE,
                'cpu'=>'0001',
                'mac'=>'0002',
                'serial_number'=>SERIALNUMBER,
         );
         $url = "http://service.tp".'-'."shop".'.'."cn/index.php?m=Home&c=Index&a=user_push&".http_build_query($vaules);
         stream_context_set_default(array('http' => array('timeout' => 3)));
         file_get_contents($url);
    }

    /**
     * ajax 修改指定表数据字段  一般修改状态 比如 是否推荐 是否开启 等 图标切换的
     * table,id_name,id_value,field,value
     */
    public function changeTableVal(){
            $table = I('table'); // 表名
            $id_name = I('id_name'); // 表主键id名
            $id_value = I('id_value'); // 表主键id值
            $field  = I('field'); // 修改哪个字段
            $value  = I('value'); // 修改字段值
            M($table)->where("$id_name = $id_value and store_id = ".STORE_ID)->save(array($field=>$value)); // 根据条件保存修改的数据
    }

    private function _getSellerFunctionList($menu_list) {
    	$format_menu = array();
    	foreach ($menu_list as $key => $menu_value) {
    		foreach ($menu_value['child'] as $submenu_value) {
    			$format_menu[$submenu_value['act']] = array(
    					'model' => $key,
    					'model_name' => $menu_value['name'],
    					'name' => $submenu_value['name'],
    					'act' => $submenu_value['act'],
    					'op' => $submenu_value['op'],
    			);
    		}
    	}
    	return $format_menu;
    }

    protected function getSellerMenuList($is_admin, $limits) {
    	$seller_menu = array();
    	// if (intval($is_admin) !== 1) {
    	// 	$menu_list = permissions();
     //        $arr_limits =  explode(',', $limits);
     //        foreach ($menu_list as $key => $value) {

     //            foreach ($value['child'] as $child_key => $child_value) {
     //                if (!in_array($child_value['act'].'@'.$child_value['op'], $arr_limits)) {
     //                    unset($menu_list[$key]['child'][$child_key]);
     //                }

     //            }

    	// 		if(count($menu_list[$key]['child']) > 0) {
    	// 			$seller_menu[$key] = $menu_list[$key];

    	// 		}
    	// 	}

     //        // echo $child_key;


    	// } else {
        $status =  M('store')->where(array('store_id'=>session('store_id')))->getField('status');
        if ($status == 2) {
            $seller_menu = getMenuList_();
        }else{
            $seller_menu = getMenuList();
        }

    	// }
    	$seller_function_list = $this->_getSellerFunctionList($seller_menu);
        // exit;
    	return array('seller_menu' => $seller_menu, 'seller_function_list' => $seller_function_list);
    }

    private function _getCurrentMenu($seller_function_list) {
    	$current_menu = $seller_function_list[$_GET['act']];
    	if(empty($current_menu)) {
    		$current_menu = array(
    				'model' => 'index',
    				'model_name' => '首页'
    		);
    	}
    	return $current_menu;
    }

    /*
     * 获取商品分类
     */
    public function get_category(){
        $parent_id = I('get.parent_id',0); // 商品分类 父id
        empty($parent_id) && exit('');
        $list = M('goods_category')->where(array('parent_id'=>$parent_id))->select();
        // 店铺id
         $store_id = session('store_id');
        //如果店铺登录了
        if($store_id)
        {
            $store = M('store')->where("store_id = $store_id")->find();

            if($store['bind_all_gc'] == 0)
            {
                $class_id1 = M('store_bind_class')->where("store_id = $store_id and state = 1")->getField('class_1',true);
                $class_id2 = M('store_bind_class')->where("store_id = $store_id and state = 1")->getField('class_2',true);
                $class_id3 = M('store_bind_class')->where("store_id = $store_id and state = 1")->getField('class_3',true);
                $class_id = array_merge($class_id1,$class_id2,$class_id3);
                $class_id = array_unique($class_id);
            }
        }
        foreach($list as $k => $v)
        {
            // 如果是某个店铺登录的, 那么这个店铺只能看到自己申请的分类,其余的看不到
            if($class_id && !in_array($v['id'],$class_id))
                continue;
            $html .= "<option value='{$v['id']}' rel='{$v['commission']}'>{$v['name']}</option>";
        }

        exit($html);
    }


    /**
     * 留言列表
     */
    public function store_email()
    {

        $message = M('store_message');
        $count = $message->where(array('store_id'=>session('store_id')))->count();
        $num        = 10;//每页显示条数
        $number     = ceil($count / $num);//页数
        $page       = new \Think\Page($count,$num);//
        $show       = $page->show();// 分页显示输出
        $messages    = $message->where(array('store_id'=>session('store_id')))->limit($page->firstRow.','.$page->listRows)->select();
        $this->assign('show',$show);//分页
        $this->assign('count',$count);
        $this->assign('messages',$messages);
        $this->display();

    }

    /**
     *  留言页面的banner
     */
     public function store_mes(){
        if (IS_POST){
            $mes_banner = I('mes_banner','');
            if ($mes_banner == ''){
                $this->error('您未选择图片,请重新操作');
                return;
            }
            $data['mes_banner'] = $mes_banner;
            $res = M('store')->where(array('store_id'=>session('store_id')))->save($data);
            if ($res){
                $this->success('图片保存成功');
                return;
            } else {
                $this->error('图片保存失败');
                return;
            }
        }
        $store = M('store')->where(array('store_id'=>session('store_id')))->find();
        $this->assign('mes_banner',$store['mes_banner']);
        $this->display();
     }

    //  留言询盘
    public function store_inquiry()
    {
        $data = M('store_inquiry')->where(array('store_id' => session('store_id')))->select();
        $this->assign('data', $data);
        $this->display();
    }

    public function delItem()
    {
        $res = M('store_inquiry')->where(array('id' => I('post.id')))->delete();
        if ($res) {
            $this->ajaxReturn(1);
        } else {
            $this->ajaxReturn(0);
        }
    }


     public function delete_mes(){
        $data['mes_banner'] = '';
        $res = M('store')->where(array('store_id'=>session('store_id')))->save($data);
        if ($res){
            $this->ajaxReturn(true);
        } else {
            $this->ajaxReturn(false);
        }
     }

    /**
     * 删除留言
     */
    public function message_delete()
    {
            $id = (int)I('id',0);
            $message = M('store_message');
            $res = $message->where(array('id'=>$id,'store_id'=>session('store_id')))->delete();
            if($res){
                $this->success('操作成功');
            }else{
                $this->error('删除失败！');
            }

    }

}
