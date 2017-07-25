<?php

namespace Home\Controller;

use Think\Controller;
use Home\Logic\GoodsLogic;
use Home\Logic\ReplyLogic;
use Home\Logic\StoreLogic;
use Think\AjaxPage;
use Think\Page;
use Think\Verify;

class InquiryController extends BaseController
{
    public $store = array();

	public function inquiry()
	{
        // $storeid = $this->store['store_id'];
        // $storeid = session(store_id);
        $storeid = I("get.id");
		$sn_id = (empty($_GET['sn'])) ? 0 : (int) $_GET['sn'];
		$text = (empty($_GET['text'])) ? 0 : (int) $_GET['text'];
		$news = M('store_art')->where(array('id' => $storeid))->find();
        $news['timer'] = date('Y-m-d', $news['timer']);
        $news['content'] = htmlspecialchars_decode($news['content']);
        $news['img'] = sp_getcontent_imgs($news['content']);
        $companyInfo = M('goods_contact')->where(array('sid' => session('store_id')))->find();

        // 处理省市数据
        $privinceData = M('region')->field('name')->where(array('id' => $companyInfo['privince']))->find();
        $companyInfo['privince'] = $privinceData['name'];
        $cityData = M('region')->field('name')->where(array('id' => $companyInfo['city']))->find();
        $companyInfo['city'] = $cityData['name'];
        $areaData = M('region')->field('name')->where(array('id' => $companyInfo['area']))->find();
        $companyInfo['area'] = $areaData['name'];
        // dump($news);exit;
		if(!$news){
			C('VIEW_PATH','./Template/pc/');
			C('DEFAULT_THEME','yundi');
			$error = new TperrorController();
            $error->tp404();exit;

		}
        $tuijian = M('goods')->field('goods_name,shop_price,on_time,store_id')->where(array('store_cat_id2' => $goods.store_cat_id2))->limit(5)->order('rand()')->select();
        for ($z=0; $z < count($tuijian); $z++) {
            $companyInfo2 = M('store')->field('store_name, people, mobile')->where(array('store_id' => $tuijian[$z]['store_id']))->find();
            $tuijian[$z]['store_name'] = $companyInfo2['store_name'];
            $tuijian[$z]['people'] = $companyInfo2['people'];
            $tuijian[$z]['mobile'] = $companyInfo2['mobile'];
        }
        for ($i = 0; $i < count($tuijian); $i++) {
            $tuijian[$i]['on_time'] = date('Y-m-d', $tuijian[$i]['on_time']);
        }
        // 上一篇下一篇
        $id = $news['id'];
        $where['id'] = array('gt',$id);
        $where['store'] = $news['store'];
        $next = M('store_art')->where($where)->find();
        $where['id'] = array('lt',$id);
        $pre = M('store_art')->where($where)->order('id desc')->find();
        // dump($news);exit;

		$this->assign('banner', $banner);
		$this->assign('pre', $pre?$pre:array('title'=>'没有了','id'=>$news['id']));
		$this->assign('next', $next?$next:array('title'=>'没有了','id'=>$news['id']));
		$this->assign('sn_id', $sn_id);
		$this->assign('navigation', $this->navigation);
        // dump($companyInfo);exit;

        $this->assign('companyInfo',$companyInfo);
		$this->assign('news', $news);
		$this->assign('tuijian', $tuijian);
		$this->display();
	}


	public function goodDeatil()
	{

		//  form表单提交
        C('TOKEN_ON',true);
        $goodsLogic = new GoodsLogic();
        $goods_id = I("get.id");
        // dump($goods_id);
        $goods = M('Goods')->where(array('goods_id'=>$goods_id))->find();
        // dump($goods);exit;
        $goods['img'] = sp_getcontent_imgs($goods['content']);
        $goods['on_time'] = date('Y-m-d', $goods['on_time']);
        $goods['goods_content'] = htmlspecialchars_decode($goods['goods_content']);
        $goods['img'] = sp_getcontent_imgs(htmlspecialchars_decode($goods['goods_content']));
        // dump(M()->getLastSQL());
        // dump($goods);
        // exit;


        // 处理省市数据
        $privinceData = M('region')->field('name')->where(array('id' => $companyInfo['privince']))->find();
        $companyInfo['privince'] = $privinceData['name'];
        $cityData = M('region')->field('name')->where(array('id' => $companyInfo['city']))->find();
        $companyInfo['city'] = $cityData['name'];
        $areaData = M('region')->field('name')->where(array('id' => $companyInfo['area']))->find();
        $companyInfo['area'] = $areaData['name'];

        $tuijian = M('goods')->field('goods_id, goods_name,shop_price,on_time,store_id')->where(array('store_cat_id2' => $goods.store_cat_id2))->limit(5)->order('rand()')->select();
        for ($z=0; $z < count($tuijian); $z++) {
            $companyInfo2 = M('store')->field('store_name, people, mobile')->where(array('store_id' => $tuijian[$z]['store_id']))->find();
            $tuijian[$z]['store_name'] = $companyInfo2['store_name'];
            $tuijian[$z]['people'] = $companyInfo2['people'];
            $tuijian[$z]['mobile'] = $companyInfo2['mobile'];
        }
        for ($i = 0; $i < count($tuijian); $i++) {
            $tuijian[$i]['on_time'] = date('Y-m-d', $tuijian[$i]['on_time']);
        }


        $imagesList = M('goods_images')->field('image_url')->where(array('goods_id' => $goods['goods_id']))->select();
        // dump($imagesList);
        $this->assign('imagesList', $imagesList);

        if(!$goods){
			C('VIEW_PATH','./Template/pc/');
			C('DEFAULT_THEME','yundi');
			$error = new TperrorController();
            $error->tp404();exit;

		}
        if($goods['brand_id']){
            $brnad = M('brand')->where("id =".$goods['brand_id'])->find();
            $goods['brand_name'] = $brnad['name'];
        }
        $goods_images_list = M('GoodsImages')->where(array('goods_id'=>$goods_id))->select(); // 商品 图册
        $goods_attribute = M('GoodsAttribute')->getField('attr_id,attr_name'); // 查询属性
        $goods_attr_list = M('GoodsAttr')->where("goods_id = $goods_id")->select(); // 查询商品属性表
		$filter_spec = $goodsLogic->get_spec($goods_id);

        //商品是否正在促销中
        if($goods['prom_type'] == 1)
        {
            $goods['flash_sale'] = get_goods_promotion($goods['goods_id']);
            $flash_sale = M('flash_sale')->where("id = {$goods['prom_id']}")->find();
            $this->assign('flash_sale',$flash_sale);
        }
        $id = $goods['goods_id'];
        // dump($id);
        $where['goods_id'] = array('gt',$id);
        $where['store_id'] = $goods['store_id'];
        $next = M('goods')->where($where)->find();
        $where['goods_id'] = array('lt',$id);
        $pre = M('goods')->where($where)->order('goods_id desc')->find();

        // dump($pre);
        $companyInfo = M('goods_contact')->where(array('sid' => session('store_id')))->find();
        $point_rate = tpCache('shopping.point_rate');
        $freight_free = tpCache('shopping.freight_free'); // 全场满多少免运费
        $spec_goods_price  = M('spec_goods_price')->where(array('goods_id'=>$goods_id))->getField("key,price,store_count"); // 规格 对应 价格 库存表
        M('Goods')->where("goods_id=$goods_id")->save(array('click_count'=>$goods['click_count']+1 )); //统计点击数
        $commentStatistics = $goodsLogic->commentStatistics($goods_id);// 获取某个商品的评论统计
        $store_logic = new StoreLogic();
        $commentStoreStatistics = $store_logic->storeCommentStatistics($goods['store_id']);//获取商家的评论统计
        $store_info = M('store')->where(array('store_id' => $goods['store_id']))->find();
        $comparisonStoreStatistics = $store_logic->storeComparison($store_info['sc_id']);//获取业内的评论统计
        $comparisonStatistics = $store_logic->storeMatch($comparisonStoreStatistics, $comparisonStoreStatistics);//获取商家的评论统计
        $goodsTotalComment = $goodsLogic->getGoodsTotalComment($goods_id); //获取商品达人评价
        $this->assign('freight_free', $freight_free);// 全场满多少免运费
        $this->assign('comparisonStoreStatistics', $comparisonStoreStatistics); // 行业评论概览
        $this->assign('commentStoreStatistics', $commentStoreStatistics); // 商家评论概览
        $this->assign('comparisonStatistics', $comparisonStatistics); // 商家行业百分比
        $this->assign('spec_goods_price', json_encode($spec_goods_price,true)); // 规格 对应 价格 库存表
        $this->assign('navigate_goods',navigate_goods($goods_id,1));// 面包屑导航
        $this->assign('commentStatistics',$commentStatistics);//评论概览
        $this->assign('goods_attribute',$goods_attribute);//属性值
        $this->assign('goods_attr_list',$goods_attr_list);//属性列表
        $this->assign('filter_spec',$filter_spec);//规格参数
        $this->assign('goods_images_list',$goods_images_list);//商品缩略图
        $this->assign('siblings_cate',$goodsLogic->get_siblings_cate($goods['cat_id2']));//相关分类
        $this->assign('look_see',$goodsLogic->get_look_see($goods));//看了又看
        $this->assign('goods',$goods);
        $this->assign('tuijian',$tuijian);
        $this->assign('next',$next?$next:array('goods_id'=>$goods['goods_id'],'goods_name'=>'没有了'));
        $this->assign('pre',$pre?$pre:array('goods_id'=>$goods['goods_id'],'goods_name'=>'没有了'));
        $this->assign('companyInfo',$companyInfo);
        $this->assign('point_rate',$point_rate);
        $this->assign('goodsTotalComment',$goodsTotalComment);
        if($goods['store_id']>0){
        	$store = M('store')->where(array('store_id'=>$goods['store_id']))->find();
            $store['store_products'] = unserialize($store['store_products']);
            $store['store_presales'] = unserialize($store['store_presales']);
            $store['store_aftersales'] = unserialize($store['store_aftersales']);

            $store_id = $store['store_id'];
            $navigation = M('store_navigation')->field('sn_content', true)->where(array('sn_store_id' => $store_id, 'sn_is_show' => 1,'sn_pid'=>0))->order('sn_sort')->select(); //店铺导航

            foreach($navigation as &$navigation_v){

                $navigation_v['son'] = M('store_navigation')->field('sn_content', true)->where(array('sn_store_id' => $store_id, 'sn_is_show' => 1,'sn_pid'=>$navigation_v['sn_id']))->order('sn_sort')->select();
            }
            //相册导航
            if ($photo = M('photo')->where(array('store_id' => $store_id, 'is_nav' => 1, 'status' => 1))->find()) {

                $navigation[] = array('sn_title' => $store['store_products']['2'], 'sn_is_list' => 2);

            }
            $navigation[] = array('sn_title' => $store['store_products']['3'], 'sn_is_list' => 3);
            // dump($navigation);
        	$this->assign('store',$store);
            $this->assign('navigation',$navigation);

            $store_goods_class_list = M('store_goods_class')->where(array('store_id' => $store_id, 'is_show' => '1'))->select(); //zhoufei 增加了 ,'is_show'=>'1'
            if ($store_goods_class_list) {
                $sub_cat = $main_cat = array();
                foreach ($store_goods_class_list as $val) {
                    if ($val['parent_id'] == 0) {
                        $main_cat[] = $val;
                    } else {
                        $sub_cat[$val['parent_id']][] = $val;
                    }
                }
                $this->assign('main_cat', $main_cat);
                $this->assign('sub_cat', $sub_cat);
            }
            // dump($main_cat);
        	$store_goods_class_list = M('store_goods_class')->where(array('store_id'=>$goods['store_id']))->select();
        	if($store_goods_class_list){
        		$sub_cat = $main_cat = array();
        		foreach ($store_goods_class_list as $val){
        			if($val['parent_id'] == 0){
        				$main_cat[] = $val;
        			}else{
        				$sub_cat[$val['parent_id']][] = $val;
        			}
        		}
                $this->assign('erweima',$store['store_erweima']);
        		$this->assign('main_cat',$main_cat);
        		$this->assign('sub_cat',$sub_cat);
        	}
        	$this->display();
        }else{
        	$this->display();
        }
	}

    // 轮询
    public function modelGetIn()
    {
        $arr = I('post.');
        unset($arr['__hash__']);
        $arr['addtime'] = time();
        $res = M('store_inquiry')->add($arr);
        if ($res) {
            $this->ajaxReturn(1);
        } else {
            $this->ajaxReturn(0);
        }
    }
}
