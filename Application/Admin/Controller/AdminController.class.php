<?php

namespace Admin\Controller;

use Think\Page;
use Think\Verify;

class AdminController extends BaseController {

    public function index(){
    	$res = $list = array();
    	$keywords = I('keywords');
    	if(empty($keywords)){
    		$res = D('admin')->select();
    	}else{
    		$res = D()->query("select * from __PREFIX__admin where user_name like '%$keywords%' order by admin_id");
    	}
    	$role = D('admin_role')->getField('role_id,role_name');
    	if($res && $role){
    		foreach ($res as $val){
    			$val['role'] =  $role[$val['role_id']];
    			$val['add_time'] = date('Y-m-d H:i:s',$val['add_time']);
    			$list[] = $val;
    		}
    	}
    	$this->assign('list',$list);
        $this->display();
    }
    
    public function admin_info(){
    	$admin_id = I('get.admin_id',0);   	
    	if($admin_id){
    		$info = D('admin')->where("admin_id=$admin_id")->find();
                $info['password'] =  "";
    		$this->assign('info',$info);
    	}
    	$act = empty($admin_id) ? 'add' : 'edit';
    	$this->assign('act',$act);
    	$role = D('admin_role')->where('1=1')->select();
    	$this->assign('role',$role);
    	$this->display();
    }
    
    public function adminHandle(){
    	$data = I('post.');
    	if(empty($data['password'])){
    		unset($data['password']);
    	}else{
    		$data['password'] = encrypt($data['password']);
    	}
    	if($data['act'] == 'add'){
    		unset($data['admin_id']);    		
    		$data['add_time'] = time();
    		if(D('admin')->where("user_name='".$data['user_name']."'")->count()){
    			$this->error("此用户名已被注册，请更换",U('Admin/Admin/admin_info'));
    		}else{
    			$r = D('admin')->add($data);
    		}
    	}
    	
    	if($data['act'] == 'edit'){
    		$r = D('admin')->where('admin_id='.$data['admin_id'])->save($data);
    	}
    	
        if($data['act'] == 'del' && $data['admin_id']>1){
    		$r = D('admin')->where('admin_id='.$data['admin_id'])->delete();
    		exit(json_encode(1));
    	}
    	
    	if($r){
    		$this->success("操作成功",U('Admin/Admin/index'));
    	}else{
    		$this->error("操作失败",U('Admin/Admin/index'));
    	}
    }
    
    
    /*
     * 管理员登陆
     */
    public function login(){
        if(session('?admin_id') && session('admin_id')>0){
             $this->error("您已登录",U('Admin/Index/index'));
        }
      
        if(IS_POST){
            $verify = new Verify();
            if (!$verify->check(I('post.vertify'), "admin_login")) {
            	exit(json_encode(array('status'=>0,'msg'=>'验证码错误')));
            }
            $condition['user_name'] = I('post.username');
            $condition['password'] = I('post.password');
            if(!empty($condition['user_name']) && !empty($condition['password'])){
                $condition['password'] = encrypt($condition['password']);
               	$admin_info = M('admin')->join('__ADMIN_ROLE__ ON __ADMIN__.role_id=__ADMIN_ROLE__.role_id')->where($condition)->find();
                if(is_array($admin_info)){
                    session('admin_id',$admin_info['admin_id']);
                    session('act_list',$admin_info['act_list']);
                    $last_login_time = M('admin_log')->where("admin_id = ".$admin_info['admin_id']." and log_info = '后台登录'")->order('log_id desc')->limit(1)->getField('log_time');                    
                    session('last_login_time',$last_login_time);                            
                    adminLog('后台登录',__ACTION__);
                    $url = session('from_url') ? session('from_url') : U('Admin/Index/index');
                    exit(json_encode(array('status'=>1,'url'=>$url)));
                }else{
                    exit(json_encode(array('status'=>0,'msg'=>'账号密码不正确')));
                }
            }else{
                exit(json_encode(array('status'=>0,'msg'=>'请填写账号密码')));
            }
        }
        
        $this->display();
    }
    
    /**
     * 退出登陆
     */
    public function logout(){
        session_unset();
        session_destroy();
        $this->success("退出成功",U('Admin/Admin/login'));
    }
    
    /**
     * 验证码获取
     */
    public function vertify()
    {
        $config = array(
            'fontSize' => 30,
            'length' => 4,
            'useCurve' => true,
            'useNoise' => false,
        	'reset' => false
        );    
        $Verify = new Verify($config);
        $Verify->entry("admin_login");
    }
    
    public function role(){
    	$list = D('admin_role')->order('role_id desc')->select();
    	$this->assign('list',$list);
    	$this->display();
    }
    
    public function role_info(){
    	$role_id = I('get.role_id');
    	$tree = $detail = array();
    	if($role_id){
    		$detail = M('admin_role')->where("role_id=$role_id")->find();
    		$detail['act_list'] = explode(',', $detail['act_list']);
    		$this->assign('detail',$detail);
    	}
    	$right = M('system_menu')->order('id')->select();
    	foreach ($right as $val){
    		if(!empty($detail)){
    			$val['enable'] = in_array($val['id'], $detail['act_list']);
    		}
    		$modules[$val['group']][] = $val;
    	}
    	//权限组
    	$group = array('system'=>'系统设置','content'=>'内容管理','goods'=>'商品中心','member'=>'会员中心',
    			'order'=>'订单中心','marketing'=>'营销推广','tools'=>'插件工具','count'=>'统计报表',
    			'weixin'=>'微信管理','store'=>'店铺管理'
    	);
    	$this->assign('group',$group);
    	$this->assign('modules',$modules);
    	$this->display();
    }
    
    public function roleSave(){
    	$data = I('post.');
    	$res = $data['data'];
    	$res['act_list'] = is_array($data['right']) ? implode(',', $data['right']) : '';
    	if(empty($data['role_id'])){
    		$r = D('admin_role')->add($res);
    	}else{
    		$r = D('admin_role')->where('role_id='.$data['role_id'])->save($res);
    	}
    	if($r){
    		adminLog('管理角色',__ACTION__);
    		$this->success("操作成功!",U('Admin/Admin/role_info',array('role_id'=>$data['role_id'])));
    	}else{
    		$this->success("操作失败!",U('Admin/Admin/role'));
    	}
    }
    
    public function roleDel(){
    	$role_id = I('post.role_id');
    	$admin = D('admin')->where('role_id='.$role_id)->find();
    	if($admin){
    		exit(json_encode("请先清空所属该角色的管理员"));
    	}else{
    		$d = M('admin_role')->where("role_id=$role_id")->delete();
    		if($d){
    			exit(json_encode(1));
    		}else{
    			exit(json_encode("删除失败"));
    		}
    	}
    }
    
    public function log(){
    	$Log = M('admin_log');
    	$p = I('p',1);
    	$logs = $Log->join('__ADMIN__ ON __ADMIN__.admin_id =__ADMIN_LOG__.admin_id')->order('log_time DESC')->page($p.',20')->select();
    	$this->assign('list',$logs);
    	$count = $Log->where('1=1')->count();
    	$Page = new \Think\Page($count,20);
    	$show = $Page->show();
    	$this->assign('page',$show); 	
    	$this->display();
    }

	/**
	 * 供应商列表
	 */
	public function supplier()
	{
		$supplier_model = M('');
		$db_prefix = C('DB_PREFIX');
		$supplier_count = $supplier_model->table($db_prefix.'suppliers')->where('')->count();
		$page = new Page($supplier_count, 10);
		$show = $page->show();
		$supplier_list = $supplier_model
				->field('s.*,a.admin_id,a.user_name')
				->table($db_prefix.'suppliers s')
				->join('LEFT JOIN '.$db_prefix.'admin AS a ON a.suppliers_id = s.suppliers_id')
				->where('')
				->limit($page->firstRow, $page->listRows)
				->select();
		$this->assign('list', $supplier_list);
		$this->assign('page', $show);
		$this->display();
	}

	/**
	 * 供应商资料
	 */
	public function supplier_info()
	{
		$suppliers_id = I('get.suppliers_id', 0);
		if ($suppliers_id) {
			$db_prefix = C('DB_PREFIX');
			$suppliers_model = M('suppliers');
			$info = $suppliers_model
					->field('s.*,a.admin_id,a.user_name')
					->table($db_prefix.'suppliers s')
					->join('LEFT JOIN '.$db_prefix.'admin AS a ON a.suppliers_id = s.suppliers_id')
					->where(array('s.suppliers_id' => $suppliers_id))
					->find();
			$this->assign('info', $info);
		}
		$act = empty($suppliers_id) ? 'add' : 'edit';
		$this->assign('act', $act);
		$admin = M('admin')->field('admin_id,user_name')->where('1=1')->select();
		$this->assign('admin', $admin);
		$this->display();
	}

	/**
	 * 供应商增删改
	 */
	public function supplierHandle()
	{
		$data = I('post.');
		$suppliers_model = M('suppliers');
		//增
		if ($data['act'] == 'add') {
			unset($data['suppliers_id']);
			$count = $suppliers_model->where("suppliers_name='" . $data['suppliers_name'] . "'")->count();
			if ($count) {
				$this->error("此供应商名称已被注册，请更换", U('Admin/Admin/supplier_info'));
			} else {
				$r = $suppliers_model->add($data);
				if (!empty($data['admin_id'])) {
					$admin_data['suppliers_id'] = $suppliers_model->getLastInsID();
					M('admin')->where(array('suppliers_id' => $admin_data['suppliers_id']))->save(array('suppliers_id' => 0));
					M('admin')->where(array('admin_id' => $data['admin_id']))->save($admin_data);
				}
			}
		}
		//改
		if ($data['act'] == 'edit' && $data['suppliers_id'] > 0) {
			$r = $suppliers_model->where('suppliers_id=' . $data['suppliers_id'])->save($data);
			if (!empty($data['admin_id'])) {
				$admin_data['suppliers_id'] = $data['suppliers_id'];
				M('admin')->where(array('suppliers_id' => $admin_data['suppliers_id']))->save(array('suppliers_id' => 0));
				M('admin')->where(array('admin_id' => $data['admin_id']))->save($admin_data);
			}
		}
		//删
		if ($data['act'] == 'del' && $data['suppliers_id'] > 1) {
			$r = $suppliers_model->where('suppliers_id=' . $data['suppliers_id'])->delete();
			M('admin')->where(array('suppliers_id' => $data['suppliers_id']))->save(array('suppliers_id' => 0));
		}

		if ($r !== false) {
			$this->success("操作成功", U('Admin/Admin/supplier'));
		} else {
			$this->error("操作失败", U('Admin/Admin/supplier'));
		}
	}

    // 建立目录和复制图片
    public function copy_img($img){
        if (!$img) return false;
        $domain = 'http://template.yundi88.com';
        $url = '/mnt/web/yundi88_com/public_html';
        if (!file_exists($url.$img)){
            $arr = explode('/', $img);
            $image = $arr[ count($arr) - 1];
            unset($arr[ count($arr) - 1]);
            $obj = implode('/', $arr);
            mkdir($url.$obj,0777,true);
            copy($domain.$img,$url.$img);
        }
        return true;
    }

    // 复制商户表图片
    public function copy_images(){
        $stat = (int)I('stat',0);
        $end = (int)I('end',0);
        if ($end){

            $store = M('store','','DB_CONFIG2');
            // $count = $store->count();
            $res = $store->field('store_logo,store_banner,store_slide,mb_slide,store_erweima,photo_banner,mes_banner,pro_banner')->limit($stat,$end)->select();
            if (!$res){
                // $this->copy_photo($end);
                header('location:/Admin/Admin/copy_photo.html?end='.$end);
                exit;
            }
            
            foreach ($res as $val){
                //logo
                $this->copy_img($val['store_logo']);

                // banner
                $this->copy_img($val['store_banner']);

                // pc轮播图
                $val['store_slide'] = explode(',', $val['store_slide']);
                foreach ($val['store_slide'] as $v) {
                    $this->copy_img($v);
                }

                //手机轮播图
                $val['mb_slide'] = explode(',', $val['mb_slide']);
                foreach ($val['mb_slide'] as $vv) {
                    $this->copy_img($vv);
                }

                //二维码
                // $this->copy_img($val['store_erweima']);

                // 相册banner
                $this->copy_img($val['photo_banner']);

                // 留言banner
                $this->copy_img($val['mes_banner']);

                // 产品banner
                $this->copy_img($val['pro_banner']);

            }

            $this->jump('copy_images',$stat,$end,'正在复制店铺表所有图片');
        }
        $this->display();


    }

    // 复制相册表图片
    public function copy_photo($end=1){
        $stat = (int)I('stat',0);
        $end = (int)I('end',0);
        if ($end){
            $photoimg = M('photoimg','','DB_CONFIG2');
            $res = $photoimg->field('img')->limit($stat,$end)->select();
            if (!$res){
                header('location:/Admin/Admin/copy_goods.html?end='.$end);
                exit;
            }
            foreach ($res as $key => $val) {
                $this->copy_img($val['img']);
            }
            $this->jump('copy_photo',$stat,$end,'正在复制相册表相片');
        }
    }

    // 复制商品表图片
    public function copy_goods($end=1){
        $stat = (int)I('stat',0);
        $end = (int)I('end',0);
        if ($end){
            $goods = M('goods','','DB_CONFIG2');
            $res = $goods->field('original_img')->limit($stat,$end)->select();
            if (!$res){
                header('location:/Admin/Admin/copy_store_art.html?end='.$end);
                exit;
            }
            foreach ($res as $val) {
                // 商品缩略图
                $this->copy_img($val['original_img']);
            }
            $this->jump('copy_goods',$stat,$end,'正在复制商品表商品图');
        }
    }

    //复制新闻图片
    public function copy_store_art($end=1){
        $stat = (int)I('stat',0);
        $end = (int)I('end',0);
        if ($end){
            $store_art = M('store_art','','DB_CONFIG2');
            $res = $store_art->field('newsimg')->limit($stat,$end)->select();
            if (!$res){
                header('location:/Admin/Admin/copy_goods_class.html?end='.$end);
                exit;
            }
            foreach ($res as $val) {
                $this->copy_img($val['newsimg']);
            }
            $this->jump('copy_store_art',$stat,$end,'正在复制新闻表缩略图');
        }
    }

    // 复制商品分类banner
    public function copy_goods_class($end=1){
        $stat = (int)I('stat',0);
        $end = (int)I('end',0);
        if ($end){
            $goods_class = M('store_goods_class','','DB_CONFIG2');
            $res = $goods_class->field('img')->limit($stat,$end)->select();
            if (!$res){
                header('location:/Admin/Admin/copy_store_navigation.html?end='.$end);
                exit;
            }
            foreach ($res as $val) {
                $this->copy_img($val['img']);
            }
            $this->jump('copy_goods_class',$stat,$end,'正在复制分类表banner图');
        }
    }

    // 复制自定义导航banner图
    public function copy_store_navigation(){
        $stat = (int)I('stat',0);
        $end = (int)I('end',0);
        if ($end){
            $store_navigation = M('store_navigation','','DB_CONFIG2');
            $res = $store_navigation->field('sn_pic')->limit($stat,$end)->select();
            if (!$res){
                $this->jump('copy_decoration',0,9999,'正在复制自定义模块所有图片');
                exit;
            }
            foreach ($res as $val) {
                $this->copy_img($val['sn_pic']);
            }
            $this->jump('copy_store_navigation',$stat,$end,'正在复制自定义模导航banner图');
        }
    }

    public function copy_decoration(){
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, "http://template.yundi88.com/copy_image.php");
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        // echo($data);
        $array = explode('**',$data);
        foreach($array as $v){
                $v = str_replace('./Public','/Public',$v);
                $this->copy_img($v);
        }
        header('location:/Admin/Admin/copy_images.html');
    }
    // 中间跳转
    public function jump($url,$stat,$end,$msg){
        if (!$stat){
            $stat = $end;
        }else{
            $stat += $end;
        }
        $this->assign('msg',$msg);
        $this->assign('stat',$stat);
        $this->assign('end',$end);
        $this->assign('url',$url);
        $this->display('jump');
    }



}