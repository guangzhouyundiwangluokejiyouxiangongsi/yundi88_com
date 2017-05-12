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

namespace Seller\Controller;
use Think\Page;

class FinanceController extends BaseController {
    
    /*
     * 初始化操作
     */
    public function _initialize() {
       parent::_initialize();
    }    
    
   
    /**
     *  转账汇款记录
     */
    public function remittance(){
        $model = M("store_remittance");         
        $_GET = array_merge($_GET,$_POST);
        unset($_GET['create_time']);
        
        $user_id = I('user_id');
        $account_bank = I('account_bank');
        $account_name = I('account_name');
        
        $create_time = I('create_time');
        $create_time = str_replace("+"," ",$create_time);
        $create_time = $create_time  ? $create_time  : date('Y-m-d',strtotime('-1 year')).' - '.date('Y-m-d',strtotime('+1 day'));
        $create_time2 = explode(' - ',$create_time);
        $where = "store_id = ".STORE_ID." and create_time >= '".strtotime($create_time2[0])."' and create_time <= '".strtotime($create_time2[1])."' ";                     
        $user_id && $where .= " and user_id = $user_id ";
        $account_bank && $where .= " and account_bank like '%$account_bank%' ";
        $account_name && $where .= " and account_name like '%$account_name%' ";
                        
        $count = $model->where($where)->count();
        $Page  = new Page($count,2);
        $list = $model->where($where)->order("`id` desc")->limit($Page->firstRow.','.$Page->listRows)->select();        
        
        $this->assign('create_time',$create_time);        
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
        $model = M("store_withdrawals");
        $status = I('status');       
        $account_bank = I('account_bank');
        $account_name = I('account_name');        
        $create_time = I('create_time');
        $create_time = str_replace("+"," ",$create_time);
        $create_time = $create_time  ? $create_time  : date('Y-m-d',strtotime('-1 year')).' - '.date('Y-m-d',strtotime('+1 day'));
        $create_time2 = explode(' - ',$create_time);
        $where = "store_id = ".STORE_ID." and create_time >= '".strtotime($create_time2[0])."' and create_time <= '".strtotime($create_time2[1])."' ";
        
        if($status === '0' || $status > 0)
            $where .= " and status = $status ";            
        $account_bank && $where .= " and account_bank like '%$account_bank%' ";
        $account_name && $where .= " and account_name like '%$account_name%' ";
                        
        $count = $model->where($where)->count();
        $Page  = new Page($count,16);
        $list = $model->where($where)->order("`id` desc")->limit($Page->firstRow.','.$Page->listRows)->select();        
                
        $show  = $Page->show();
        $store = M('store')->where("store_id = ".STORE_ID)->find();
         
        $this->assign('store',$store);
        $this->assign('create_time',$create_time);
        $this->assign('show',$show);
        $this->assign('list',$list);
        C('TOKEN_ON',false);        
        $this->display();    
    }
    
    /**
     * 添加提现申请
     */
    public function add_edit_withdrawals()
    {
        $id = I('id',0);
        $Model = M('StoreWithdrawals');
        $withdrawals = $Model->where(array('id'=>$id,'store_id'=>STORE_ID))->find();

         if(IS_POST)
         {
             if($withdrawals['status'] == 1)
                $this->error('申请成功的不能再修改');    
             
             $Model->create();             
             if($Model->id)
             {
                 $Model->save();
             }else
             {
                $Model->store_id = STORE_ID; //
                $Model->create_time = time();
                $Model->add();
             }             
             $this->success('保存完成',U('withdrawals'));
             exit();
          }
        $withdrawals_max = M('store')->where(array('store_id'=>STORE_ID))->getField('store_money');
        $withdrawals_min = tpCache('basic.min');
        $this->assign('withdrawals_max',$withdrawals_max);
        $this->assign('withdrawals_min',$withdrawals_min);
        $this->assign('withdrawals',$withdrawals);
        $this->display('_withdrawals');
    }

    /**
     * 删除申请记录
     */
    public function delWithdrawals()
    {                        
        $id = I('id');
        $model = M("StoreWithdrawals"); 
        $model->where("id = $id and store_id =".STORE_ID." and status != 1")->delete(); 
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
                $this->success("操作成功!",U('Admin/Finance/remittance'),3);
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
        $create_date = I('create_date');
        $create_date = str_replace("+"," ",$create_date);
        $create_date2 = $create_date  ? $create_date  : date('Y-m-d',strtotime('-1 month')).' - '.date('Y-m-d',strtotime('+1 month'));
        $create_date3 = explode(' - ',$create_date2);
        $where = "store_id = ".STORE_ID." and create_date >= '".strtotime($create_date3[0])."' and create_date <= '".strtotime($create_date3[1])."' ";
                        
        $count = $model->where($where)->count();
        $Page  = new Page($count,16);
        $list = $model->where($where)->order("`id` desc")->limit($Page->firstRow.','.$Page->listRows)->select();        
        
        $this->assign('create_date',$create_date2);
        $show  = $Page->show();
        $this->assign('show',$show);
        $this->assign('list',$list);
        C('TOKEN_ON',false);
        $this->display();    
    }

    public function order_no_statis()
    {
        $create_date = I('create_date');
        $create_date = str_replace("+"," ",$create_date);
        $create_date2 = $create_date ? $create_date : date('Y-m-d', strtotime('-1 month')) . ' - ' . date('Y-m-d', strtotime('+1 month'));
        $create_date3 = explode(' - ', $create_date2);
        $where = array(
            'store_id' => STORE_ID,
            'pay_status' => 1,
            'add_time' => array(array('gt', strtotime($create_date3[0])), array('lt', strtotime($create_date3[1]))),
            'is_checkout' => 0
        );
        $model = M('order');
        $count = $model->where($where)->count();
        $Page = new Page($count, 16);
        $list = $model->where($where)->order("`add_time` desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('create_date', $create_date2);
        $show = $Page->show();
        $order_status = C('ORDER_STATUS');
        $shipping_status = C('SHIPPING_STATUS');
        $this->assign('shipping_status',$shipping_status);
        $this->assign('order_status',$order_status);
        $this->assign('show', $show);
        $this->assign('list', $list);
        C('TOKEN_ON', false);
        $this->display();
    }

    public function baike()
    {
        $this->display();
    }
}