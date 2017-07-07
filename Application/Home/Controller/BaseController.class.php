<?php

namespace Home\Controller;
use Think\Controller;
class BaseController extends Controller {
    public $session_id;
    public $cateTrre = array();
    /*
     * 初始化操作
     */
    public function _initialize() {  
      // 如果是手机跳转到 手机模块
        if(true == isMobile()){
            header("Location: /Mobile".$_SERVER['REQUEST_URI']);
        }

    	$this->session_id = session_id(); // 当前的 session_id
        define('SESSION_ID',$this->session_id); //将当前的session_id保存为常量，供其它方法调用
        // 判断当前用户是否手机                
        if(isMobile())
            cookie('is_mobile','1',3600); 
        else 
            cookie('is_mobile','0',3600);
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
                
              // header("Location: ".U('seller/Index/index'));
              // exit;
          }
        }
                $where['url']  = array('like', '%/'.CONTROLLER_NAME.'/'.ACTION_NAME.'%');
                $data = M('navigation')->field('title,keywords,description')->where($where)->find();
                // dump(M()->getLastSQL());
                // dump($data);exit;
                if($data){
                $this->assign('title',$data['title']);
                $this->assign('keywords',$data['keywords']);
                $this->assign('description',$data['description']);
                }
                  
        $this->public_assign(); 
    }
    /**
     * 保存公告变量到 smarty中 比如 导航 
     */
    public function public_assign()
    {
        
       $tpshop_config = array();
       $tp_config = M('config')->cache(true,TPSHOP_CACHE_TIME)->select();       
       foreach($tp_config as $k => $v)
       {
       	  if($v['name'] == 'hot_keywords'){
       	  	 $tpshop_config['hot_keywords'] = explode('|', $v['value']);
       	  }       	  
          $tpshop_config[$v['inc_type'].'_'.$v['name']] = $v['value'];
       }                        
       
       $goods_category_tree = get_goods_category_tree();    
       $this->cateTrre = $goods_category_tree;
       $this->assign('goods_category_tree', $goods_category_tree);                     
       $brand_list = M('brand')->cache(true,TPSHOP_CACHE_TIME)->field('id,cat_id1,logo,is_hot')->where("cat_id1>0")->select();              
       $this->assign('brand_list', $brand_list);
       $this->assign('tpshop_config', $tpshop_config);

    }  


    // public function _after_index()
    // {
    //    DUMP($_SERVER['PATH_INFO']);
    // }


}