<?php

namespace Home\Controller;

use Think\Controller;
class MachineController extends Controller 
{

	public function index()
	{
		if(file_exists('.'.session('user.head_pic'))){
			$icon = session('user.head_pic');
		}else{
			$icon = '/Template/pc/yundi/Static/Machine/img/yunjqr.png';
		}
		$this->assign('icon',$icon);
		$this->assign('data',$this->data());
		$this->display();
	}

	public function ajaxdata()
	{
		if(IS_AJAX){
			$val = trim(I('val'));
			$val = str_replace('/', '\/', $val);
			if(!$val){
			$res = array('content'=>"你傻呀？",'time'=>date('Y-m-d :H:i:s',time()));
			$this->ajaxReturn($res);
			}

			$data = $this->data();
			if($data[$val]){
				$res = array('content'=>$data[$val]['content'],'time'=>date('Y-m-d :H:i:s',time()));
				$this->ajaxReturn($res);
			}else{
				foreach($data as $v){
					if(preg_match("/{$val}/", $v['title'])){

						$res = array('content'=>$v['content'],'time'=>date('Y-m-d :H:i:s',time()));
						$this->ajaxReturn($res);
					}
				}
			}

			foreach($data as $vv){
				if(preg_match("/{$val}/", $vv['content'])){
						$res = array('content'=>$vv['content'],'time'=>date('Y-m-d :H:i:s',time()));
						$this->ajaxReturn($res);
					}
			}
			$res = array('content'=>"很抱歉！这个问题云云还没有答案呢，请您先拨打020-81000500的客服热线进行咨询，或者咨询我们的在线客服。云云会在全年无休的学习为您竭诚服务的哦，祝您生意兴隆，财源广进！",'time'=>date('Y-m-d :H:i:s',time()));
						$this->ajaxReturn($res);

		}else{

		}
	}



	public function data()
	{
		return $data = array(
			1=>array('title'=>'怎么发布产品需要什么操作？','content'=>'您好，请由此登录 http://'.$_SERVER['HTTP_HOST'].'/User/login.html 进入会员中心→产品推广→产品发布，填写商品信息→填写商品基本信息→图片和详细说明→物流运费信息→其他信息→提交。发布成功！详情请点击查看教程。'),


			2=>array('title'=>'怎么建设/开通网站？','content'=>'您好，请由此登录 http://'.$_SERVER['HTTP_HOST'].'/User/login.html 进入会员中心→网站模版→选择官网/手机/微商城模版→点击查看或者应用模版数据，接下来→网站建设→网站设置→编辑相关资料提交完成，网站搭建完毕，可在前台预览效果。详情请点击查看教程'),


			3=>array('title'=>'发布商品可发布多少信息？','content'=>'您好，云狄网不限制用户发多少条产品信息，但用户所发商品信息不能涉及到黄、毒、博彩、军火等非法信息，一经查属立即永久封闭账号，并依法追究责任。'),

			4=>array('title'=>'怎么提高产品信息排名？','content'=>'您好，每周定期更新产品，每日定时打理平台（也就是我们常说的重发供应信息）。
因为99%的客户，是通过搜索商机寻找供应商的，发布商机可以提高曝光率；可以让客户能够了解您的产品，主动找上门来。产品24小时只能重发一次，所以建议您可以多增加发布产品信息条数。详情请点击查看教程'),

			5=>array('title'=>'怎么编辑/删除/下架产品?','content'=>'您好，您可在后台的产品推广-产品管理-删除/增加。详情请点击查看教程'),

			6=>array('title'=>'发布商品为什么必须填写价格问题？','content'=>'您好，为了杜绝信息不全，增加平台信息的真实性，发布产品是价格必填。详情请点击查看教程'),

			7=>array('title'=>'认证信息审核不通过/审核多久？','content'=>'您好，提交企业/个人认证信息后，一直显示在审核中，有以下可能性：(1）信息需要人工审核，由于待审信息量大，没来得及审核通过；（2）您在待审状态下反复修改提交，造成推送时间不断向后推迟（3）如果是初次上传图片，或修改过图片，在审核后需要对图片进行处理。（4）一般审核时间为3个工作日即通过。如果待处理信息过多，也会造成一定延迟,详情请点击查看教程 或 咨询客服'),

			// 8=>array('title'=>'','content'=>'很抱歉！这个问题云云还没有答案呢，请您先拨打020-81000500的客服热线进行咨询，或者咨询我们的在线客服。小狄会在全年无休的学习为您竭诚服务的哦，祝您生意兴隆，财源广进！'),

			);
	}

}

