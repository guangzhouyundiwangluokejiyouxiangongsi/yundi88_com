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
 * Author: IT宇宙人      
 * 
 * Date: 2016-03-09
 */

namespace Admin\Controller;
use Think\Page;

class FinanceController extends BaseController {
    
    /*
     * 初始化操作
     */
    public function _initialize() {
       parent::_initialize();
    }    
 
    /**
     *  店家转账汇款记录
     */
    public function store_remittance(){
        $model = M("");
        $store_id = I('store_id');
        $account_bank = I('account_bank');
        $account_name = I('account_name');
        
        $create_time = I('create_time');
        $create_time = str_replace("+"," ",$create_time);
        $create_time2 = $create_time  ? $create_time  : date('Y-m-d',strtotime('-1 year')).' - '.date('Y-m-d',strtotime('+1 day'));
        $create_time3 = explode(' - ',$create_time2);
        $where['sr.create_time'] =  array(array('gt', strtotime(strtotime($create_time3[0])), array('lt', strtotime($create_time3[1]))));
        $store_id && $where['s.store_id'] = $store_id;
        $account_bank && $where['sr.account_bank'] = array('like','%'.$account_bank.'%');
        $account_name && $where['sr.account_name'] = array('like','%'.$account_name.'%');
                        
        $count = $model->table(C('DB_PREFIX').'store_remittance sr')->join('INNER JOIN __STORE__ s ON s.store_id = sr.store_id')->where($where)->count();
        $Page  = new Page($count,2);
        $list = $model->table(C('DB_PREFIX').'store_remittance sr')->join('INNER JOIN __STORE__ s ON s.store_id = sr.store_id')->where($where)->order("sr.id desc")->limit($Page->firstRow.','.$Page->listRows)->select();
        
        $this->assign('create_time',$create_time2);
        $show  = $Page->show();                 
        $this->assign('show',$show);
        $this->assign('list',$list);
        C('TOKEN_ON',false);
        $this->display();    
    }
    /**
     *  转账汇款记录
     */
    public function remittance(){
        $model = M("");
        $user_id = I('user_id');
        $account_bank = I('account_bank');
        $account_name = I('account_name');
        $create_time = I('create_time');
        $create_time = str_replace("+"," ",$create_time);
        $create_time2 = $create_time  ? $create_time  : date('Y-m-d',strtotime('-1 year')).' - '.date('Y-m-d',strtotime('+1 day'));
        $create_time3 = explode(' - ',$create_time2);
        $where['w.create_time'] =  array(array('gt', strtotime(strtotime($create_time3[0])), array('lt', strtotime($create_time3[1]))));
        $user_id && $where['u.user_id'] = $user_id;
        $account_bank && $where['w.account_bank'] = array('like','%'.$account_bank.'%');
        $account_name && $where['w.account_name'] = array('like','%'.$account_name.'%');
                        
        $count = $model->table(C('DB_PREFIX').'withdrawals w')->join('INNER JOIN __USERS__ u ON u.user_id = w.user_id')->where($where)->count();
        $Page  = new Page($count,16);        
        $list = $model->table(C('DB_PREFIX').'withdrawals w')->join('INNER JOIN __USERS__ u ON u.user_id = w.user_id')->where($where)->order("w.id desc")->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('create_time',$create_time2);
        $show  = $Page->show();                 
        $this->assign('show',$show);
        $this->assign('list',$list);
        C('TOKEN_ON',false);
        $this->display();    
    }

    /**
     * 提现申请记录
     */
    public function withdrawals()
    {
        $model = M('');
        $status = I('status');
        $user_id = I('user_id');
        $account_bank = I('account_bank');
        $account_name = I('account_name');
        $create_time = I('create_time');
        $create_time = str_replace("+"," ",$create_time);
        $create_time2 = $create_time  ? $create_time  : date('Y-m-d',strtotime('-1 year')).' - '.date('Y-m-d',strtotime('+1 day'));
        $create_time3 = explode(' - ',$create_time2);

        $where['w.create_time'] =  array(array('gt', strtotime(strtotime($create_time3[0])), array('lt', strtotime($create_time3[1]))));
        if($status === '0' || $status > 0)
            $where['w.status'] = $status;
        $user_id && $where['u.user_id'] = $user_id;
        $account_bank && $where['w.account_bank'] = array('like','%'.$account_bank.'%');
        $account_name && $where['w.account_name'] = array('like','%'.$account_name.'%');

        $count = $model->table(C('DB_PREFIX').'withdrawals w')->join('INNER JOIN __USERS__ u ON u.user_id = w.user_id')->where($where)->count();
        $Page  = new Page($count,2);
        $list = $model->table(C('DB_PREFIX').'withdrawals w')->join('INNER JOIN __USERS__ u ON u.user_id = w.user_id')->where($where)->order("w.id desc")->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('create_time',$create_time2);
        $show  = $Page->show();
        $this->assign('show',$show);
        $this->assign('list',$list);
        C('TOKEN_ON',false);
        $this->display();
    }
    /**
     * 商家提现申请记录
     */
    public function store_withdrawals()
    {
        $model = M("store_withdrawals");
        $store_id = I('store_id');
        $account_bank = I('account_bank');
        $account_name = I('account_name');
        $create_time = I('create_time');

        $create_time = str_replace("+"," ",$create_time);
        $create_time2 = $create_time  ? $create_time  : date('Y-m-d',strtotime('-1 year')).' - '.date('Y-m-d',strtotime('+1 day'));
        $create_time3 = explode(' - ',$create_time2);
        $where['sw.create_time'] =  array(array('gt', strtotime(strtotime($create_time3[0])), array('lt', strtotime($create_time3[1]))));
        $store_id && $where['s.store_id'] = $store_id;
        $account_bank && $where['sw.account_bank'] = array('like','%'.$account_bank.'%');
        $account_name && $where['sw.account_name'] = array('like','%'.$account_name.'%');

        $count = $model->table(C('DB_PREFIX').'store_withdrawals sw')->join('INNER JOIN __STORE__ s ON s.store_id = sw.store_id')->where($where)->count();
        $Page  = new Page($count,4);
        $list = $model->table(C('DB_PREFIX').'store_withdrawals sw')->join('INNER JOIN __STORE__ s ON s.store_id = sw.store_id')->where($where)->order("`id` desc")->limit($Page->firstRow.','.$Page->listRows)->select();

        $this->assign('create_time',$create_time2);
        $show  = $Page->show();
        $this->assign('show',$show);
        $this->assign('list',$list);
        C('TOKEN_ON',false);
        $this->display();
    }
    
    /**
     * 删除申请记录
     */
    public function delStoreWithdrawals()
    {                        
        $model = M("store_withdrawals"); 
        $model->where('id ='.$_GET['id'])->delete(); 
        $return_arr = array('status' => 1,'msg' => '操作成功','data'  =>'',);   //$return_arr = array('status' => -1,'msg' => '删除失败','data'  =>'',);        
        $this->ajaxReturn(json_encode($return_arr));
    }
        
    /**
     * 修改编辑商家 申请提现
     */
    public  function editStoreWithdrawals(){        
        $id = I('id');
        $model = M("store_withdrawals");
        $withdrawals = $model->find($id);
        $store = M('store')->where("store_id = {$withdrawals[store_id]}")->find();
        
        if(IS_POST)
        {
                $model->create();
                
                // 如果是已经给店家转账 则生成转账流水记录
                if($model->status == 1 && $withdrawals['status'] != 1)
                {                
                    if($store['store_money'] < $withdrawals['money'])
                    {
                        $this->error("店家余额不足{$withdrawals['money']}，不够提现");    
                        exit;
                    }
                    storeAccountLog($withdrawals['store_id'], ($withdrawals['money'] * -1),0,$desc = '平台提现');
                    $remittance = array(
                        'store_id' => $withdrawals['store_id'],
                        'bank_name' => $withdrawals['bank_name'],
                        'account_bank' => $withdrawals['account_bank'],
                        'account_name' => $withdrawals['account_name'],
                        'money' => $withdrawals['money'],
                        'status' => 1,
                        'create_time' => time(),
                        'admin_id' => session('admin_id'),
                        'withdrawals_id' => $withdrawals['id'],
                        'remark'=>$model->remark,
                    );
                    M('store_remittance')->add($remittance);
                }                
                $model->save();                               
                $this->success("操作成功!",U('store_remittance'),3);
                exit;
        }              
       $this->assign('store',$store);
       $this->assign('data',$withdrawals);
       $this->display();           
    }      

    /**
     * 删除申请记录
     */
    public function delWithdrawals()
    {                        
        $model = M("withdrawals"); 
        $model->where('id ='.$_GET['id'])->delete(); 
        $return_arr = array('status' => 1,'msg' => '操作成功','data'  =>'',);   //$return_arr = array('status' => -1,'msg' => '删除失败','data'  =>'',);        
        $this->ajaxReturn(json_encode($return_arr));
    } 
    
    /**
     * 修改编辑 申请提现
     */
    public  function editWithdrawals(){        
        $id = I('id');
        $model = M("withdrawals");
        $withdrawals = $model->find($id);
        $user = M('users')->where("user_id = {$withdrawals[user_id]}")->find();
        
        if(IS_POST)
        {
                $model->create();
                
                // 如果是已经给用户转账 则生成转账流水记录
                if($model->status == 1 && $withdrawals['status'] != 1)
                {                
                    if($user['user_money'] < $withdrawals['money'])
                    {
                        $this->error("用户余额不足{$withdrawals['money']}，不够提现");    
                        exit;
                    }                                       
                    accountLog($withdrawals['user_id'], ($withdrawals['money'] * -1), 0,"平台提现");
                    $remittance = array(
                        'user_id' => $withdrawals['user_id'],
                        'bank_name' => $withdrawals['bank_name'],
                        'account_bank' => $withdrawals['account_bank'],
                        'account_name' => $withdrawals['account_name'],
                        'money' => $withdrawals['money'],
                        'status' => 1,
                        'create_time' => time(),
                        'admin_id' => session('admin_id'),
                        'withdrawals_id' => $withdrawals['id'],
                        'remark'=>$model->remark,
                    );
                    M('remittance')->add($remittance);
                }                
                $model->save();                               
                $this->success("操作成功!",U('remittance'),3);
                exit;
        }       
       if($user['nickname'])        
           $withdrawals['user_name'] = $user['nickname'];
       elseif($user['email'])        
           $withdrawals['user_name'] = $user['email'];
       elseif($user['mobile'])        
           $withdrawals['user_name'] = $user['mobile'];            
       
       $this->assign('user',$user);
       $this->assign('data',$withdrawals);
       $this->display();           
    }      
    
    /**
     *  商家结算记录
     */
    public function order_statis(){
        $model = M("order_statis");
        $store_id = I('store_id');        
        $create_date = I('create_date');
        $create_date = str_replace("+"," ",$create_date);
        $create_date2 = $create_date  ? $create_date  : date('Y-m-d',strtotime('-1 month')).' - '.date('Y-m-d',strtotime('+1 month'));
        $create_date3 = explode(' - ',$create_date2);
        $where = " create_date >= '".strtotime($create_date3[0])."' and create_date <= '".strtotime($create_date3[1])."' ";
        $store_id && $where .= " and store_id = $store_id ";
                        
        $count = $model->where($where)->count();
        $Page  = new Page($count,16);
        $list = $model->join('INNER JOIN __STORE__ ON __STORE__.store_id = __ORDER_STATIS__.store_id')->where($where)->order("`id` desc")->limit($Page->firstRow.','.$Page->listRows)->select();
        
        $this->assign('create_date',$create_date2);
        $show  = $Page->show();                 
        $this->assign('show',$show);
        $this->assign('list',$list);
        C('TOKEN_ON',false);
        $this->display();    
    }
}