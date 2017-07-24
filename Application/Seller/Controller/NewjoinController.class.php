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
namespace Seller\Controller;

class NewjoinController extends BaseController {
	public $user_id;
	public $apply = array();
	
	public function _initialize() {
        parent::_initialize();
        $this->user_id = cookie('user_id');
		if(empty($this->user_id) && ACTION_NAME !='index'){
			redirect(U('Seller/Admin/login'));
		}else if(!empty($this->user_id)){
			$this->apply = M('store_apply')->where(array('user_id'=>$this->user_id))->find();
		}
		$user = get_user_info($this->user_id);
		
		$this->assign('user',$user);
	}
	
	public function index(){
		$this->display();
	}
	
	public function contact(){
		// if($this->apply['apply_state'] == 1) redirect(U('Newjoin/apply_info'));
		if(IS_POST){
			$data = I('post.');
			if(empty($this->apply)){
				$data['user_id'] = $this->user_id;
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
	
	// 企业认证
	public function basic_info(){
		// if($this->apply['apply_state'] == 1) redirect(U('Newjoin/apply_info'));
		if ($this->apply['apply_type'] > 0){
			$this->error('您已经申请了个人认证,不能再申请企业认证','/Seller/Newjoin/basic');
		}
		$store_name = M('store')->where(array('store_id'=>session('store_id')))->getField('store_name');
		$this->assign('store_name',$store_name);
		if(IS_POST){

			if (!I('company_province')){
				$this->error('请填写公司所在地');
			} 
			if (!I('company_name')){
				$this->error('请填写公司名字');
			}
			if (!I('company_address')){
				$this->error('请填写公司详细地址');
			}
			$data = I('post.');
			$data['user_id'] = session('user')['user_id'];
			$data['add_time'] = time();
			$data2['sc_id'] = $data['sc_id'];
			$data2['apply_state'] = null;
			$store_id = session('seller')['store_id'];
			if (M('store_apply')->add($data)){
				M('store')->where(array('store_id'=>$store_id))->save($data2);
				$this->success('资料提交成功，正在审核！');
			} else {
				$this->error('资料提交失败，请重新填写！');
			}
		} else {
			$rate_list = array(0=>0,3=>3,6=>6,7=>7,11=>11,13=>13,17=>17);
			$company_type = array('股份有限公司','个人独立企业','有限责任公司','外资','中外合资','国企','合伙制企业','其它');
			$store_name = M('store')->where(array('store_id'=>session('store_id')))->getField('store_name');
			$this->assign('store_name',$store_name);
			$this->assign('company_type',$company_type);
			$this->assign('apply',$this->apply);
			$this->assign('rate_list',$rate_list);
			$province = M('region')->where(array('parent_id'=>0))->select();
			$this->assign('province',$province);
			$this->assign('store_class',M('store_class')->getField('sc_id,sc_name'));
			$this->assign('province',M('region')->where(array('parent_id'=>0,'level'=>1))->select());
			if(!empty($this->apply['company_province'])){
				$this->assign('city',M('region')->where(array('parent_id'=>$this->apply['company_province']))->select());
				$this->assign('district',M('region')->where(array('parent_id'=>$this->apply['company_city']))->select());
			}
			$this->display();
		}
		
	}
	
	// 个人认证
	public function basic(){
		// if ($this->apply['apply_state'] > 0) redirect(U('Newjoin/personal_info'));
		if ($this->apply['apply_type'] === '0'){
			$this->error('您已经申请了企业认证,不能再申请个人认证','/Seller/Newjoin/basic_info');
		}
		if(IS_POST){
			if (!I('store_person_name')){
				$this->error('请填写店铺负责人姓名');
			}
			if (!I('store_person_mobile')){
				$this->error('请填写店铺负责人手机号码');
			}
			$data = I('post.');
			$data['company_name'] = M('store')->where(array('store_id'=>session('store_id')))->getField('store_name');
			$data['user_id'] = session('user')['user_id'];
			$data['add_time'] = time();
			$data2['sc_id'] = $data['sc_id'];
			$data2['apply_state'] = null;
			$store_id = session('seller')['store_id'];
			if (M('store_apply')->add($data)){
				M('store')->where(array('store_id'=>$store_id))->save($data2);
				$this->success('资料提交成功，正在审核！');
			} else {
				$this->error('资料提交失败，请重新填写！');
			};
		} else {
			$rate_list = array(0=>0,3=>3,6=>6,7=>7,11=>11,13=>13,17=>17);
			$company_type = array('股份有限公司','个人独立企业','有限责任公司','外资','中外合资','国企','合伙制企业','其它');
			$this->assign('company_type',$company_type);
			$this->assign('apply',$this->apply);
			$this->assign('rate_list',$rate_list);
			$province = M('region')->where(array('parent_id'=>0))->select();
			$this->assign('province',$province);
			$this->assign('store_class',M('store_class')->getField('sc_id,sc_name'));
			$this->assign('province',M('region')->where(array('parent_id'=>0,'level'=>1))->select());
			if(!empty($this->apply['company_province'])){
				$this->assign('city',M('region')->where(array('parent_id'=>$this->apply['company_province']))->select());
				$this->assign('district',M('region')->where(array('parent_id'=>$this->apply['company_city']))->select());
			}
			$this->assign('province2',M('region')->where(array('parent_id'=>0,'level'=>1))->select());
			if(!empty($this->apply['bank_province'])){
				$this->assign('city2',M('region')->where(array('parent_id'=>$this->apply['bank_province']))->select());
			}
			$this->display();
		}
	}

	// 修改企业认证
	public function basicedit(){
		// if ($this->apply['apply_state'] > 0) redirect(U('Newjoin/apply_info'));
		// if($this->apply['apply_state'] == 1) redirect(U('Newjoin/apply_info'));
		if(IS_POST){
			$tmp = array();
			$data = I('post.');
			$data['user_id'] = session('user')['user_id'];
			$data3['add_time'] = time();
			$data2['sc_id'] = $data['sc_id'];
			$data2['apply_state'] = null;
			$data3['apply_state'] = '0';
			$store_id = session('seller')['store_id'];
			if (!M('store_apply')->where((array('user_id'=>$data['user_id'])))->find()) redirect(U('Newjoin/basic_info'));
			if (M('store_apply')->where(array('user_id'=>$data['user_id']))->save($data)){
				M('store_apply')->where(array('user_id'=>$data['user_id']))->save($data3);
				M('store')->where(array('store_id'=>$store_id))->save($data2);
				$this->success('资料提交成功，正在审核！');
			} else {
				$this->error('资料提交失败，请重新填写！');
			}
		} else {
			$rate_list = array(0=>0,3=>3,6=>6,7=>7,11=>11,13=>13,17=>17);
			$company_type = array('股份有限公司','个人独立企业','有限责任公司','外资','中外合资','国企','合伙制企业','其它');
			$this->assign('company_type',$company_type);
			$this->assign('apply',$this->apply);
			$this->assign('rate_list',$rate_list);
			$province = M('region')->where(array('parent_id'=>0))->select();
			$this->assign('province',$province);
			$this->assign('store_class',M('store_class')->getField('sc_id,sc_name'));
			$this->assign('province',M('region')->where(array('parent_id'=>0,'level'=>1))->select());
			if(!empty($this->apply['company_province'])){
				$this->assign('city',M('region')->where(array('parent_id'=>$this->apply['company_province']))->select());
				$this->assign('district',M('region')->where(array('parent_id'=>$this->apply['company_city']))->select());
			}
			$this->display('basic_info');
		}
	}

	// 修改个人认证
	public function info_edit(){
		// if ($this->apply['apply_state'] > 0) redirect(U('Newjoin/apply_info'));
		// if($this->apply['apply_state'] == 1) redirect(U('Newjoin/apply_info'));
		if(IS_POST){
			$tmp = array();
			$data = I('post.');
			$data['company_name'] = M('store')->where(array('store_id'=>session('store_id')))->getField('store_name');
			$data['user_id'] = session('user')['user_id'];
			$data3['add_time'] = time();
			$data3['apply_state'] = '0';
			$data2['sc_id'] = $data['sc_id'];
			$data2['apply_state'] = null;
			$store_id = session('seller')['store_id'];
			if (!M('store_apply')->where((array('user_id'=>$data['user_id'])))->find()) redirect(U('Newjoin/basic'));
			if (M('store_apply')->where((array('user_id'=>$data['user_id'])))->save($data)){
				M('store_apply')->where((array('user_id'=>$data['user_id'])))->save($data3);
				M('store')->where(array('store_id'=>$store_id))->save($data2);
				$this->success('资料提交成功，正在审核！');
			} else {
				$this->error('资料提交失败，请重新填写！');
			}
		} else {
			$rate_list = array(0=>0,3=>3,6=>6,7=>7,11=>11,13=>13,17=>17);
			$company_type = array('股份有限公司','个人独立企业','有限责任公司','外资','中外合资','国企','合伙制企业','其它');
			$this->assign('company_type',$company_type);
			$this->assign('apply',$this->apply);
			$this->assign('rate_list',$rate_list);
			$province = M('region')->where(array('parent_id'=>0))->select();
			$this->assign('province',$province);
			$this->assign('store_class',M('store_class')->getField('sc_id,sc_name'));
			$this->assign('province',M('region')->where(array('parent_id'=>0,'level'=>1))->select());
			if(!empty($this->apply['company_province'])){
				$this->assign('city',M('region')->where(array('parent_id'=>$this->apply['company_province']))->select());
				$this->assign('district',M('region')->where(array('parent_id'=>$this->apply['company_city']))->select());
			}
			$this->display('basic');
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
	
	public function personal_info(){
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