<?php

namespace Home\Controller;

use Think\Controller;

class PurchaseController extends BaseController
{
    public function promote() {
        parent::_initialize();
        if (session('?user')) {
            $this->display();
        } else {
            $nologin = array(
                    'login','pop_login','do_login','logout','verify','set_pwd','finished',
                    'verifyHandle','reg','send_sms_reg_code','send_sms_reg_code2','identity','check_validate_code',
                    'forget_pwd','check_captcha','check_username','send_validate_code','regstore',
                    'ajax_seller','ajax_mobile','ajax_store','regstore2','regstoress','login3','registered','ajax_jump','putcode','is_beautiful_username','test'
            );
            if (!in_array(ACTION_NAME,$nologin)) {
                header("location:".U('/User/login'));
                exit;
            }
        }

        // 判断用户是否登陆

        // if(IS_AJAX){//前端请求
        //     $num = M('config')->where(array('id'=>88))->getField('value');
        //     $this->ajaxReturn($num);
        //
        // }elseif(IS_POST){//操作系统增加
        // $num = rand(1,3);
        //   M('config')->where(array('id'=>88))->setInc('value',$num);  exit;
        //
        // }else{
        //     $num = M('config')->where(array('id'=>88))->getField('value');
        //     $this->assign('num',$num);
        //     $this->display();
        //
        // }

    }
<<<<<<< HEAD

    public function ajaxdata()
   {
     if(IS_AJAX){
       $name = trim(I('val'));
       $data = M('store')->where("store_name like '%{$name}%'")->getField('store_id,store_name');
       if($data){
           $this->ajaxReturn($data);

       }else{
           $data[0] = '';
           $this->ajaxReturn($data);
       }
     }else{


     }
   }
=======
>>>>>>> 53da1d4a0acc6c4030495ab7a8be9674e5553335
}
