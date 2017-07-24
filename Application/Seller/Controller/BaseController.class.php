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
use Think\Controller;
use Admin\Logic\UpgradeLogic;
class BaseController extends Controller {

    /**
     * 析构函数
     */
    function __construct()
    {
        parent::__construct();
        $upgradeLogic = new UpgradeLogic();
        $upgradeMsg = $upgradeLogic->checkVersion(); //升级包消息
        $this->assign('upgradeMsg',$upgradeMsg);
        //用户中心面包屑导航
        $navigate_admin = navigate_admin();
        $navigate_admin['后台首页'] = '/seller/index/welcome';
        // $this->assign('navigate_admin',$navigate_admin);
        tpversion();
   }

    /*
     * 初始化操作
     */
    public function _initialize()
    {
        $this->quanxian(__SELF__);
        $this->assign('action',ACTION_NAME);
        //过滤不需要登陆的行为
        if(in_array(ACTION_NAME,array('login','logout','vertify')) || in_array(CONTROLLER_NAME,array('Ueditor','Uploadify'))){
        	return;
        }else{
        	if(session('seller_id') > 0 && session('user') != '' && session('store_id') > 0 && session('seller') !=''){
                define('STORE_ID',session('store_id')); //将当前的session_id保存为常量，供其它方法调用
        		$this->check_priv();//检查管理员菜单操作权限
        	}else{
                echo "<script>top.location.href='http://".$_SERVER['HTTP_HOST']."/User/login.html'</script>";
                exit;
        	}
        }
        // $store_art = M('store')->where(array('store_id'=>session('store_id')))->getField('status');
        // if ($store_art == 2) $this->redirect('/Seller/Storetwo/index');
        $this->public_assign();
    }


    public function quanxian($url='/index.php/Seller/Index/index')
    {

        $url = str_replace('/', '_', $url);
        $status =  M('store')->where(array('store_id'=>session('store_id')))->getField('status');
        // $status = 2;
        // echo __SELF__;
        if($status == 2){
            // echo $url;exit;
            $ajax = (IS_AJAX)?1:null;
            $arr = array(
                '_index.php_Seller_Sellerstore_addpr',
                '_index.php_Seller_Sellerstore_prList',
                '_index.php_Seller_Sellerstore_addinfo',
                '_index.php_Seller_Store_store_setting',
                '_index.php_Seller_Newjoin_basic',
                '_index.php_Seller_Newjoin_basic_info'
            );
            $s = "/_index\.php_Seller_Sellerstore_addEditGoods_goods_id_.*?/";
            $s2 = '/_index.php_Seller_Sellerstore_infolist.*?/';
            $s3 = '/_index.php_Seller_Sellerstore_uddinfo.*?/';
            // $s = array('/_index\.php\?m=Seller&c=Sellerstore&a=ajaxPrList&p=/','/_index\.php_Seller_Sellerstore_uddinfo_id/');

                if(!in_array($url,$arr) && !preg_match($s,$url) && !preg_match($s2,$url) && !preg_match($s3,$url) && !$ajax){
                    echo "<script>alert('没有权限！');top.location='http://".$_SERVER['HTTP_HOST']."/seller/index/index';</script>";
                }

        }

    }


    /**
     * 保存公告变量到 smarty中 比如 导航
     */
    public function public_assign()
    {
       $tpshop_config = array();
       $tp_config = M('config')->select();
       foreach($tp_config as $k => $v)
       {
          $tpshop_config[$v['inc_type'].'_'.$v['name']] = $v['value'];
       }
       $this->assign('tpshop_config', $tpshop_config);
    }

    public function check_priv()
    {
        // dump(session());exit;
    	$seller = session('seller');
         $seller['act_limits'] = M('seller_group')->where(array('store_id'=>session('store_id')))->getField('act_limits');
    	if($seller['is_admin'] == 0){
    		$ctl = CONTROLLER_NAME;
    		$act = ACTION_NAME;
    		$act_list = $seller['act_limits'];
    		//无需验证的操作
    		$uneed_check = array('login','logout','vertifyHandle','vertify','imageUp','upload','login_task');
    		if($ctl == 'Index' || $act_list == 'all'){
    			//后台首页控制器无需验证,超级管理员无需验证
    			return true;
    		}elseif(strpos('ajax',$act) || in_array($act,$uneed_check)){
    			//所有ajax请求不需要验证权限
    			return true;
    		}else{
    			$role_right = explode(',', $act_list);
                foreach($role_right as $v){
                    $a = explode('@', $v);
                    $arr[] = $a[1];
                }
                // dump($arr);exit;



    			//检查是否拥有此操作权限
    			if(!in_array($ctl.'@'.$act, $role_right)){
    				$this->error('您没有操作权限,请联系店铺超级管理员分配权限',U('Index/welcome'));
    			}


    		}
    	}
    	return true;
    }

}
