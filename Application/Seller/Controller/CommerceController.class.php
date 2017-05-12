<?php


namespace Seller\Controller;
class CommerceController extends IndexController 
{

	public function index()
	{	
		$store_id = session('store_id');
		$commerce_state = M('store')->where(array('store_id'=>$store_id))->getField('commerce_state');
		if (!$commerce_state){
			$records = M('records')->where(array('store_id'=>$store_id))->find();
			if (!$records){
				$data['order_id'] = date('YmdHis',time()).rand(111111,999999);
				$data['store_id'] = $store_id;
				$data['name'] = '加入云商会会员(首年)';
				$data['addtime'] = time();
				$data['price'] 	  = 1688;
				M('records')->add($data);
				$records['order_id'] = $data['order_id'];
			}
			if (!$store_id) {
				echo "<script>top.location.href='http://".$_SERVER['HTTP_HOST']."/User/login.html'</script>";
				exit;
			} else {
				include_once(PATH.'/plugins/payment/alipay/alipay3.class.php');
				$pay = new \alipay();
				$order['order_sn'] = $records['order_id'];
				$order['order_amount'] = 1688;
				// $order['store_id'] = $store_id;
				$config_value['bank_code'] = 1;
				$res = $pay->get_code($order,$config_value);
						echo $res;

			}
		
		} else {
			$this->error('您已经加入云商会了','/Seller/index/index');
		}
	}

	
	// 服务器点对点 // http://www.tp-shop.cn/index.php/Home/Payment/notifyUrl        
    public function notifyUrl(){  
    include_once(PATH.'/plugins/payment/alipay/alipay3.class.php');
	$pay = new \alipay();          
    $pay->response();            
         exit();
    }

    // 页面跳转 // http://www.tp-shop.cn/index.php/Home/Payment/returnUrl        
    public function returnUrl(){
    	 include_once(PATH.'/plugins/payment/alipay/alipay3.class.php');
		 $pay = new \alipay();  
         $result = $pay->respond2(); // $result['order_sn'] = '201512241425288593'; 

         if($result['status'] == 1){
         	 // M('records')->where(array('order_id'=>"{$result['order_sn']}"))->save(array('status'=>1));
         	$this->success('支付成功,您已经加入云商会','/seller/index/index?nav=order_list');
         }else{
         	 
         	$this->error('支付失败！','/seller/index/index?nav=order_list');
         }



         if(stripos($result['order_sn'],'recharge') !== false)
         {
         	$order = M('recharge')->where("order_sn = '{$result['order_sn']}'")->find();
         	$this->assign('order', $order);
         	if($result['status'] == 1)
         		$this->display('recharge_success');
         	else
         		$this->display('recharge_error');
         	exit();
         }
        
    }             




	public function confirm()
	{

		
		$this->assign('pre','100.00');
		$this->display();
	}



	public function pay()
	{

	}


}