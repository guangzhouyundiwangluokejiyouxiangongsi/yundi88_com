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

   


    public function new_goods2()
    {
        $goods_list = M('ad')->where(array('pid'=>5114400))->order('orderby')->select();
        $this->assign('goods_list',$goods_list);
        $this->display('ajaxnewgoods');

    }


    //特色厂家
    public function features()
    {
        $goods_list = M('ad')->where(array('pid'=>5114401))->order('orderby')->limit(8)->select();
        $this->assign('goods_list',$goods_list);
        $this->display('ajaxfeaturesgoods');
    }



    // 推荐精品
    public function recommend_goods(){
        $goods_list = M('')->field('g.store_id,g.original_img,g.goods_name,g.shop_price,g.goods_id,s.apply_state,s.commerce_state,IFNULL(commerce_state,0) + IFNULL(apply_state,0) as num')->table('__GOODS__ AS g')->join('INNER JOIN __STORE__ AS s ON s.store_id = g.store_id')->where(array('recommend'=>1,'goods_state'=>1))->limit(6)->cache()->select();
        $this->assign('goods_list',$goods_list);
        $this->display('ajaxrecommendgoods');
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
    // public function street()
    // {
    //     $store_class = M('store_class')->where('')->select();
    //     $this->assign('store_class', $store_class);//店铺分类
    //     $this->display();
    // }

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
        $name = trim(I('name',''));
        if(!$name){
            $this->display();
            exit;
        }
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

    public function street()
    {
        $this->display();
    }

    public function ajaxstreet()
    {   
        $street = M('store')->field('store_id,store_name,store_zy,store_phone,province_id,city_id,district,store_address,apply_state,commerce_state,store_desccredit,store_servicecredit,store_deliverycredit')->where(array('store_state'=>1))->order('commerce_state desc,apply_state desc,store_time desc')->page($_GET['p'].',5')->select();
        $this->assign('street',$street);
        $this->display();
    }

    public function search_store()
    {   
        $name = I('name');
        $this->assign('name',$name);
        $this->display();
    }

    public function ajaxsearch()
    {   
        $name = I('name');
        $where['store_name'] = array('like','%'.$name.'%');
        $where['store_zy'] = array('like','%'.$name.'%');
        $where['_logic'] = 'or';
        $map['_complex'] = $where;
        $map['store_state'] = 1;
        $street = M('store')->field('store_id,store_name,store_zy,store_phone,province_id,city_id,district,store_address,apply_state,commerce_state,store_desccredit,store_servicecredit,store_deliverycredit')->where($map)->order('commerce_state desc,apply_state desc,store_time desc')->page($_GET['p'].',5')->select();
        foreach($street as &$v){
            $v['store_name'] = str_replace($name,'<span style="color:red">'.$name.'</span>',$v['store_name']);
            $v['store_zy'] = str_replace($name,'<span style="color:red">'.$name.'</span>',$v['store_zy']);
        }
        $this->assign('street',$street);
        $this->display('ajaxstreet');
    }

    public function promote()
    {

        if(IS_AJAX){//前端请求
            $num = M('config')->where(array('id'=>88))->getField('value');
            $this->ajaxReturn($num);

        }elseif(IS_POST){//操作系统增加
        $num = rand(1,3);
          M('config')->where(array('id'=>88))->setInc('value',$num);  exit;

        }else{
            $num = M('config')->where(array('id'=>88))->getField('value');
            $this->assign('num',$num);
            $this->display();
            
        }
    }

    public function enter()
    {
        $store_id = I('store_id','');
        if (!$store_id){
            header('location:http://association.yundi88.com/Mobile/index');
            exit;
        }
        $store = M('store')->field('store_id,store_name,province_id,city_id,district,store_address,domain,commerce_state')->where(array('store_id'=>$store_id))->find();
        if (!$store['commerce_state']){
            header('location:http://association.yundi88.com/Mobile/index');
            exit;
        }
        $this->assign('store',$store);
        $this->display();
    }

    public function certification_info()
    {
        $store_id = I('store_id',606);
        if (!$store_id) $this->redirect('/Mobile/Index/certification');
        $store = M('store')->where(array('store_id'=>$store_id))->field('store_name,store_id,user_id,commerce_state,apply_state')->find();
        $user_id = $store['user_id'];
        $apply = M('store_apply')->where(array('user_id'=>$user_id))->find();
        if (!$apply['apply_state']) $this->redirect('/Mobile/Index/certification');
        $goods = M('goods')->where(array('store_id'=>$store_id))->order('on_time desc')->limit(6)->select();
        $this->assign('goods',$goods);
        $this->assign('store',$store);
        $this->assign('apply',$apply);
        $this->display();
    }
}