<?php
/**
 * tpshop
 * 回复模型
 * @auther：dyr
 */ 
namespace Mobile\Model;
use Think\Model;


class StoreModel extends Model{
    /**
     * 店铺街
     * @author dyr
     * @param $sc_id 店铺分类ID，可不传，不传将检索所有分类
     * @param int $p 分页
     * @param int $item 每页多少条记录
     * @return mixed
     */
    public function getStreetList($sc_id,$p=1,$item=10)
    {
        $model = M('');;
        $store_where = array('s.store_state' => 1); //@modify by wangqh. 只显示开启的店铺
        $db_prefix = C('DB_PREFIX');
        if(!empty($sc_id)){
            $store_where['s.sc_id'] = $sc_id;
        }
        $store_list = $model
            ->table($db_prefix .'store s')
            ->field('s.store_id,s.store_address,s.domain,s.commerce_state,s.user_id,s.store_phone,s.store_logo,s.store_name,s.store_desccredit,s.store_servicecredit,
						s.store_deliverycredit,r1.name as province_name,r2.name as city_name,r3.name as district_name,
						s.deleted as goods_array,IFNULL(s.commerce_state,0) + IFNULL(s.apply_state,0) as num')
            ->join('LEFT JOIN '.$db_prefix . 'region As r1 ON r1.id = s.province_id')
            ->join('LEFT JOIN '.$db_prefix . 'region As r2 ON r2.id = s.city_id')
            ->join('LEFT JOIN '.$db_prefix . 'region As r3 ON r3.id = s.district')
            ->where($store_where)
            ->order('num desc,s.commerce_state desc,s.apply_state desc')
            ->page($p,$item)
            ->cache(true,TPSHOP_CACHE_TIME)
            ->select();
        return $store_list;
    }

    /**
     * 搜索店铺街
     * @author dyr
     * @param $sc_id 店铺分类ID，可不传，不传将检索所有分类
     * @param int $p 分页
     * @param int $item 每页多少条记录
     * @return mixed
     */
    public function getStreetsearch($name,$p=1,$item=10)
    {
        $model = M('');
        $store_where['s.store_state'] = array('eq','1'); //@modify by wangqh. 只显示开启的店铺
        $db_prefix = C('DB_PREFIX');
        if(!empty($name)){
            $store_where['s.store_name'] = array('like','%'.$name.'%');
        }
        $store_list = $model
            ->table($db_prefix .'store s')
            ->field('s.store_id,s.store_address,s.domain,s.commerce_state,s.user_id,s.store_phone,s.store_logo,s.store_name,s.store_desccredit,s.store_servicecredit,
                        s.store_deliverycredit,r1.name as province_name,r2.name as city_name,r3.name as district_name,
                        s.deleted as goods_array,IFNULL(s.commerce_state,0) + IFNULL(s.apply_state,0) as num')
            ->join('LEFT JOIN '.$db_prefix . 'region As r1 ON r1.id = s.province_id')
            ->join('LEFT JOIN '.$db_prefix . 'region As r2 ON r2.id = s.city_id')
            ->join('LEFT JOIN '.$db_prefix . 'region As r3 ON r3.id = s.district')
            ->where($store_where)
            ->order('num desc,s.commerce_state desc,s.apply_state desc')
            ->page($p,$item)
            ->cache(true,TPSHOP_CACHE_TIME)
            ->select();
        return $store_list;
    }

    /**
     * 新品
     * @author dyr
     * @param $sc_id 店铺分类ID，可不传，不传将检索所有分类
     * @param int $p 分页
     * @param int $item 每页多少条记录
     * @return mixed
     */
    public function getnewgoods($p=1,$num=10)
    {
        $model = M('');;
        $store_where = array('store_state' => 1,'is_on_sale'=>1); //@modify by wangqh. 只显示开启的店铺
        // $goods_list = $model
        //     ->table($db_prefix .'goods g')
        //     ->field('g.goods_name,g.goods_id,g.shop_price,s.commerce_state,s.apply_state,g.store_id as s.store_id')
        //     ->join('LEFT JOIN __GOODS__  __STORE__.store_id ON g.store_id = s.store_id')
        //     ->where($store_where)
        //     ->order('num desc,s.commerce_state desc,s.apply_state desc')
        //     ->page($p,$num)
        //     ->cache(true,TPSHOP_CACHE_TIME)
        //     ->select();
        $goos_list = M('goods')->field('goods_name,goods_id,shop_price,commerce_state,apply_state')->join('INNER JOIN __STORE__ ON __STORE__.store_id = __GOODS__.store_id')->where($store_where)->order('commerce_state desc,apply_state desc')
            ->page($p,$num)
            // ->cache(true,TPSHOP_CACHE_TIME)
            ->select();
            // "select * from goods inner jion store on store.id = goods.id"
        return $goods_list;
    }

    /**
     * 获取店铺商品详细
     * @param $store_id
     * @param $limit
     * @return mixed
     */
    public function getStoreGoods($store_id,$limit)
    {
        $goods_model = M('goods');
        $goods_where = array(
            'is_on_sale'=>1,
//            'is_recommend'=>1,
//            'is_hot'=>1,
            'goods_state'=>1,
            'store_id'=>$store_id
        );
        $res['goods_list'] = $goods_model->field('goods_id,goods_name,shop_price')->where($goods_where)->limit($limit)->order('sort desc')->select();
        $count_where = array(
//            'goods_state'=>1,
            'store_id'=>$store_id
        );
        $res['goods_count'] = $goods_model->where($count_where)->count();
        return $res;
    }
}