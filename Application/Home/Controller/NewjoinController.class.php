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
 * Date: 2016-05-29
 */

namespace Home\Controller;

class NewjoinController extends BaseController {
	public $user_id;
	public $apply = array();
	
	public function _initialize() {
        parent::_initialize();
        $this->user_id = cookie('user_id');
		if(empty($this->user_id) && ACTION_NAME !='index'){
			redirect(U('User/login'));
		}else if(!empty($this->user_id)){
			$this->apply = M('store_apply')->where(array('user_id'=>$this->user_id))->find();
		}
		$user = get_user_info($this->user_id);
		if(empty($user['password'])){
			$this->error('您使用的是第三方账号登陆，请先设置账号密码',U('User/password'));
		}
		$this->assign('user',$user);
	}
	
	public function index(){
		$this->display();
	}
	
	public function contact(){
		if($this->apply['apply_state'] == 1) redirect(U('Newjoin/apply_info'));
		if(IS_POST){
			$data = I('post.');
			if(empty($this->apply)){
				$data['user_id'] = $this->user_id;
				$data['seller_id'] = $this->user_id;
				$data['add_time'] = time();
				if(M('store_apply')->add($data)){
					if($data['apply_type'] == 0){
						redirect(U('Newjoin/basic_info'));
					}else{
						redirect(U('Newjoin/basic_info',array('apply_type'=>1)));
					}
				}else{
					$this->error('服务器繁忙,请联系官方客服');
				}
			}else{
				M('store_apply')->where(array('user_id'=>$this->user_id))->save($data);
				redirect(U('Newjoin/basic_info',array('apply_type'=>$data['apply_type'])));
			}
		}
		$this->assign('apply',$this->apply);
		$this->display();
	}
	
	public function basic_info(){
		// if($this->apply['apply_state'] == 1) redirect(U('Newjoin/apply_info'));
		if(IS_POST){
			$data['user_id'] = $this->user_id;
			$data['add_time'] = time();
			// die;
			// dump($data1);die;
			// dump($_POST);die;
			$data = I('post.supplier');
			M('store_apply')->where(array('user_id'=>$this->user_id))->save($data);
			redirect(U('Newjoin/seller_info'));
		}
		$rate_list = array(0=>0,3=>3,6=>6,7=>7,11=>11,13=>13,17=>17);
		$company_type = array('股份有限公司','个人独立企业','有限责任公司','外资','中外合资','国企','合伙制企业','其它');
		$this->assign('company_type',$company_type);
		$this->assign('apply',$this->apply);
		$this->assign('rate_list',$rate_list);
		$province = M('region')->where(array('parent_id'=>0))->select();
		$this->assign('province',$province);
		if(!empty($this->apply['company_province'])){
			$this->assign('city',M('region')->where(array('parent_id'=>$this->apply['company_province']))->select());
			$this->assign('district',M('region')->where(array('parent_id'=>$this->apply['company_city']))->select());
		}
		$apply_type = I('apply_type',0);
		if($apply_type == 1 || $this->apply['apply_type'] == 1){
			$this->assign('store_class',M('store_class')->getField('sc_id,sc_name'));
			$this->assign('goods_category',M('goods_category')->where(array('parent_id'=>0))->getField('id,name'));
			$this->assign('province',M('region')->where(array('parent_id'=>0,'level'=>1))->select());
			$this->display('basic');
		}else{
			$this->display();
		}
	}
	
	public function agreement(){
		if(!empty($this->apply)){
			if($this->apply['apply_state'] == 1){
				redirect(U('Newjoin/apply_info'));
			}else if($this->apply['apply_state'] == 0 && empty($this->apply['company_name'])){
				redirect(U('Newjoin/basic_info'));
			}else if(empty($this->apply['store_name'])){
				if($this->apply['apply_type'] == 1){
					redirect(U('Newjoin/basic'));
				}else{
					redirect(U('Newjoin/seller_info'));
				}
			}else if($this->apply['apply_state'] == 0 && empty($this->apply['business_licence_cert'])){
				redirect(U('Newjoin/remark'));
			}else{
				redirect(U('Newjoin/apply_info'));
			}
		}
		if(IS_POST){
			redirect(U('Newjoin/contact'));
		}		
		$this->display();
	}
	
	public function seller_info(){
		if($this->apply['apply_state'] == 1) redirect(U('Newjoin/apply_info'));
		if(IS_POST){
			$data = I('post.');
			if(!empty($data['store_class_ids'])){
				$data['store_class_ids'] = serialize($data['store_class_ids']);
			}
			if($this->apply['apply_type'] == 1){
				//个人申请
				if(empty($this->apply['legal_identity_cert'])){
					foreach($_FILES as $k=>$v){
						if(empty($v['tmp_name'])){
							$this->error('请上传必要证件');
						}
					}
					$upload = new \Think\Upload();//实例化上传类
					$upload->maxSize   =  1024*1024*3;//设置附件上传大小 管理员10M  否则 3M
					$upload->exts      =  array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
					$upload->savePath  =  'store/cert/'; // 设置附件上传根目录
					$upload->replace   =  true; //存在同名文件是否是覆盖，默认为false
					$upinfo  =  $upload->upload($_FILES);
					if(!$upinfo) {
						$this->error($upload->getError());//上传错误提示错误信息
					}else{
						foreach($upinfo as $key => $val){
							$data[$key] = '/Public/upload/'.$val['savepath'].$val['savename'];
						}
					}
				}
			}
			M('store_apply')->where(array('user_id'=>$this->user_id))->save($data);
			if($this->apply['apply_type'] == 1){
				redirect(U('Newjoin/apply_info'));
			}else{
				redirect(U('Newjoin/remark'));
			}
		}
		$this->assign('apply',$this->apply);
		$this->assign('store_class',M('store_class')->getField('sc_id,sc_name'));
		if(!empty($this->apply['store_class_ids'])){
			$goods_cates = M('goods_category')->getField('id,name,commission');
			$store_class_ids = unserialize($this->apply['store_class_ids']);
			foreach ($store_class_ids as $val){
				$cat = explode(',', $val);
				$bind_class_list[] = array('class_1'=>$goods_cates[$cat[0]]['name'],'class_2'=>$goods_cates[$cat[1]]['name'],
					'class_3'=>$goods_cates[$cat[2]]['name'].'(分佣比例：'.$goods_cates[$cat[2]]['commission'].'%)','value'=>$val
				);
			}
			$this->assign('bind_class_list',$bind_class_list);
		}
		$this->assign('goods_category',M('goods_category')->where(array('parent_id'=>0))->getField('id,name'));
		$this->assign('province',M('region')->where(array('parent_id'=>0,'level'=>1))->select());
		if(!empty($this->apply['bank_province'])){
			$this->assign('city',M('region')->where(array('parent_id'=>$this->apply['bank_province']))->select());
		}
		$this->display();
	}
	
	public function query_progress(){
		$this->display();
	}
	
	public function remark(){
		if($this->apply['apply_state'] == 1) redirect(U('Newjoin/apply_info'));
		if(IS_POST){
			$data = I('post.');
			$flag = false;
			foreach($_FILES as $k=>$v){
				if(!empty($v['tmp_name'])){
					$flag = true;
				}
			}
			if($flag){
				$upload = new \Think\Upload();//实例化上传类
				$upload->maxSize   =  1024*1024*3;//设置附件上传大小 管理员10M  否则 3M
				$upload->exts      =  array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
				$upload->savePath  =  'store/cert/'; // 设置附件上传根目录
				$upload->replace   =  true; //存在同名文件是否是覆盖，默认为false
				$upinfo  =  $upload->upload($_FILES);
				if(!$upinfo) {
					$this->error($upload->getError());//上传错误提示错误信息
				}else{
					foreach($upinfo as $key => $val)
					{
						$data[$key] = '/Public/upload/'.$val['savepath'].$val['savename'];
					}
				}
			}
			$data['apply_state'] = 0 ;//每次提交资料回到待审核状态
			M('store_apply')->where(array('user_id'=>$this->user_id))->save($data);
			$this->success('提交成功',U('Newjoin/apply_info'));
		}

		$this->assign('apply',$this->apply);
		$this->display();
	}
	
	public function apply_info(){
		$this->assign('apply',$this->apply);
		if(IS_POST){
			$paying_amount_cert = I('paying_amount_cert');
			if(empty($paying_amount_cert)){
				$this->error('请上传支付凭证');
			}else{
				M('store_apply')->where(array('user_id'=>$this->user_id))->save(array('paying_amount_cert'=>$paying_amount_cert));
				$this->success('提交成功');
			}
		}
		$this->display();
	}
	
	public function check_company(){
		$company_name = I('company_name');
		if(empty($company_name)) exit('fail');
		if($company_name && M('store_apply')->where(array('company_name'=>$company_name))->count()>0){
			exit('fail');
		}
		exit('success');
	}
	
	public function check_store(){
		$store_name = I('store_name');
		if(empty($store_name)) exit('fail');
		if(M('store_apply')->where(array('store_name'=>$store_name))->count()>0){
			exit('fail');
		}
		exit('success');
	}
	
	public function check_seller(){
		$seller_name = I('seller_name');
		if(empty($seller_name)) exit('fail');
		if(M('seller')->where(array('seller_name'=>$seller_name))->count()>0){
			exit('fail');
		}
		exit('success');
	}

	public function question(){
		$cat_id = I('cat_id');
	    $article = M('article')->where("cat_id=$cat_id")->select();
    	if($article){
    		$parent = M('article_cat')->where(array('cat_id'=>$cat_id))->find();
    		$this->assign('cat_name',$parent['cat_name']);
    		$this->assign('article',$article[0]);
    		$this->assign('article_list',$article);
    	}
    	$this->display('Article/detail');
	}
}