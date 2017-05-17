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
        // $_GET['p'] = 2;
        $model = M('store_art');
        $count =  $model->where(array('is_show'=>1))->count();
        $page = new Page($count,10000);
        //查询当前页文章id
        $artid = $model->field('concat(store) as store_id')->where(array('is_show'=>1))->limit($page->firstRow,$page->listRows)->select();
        $store_id = '';
        foreach($artid as $v){
            $store_id .= $v['store_id'].',';
        }

        //查询当前页文章的商户信息
        $store = M('store')->field('store_id,IFNULL(commerce_state,0) as commerce_state,IFNULL(apply_state,0) as apply_state,IFNULL(commerce_state,0) + IFNULL(apply_state,0) as num')->where('store_id in('.substr($store_id,0,-1).')')->select();

        //查询文章
        $articlelist = $model->field('id,store')->where(array('is_show'=>1))->limit($page->firstRow,$page->listRows)->select();
        //合并
        foreach($articlelist as &$vv){
                        $vv['commerce_state'] = 0;
                        $vv['apply_state'] = 0;
                        $vv['num'] = 0;
                        $vv['time'] = time();
            foreach($store as $vvv){
                if($vv['store'] == $vvv['store_id']){
                    $vv['commerce_state'] = $vvv['commerce_state'];
                    $vv['apply_state'] = $vvv['apply_state'];
                    $vv['num'] = $vvv['num'];
                    $vv['time'] = time();
                }
                     
            }
        }

        // echo '<pre>';
        // print_r($articlelist);
        M('ranking_art')->addAll($articlelist);

    }

}