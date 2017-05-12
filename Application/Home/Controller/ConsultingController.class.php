<?php

namespace Home\Controller;
class ConsultingController extends BaseController 
{
	public function service()
	{

		$str = "725dac60-2709-11e7-97af-cd4d655a0e09";

		$send = M('msg')->where(array('send'=>$_GET['send'],'receive'=>$_GET['receive']))->order('')->select();
		$receive = M('msg')->where(array('send'=>$_GET['receive']))->order('')->select();


		$nickname = M('store')->where(array('user_id'=>I('receive')))->getField('store_name');//发送通道昵称

		$nickname2 = M('store')->where(array('store_id'=>I('send')))->getField('store_name');//接收通道昵称
		$nickname = $nickname?$nickname:'我';
		$this->assign('itme',date('Y-m-d H:i:s',time()));
		$this->assign('nickname',$nickname);
		$this->assign('nickname2',$nickname2);
		$this->assign('send',$send);
		$this->assign('receive',$receive);
		$this->display();
	}

	public function service2()
	{

		$send = M('msg')->where(array('receive'=>$_GET['receive']))->order('')->select();
		$receive = M('msg')->where(array('receive'=>$_GET['send']))->order('')->select();
		// $nickname1 = M('store')->where(array('store_id'=>I('send')))->getField('store_name');//发送通道昵称
		$nickname = M('store')->where(array('store_id'=>I('receive')))->getField('store_name');//接收通道昵称
		$nickname2 = M('store')->where(array('user_id'=>I('send')))->getField('store_name');//接收通道昵称
		$this->assign('itme',date('Y-m-d H:i:s',time()));
		$this->assign('nickname',$nickname);
		$this->assign('nickname2',$nickname2);
		$this->assign('send',$send);
		$this->assign('receive',$receive);
		$this->display();
	}




	public function addmsg2()
	{
		if(IS_AJAX){

			// $data['nickname'] = M('store')->where(array('user_id'=>I('send')))->getField('store_name');//发送通道昵称
			$data['nickname'] = M('store')->where(array('store_id'=>I('receive')))->getField('store_name');//接收通道昵称
			$data['msg'] = I('msg');
			$data['send'] = I('send');
			$data['receive'] = I('receive');
			$data['addtime'] = time();
			$res = M('msg')->add($data);
			$this->ajaxReturn($data['nickname']);
		}else{
			$this->_empty();
		}
	}


	public function addmsg()
	{
		if(IS_AJAX){

			// $data['nickname'] = M('store')->where(array('user_id'=>I('send')))->getField('store_name');//发送通道昵称
			$nickname = M('store')->where(array('user_id'=>I('receive')))->getField('store_name');//接收通道昵称
			$data['nickname'] = $nickname?$nickname2:'我';

			$data['msg'] = I('msg');
			$data['send'] = I('send');
			$data['receive'] = I('receive');
			$data['addtime'] = time();
			$res = M('msg')->add($data);
			$this->ajaxReturn($data['nickname']);
		}else{
			$this->_empty();
		}
	}



	public function _empty(){
		echo 404;
		// $this->display('404');
	}
}