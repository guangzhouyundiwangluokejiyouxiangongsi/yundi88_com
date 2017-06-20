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
 * Date: 2016-05-27
 */

namespace Admin\Controller;
use Admin\Logic\StoreLogic;

class StoreController extends BaseController{
	
	//店铺等级
	public function store_grade(){
		$model =  M('store_grade');
		$count = $model->where('1=1')->count();
		$Page = new \Think\Page($count,10);
		$list = $model->order('sg_id')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$show = $Page->show();
		$this->assign('page',$show);
		$this->display();
	}
	
	public function grade_info(){
		$sg_id = I('sg_id');
		if($sg_id){
			$info = M('store_grade')->where("sg_id=$sg_id")->find();
			$this->assign('info',$info);
		}
		$this->display();
	}
	
	public function grade_info_save(){
		$data = I('post.');
		if($data['sg_id'] > 0 || $data['act']=='del'){
			if($data['act'] == 'del'){
				if(M('store')->where(array('grade_id'=>$data['del_id']))->count()>0){
					respose('该等级下有开通店铺，不得删除');
				}else{
					$r = M('store_grade')->where("sg_id=".$data['del_id'])->delete();
					respose(1);
				}
			}else{
				$r = M('store_grade')->where("sg_id=".$data['sg_id'])->save($data);
			}
		}else{
			$r = M('store_grade')->add($data);
		}
		if($r){
			$this->success('编辑成功',U('Store/store_grade'));
		}else{
			$this->error('提交失败');
		}
	}
	
	public function store_class(){
		$model =  M('store_class');
		$count = $model->where('1=1')->count();
		$Page = new \Think\Page($count,10);
		$list = $model->order('sc_id')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$show = $Page->show();
		$this->assign('page',$show);
		$this->display();
	}
	
	//店铺分类
	public function class_info(){
		$sc_id = I('sc_id');
		if($sc_id){
			$info = M('store_class')->where("sc_id=$sc_id")->find();
			$this->assign('info',$info);
		}
		$this->display();
	}
	
	public function class_info_save(){
		$data = I('post.');
		if($data['sc_id'] > 0 || $data['act']=='del'){
			if($data['act']== 'del'){
				if(M('store')->where(array('sc_id'=>$data['del_id']))->count()>0){
					respose('该分类下有开通店铺，不得删除');
				}else{
					$r = M('store_class')->where("sc_id=".$data['del_id'])->delete();
					respose(1);
				}
			}else{
				$r = M('store_class')->where("sc_id=".$data['sc_id'])->save($data);
			}
		}else{
			$r = M('store_class')->add($data);
		}
		if($r){
			$this->success('编辑成功',U('Store/store_class'));
		}else{
			$this->error('提交失败');
		}
	}
	
	//普通店铺列表
	public function store_list(){
		$model =  M('store');
		$map['is_own_shop'] = 0 ;
		$grade_id = I("grade_id");
		if($grade_id>0) $map['grade_id'] = $grade_id;
		$sc_id =I('sc_id');
		if($sc_id>0) $map['sc_id'] = $sc_id;
		$store_id = I('store_id');
		if($store_id)$map['store_id'] = $store_id;
		$store_state = I("store_state");
		if($store_state>0)$map['store_state'] = $store_state;
		$seller_name = I('seller_name');
		if($seller_name) $map['seller_name'] = array('like',"%$seller_name%");
		$store_name = I('store_name');
		if($store_name) $map['store_name'] = array('like',"%$store_name%");
		$count = $model->where($map)->count();
		$Page = new \Think\Page($count,10);
		$list = $model->where($map)->order('store_id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		
		$show = $Page->show();
		$this->assign('page',$show);
		$store_grade = M('store_grade')->getField('sg_id,sg_name');
		$this->assign('store_grade',$store_grade);
		$this->assign('store_class',M('store_class')->getField('sc_id,sc_name'));
		$this->display();
	}
	
	/*添加店铺*/
	public function store_add(){
		if(IS_POST){
			$store_name = I('store_name');
			$user_name = I('user_name');
			$seller_name = I('seller_name');
			if(M('store')->where("store_name='$store_name'")->count()>0){
				$this->error("店铺名称已存在");
			}
			if(M('seller')->where("seller_name='$seller_name'")->count()>0){
				$this->error("此名称已被占用");
			}
			$user_id = M('users')->where("email='$user_name' or mobile='$user_name'")->getField('user_id');
			if($user_id){
				if(M('store')->where(array('user_id'=>$user_id))->count()>0){
					$this->error("该会员已经申请开通过店铺");
				}
			}
			$store = array('store_name'=>$store_name,'user_name'=>$user_name,'store_state'=>1,
					'seller_name'=>$seller_name,'password'=>I('password'),
					'store_time'=>time(),'is_own_shop'=>I('is_own_shop')
			);
			$storeLogic = new StoreLogic();
			if($storeLogic->addStore($store)){
				if(I('is_own_shop') == 1){
					$this->success('店铺添加成功',U('Store/store_own_list'));
				}else{
					$this->success('店铺添加成功',U('Store/store_list'));
				}
				exit;
			}else{
				$this->error("店铺添加失败");
			}
		}
		$is_own_shop = I('is_own_shop',1);
		$this->assign('is_own_shop',$is_own_shop);
		$this->display();
	}
	
	/*验证店铺名称，店铺登陆账号*/
	public function store_check(){
		$store_name = I('store_name');
		$seller_name = I('seller_name');
		$user_name = I('user_name');
		$res = array('stat'=>'ok');
		if($store_name && M('store')->where("store_name='$store_name'")->count()>0){
			$res = array('stat'=>'fail','msg'=>'店铺名称已存在');
		}
		
		if(!empty($user_name)){
			if(!check_email($user_name) && !check_mobile($user_name)){
				$res = array('stat'=>'fail','msg'=>'店主账号格式有误');
			}
			if(M('users')->where("email='$user_name' or mobile='$user_name'")->count()>0){
				$res = array('stat'=>'fail','msg'=>'会员名称已被占用');
			}
		}

		if($seller_name && M('seller')->where("seller_name='$seller_name'")->count()>0){
			$res = array('stat'=>'fail','msg'=>'此账号名称已被占用');
		}
		respose($res);
	}
	
	/*编辑自营店铺*/
	public function store_edit(){
		if(IS_POST){
			$data = I('post.');
			if(M('store')->where("store_id=".$data['store_id'])->save($data)){
				$this->success('编辑店铺成功',U('Store/store_own_list'));
				exit;
			}else{
				$this->error('编辑失败');
			}
		}
		$store_id = I('store_id',0);
		$store = M('store')->where("store_id=$store_id")->find();
		$this->assign('store',$store);
		$this->display();
	}
	
	//编辑外驻店铺
	public function store_info_edit(){
		if(IS_POST){
			$map = I('post.');
			$store = $map['store'];
			unset($map['store']);
			$a = M('store')->where(array('store_id'=>$map['store_id']))->save($store);
			$b = M('store_apply')->where(array('user_id'=>$map['user_id']))->save($map);
			if($b || $a){
				if($store['store_state'] == 0){
					//关闭店铺，同时下架店铺所有商品
					M('goods')->where(array('store_id'=>$map['store_id']))->save(array('is_on_sale'=>0));
				}
				$this->success('编辑店铺成功',U('Store/store_list'));
				exit;
			}else{
				$this->error('编辑失败');
			}
		}
		$store_id = I('store_id');
		if($store_id>0){
			$store = M('store')->where("store_id=$store_id")->find();
			$this->assign('store',$store);
			$apply = M('store_apply')->where('user_id='.$store['user_id'])->find();
			$this->assign('apply',$apply);
		}
		$this->assign('store_grade',M('store_grade')->getField('sg_id,sg_name'));
		$this->assign('store_class',M('store_class')->getField('sc_id,sc_name'));
		$province = M('region')->where(array('parent_id'=>0,'level'=>1))->select();
		$this->assign('province',$province);
		$this->display();
	}
	
	/*删除店铺*/
	// public function store_del(){
	// 	$store_id = I('del_id');
	// 	if($store_id > 1){
	// 		$store = M('store')->where("store_id=$store_id")->find();
	// 		if(M('goods')->where("store_id=$store_id")->count()>0){
	// 			respose('该店铺有发布商品，不得删除');
	// 		}else{
	// 			M('store')->where("store_id=$store_id")->delete();
	// 			M('seller')->where("store_id=$store_id")->delete();
	// 			adminLog("删除店铺".$store['store_name']);
	// 			respose(1);
	// 		}
	// 	}else{
	// 		respose('基础自营店，不得删除');
	// 	}
	// }




	public function store_del()
	{
		$store_id = I('del_id');
		if(!$store_id)return;
		//自定义导航表
		M('store_navigation')->where(array('sn_store_id'=>$store_id))->delete();
		//文章表
		M('store_art')->where(array('store'=>$store_id))->delete();
		//商品分类表
		M('store_goods_class')->where(array('store_id'=>$store_id))->delete();
		//商品表
		M('goods')->where(array('store_id'=>$store_id))->delete();

		//认证数据
		$user_id = M('store')->where(array('store_id'=>$store_id))->getField('user_id');
		M('store_apply')->where(array('user_id'=>$user_id))->delete();
		//store
		M('store')->where(array('store_id'=>$store_id))->delete();
		//store_mod 自定义模块
		M('store_mod')->where(array('store_id'=>$store_id))->delete();
		//相册
		$photoid = M('photo')->field("group_concat(id) as photoid")->where(array('store_id'=>$store_id))->select()[0]['photoid'];
		
		M('photoimg')->where('photoid in('.$photoid.')')->delete();
		M('photo')->where(array('store_id'=>$store_id))->delete();

		//user
		M('users')->where(array('user_id'=>$user_id))->delete();

		//商户表
		M('seller')->where(array('user_id'=>$user_id))->delete();

		//yd_store_extend
		M('store_extend')->where(array('store_id'=>$store_id))->delete();
		respose(1);
	}
	
	//店铺信息
	public function store_info(){
		$store_id = I('store_id');
		$store = M('store')->where("store_id=".$store_id)->find();
		$this->assign('store',$store);
		$apply = M('store_apply')->where("user_id=".$store['user_id'])->find();
		$this->assign('apply',$apply);
		$bind_class_list = M('store_bind_class')->where("store_id=".$store_id)->select();
		$goods_class = M('goods_category')->getField('id,name');
		for($i = 0, $j = count($bind_class_list); $i < $j; $i++) {
			$bind_class_list[$i]['class_1_name'] = $goods_class[$bind_class_list[$i]['class_1']];
			$bind_class_list[$i]['class_2_name'] = $goods_class[$bind_class_list[$i]['class_2']];
			$bind_class_list[$i]['class_3_name'] = $goods_class[$bind_class_list[$i]['class_3']];
		}
		$this->assign('bind_class_list',$bind_class_list);
		$this->display();
	}
	
	//自营店铺列表
	public function store_own_list(){
		$model =  M('store');
		$map['is_own_shop'] = 1 ;
		$grade_id = I("grade_id");
		if($grade_id>0) $map['grade_id'] = $grade_id;
		$sc_id =I('sc_id');
		if($sc_id>0) $map['sc_id'] = $sc_id;
		$store_state = I("store_state");
		if($store_state>0)$map['store_state'] = $store_state;
		$seller_name = I('seller_name');
		if($seller_name) $map['seller_name'] = array('like',"%$seller_name%");
		$store_name = I('store_name');
		if($store_name) $map['store_name'] = array('like',"%$store_name%");
		$count = $model->where($map)->count();
		$Page = new \Think\Page($count,10);
		$list = $model->where($map)->order('store_id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);	
		$show = $Page->show();
		$this->assign('page',$show);
		$store_grade = M('store_grade')->getField('sg_id,sg_name');
		$this->assign('store_grade',$store_grade);
		$this->assign('store_class',M('store_class')->getField('sc_id,sc_name'));
		$this->display();
	}
	
	//店铺申请列表
	public function apply_list(){
		$model =  M('store_apply');
		$map['apply_state'] = array('neq',1);
		$grade_id = I("grade_id");
		if($grade_id>0) $map['grade_id'] = $grade_id;
		$sc_id =I('sc_id');
		if($sc_id>0) $map['sc_id'] = $sc_id;
		$seller_name = I('seller_name');
		if($seller_name) $map['seller_name'] = array('like',"%$seller_name%");
		$store_name = I('store_name');
		if($store_name) $map['store_name'] = array('like',"%$store_name%");
		$count = $model->where($map)->count();
		$Page = new \Think\Page($count,10);
		$list = $model->where($map)->order('add_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$show = $Page->show();
		$this->assign('page',$show);
		$this->assign('store_grade',M('store_grade')->getField('sg_id,sg_name'));
		$this->assign('store_class',M('store_class')->getField('sc_id,sc_name'));
		$this->display();
	}
	
	public function apply_del(){
		$id = I('del_id');
		if($id && M('store_apply')->where(array('id'=>$id))->delete()){
			$this->success('操作成功',U('Store/apply_list'));
		}else{
			$this->error('操作失败');
		}
	}
	//经营类目申请列表
	public function apply_class_list(){
		$state = I('state');
		if($state != ""){
			$bind_class = M('store_bind_class')->where(array('state'=>$state))->select();
		}else{
			$bind_class = M('store_bind_class')->select();
		}		
		$goods_class = M('goods_category')->getField('id,name');
		for($i = 0, $j = count($bind_class); $i < $j; $i++) {
			$bind_class[$i]['class_1_name'] = $goods_class[$bind_class[$i]['class_1']];
			$bind_class[$i]['class_2_name'] = $goods_class[$bind_class[$i]['class_2']];
			$bind_class[$i]['class_3_name'] = $goods_class[$bind_class[$i]['class_3']];
			$store = M('store')->where("store_id=".$bind_class[$i]['store_id'])->find();
			$bind_class[$i]['store_name'] = $store['store_name'];
			$bind_class[$i]['seller_name'] = $store['seller_name'];
		}
		$this->assign('bind_class',$bind_class);
		$this->display();
	}
	
	//查看-添加店铺经营类目
	public function store_class_info(){
		$store_id = I('store_id');
		$store = M('store')->where(array('store_id'=>$store_id))->find();
		$this->assign('store',$store);
		if(IS_POST){
			$data = I('post.');
			$data['state'] = empty($store['is_own_shop']) ? 0 : 1;
			$where = 'class_3 ='.$data['class_3'].' and store_id='.$store_id;
			if(M('store_bind_class')->where($where)->count()>0){
				$this->error('该店铺已申请过此类目');
			}
			if(M('store_bind_class')->add($data)){
				adminLog('添加店铺经营类目，类目编号:'.$data['class_3'].',店铺编号:'.$data['store_id']);
				$this->success('添加经营类目成功');exit;
			}else{
				$this->error('操作失败');
			}
		}
		$bind_class_list = M('store_bind_class')->where('store_id='.$store_id)->select();
		$goods_class = M('goods_category')->getField('id,name');
		for($i = 0, $j = count($bind_class_list); $i < $j; $i++) {
			$bind_class_list[$i]['class_1_name'] = $goods_class[$bind_class_list[$i]['class_1']];
			$bind_class_list[$i]['class_2_name'] = $goods_class[$bind_class_list[$i]['class_2']];
			$bind_class_list[$i]['class_3_name'] = $goods_class[$bind_class_list[$i]['class_3']];
		}
		$this->assign('bind_class_list',$bind_class_list);
		$cat_list = M('goods_category')->where("parent_id = 0")->select();
		$this->assign('cat_list',$cat_list);
		$this->display();
	}
	
	
	public function apply_class_save(){
		$data = I('post.');
		if($data['act']== 'del'){
			$r = M('store_bind_class')->where("bid=".$data['del_id'])->delete();
			respose(1);
		}else{
			$data = I('get.');
			$r = M('store_bind_class')->where("bid=".$data['bid'])->save(array('state'=>1));
		}
		if($r){
			$this->success('操作成功',U('Store/apply_class_list'));
		}else{
			$this->error('提交失败');
		}
	}
	
	//店铺申请信息详情
	public function apply_info(){
		$id = I('id');
		$apply = M('store_apply')->where("id=$id")->find();
		$array = array('股份有限公司','个人独立企业','有限责任公司','外资','中外合资','国企','合伙制企业','其他');
		$apply['company_type'] = $array[ $apply['company_type'] ];
		$goods_cates = M('goods_category')->getField('id,name,commission');
		if(!empty($apply['store_class_ids'])){
			$store_class_ids = unserialize($apply['store_class_ids']);
			foreach ($store_class_ids as $val){
				$cat = explode(',', $val);
				$bind_class_list[] = array('class_1'=>$goods_cates[$cat[0]]['name'],'class_2'=>$goods_cates[$cat[1]]['name'],
						'class_3'=>$goods_cates[$cat[2]]['name'].'(分佣比例：'.$goods_cates[$cat[2]]['commission'].'%)',
						'value'=>$val,
				);
			}
			$this->assign('bind_class_list',$bind_class_list);
		}
		$this->assign('apply',$apply);
		$this->assign('province',M('region')->where(array('parent_id'=>0))->select());
		$this->assign('city',M('region')->where(array('city_id'=>0))->select());
		$this->assign('district',M('region')->where(array('district_id'=>0))->select());
		$apply_log = M('admin_log')->where(array('log_type'=>1))->select();
		$this->assign('apply_log',$apply_log);
		$this->assign('store_grade',M('store_grade')->select());
		$this->display();
	}
	
	//审核店铺开通申请
	public function review(){
		$data = I('post.');
		if($data['id']){
			$apply = M('store_apply')->where(array('id'=>$data['id']))->find();
			if(M('store_apply')->where("id=".$data['id'])->save($data)){
				if($data['apply_state'] == 1){
					$users = M('users')->where(array('user_id'=>$apply['user_id']))->find();
					if(empty($users)) $this->error('申请会员不存在或已被删除，请检查');
					$store = array('user_id'=>$apply['user_id'],'seller_name'=>$apply['seller_name'],
							'user_name'=>empty($users['email']) ? $users['mobile'] : $users['email'],
							'grade_id'=>$data['sg_id'],'store_name'=>$apply['store_name'],'sc_id'=>$apply['sc_id'],
							'company_name'=>$apply['company_name'],'store_phone'=>$apply['store_person_mobile'],
							'store_address'=>empty($apply['store_address']) ? '待完善' : $apply['store_address'] ,
							'store_time'=>time(),'store_state'=>1,'store_qq'=>$apply['store_person_qq'],
							'province_id'=>$apply['company_province'],'city_id'=>$apply['company_city'],'district'=>$apply['company_district']							
					);
					// $store_id = M('store')->add($store);//通过审核开通店铺
					

					if($store_id){
						$seller = array('seller_name'=>$apply['seller_name'],'user_id'=>$apply['user_id'],
							'group_id'=>0,'store_id'=>$store_id,'is_admin'=>1
						);
						// M('seller')->add($seller);//点击店铺管理员
						//绑定商家申请类目
						if(!empty($apply['store_class_ids'])){
							$goods_cates = M('goods_category')->where(array('level'=>3))->getField('id,name,commission');
							$store_class_ids = unserialize($apply['store_class_ids']);
							foreach ($store_class_ids as $val){
								$cat = explode(',', $val);
								$bind_class = array('store_id'=>$store_id,'commis_rate'=>$goods_cates[$cat[2]]['commission'],
										'class_1'=>$cat[0],'class_2'=>$cat[1],'class_3'=>$cat[2],'state'=>1);
								// M('store_bind_class')->add($bind_class);
							}
						}
					}
					adminLog($apply['store_name'].'开店申请审核通过',1);
				}else if($data['apply_state'] == 2){
					adminLog($apply['store_name'].'开店申请审核未通过，备注信息：'.$data['review_msg'],1);
				}

				$data_['apply_state'] = ($data['apply_state'] == 1)?1:null;
				M('store')->where(array('user_id'=>$apply['user_id']))->save($data_);

				$this->success('操作成功',U('Store/apply_list'));
			}else{
				$this->error('提交失败');
			}
		}
	}
	
	
	public function reopen_list(){
		$this->assign('store_class',M('store_class')->getField('sc_id,sc_name'));
		$this->display();
	}

	public function store_info_news(){
		$store_id = (!empty($_GET['store_id']))?$_GET['store_id']:0;
		$where = array('store'=>$store_id);
		$list= M('store_art')->where($where)->page($_GET['p'].',10')->select();
		$nav = M('store_navigation')->where(" sn_is_list = 1 and sn_store_id=".$store_id)->getfield('sn_id,sn_title');
		foreach ($list as $key => $val) {
			$list[$key]['is_show'] = $val['is_show']>0 ? '显示' : '隐藏';
			$list[$key]['sn_id'] = (empty($nav[$val['sn_id']]))?'所有':$nav[$val['sn_id']];
		}
		$count = M('store_art')->where($where)->count();
		$Page = new \Think\Page($count,10);
		$show = $Page->show();
		$this->assign('page',$show);
		$this->assign('nav',$nav);
		$this->assign('list',$list);
		$this->display();
	}

	public function ajax_news_edit(){
		$data['bidding'] = I('bidding');
		$id = I('id');
		$res = M('store_art')->where(array('id'=>$id))->save($data);
		if ($res){
			$this->ajaxReturn(true);
		} else {
			$this->ajaxReturn();
		}
	}
}