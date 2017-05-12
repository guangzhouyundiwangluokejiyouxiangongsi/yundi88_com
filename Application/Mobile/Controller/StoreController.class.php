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
 * Date: 2016-05-28
 */

namespace Mobile\Controller;

use Home\Logic\StoreLogic;
use Think\Controller;
use Think\Page;

class StoreController extends Controller {
	public $store = array();
	public $navigation = null;
	
	public function _initialize() {
		$store_id = I('store_id');
		if(empty($store_id)){
			$this->error('参数错误,店铺系列号不能为空',U('Index/index'));
		}
		$store = M('store')->where(array('store_id'=>$store_id))->find();
        $store['store_presales'] = unserialize($store['store_presales']);
        $store['store_products'] = unserialize($store['store_products']);
		if($store){
			if($store['store_state'] == 0){
				$this->error('该店铺不存在或者已关闭', U('Index/index'));
			}
			$store['mb_slide'] = explode(',', $store['mb_slide']);
            $store['mb_slide_url'] = explode(',', $store['mb_slide_url']);
            $config = M('config')->where(array('name' => 'copyright'))->find();
            $config2 = M('config')->where(array('name' => 'record_no'))->find();
            $store['record_no'] = $config2['value'];
            $store['copyright'] = $config['value'];
			$this->store = $store;
			$this->assign('store',$store);
			$this->navigation = M('store_navigation')->field('sn_content', true)->where(array('sn_store_id' => $store_id, 'sn_is_show' => 1))->select(); //店铺导航

            //相册导航
            if ($photo = M('photo')->where(array('store_id' => $store_id, 'is_nav' => 1, 'status' => 1))->find()) {

                $this->navigation[] = array('sn_title' => $store['store_products'][2], 'sn_is_list' => 2);
                $this->navigation[] = array('sn_title' => $store['store_products'][3], 'sn_is_list' => 3);

            }
			//店铺内部分类
        	$goods_class = M('store_goods_class')->where(array('store_id' => $store_id,'is_show'=>'1'))->select(); //'is_nav_show'=>'1',

            foreach($goods_class as $v){
                if($v['is_nav_show'] == 1){$goods_class_nav[]  = $v;}
                if($v['parent_id'] == 0){
                    $goods_class1[] = $v;
                }else{
                    $goods_class2[$v['parent_id']][] = $v;
                }
            }

			 $this->assign('navigation',$this->navigation);
             $this->assign('goods_class',$goods_class);//全部分类
             $this->assign('goods_class1',$goods_class1);//一级分类
             $this->assign('goods_class2',$goods_class2);//二级分类
			 $this->assign('goods_class_nav',$goods_class_nav);//分类在导航显示
			 $this->assign('storeid',$this->store['store_id']);
		}else{
			$this->error('该店铺不存在或者已关闭',U('Index/index'));
		}
		if (session('?user')) {
			$user = session('user');
			$this->user_id = $user['user_id'];
			$this->assign('user', $user); //存储用户信息
		}

		 	// zhoufei
            C('VIEW_PATH','./Merchants_tpl/mobile/');//模板路径
           $tplconfig = M('store')->where(array('store_id' => $store_id))->getField('mtpl');
           if(!$tplconfig){
            $tplconfig = include('./Merchants_tpl/pc/'.M('store')->where(array('store_id' => $store_id))->getField('tpl').'/config.php');
            $tplconfig = $tplconfig['mtpl'];
            
           }
            C('DEFAULT_THEME',$tplconfig);//模板名称

            define('STYLE',substr(C('VIEW_PATH').C('DEFAULT_THEME'),1));
            C('DOMAIN','http://'.$_SERVER['HTTP_HOST']);
            // zhoufei

            // if(!is_mobile()){
            //     header("location:".C('DOMAIN').'/Store/index/store_id/'.$store_id);
            // }
	}
	
	public function index()
	{
		//热门商品排行
		$hot_goods = M('goods')->field('goods_content',true)->where(array('store_id'=>$this->store['store_id'],'is_on_sale'=>1))->order('sales_sum desc')->limit(9)->select();
		//新品
		$new_goods = M('goods')->field('goods_content',true)->where(array('store_id'=>$this->store['store_id'],'is_new'=>1,'is_on_sale'=>1))->order('goods_id desc')->limit(9)->select();
		//推荐商品
		$recomend_goods = M('goods')->field('goods_content',true)->where(array('store_id'=>$this->store['store_id'],'is_recommend'=>1,'is_on_sale'=>1))->order('goods_id desc')->limit(9)->select();
		//所有商品
		$total_goods = M('goods')->where(array('store_id'=>$this->store['store_id'],'is_on_sale'=>1,'is_on_sale'=>1))->count();


        //新闻
        $store_navigation = M('store_navigation')->where(array('sn_store_id'=>$this->store['store_id'],'sn_is_list'=>1,'sn_is_show'=>1))->getField('sn_id',true);
        if($store_navigation){
            
        $news = M('store_art')->where('(sn_id in('.implode(',',$store_navigation).')) and is_show = 1')->order('id desc')->limit(10)->select();
        }
        $this->assign('news',$news);
		$this->assign('hot_goods',$hot_goods);
		$this->assign('new_goods',$new_goods);
		$this->assign('recomend_goods',$recomend_goods);
		$this->assign('total_goods',$total_goods);
		$total_goods = M('goods')->where(array('store_id'=>$this->store['store_id'],'is_on_sale'=>1,'is_on_sale'=>1))->count();
		$this->assign('total_goods',$total_goods);
        $this->assign('recommend',$this->recommend());
		$this->display('/index');
	}



    
       /**
     * 首页推荐
     */
    public function recommend()
    {
        //查询首页推荐栏目
        $product_m = M('goods');
        $recommend = M('store_goods_class')->where(array('store_id'=>$this->store_id['store_id'],'is_show'=>1,'is_recommend'=>1))->select();
        foreach($recommend as &$v){
            // 查询推荐商品
            $v['cat_id_goods'] = $product_m->where('('.'store_cat_id1 = '.$v['cat_id'].' or store_cat_id2 = '.$v['cat_id'].')' .'and is_on_sale = 1')->field('goods_id,goods_name,original_img,shop_price')->limit($v['show_num'])->select();
        }

        return $recommend;
    }
    
	
	public function goods_list(){
        $store_id = I('store_id', 1);
        $cat_id = I('cat_id', 0);
        $key = I('key', 'is_new');
        $sort = I('sort', 'desc');
        $keyword = urldecode(trim(I('keyword','')));
        $map = array('store_id' => $store_id, 'is_on_sale' => 1);
        $keyword && $map['goods_name']  = array('like','%'.$keyword.'%');
        $this->page('?');//上一页 下一页 按钮
        $cat_name = "全部商品";
        if ($cat_id > 0) {
            $map['_string'] = "store_cat_id1=$cat_id OR store_cat_id2=$cat_id";
            $cat_name = M('store_goods_class')->where(array('cat_id' => $cat_id))->getField('cat_name');
        }
        $filter_goods_id = M('goods')->where($map)->cache(true)->getField("goods_id", true);
        $count = count($filter_goods_id);
        $num = ceil($count / 10);
        $this->assign('num',$num);//页码
        $this->assign('current',$_GET['p']?$_GET['p']:1);//当前页
        $Page = new \Think\Page($count, 10);
        if ($count > 0) {
            $goods_list = M('goods')->where("(goods_id in (" . implode(',', $filter_goods_id) . ")) and is_on_sale = 1")->order("$key $sort")->limit($Page->firstRow . ',' . $Page->listRows)->select();
            $filter_goods_id2 = get_arr_column($goods_list, 'goods_id');
            if ($filter_goods_id2) {
                $goods_images = M('goods_images')->where("goods_id in (" . implode(',', $filter_goods_id2) . ")")->cache(true)->select();
            }
        }

        $sort = ($sort == 'desc') ? 'asc' : 'desc';
        $this->assign('sort', $sort);
        $this->assign('keys', $key);
        $link_arr = array(
            array('key' => 'is_new', 'name' => '新品', 'url' => U('Store/goods_list', array('store_id' => $store_id, 'key' => 'is_new', 'sort' => $sort, 'cat_id'=>$cat_id, 'keyword'=>$keyword))),
            array('key' => 'shop_price', 'name' => '价格', 'url' => U('Store/goods_list', array('store_id' => $store_id, 'key' => 'shop_price', 'sort' => $sort, 'cat_id'=>$cat_id,'keyword'=>$keyword))),
            array('key' => 'sales_sum', 'name' => '销量', 'url' => U('Store/goods_list', array('store_id' => $store_id, 'key' => 'sales_sum', 'sort' => $sort, 'cat_id'=>$cat_id, 'keyword'=>$keyword))),
            array('key' => 'collect_sum', 'name' => '收藏', 'url' => U('Store/goods_list', array('store_id' => $store_id, 'key' => 'collect_sum', 'sort' => $sort, 'cat_id'=>$cat_id, 'keyword'=>$keyword))),
            array('key' => 'is_recommend', 'name' => '人气', 'url' => U('Store/goods_list', array('store_id' => $store_id, 'key' => 'is_recommend', 'sort' => $sort, 'cat_id'=>$cat_id, 'keyword'=>$keyword)))
        );

        //栏目信息
        $navlist = M('store_goods_class')->where(array('store_id' => $store_id, 'cat_id' => $cat_id))->find();
        $this->assign('navlist', $navlist);
        $this->assign('link_arr', $link_arr);
        $this->assign('goods_list', $goods_list);
        $this->assign('goods_images', $goods_images);  //相册图片
        $this->assign('cat_name', $cat_name);
        $page_show = $Page->show();// 分页显示输出
        $this->assign('page_show', $page_show);// 赋值分页输出
        $this->assign('keyword',$keyword);
        $this->display('/goods_list');
	}
	

	// public function about(){
	// 	$total_goods = M('goods')->where(array('store_id'=>$this->store['store_id'],'is_on_sale'=>1))->count();
	// 	$this->assign('total_goods',$total_goods);
	// 	$this->display('/about');
	// }
	
	public function store_goods_class(){
		$store_goods_class_list = M('store_goods_class')->where(array('store_id'=>$this->store['store_id']))->select();
		if($store_goods_class_list){
			$sub_cat = $main_cat = array();
			foreach ($store_goods_class_list as $val){
			    if ($val['parent_id'] == 0) {
                    $main_cat[] = $val;
                } else {
                    $sub_cat[$val['parent_id']][] = $val;
                }
			}
			$this->assign('main_cat',$main_cat);
			$this->assign('sub_cat',$sub_cat);
		}
		$this->display('/store_goods_class');
	}

	public function store_news()
	{
		$sn_id = I('sn_id');
		if(is_numeric($sn_id)){
	    $navlist = M('store_navigation')->where(array('sn_store_id' => $this->store['store_id'], 'sn_id' => $sn_id))->find();
        $this->assign('banner', $banner);
        $navlist['sn_content'] = htmlspecialchars_decode($navlist['sn_content']);
        $this->assign('navlist', $navlist);
        $this->display('/store_news');
		}else{
			$this->_empty();
		}
		
	}

    public function newsList(){

        $storeid = $this->store['store_id'];
        $sn_id = (empty($_GET['sn']))?0:(int)$_GET['sn'];
        $this->page('?');//上一页 下一页 按钮
        $_GET['p'] = isset($_GET['p'])?$_GET['p']:1;
        if(is_numeric($sn_id)){
	        $news = M('store_art')->where('(store = '.$storeid.' ) and is_show = 1')->page($_GET['p'].',12')->select();
	        $count = M('store_art')->where('(store = '.$storeid.' ) and is_show = 1')->count();

            $num = ceil($count / 12);
            $this->assign('num',$num);//页码
            $this->assign('current',$_GET['p']?$_GET['p']:1);//当前页

	        $page = new \Think\Page($count,12);
	        $this->assign('sn_id',$sn_id);
	        $this->assign('page',$page->show());
        // //栏目信息
        // $navlist = M('store_navigation')->where(array('store_id' => $storeid,'sn_id'=>$sn_id))->find();
        // $this->assign('navlist', $navlist);

	        $this->assign('news',$news);
	        $this->display('/newslist');
    	}else{
    		$this->_empty();
    	}
    }
   
    public function newscontent(){
    	$article = M('store_art');
        $storeid = $this->store['store_id'];
        $sn_id = (empty($_GET['sn']))?0:(int)$_GET['sn'];
        $text = (empty($_GET['text']))?0:(int)$_GET['text'];
        if(!$text){echo "<script>window.history.go(-1);</script>";}
        $news = $article->where('store = '.$storeid.' and id = '.$text)->find();


        $where['id'] = array('gt',$news['id']);
        $where['store'] = $storeid;
        $where['sn_id'] = $news['sn_id'];
        $where['is_show'] = 1;
        $next = $article->where($where)->find();//下一篇
        unset($where['id']);
        $where['id'] = array('lt',$news['id']);
        $pre = $article->where($where)->find();//上一篇

        $banner = M('store')->where(array('store_id' => $this->store['store_id']))->getField('store_banner');
        $this->assign('banner', $banner);
        //点击量
        M('store_art')->where('id='.$text)->setInc('m_click',1);
        $this->assign('pre',$pre);
        $this->assign('next',$next);
        $this->assign('sn_id',$sn_id);
        $this->assign('news',$news);
        $this->display('/news');
    }




    // 店内搜索
    public function search()
    {
        $keywords = I('get.keywords');
        $cat_id = $_GET['store_id'];
        if(!$cat_id){$this->redirect('Index/index'); }
        $map['store_id'] = array('eq',$cat_id);
        $where['goods_name'] = array('like','%'.$keywords.'%');
        $where['keywords'] = array('like','%'.$keywords.'%');
        $where['goods_remark'] = array('like','%'.$keywords.'%');
        $where['_logic'] = 'or';
        $map['_complex'] = $where;
        $m = M('goods');
        $count = $m->where($map)->count();
        $num = ceil($count / 2);
        $page = new \Think\Page($count,2);
        $goods = $m->where($map)->limit($page->firstRow.','.$page->listRows)->select();
         foreach($goods as &$v){
            $v['original_img'] = str_replace('/Public', C('DOMAIN').'/Public', $v['original_img']);
        }
        $this->page('&');//上一页 下一页 按钮
        $this->assign('num',$num);//页码
        $this->assign('current',$_GET['p']?$_GET['p']:1);//当前页
        $this->assign('goods_list',$goods);
        $this->assign('page',$page->show);// 赋值分页输出
        $this->display('/goods_list');

    }


    /**
     * 上一页 下一页 按钮
     *$symbol  符号
     */
    public function page($symbol)
    {
        $prev = str_replace('p='.$_GET['p'], 'p='.($_GET['p']-1), $_SERVER['REQUEST_URI']);
        $request_uri = isset($_GET['p'])?$_SERVER['REQUEST_URI']:$_SERVER['REQUEST_URI'].$symbol.'p=2';
        $_GET['p'] = $_GET['p']?$_GET['p']:1;
        $next = str_replace('p='.$_GET['p'], 'p='.($_GET['p']+1), $request_uri);
        $this->assign('prev',$prev);//上一页
        $this->assign('next',$next);//下一页
    }

    /**
     * 相册列表
     */
    public function photolist() {
        $photoimg = M('photoimg');
        $photolist = M('photo')->where(array('store_id' => $this->store['store_id'], 'status' => 1))->select();
        foreach ($photolist as &$v) {
            $v['photoimg'] = $photoimg->where(array('photoid' => $v['id']))->select();
        }
        $this->assign('photolist', $photolist);
        $this->assign('navigation',$this->navigation);
        $this->display('/photolist');
    }

    /**
     * 在线留言页面
     */
    public  function message()
    {
        $this->assign('navigation', $this->navigation);

        $this->display('/message');
    }


	public function _empty()
	{

		$this->display('/404');
	}



    public function map()
    {

         //商户名称
        $store_m = M('store');
        $store = $store_m->field('store_name,province_id,city_id,district,store_address')->where(array('store_id'=>$this->store['store_id']))->find();

        $sql = "id = {$store['province_id']} or id = {$store['city_id']} or id = {$store['district']}";
        $addres = M('region')->field('name')->where($sql)->select();
        $this->assign('addres',$addres[0]['name'].$addres[1]['name'].$addres[2]['name']);
        $this->assign('store',$store['store_name']);

        $this->display('/map');
    }

}

