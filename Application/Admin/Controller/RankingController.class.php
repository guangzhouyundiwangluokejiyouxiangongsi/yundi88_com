<?php


namespace Admin\Controller;

use Think\Controller;
use Think\Page;

/**
 * Class UeditorController
 * @package Admin\Controller
 */
class RankingController extends Controller
{


    public function index()
    {

        $d = date('d',time()) % 2;
        if(M('ranking_art')->count()){return;}
        $_GET['p'] = I('p',1);
        $model = M('store_art');
        $count =  $model->where(array('is_show'=>1))->count();
        $page = new Page($count,5000);
        //查询当前页文章id
        $artid = $model->field('concat(store) as store_id')->where(array('is_show'=>1))->limit($page->firstRow,$page->listRows)->select();
        $store_id = '';
        foreach($artid as $v){
            $store_id .= $v['store_id'].',';
        }

        //查询当前页文章的商户信息
        $store = M('store')->field('store_id,status,IFNULL(commerce_state,0) as commerce_state,IFNULL(apply_state,0) as apply_state,IFNULL(commerce_state,0) + IFNULL(apply_state,0) as num')->where('store_id in('.substr($store_id,0,-1).')')->select();

        //查询文章
        $articlelist = $model->field('id,store')->where(array('is_show'=>1))->limit($page->firstRow,$page->listRows)->select();
        //合并
        foreach($articlelist as &$vv){
                        // $vv['id'] = $vv['id'];
                        $vv['commerce_state'] = 0;
                        $vv['apply_state'] = 0;
                        $vv['num'] = 0;
                        $vv['status'] = 0;
                        $vv['time'] = time();
            foreach($store as $vvv){
                if($vv['store'] == $vvv['store_id']){
                    $vv['commerce_state'] = $vvv['commerce_state'];
                    $vv['apply_state'] = $vvv['apply_state'];
                    $vv['num'] = $vvv['num'];
                    $vv['status'] = $vvv['status'];
                    $vv['time'] = time();
                }

            }
        }

        M('ranking_art')->addAll($articlelist);
        $pa = ceil($count / 5000);
        if($_GET['p'] < $pa){
             $p = $_GET['p']+1;
            $this->assign('url',"/Admin/Ranking/index/p/{$p}.html");
            $this->display();
        }else{
            echo '完毕';
        }

    }



    public function goods()
    {
        if(M('ranking_goods')->count()){return;}
        $_GET['p'] = I('p',1);
        $model = M('goods');
        $count =  $model->where(array('is_on_sale'=>1,'goods_state'=>1,'home_is_show'=>1))->count();
        $page = new Page($count,5000);
        //查询当前页商品的商户id
        $artid = $model->field('concat(store_id) as store_id')->where(array('is_on_sale'=>1,'goods_state'=>1,'home_is_show'=>1))->limit($page->firstRow,$page->listRows)->select();
        $store_id = '';
        foreach($artid as $v){
            $store_id .= $v['store_id'].',';
        }

        //查询当前页文章的商户信息
        $store = M('store')->field('store_id,status,IFNULL(commerce_state,0) as commerce_state,IFNULL(apply_state,0) as apply_state,IFNULL(commerce_state,0) + IFNULL(apply_state,0) as num')->where('store_id in('.substr($store_id,0,-1).')')->select();

        //查询文章
        $goodslist = $model->field('goods_id,store_id,cat_id1,cat_id2,cat_id3,shop_price')->where(array('is_on_sale'=>1,'goods_state'=>1,'home_is_show'=>1))->limit($page->firstRow,$page->listRows)->select();
        //合并
        foreach($goodslist as &$vv){
                        $vv['id'] = $vv['goods_id'];
                        $vv['commerce_state'] = 0;
                        $vv['apply_state'] = 0;
                        $vv['num'] = 0;
                        $vv['status'] = 0;
                        $vv['time'] = time();
            foreach($store as $vvv){
                if($vv['store_id'] == $vvv['store_id']){
                    $vv['commerce_state'] = $vvv['commerce_state'];
                    $vv['apply_state'] = $vvv['apply_state'];
                    $vv['num'] = $vvv['num'];
                    $vv['status'] = $vvv['status'];
                    $vv['time'] = time();
                }

            }
        }
        // echo '<pre>';
// print_r($goodslist);exit;

        M('ranking_goods')->addAll($goodslist);
        $pa = ceil($count / 5000);
        if($_GET['p'] < $pa){
            $p = $_GET['p']+1;
            $this->assign('url',"/Admin/Ranking/goods/p/{$p}.html");
            $this->display('index');
        }else{
            echo '完毕';
        }


    }

}



// 0 03 * * *
// http://www.yundi88.com/Admin/Ranking/index.html
// http://www.yundi88.com/Admin/Ranking/goods.html
