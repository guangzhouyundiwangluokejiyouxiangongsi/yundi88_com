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
 * Date: 2016-03-19
 */

namespace Home\Logic;

use Think\Model\RelationModel;
use Think\Model;
use Think\Page;

/**
 *
 * Class orderLogic
 * @package Home\Logic
 */
class StoreLogic extends RelationModel
{
    /**
     * 更新店铺评分
     * @param $store_id
     */
    public function updateStoreScore($store_id){
        $store_where = array('store_id'=>$store_id,'deleted'=>0);
        $store['store_desccredit'] = M('order_comment')->where($store_where)->avg('describe_score');
        $store['store_servicecredit'] = M('order_comment')->where($store_where)->avg('seller_score');
        $store['store_deliverycredit'] = M('order_comment')->where($store_where)->avg('logistics_score');
        M('store')->where(array('store_id'=>$store_id))->save($store);
    }
    /**
     * 获取行业评分
     * @param $sc_id
     * @return array
     */
    public function storeComparison($sc_id)
    {
        $comparison_where = array('sc_id' => $sc_id, 'deleted' => 0);
        $comparison['store_desccredit_avg'] =  number_format(M('store')->where($comparison_where)->avg('store_desccredit'),1);
        $comparison['store_servicecredit_avg'] = number_format(M('store')->where($comparison_where)->avg('store_servicecredit'),1);
        $comparison['store_deliverycredit_avg'] = number_format( M('store')->where($comparison_where)->avg('store_deliverycredit'),1);
        return $comparison;
    }
    /**
     * 获取店铺评分
     * @param $store_id
     * @return array
     */
    public function storeCommentStatistics($store_id)
    {
        $store = M('store')->where(array('store_id' => $store_id, 'deleted' => 0))->find();
        $store_comment_score = array(
            'store_desccredit' => number_format($store['store_desccredit'], 1),
            'store_servicecredit' => number_format($store['store_servicecredit'], 1),
            'store_deliverycredit' => number_format($store['store_deliverycredit'], 1)
        );
        return $store_comment_score;
    }

    /**
     * 获取百分数
     * @param $comparison
     * @param $store_comment_score
     * @return array
     */
    public function storeMatch($comparison, $store_comment_score)
    {
        if ($store_comment_score['store_desccredit'] == 0
            || $store_comment_score['store_servicecredit'] == 0
            || $store_comment_score['store_deliverycredit'] == 0
        ) {
            $store_match = array(
                'desccredit_match' => 0,
                'servicecredit_match' => 0,
                'servicecredit_deliverycredit' => 0
            );
        } else {
            $store_match = array(
                'desccredit_match' => $this->getPercent($store_comment_score['store_desccredit'], $comparison['store_desccredit_avg']),
                'servicecredit_match' => $this->getPercent($store_comment_score['store_servicecredit'], $comparison['store_servicecredit_avg']),
                'deliverycredit_match' => $this->getPercent($store_comment_score['store_deliverycredit'], $comparison['store_deliverycredit_avg'])
            );
        }
        return $store_match;
    }


    public function getPercent($score, $avg)
    {
        if ($avg == 0) {
            return 100;
        } else {
            return round(($score - $avg) / $avg * 100, '2');
        }
    }


    /**
     * 获取用户收藏的店铺
     * @author dyr
     * @param $user_id
     * @param null $sc_id
     * @return mixed
     */
    public function getCollectStore($user_id,$sc_id=null)
    {
        if(!empty($sc_id)){
            $store_collect_where['s.sc_id'] = $sc_id;
        }
        $store_collect_where['sc.user_id'] = $user_id;
        $db_prefix = C('DB_PREFIX');
        $model = M('');
        $count = $model
                ->table($db_prefix.'store_collect sc')
                ->join('LEFT JOIN '.$db_prefix.'store AS s ON s.store_id = sc.store_id')
                ->where($store_collect_where)
                ->count();
        $page = new Page($count,10);
        $show = $page->show();
        if ($count === 0){
            $return['result'] = array();
            $return['show'] = $show;
            return $return;
        }
        $store_collect_list = $model
            ->field('sc.log_id,s.store_id,s.store_qq,s.store_name,s.store_logo,s.store_avatar,s.store_qq,s.store_desccredit,s.store_servicecredit,
            s.store_deliverycredit,r1.name as province_name,r2.name as city_name,r3.name as district_name,s.deleted as goods_array')
            ->table($db_prefix.'store_collect sc')
            ->join('INNER JOIN '.$db_prefix.'store AS s ON s.store_id = sc.store_id')
            ->join('LEFT JOIN '.$db_prefix . 'region As r1 ON r1.id = s.province_id')
            ->join('LEFT JOIN '.$db_prefix . 'region As r2 ON r2.id = s.city_id')
            ->join('LEFT JOIN '.$db_prefix . 'region As r3 ON r3.id = s.district')
            ->where($store_collect_where)
            ->order('sc.add_time DESC')
            ->limit($page->firstRow,$page->listRows)
            ->select();
        foreach($store_collect_list as $key=>$value){
            $store_collect_list[$key]['goods_array'] = D("store")->getStoreGoods($value['store_id'],3);
        }

        $return['result'] = $store_collect_list;
        $return['show'] = $show;
        return $return;
    }


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
    /**
     * 店铺街
     * @param null $sc_id 分类id
     * @param int $item 记录条数
     */
    public function getStoreList($sc_id = null, $item = 10)
    {
        $db_prefix = C('DB_PREFIX');
        $store_where = array('s.store_state' => 1);
        $model = M('');
        if (!empty($sc_id)) {
            $store_where['s.sc_id'] = $sc_id;
        }
        if(!$_GET['p']){$_SERVER['REDIRECT_URL'] = '/'.$_SERVER['PATH_INFO'].'/p/1.html';}
        $store_count = $model->table($db_prefix . 'store s')->where($store_where)->count();
        $num = ceil($store_count / $item);
        $page = new Page($store_count, $item);
        $show = $page->show();
        $store_list = $model
            ->field("s.commerce_state,s.apply_state,s.domain,s.store_aftersales,s.store_id,s.store_qq,s.store_name,s.store_logo,s.store_zy,s.store_phone,s.store_presales,s.store_banner,s.store_aliwangwang,s.store_qq,s.store_desccredit,s.store_servicecredit,
            s.store_deliverycredit,r1.name as province_name,r2.name as city_name,r3.name as district_name,s.deleted as goods_array,IFNULL(s.commerce_state,0) + IFNULL(s.apply_state,0) as num")
            ->table($db_prefix . 'store s')
            ->join('LEFT JOIN '.$db_prefix . 'region As r1 ON r1.id = s.province_id')
            ->join('LEFT JOIN '.$db_prefix . 'region As r2 ON r2.id = s.city_id')
            ->join('LEFT JOIN '.$db_prefix . 'region As r3 ON r3.id = s.district')
            ->where($store_where)
            ->order('num desc,s.commerce_state desc,s.apply_state desc')
            ->limit($page->firstRow, $page->listRows)
            ->select();

            // dump($store_list);exit;
        foreach ($store_list as $key => $value) {
            $store_list[$key]['goods_array'] = D("store")->getStoreGoods($value['store_id'], 3);
        }
        $return['result'] = $store_list;
        $return['show'] = $show;
        $return['num'] = $num;
        return $return;
    }



   /**
     * 店铺街
     * @param array $store_id
     * @param int $item 记录条数
     */
    public function getStoreList2($store_id, $item = 10)
    {
        $db_prefix = C('DB_PREFIX');
        $store_where = array('s.store_state' => 1);
        $model = M('');
        
        if(!$_GET['p']){$_SERVER['REDIRECT_URL'] = '/'.$_SERVER['PATH_INFO'].'/p/1.html';}
        $store_count = $model->table($db_prefix . 'store s')->where("store_id in(".implode(',',$store_id).")")->count();
        $num = ceil($store_count / $item);
        $page = new Page($store_count, $item);
        $show = $page->show();
        $store_list = $model
            ->field("s.store_zy,s.store_phone,s.commerce_state,s.apply_state,s.store_id,s.store_qq,s.store_name,s.store_logo,s.store_presales,s.store_banner,s.store_aliwangwang,s.store_qq,s.store_desccredit,s.store_servicecredit,
            s.store_deliverycredit,r1.name as province_name,r2.name as city_name,r3.name as district_name,s.deleted as goods_array,IFNULL(s.commerce_state,0) + IFNULL(s.apply_state,0) as num")
            ->table($db_prefix . 'store s')
            ->join('LEFT JOIN '.$db_prefix . 'region As r1 ON r1.id = s.province_id')
            ->join('LEFT JOIN '.$db_prefix . 'region As r2 ON r2.id = s.city_id')
            ->join('LEFT JOIN '.$db_prefix . 'region As r3 ON r3.id = s.district')
            ->where("store_id in(".implode(',',$store_id).")")
            ->order('num desc,s.commerce_state desc,s.apply_state desc')
            ->limit($page->firstRow, $page->listRows)
            ->select();
        foreach ($store_list as $key => $value) {
            $store_list[$key]['goods_array'] = D("store")->getStoreGoods($value['store_id'], 4);
        }
        $return['result'] = $store_list;
        $return['show'] = $show;
        $return['num'] = $num;
        return $return;
    }



/**
     * 添加店铺
     * @param $user
     * @return array
     */
    public function addStore($store)
    {   
        //添加前台登陆账号
        $model = new Model();
        $model->startTrans();
        $db_prefix = C('DB_PREFIX'); 
        $utype = check_email($store['user_name']) ? 'email' : 'mobile';
        $user = array($utype=>$store['user_name'],'password'=>encrypt($store['password']),'reg_time'=>time(),'sex'=>$store['sex'],'nickname'=>$store['nickname']);
        $user_id = $model->table($db_prefix.'users')->add($user);
        unset($store['sex']);
        unset($store['nickname']);
        $users   = $model->table($db_prefix.'users')->where(array('user_id' => $user_id))->find();

        session('user',$users);
        setcookie('user_id',$users['user_id'],null,'/');
        setcookie('is_distribut',$users['is_distribut'],null,'/');
        $nickname = empty($users['nickname']) ? $store['seller_name'] : $users['nickname'];
        setcookie('uname',urlencode($nickname),null,'/');
        $cartLogic = new \Home\Logic\CartLogic();
        $cartLogic->login_cart_handle($this->session_id,$users['user_id']);  //用户登录后 需要对购物车 一些操作
        $store['user_id'] = $user_id;
        $password = $store['password'];
        unset($store['password']);
        //添加店铺信息
        $store_id = $model->table($db_prefix.'store')->add($store);
        $model->table($db_prefix.'store_extend')->add(array('store_id'=>$store_id));
        if($store['is_own_shop'] == 1){
            //添加驻外店铺
            $apply = array('seller_name'=>$store['seller_name'],'user_id'=>$user_id,
                    'store_name'=>$store['store_name'],'company_province'=>0,'sc_bail'=>0,'apply_state'=>1,
            );
            M('store_apply')->add($apply);
        }
        //添加店铺管理员
        $seller = array('seller_name'=>$store['seller_name'],'store_id'=>$store_id,'user_id'=>$user_id,'is_admin'=>1);
        $seller_id = $model->table($db_prefix.'seller')->add($seller);
        $this->add_user($store['seller_name'],$password,$users['mobile'],$users['email'],$users['nickname']);
        if($user_id && $store_id && $seller_id){
            $model->commit();
            $pay_points = tpCache('basic.reg_integral'); // 会员注册赠送积分
            if($pay_points > 0){
                accountLog($user_id, 0,$pay_points, '会员注册赠送积分'); // 记录日志流水
            }
            adminLog('新增店铺：'.$store['store_name']);
            return true;
        }else{
            $model->rollback();
            return false;
        }   
    }

    public function add_user($username,$password,$mobile,$email,$nick_name){
       $rand = mt_rand(111111,999999);
       $data['user_name'] = $username;
       $data['password'] = md5(md5($password).md5($rand));
       $data['mobile'] = $mobile;
       $data['email'] = $email;
       $data['nick_name'] = $nick_name;
       $data['rand_pwd'] = md5($rand);
       $data['add_time'] = time();
       M('user','','DB_CONFIG3')->add($data);
    }

}