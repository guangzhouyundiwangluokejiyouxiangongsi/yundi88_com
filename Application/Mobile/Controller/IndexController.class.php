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
 * $Author: 当燃 2016-01-09
 */ 
namespace Mobile\Controller;

use Mobile\Model\StoreModel;
use Think\AjaxPage;
use Think\Page;
class IndexController extends MobileBaseController {

    public function index(){                
        /*
            //获取微信配置
            $wechat_list = M('wx_user')->select();
            $wechat_config = $wechat_list[0];
            $this->weixin_config = $wechat_config;        
            // 微信Jssdk 操作类 用分享朋友圈 JS            
            $jssdk = new \Mobile\Logic\Jssdk($this->weixin_config['appid'], $this->weixin_config['appsecret']);
            $signPackage = $jssdk->GetSignPackage();              
            print_r($signPackage);
        */

        $nav = M('navigation')->where(array('is_show' => 1, 'sn_is_show' => 1))->field('name,url')->select();
        // dump($nav);exit;
        foreach($nav as &$v){
            $v['url'] = '/Mobile'.$v['url'];
        }
        $this->assign('nav',$nav);
        $hot_goods = M('goods')->where("is_hot=1 and is_on_sale=1")->order('goods_id DESC')->limit(20)->cache(true,TPSHOP_CACHE_TIME)->select();//首页热卖商品
        $thems = M('goods_category')->where('level=1')->order('sort_order')->limit(9)->cache(true,TPSHOP_CACHE_TIME)->select();
        $this->assign('thems',$thems);
        $this->assign('hot_goods',$hot_goods);
        $favourite_goods = M('goods')->where("is_recommend=1 and is_on_sale=1")->order('goods_id DESC')->limit(20)->cache(true,TPSHOP_CACHE_TIME)->select();//首页推荐商品
        $this->assign('favourite_goods',$favourite_goods);
        $this->display();
    }

    public function new_goods(){
        $p = I('p',1);
        $goods_list = M('goods')->field('*,IFNULL(commerce_state,0) + IFNULL(apply_state,0) as num')->join('INNER JOIN __STORE__ ON __STORE__.store_id = __GOODS__.store_id')->where(array('is_on_sale'=>1,'goods_state'=>1,'home_is_show'=>1,'is_new'=>1))->order('num desc,commerce_state desc,apply_state desc,on_time desc')->page($p,10)->select();
        $this->assign('goods_list',$goods_list);
        $this->display('ajaxgoods');
    }

    public function recommend_goods(){
        $p = I('p',1);
        $goods_list = M('goods')->field('*,IFNULL(commerce_state,0) + IFNULL(apply_state,0) as num')->join('INNER JOIN __STORE__ ON __STORE__.store_id = __GOODS__.store_id')->where(array('is_on_sale'=>1,'goods_state'=>1,'home_is_show'=>1,'is_recommend'=>1))->order('num desc,commerce_state desc,apply_state desc,on_time desc')->page($p,10)->select();
        $this->assign('goods_list',$goods_list);
        $this->display('ajaxgoods');
    }

    public function hot_goods(){
        $p = I('p',1);
        $goods_list = M('goods')->field('*,IFNULL(commerce_state,0) + IFNULL(apply_state,0) as num')->join('INNER JOIN __STORE__ ON __STORE__.store_id = __GOODS__.store_id')->where(array('is_on_sale'=>1,'goods_state'=>1,'home_is_show'=>1,'is_hot'=>1))->order('num desc,commerce_state desc,apply_state desc,on_time desc')->page($p,10)->select();
        $this->assign('goods_list',$goods_list);
        $this->display('ajaxgoods');
    }
    /**
     * 分类列表显示
     */
    public function categoryList(){
        $this->display();
    }


    public function ajaxstoregoods(){
        $store = D('store');
        $goods_list = $store->getnewgoods();
        $this->display();
    }
    /**
     * 模板列表
     */
    public function mobanlist(){
        $arr = glob("D:/wamp/www/svn_tpshop/mobile--html/*.html");
        foreach($arr as $key => $val)
        {
            $html = end(explode('/', $val));
            echo "<a href='http://www.php.com/svn_tpshop/mobile--html/{$html}' target='_blank'>{$html}</a> <br/>";            
        }        
    }
    
    /**
     * 商品列表页
     */
    public function goodsList(){
        $goodsLogic = new \Home\Logic\GoodsLogic(); // 前台商品操作逻辑类
        $id = I('get.id',0); // 当前分类id
        $lists = getCatGrandson($id);
        $this->assign('lists',$lists);
        $this->display();
    }
    
    public function ajaxGetMore(){
    	$p = I('p',1);
    	$favourite_goods = M('goods')->where("is_recommend=1 and is_on_sale=1  and goods_state = 1 ")->order('sort DESC')->page($p,10)->cache(true,TPSHOP_CACHE_TIME)->select();//首页推荐商品
    	$this->assign('favourite_goods',$favourite_goods);
    	$this->display();
    }

    public function ajaxGetstreet(){

    }

    /**
     * 店铺街
     * @author dyr
     * @time 2016/08/15
     */
    public function street()
    {
        $store_class = M('store_class')->where('')->select();
        $this->assign('store_class', $store_class);//店铺分类
        $this->display();
    }

    /**
     * ajax 获取店铺街
     */
    public function ajaxStreetList()
    {
        $p = I('p',1);
        $sc_id = I('get.sc_id','');
        $store_list = D('store')->getStreetList($sc_id,$p,10);
        foreach($store_list as $key=>$value){
            $store_list[$key]['goods_array'] = D('store')->getStoreGoods($value['store_id'],4);
            $store_list[$key]['domain'] = $store_list[$key]['domain'] ? $store_list[$key]['domain'] : 'www.yundi88.com';
            $store_list[$key]['apply_state'] = M('store_apply')->where(array('user_id'=>$value['user_id']))->getField('apply_state');
        }
        // dump($store_list);
        $this->assign('store_list',$store_list);
        $this->display();
    }

    /**
     * ajax 获取店铺街
     */
    public function search()
    {   
        $p = I('p');
        $name = I('name','');
        $store_list = D('store')->getStreetsearch($name,$p,10);
        // dump($store_list);
        foreach($store_list as $key=>$value){
            $store_list[$key]['goods_array'] = D('store')->getStoreGoods($value['store_id'],10);
            $store_list[$key]['domain'] = $store_list[$key]['domain'] ? $store_list[$key]['domain'] : 'yundi88.ydwzjs.cn';
            $store_list[$key]['apply_state'] = M('store_apply')->where(array('user_id'=>$value['user_id']))->getField('apply_state');
        }
        $this->assign('name',$name);
        $this->assign('store_list',$store_list);
        if ($p){
            $this->display('ajaxStreetList');
        }else{
            $this->display();
        }
    }

    /**
     * 品牌街
     * @author dyr
     * @time 2016/08/15
     */
    public function brand()
    {
        $brand_model = M('brand');
        $brand_where['status'] = 0;
        $brand_class = $brand_model->field('cat_name')->group('cat_name')->order(array('sort'=>'desc','id'=>'asc'))->where($brand_where)->select();
        $brand_list = $brand_model->field('id,name,logo,url')->order(array('sort'=>'desc','id'=>'asc'))->where($brand_where)->select();
        $brand_count = count($brand_list);
        for ($i = 0; $i < $brand_count; $i++) {
            if (($i + 1) % 4 == 0) {
                $brand_list[$i]['brandLink'] = 'brandRightLink';
            } else {
                $brand_list[$i]['brandLink'] = 'brandLink';
            }
        }
        $this->assign('brand_list', $brand_list);//品牌列表
        $this->assign('brand_class', $brand_class);//品牌分类
        $this->display();
    }
}