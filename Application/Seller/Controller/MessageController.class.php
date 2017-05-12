<?php
namespace Seller\Controller;
use Think\Controller;

class MessageController extends Controller 
{
    

    public function add()
    {
        if(IS_POST){
            if (!$_POST['code']){
              echo "<script>alert('请填写验证码！');window.history.go(-1);</script>";
              exit;
            }
            $email_ = "/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/";
            $qq_ = "/[1-9][0-9]{4,}/";
            $mobile_ = "/^(13[0-9]|15[0|3|6|7|8|9]|18[8|9])\d{8}$/";
            $tel_ = "/\d{3}-\d{8}|\d{4}-\d{7}/";

            if(!preg_match($email_, $_POST['email']) && !preg_match($qq_, $_POST['qq']) && !preg_match($mobile_, $_POST['tel']) &&  !preg_match($tel_, $_POST['tel'])){
                 echo "<script>alert('邮箱、电话、手机、QQ必须填写一样！');window.history.go(-1);</script>";
              exit;
            }
            if (!$_POST['store_id']){
              echo "<script>alert('网络故障，请刷新页面重新提交！');window.history.go(-1);</script>";
              exit;
            }
            if (!$_POST['username']){
              echo "<script>alert('请填写用户名！');window.history.go(-1);</script>";
              exit;
            }
            if (mb_strlen($_POST['text']) > 500){
               echo "<script>alert('字数不能超出500个！');window.history.go(-1);</script>";
               exit;
            }
            $verify = new \Think\Verify(); 
             $code = $verify->check($_POST['code']);
             if(!$code){
                echo "<script>alert('验证码错误！');window.history.go(-1);</script>";
                exit;
             }
            if(!$_POST['username']){ echo "<script>alert('未知错误！');window.history.go(-1);</script>";exit;}
            $message = M('store_message');
            $_POST['addtime'] = time();
            // $data = $message->create($_POST, 1); //根据表单提交的POST数据创建数据对象
            // if(!$data){
            //   $erroer = $message->getError();
            //   // $this->error($erroer);
            // }
            $res = $message->add($_POST);
            if($res){
                // $useremail = M('store')->where(array('store_id'=>$data['store_id']))->field('email')->find();
                //向用户发送邮件
                //  $time = date('Y-m-d H:i:s',$data['addtime']);
                //  $body = "留言者姓名:{$data['username']}<br>
                //           留言者电话:{$data['tel']}<br>
                //           留言者邮箱:{$data['email']}<br>
                //           留言者Q  Q:{$data['qq']}<br>
                //           留言内容:{$data['text']}<br>
                //           留言页面:{$data['url']}<br>
                //           留言时间:{$time}<br> ";
                // $email_res = send_email($useremail['email'],'云狄建站提醒您，您有新的留言。',$body);
                echo "<script>alert('留言成功，我们会尽快与您联系。');location.href='/Store/index/store_id/{$_POST['store_id']}.html';</script>";
            }else{
                 echo "<script>alert('提交失败！');window.history.go(-1);</script>";
            }
        }else{
            $this->redirect('Login/login');
        }
    }



    /**
     * 验证码
     */
    public function code()
    {
        $config = array('fontSize' => 100,'useCurve' => false,'length' => 4);
        $Verify = new \Think\Verify($config);
        $Verify->entry();
    }



    public function _empty()
    {
    	$this->display('Public/404');
    }





}