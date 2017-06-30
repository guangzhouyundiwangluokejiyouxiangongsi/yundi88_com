<?php

namespace Seller\Controller;
use Think\Page;

class StoreController extends BaseController
{


	public function initdata()
	{	
		// echo '<iframe style="width:100%;height:100%;" src="http://www.baidu.com"></iframe>';
		// ob_flush();
  //       flush();
		$data['num'] = 1;
		$res = M('seller')->where(array('store_id'=>session('store_id')))->save($data);
		$status = M('store')->where(array('store_id'=>session('store_id')))->getField('status');
		if($res && $status != 2){
			$this->import1(38);
			if(I('shanghui')){
				$this->redirect('Commerce/index');
				exit;
			}else{
				$this->success('初始化数据创建成功！','/Seller/Index/index');			
			}
		}else{
			$data['num'] = '';
			M('seller')->where(array('store_id'=>session('store_id')))->save($data);
		}
		$this->redirect('/Seller/Index/index');
	}


	/**
	 * 选择模板周飞
	 */
	public function store_tpl()
	{	
		$self = ($_SERVER['QUERY_STRING'] != '')?$_SERVER['QUERY_STRING']:'t=pc&tpl=fuzhuang&layer=1';
		$tpl = M('store')->where(array('store_id'=>session('store_id')))->getField('tpl');
		$tpl = str_replace('/','',$tpl);
		$curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, "template.yundi88.com/tpl/template.html?".$self);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
		// dump(__SELF__);

        $str = "<script>$('#{$tpl}').css('color','#FFF');$('#{$tpl}').css('background-color','#00a65a');</script>";
        $this->show($data.$str);
		// dump($_SERVER['QUERY_STRING']);
        // echo 'template.yundi88.com'.$self;
        // dump($data);exit;
	}


	//处理分页
	public function mypage($p)
	{
		$url = (strstr($_SERVER['REQUEST_URI'],'p='))?$_SERVER['REQUEST_URI']:$_SERVER['REQUEST_URI'].'&p=';

		return str_replace('p='.$_GET['p'], 'p='.$p, $url);
	}



	public function store_mtpl()
	{
        $mtpl = M('store')->where(array('store_id' => session('store_id')))->getField('mtpl');

		$arr = scandir("./Merchants_tpl/mobile");
		foreach($arr as $v){
			if($v != '..' && $v != '.'){
				$tpl_config[$v] = include "./Merchants_tpl/mobile/{$v}/config.php";
				
			}
		}
		$this->assign('mtpl',$mtpl);
		$this->assign('tpl_config',$tpl_config);
		$this->display();
	}

	public function store_weixin()
	{
        $mtpl = M('store')->where(array('store_id' => session('store_id')))->getField('mtpl');

		$arr = scandir("./Merchants_tpl/mobile");
		foreach($arr as $v){
			if($v != '..' && $v != '.'){
				$tpl_config[$v] = include "./Merchants_tpl/mobile/{$v}/config.php";
				
			}
		}
		$this->assign('mtpl',$mtpl);
		$this->assign('tpl_config',$tpl_config);
		$this->display();
	}

	public function configmobile()
	{
		$tpl['mtpl'] = I('tpl','shouji1');
		$res = M('store')->where(array('store_id'=>session('store_id')))->save($tpl);
		if($res){
			$this->success("操作成功!!!",'/seller/store/store_setting?tpl=m');
		}else{
			$this->error('操作失败！');
		}

		
	}




	/**
	 * 修改配置文件 周飞
	 */
	  public function changeTemplate()
   {
   		$key['tpl'] = I('tplclass').'/'.I('key');
   		// $key['mtpl'] = I('mtpl');
   		$t = I('t','pc');
   		$tpl = ($t == 'pc')?'Home':'Mobile';
   		$store = M('store');
		$tpl = $store->where('store_id = '.session('store_id'))->field('tpl')->find();
		if(!$tpl){$this->success("请先配置您的店铺!",U('store_setting'));exit;}

		$res = $store->where('store_id = '.session('store_id'))->save($key);
		if((int)I('tnameid')){

			$this->deletedata(session('store_id'));//删除原有数据
			// $this->redirect('store/jump', array('store_id' => (int)I('tnameid'),'jump'=>'import','html'=>'开始复制模板数据'));
			$this->jump(I('tnameid'),'import');
			// $res = $this->import88((int)I('tnameid'));//复制数据
		}
		if($res){
   			$this->success("操作成功!!!",'/Seller/index/index?hometpl=1');
		}else{
   			$this->success("操作失败!!!");
		}

   }

 

   public function changeTemplate2()
   {

   	$tplid = I('tplid','');

   		if (!$tplid) $this->error('模板不存在，请重新选择');
   		$data['tpl'] = M('store','','DB_CONFIG1')->where(array('store_id'=>$tplid))->getField('tpl');
   		$res = M('store')->where(array('store_id'=>session('store_id')))->save($data);
   		if ((int)$tplid){
   			$this->deletedata(session('store_id'));//删除原有数据
   				$this->jump($tplid,'import');
			// $res = $this->import88((int)$tplid);//复制数据
   		} else {
   			$this->error('操作失败','/seller/index/index');
   		}

   		if($res){
   			$this->success("操作成功!!!",'/Seller/index/index?hometpl=1');
		}else{
   			$this->success("操作失败!!!",'/seller/index/index');
		} 
   }


   //为新用户复制数据
   public function import1($store_id)
   {

   	//自定义导航
   	$nav = M('store_navigation');
   	$article = M('store_art');
   	$navigation = $nav->where(array('sn_store_id'=>$store_id))->select();//自定义导航
   	if($navigation){

	   	foreach($navigation as &$vv){
	   		$sn_id = $vv['sn_id'];
	   		$vv['sn_store_id'] = session('store_id');
	   		unset($vv['sn_id']);
	   		$res = M('store_navigation')->add($vv);
	   		$articles = $article->where(array('sn_id'=>$sn_id))->select();//文章
	   		if($articles){

		   		foreach($articles as &$v_art){
		   			$v_art['store'] = session('store_id');
		   			$v_art['sn_id'] = $res;
		   			$v_art['home_is_show'] = 2;
		   			$v_art['pc_click'] = 1;
		   			$v_art['m_click'] = 1;
		   			unset($v_art['id']);
		   		}
		   		foreach ($articles as $key => $val) {
		   			M('store_art')->add($val);
		   		}
		   		
	   		}

	   	}
   	}




   	// 分类
   	$class = M('store_goods_class');
   	$goods_m = M('goods');
   	$goods_class = $class->where(array('store_id'=>$store_id,'parent_id'=>0))->select();//查询一级分类
   	if($goods_class){
   		//循环复制分类
	   	foreach($goods_class as &$vo){
	   		$parent_id = $vo['cat_id'];
	   		$vo['store_id'] = session('store_id');
	   		unset($vo['cat_id']);
	   		$res = M('store_goods_class')->add($vo);
	   		$goods = $goods_m->where(array('store_id'=>$store_id,'store_cat_id1'=>$parent_id,'store_cat_id2'=>0))->select();//查询一级分类商品
	   		if($goods){

	   			foreach($goods as &$v_goods){
	   				$v_goods['store_id'] = session('store_id');
	   				$v_goods['store_cat_id1'] = $res;
	   				$v_goods['goods_id'] = null;
	   				$v_goods['home_is_show'] = 2;

	   			}	
	   				foreach($goods as $vc){
	   					M('goods')->add($vc);
	   				}
	   			
	   			
	   		}

	   		
	   		$son = $class->where(array('store_id'=>$store_id,'parent_id'=>$parent_id))->select();//查询二级分类
	   		if($son){
	   			foreach($son as &$vson){
	   				$goodsid = $vson['cat_id'];
		   			$vson['parent_id'] = $res;
		   			$vson['store_id'] = session('store_id');
		   			unset($vson['cat_id']);
		   			
		   			$res2 = M('store_goods_class')->add($vson);
		   			$goods2 = $goods_m->where(array('store_id'=>$store_id,'store_cat_id1'=>$parent_id,'store_cat_id2'=>$goodsid))->select();//查询二级分类商品
			   		if($goods2){

			   			foreach($goods2 as &$v_goods2){
			   				$v_goods2['store_id'] = session('store_id');
			   				$v_goods2['store_cat_id1'] = $res;
			   				$v_goods2['store_cat_id2'] = $res2;
			   				$v_goods2['goods_id'] = null;
			   				$v_goods2['home_is_show'] = 2;
			   			}
			   				
			   				foreach($goods2 as $vz){
			   					// dump($vz);
			   					M('goods')->add($vz);//添加二级分类商品
			   				}
			   				
			   				
			   			
			   		}


	   			}

	   		}

	   	}
	   		
   	}

   		// 配置数据和幻灯图片
   		$yd_store = M('store');
   		$store = $yd_store->where(array('store_id'=>$store_id))->field('store_logo,store_banner,seo_keywords,seo_description,store_zy,store_slide,store_slide_url,mb_slide,mb_slide_url,copyright,store_qq,store_phone,store_aliwangwang,city_id,district,store_address,store_products,photo_banner,mes_banner,pro_banner')->find();
   		if($store){
   			M('store')->where(array('store_id'=>session('store_id')))->save($store);
   		}

   		//自定义模块
   		$store_mod = M('store_mod');
   		$texts = $store_mod->where(array('store_id'=>$store_id))->field('content')->find();

   		if($texts){
			if($store_mod->where(array('store_id'=>session('store_id')))->find()){
			   			$res = M('store_mod')->where(array('store_id'=>session('store_id')))->save($texts);

			   		}else{
				   		$texts['store_id'] = session('store_id');
				   		M('store_mod')->add($texts);

			   			
			 }
   		}

   		//相册数据
   		$photo_m = M('photo');
   		$photoimg_m = M('photoimg');
   		$photo = $photo_m->where(array('store_id'=>$store_id))->select();
   		if($photo){

	   		foreach($photo as &$vv){
	   			$photoimg = $photoimg_m->where(array('photoid'=>$vv['id']))->select();
	   			$vv['store_id'] = session('store_id');
	   			unset($vv['id']);
	   			$res5 = M('photo')->add($vv);
	   			if($photoimg){
	   				foreach($photoimg as $vvv){
	   					unset($vvv['id']);
	   					$vvv['photoid'] = $res5;
	   					M('photoimg')->add($vvv);
	   				}
	   			}

	   		}

   		}

   		return true;
   }



   //为新用户复制数据
   public function import()
   {
   		$store_id = I('get.store_id');
	   	// $store_id = 526;
	   	// $domain = '/Public';
	   	//自定义导航
	   	$nav = M('store_navigation','','DB_CONFIG2');
	   	$article = M('store_art','','DB_CONFIG2');
	   	$navigation = $nav->where(array('sn_store_id'=>$store_id))->select();//自定义导航

	   	if($navigation){

		   	foreach($navigation as &$vv){
		   		$sn_id = $vv['sn_id'];
		   		$vv['sn_store_id'] = session('store_id');
		   		unset($vv['sn_id']);
		   		get_images($vv['sn_pic']);
		   		// $vv['sn_pic'] = str_replace('/Public', $domain, $vv['sn_pic']);
		   		$res = M('store_navigation')->add($vv);
		   		$articles = $article->where(array('sn_id'=>$sn_id))->select();//文章
		   		if($articles){

			   		foreach($articles as &$v_art){
			   			$v_art['store'] = session('store_id');
			   			$v_art['sn_id'] = $res;
			   			$v_art['home_is_show'] = 2;
			   			$v_art['pc_click'] = 1;
			   			$v_art['m_click'] = 1;
			   			foreach(sp_getcontent_imgs(htmlspecialchars_decode($v_art['content'])) as $vv){
	    					get_images($vv['src']);
	    				}
	    				// if(!preg_match('/http:/',$v_art['content'])){
			   			// $v_art['content'] = str_replace('/Public', $domain, $v_art['content']);
			   			// }
			   			get_images($v_art['newsimg']);
			   			// $v_art['newsimg'] = str_replace('/Public', $domain, $v_art['newsimg']);
			   			unset($v_art['id']);
			   		}
			   		foreach ($articles as $key => $val) {
			   			if(!M('store_art')->add($val)){$this->jump($store_id,'import');}
			   		}
			   		
		   		}

		   	}
	   	}
	   	// $this->redirect('store/jump', array('store_id' => $store_id,'jump'=>'import2','html'=>'正在复制'));
	   	$this->jump($store_id,'import2');
	}

	public function import2(){
		$store_id = I('get.store_id');
		// $domain = '/Public/temporary';
	   	// 分类
	   	$class = M('store_goods_class','','DB_CONFIG2');
	   	$goods_m = M('goods','','DB_CONFIG2');
	   	$goods_class = $class->where(array('store_id'=>$store_id,'parent_id'=>0))->select();//查询一级分类
	   	if($goods_class){
	   		//循环复制分类
		   	foreach($goods_class as &$vo){
		   		$parent_id = $vo['cat_id'];
		   		$vo['store_id'] = session('store_id');
		   		unset($vo['cat_id']);
		   		get_images($vo['img']);
		   		// $vo['img'] = str_replace('/Public', $domain, $vo['img']);
		   		$res = M('store_goods_class')->add($vo);
		   		$goods = $goods_m->where(array('store_id'=>$store_id,'store_cat_id1'=>$parent_id,'store_cat_id2'=>0))->select();//查询一级分类商品
		   		if($goods){

		   			foreach($goods as &$v_goods){
		   				$v_goods['store_id'] = session('store_id');
		   				$v_goods['store_cat_id1'] = $res;
		   				$v_goods['goods_id'] = null;
		   				$v_goods['home_is_show'] = 2;
		   				foreach(sp_getcontent_imgs(htmlspecialchars_decode($v_goods['goods_content'])) as $vvg){
	    					get_images($vvg['src']);
	    				}
	    				// if(!preg_match('/http:/',$v_goods['goods_content'])){

		   				// $v_goods['goods_content'] = str_replace('/Public', $domain, $v_goods['goods_content']);
	    				// }
		   				get_images($v_goods['original_img']);
		   				// $v_goods['original_img'] = str_replace('/Public', $domain, $v_goods['original_img']);

		   			}	
		   				foreach($goods as $vc){
		   					if(!M('goods')->add($vc)){$this->jump($store_id,'import2');}
		   				}
		   			
		   			
		   		}

		   		
		   		$son = $class->where(array('store_id'=>$store_id,'parent_id'=>$parent_id))->select();//查询二级分类
		   		if($son){
		   			foreach($son as &$vson){
		   				$goodsid = $vson['cat_id'];
			   			$vson['parent_id'] = $res;
			   			$vson['store_id'] = session('store_id');
			   			unset($vson['cat_id']);
			   			
			   			$res2 = M('store_goods_class')->add($vson);
			   			$goods2 = $goods_m->where(array('store_id'=>$store_id,'store_cat_id1'=>$parent_id,'store_cat_id2'=>$goodsid))->select();//查询二级分类商品
				   		if($goods2){

				   			foreach($goods2 as &$v_goods2){
				   				$v_goods2['store_id'] = session('store_id');
				   				$v_goods2['store_cat_id1'] = $res;
				   				$v_goods2['store_cat_id2'] = $res2;
				   				$v_goods2['goods_id'] = null;
				   				$v_goods2['home_is_show'] = 2;
				   				foreach(sp_getcontent_imgs(htmlspecialchars_decode($v_goods2['goods_content'])) as $vvg2){
	    								get_images($vvg2['src']);
	    						}
	    						// if(!preg_match('/http:/',$v_goods2['goods_content'])){
				   				// $v_goods2['goods_content'] = str_replace('/Public', $domain, $v_goods2['goods_content']);
				   				// }
				   				get_images($v_goods2['original_img']);
		   						// $v_goods2['original_img'] = str_replace('/Public', $domain, $v_goods2['original_img']);
				   			}
				   				
				   				foreach($goods2 as $vz){
				   					// dump($vz);
				   					if(!M('goods')->add($vz)){$this->jump($store_id,'import2');}//添加二级分类商品
				   				}
				   				
				   				
				   			
				   		}


		   			}

		   		}

		   	}
		   		
	   	}
	   	// $this->redirect('store/jump', array('store_id' => $store_id,'jump'=>'import3','html'=>'分类和商品复制成功'));
	   	$this->jump($store_id,'import3');
	   	
	}

	public function import3(){
		$store_id = I('get.store_id');
		// $domain = '/Public/temporary';
   		// 配置数据和幻灯图片
   		$yd_store = M('store','','DB_CONFIG2');
   		$store = $yd_store->where(array('store_id'=>$store_id))->field('store_logo,store_banner,seo_keywords,seo_description,store_zy,store_slide,store_slide_url,mb_slide,mb_slide_url,copyright,store_qq,store_phone,store_aliwangwang,city_id,district,store_address,store_products,photo_banner,mes_banner,pro_banner')->find();
   		if($store){
   			get_images($store['store_logo']);
   			get_images($store['photo_banner']);
   			get_images($store['mes_banner']);
   			get_images($store['pro_banner']);
   			// $store['store_logo'] ? $store['store_logo'] = str_replace('/Public', $domain, $store['store_logo']) : $store['store_logo'] = '';
   			// myget_images($store['store_banner']);
   			// $store['store_banner'] ? str_replace('/Public', $domain, $store['store_banner']) : $store['store_banner'] = '';
   			foreach(explode(',', $store['store_slide']) as $vv){
   				get_images($vv);
   			}
   			// $store['store_slide'] = str_replace('/Public', $domain, $store['store_slide']);
   			foreach(explode(',', $store['mb_slide']) as $vv2){
   				get_images($vv2);
   			}
   			// $store['mb_slide'] = str_replace('/Public', $domain, $store['mb_slide']);
   			M('store')->where(array('store_id'=>session('store_id')))->save($store);
   		}

   		//自定义模块
   		$store_mod = M('store_mod','','DB_CONFIG2');
   		$texts = $store_mod->where(array('store_id'=>$store_id))->field('content')->find();

   		if($texts){
   			$texts['content'] = unserialize($texts['content']);
   			foreach($texts['content'] as $con_v){
		   			foreach(sp_getcontent_imgs(htmlspecialchars_decode($con_v)) as $vv3){
		    								get_images($vv3['src']);

		    		}

			   		$texts['content'][] = $con_v;
			}



		   			$texts['content'] = serialize($texts['content']);

			if(M('store_mod')->where(array('store_id'=>session('store_id')))->find()){
			   			$res = M('store_mod')->where(array('store_id'=>session('store_id')))->save($texts);

	   		}else{
		   		$texts['store_id'] = session('store_id');
		   		if(!M('store_mod')->add($texts)){$this->jump($store_id,'import3');};
				   	
			 }
   		}

   			// $this->redirect('store/jump', array('store_id' => $store_id,'jump'=>'import4','html'=>'分类和商品复制成功'));
   			$this->jump($store_id,'import4');
   		
   	}

   	public function import4(){
   		$store_id = I('get.store_id');
   		// $domain = '/Public/temporary';
   		//相册数据
   		$photo_m = M('photo','','DB_CONFIG2');
   		$photoimg_m = M('photoimg','','DB_CONFIG2');
   		$photo = $photo_m->where(array('store_id'=>$store_id))->select();
   		if($photo){

	   		foreach($photo as &$vv){
	   			$photoimg = $photoimg_m->where(array('photoid'=>$vv['id']))->select();
	   			$vv['store_id'] = session('store_id');
	   			unset($vv['id']);
	   			$res5 = M('photo')->add($vv);
	   			if($photoimg){
	   				foreach($photoimg as $vvv){
	   					unset($vvv['id']);
	   					$vvv['photoid'] = $res5;
	   					get_images($vvv['img']);
	   					// $vvv['img'] = str_replace('/Public', $domain, $vvv['img']);
	   					if(!M('photoimg')->add($vvv)){$this->jump($store_id,'import4');}
	   				}
	   			}

	   		}

   		}

   		$this->success("操作成功!!!",'/Seller/index/index?hometpl=1');
   }

    //为新用户复制数据
   public function import88($store_id)
   {
	   	// $store_id = 526;
	   	$domain = '/Public/temporary';
	   	//自定义导航
	   	$nav = M('store_navigation','','DB_CONFIG1');
	   	$article = M('store_art','','DB_CONFIG1');
	   	$navigation = $nav->where(array('sn_store_id'=>$store_id))->select();//自定义导航

	   	if($navigation){

		   	foreach($navigation as &$vv){
		   		$sn_id = $vv['sn_id'];
		   		$vv['sn_store_id'] = session('store_id');
		   		unset($vv['sn_id']);
		   		myget_images($vv['sn_pic']);
		   		$vv['sn_pic'] = str_replace('/Public', $domain, $vv['sn_pic']);
		   		$res = M('store_navigation')->add($vv);
		   		$articles = $article->where(array('sn_id'=>$sn_id))->select();//文章
		   		if($articles){

			   		foreach($articles as &$v_art){
			   			$v_art['store'] = session('store_id');
			   			$v_art['sn_id'] = $res;
			   			$v_art['home_is_show'] = 2;
			   			$v_art['pc_click'] = 1;
			   			$v_art['m_click'] = 1;
			   			foreach(sp_getcontent_imgs(htmlspecialchars_decode($v_art['content'])) as $vv){
	    					myget_images($vv['src']);
	    				}
	    				if(!preg_match('/http:/',$v_art['content'])){
			   			$v_art['content'] = str_replace('/Public', $domain, $v_art['content']);
			   			}
			   			myget_images($v_art['newsimg']);
			   			$v_art['newsimg'] = str_replace('/Public', $domain, $v_art['newsimg']);
			   			unset($v_art['id']);
			   		}
			   		foreach ($articles as $key => $val) {
			   			M('store_art')->add($val);
			   		}
			   		
		   		}

		   	}
	   	}
	   	// $this->redirect('store/jump', array('store_id' => $store_id,'jump'=>'import2','html'=>'正在复制'));
	 
		$domain = '/Public/temporary';
	   	// 分类
	   	$class = M('store_goods_class','','DB_CONFIG1');
	   	$goods_m = M('goods','','DB_CONFIG1');
	   	$goods_class = $class->where(array('store_id'=>$store_id,'parent_id'=>0))->select();//查询一级分类
	   	if($goods_class){
	   		//循环复制分类
		   	foreach($goods_class as &$vo){
		   		$parent_id = $vo['cat_id'];
		   		$vo['store_id'] = session('store_id');
		   		unset($vo['cat_id']);
		   		myget_images($vo['img']);
		   		$vo['img'] = str_replace('/Public', $domain, $vo['img']);
		   		$res = M('store_goods_class')->add($vo);
		   		$goods = $goods_m->where(array('store_id'=>$store_id,'store_cat_id1'=>$parent_id,'store_cat_id2'=>0))->select();//查询一级分类商品
		   		if($goods){

		   			foreach($goods as &$v_goods){
		   				$v_goods['store_id'] = session('store_id');
		   				$v_goods['store_cat_id1'] = $res;
		   				$v_goods['goods_id'] = null;
		   				$v_goods['home_is_show'] = 2;
		   				foreach(sp_getcontent_imgs(htmlspecialchars_decode($v_goods['goods_content'])) as $vvg){
	    					myget_images($vvg['src']);
	    				}
	    				if(!preg_match('/http:/',$v_goods['goods_content'])){

		   				$v_goods['goods_content'] = str_replace('/Public', $domain, $v_goods['goods_content']);
	    				}
		   				myget_images($v_goods['original_img']);
		   				$v_goods['original_img'] = str_replace('/Public', $domain, $v_goods['original_img']);

		   			}	
		   				foreach($goods as $vc){
		   					M('goods')->add($vc);
		   				}
		   			
		   			
		   		}

		   		
		   		$son = $class->where(array('store_id'=>$store_id,'parent_id'=>$parent_id))->select();//查询二级分类
		   		if($son){
		   			foreach($son as &$vson){
		   				$goodsid = $vson['cat_id'];
			   			$vson['parent_id'] = $res;
			   			$vson['store_id'] = session('store_id');
			   			unset($vson['cat_id']);
			   			
			   			$res2 = M('store_goods_class')->add($vson);
			   			$goods2 = $goods_m->where(array('store_id'=>$store_id,'store_cat_id1'=>$parent_id,'store_cat_id2'=>$goodsid))->select();//查询二级分类商品
				   		if($goods2){

				   			foreach($goods2 as &$v_goods2){
				   				$v_goods2['store_id'] = session('store_id');
				   				$v_goods2['store_cat_id1'] = $res;
				   				$v_goods2['store_cat_id2'] = $res2;
				   				$v_goods2['goods_id'] = null;
				   				$v_goods2['home_is_show'] = 2;
				   				foreach(sp_getcontent_imgs(htmlspecialchars_decode($v_goods2['goods_content'])) as $vvg2){
	    								myget_images($vvg2['src']);
	    						}
	    						if(!preg_match('/http:/',$v_goods2['goods_content'])){
				   				$v_goods2['goods_content'] = str_replace('/Public', $domain, $v_goods2['goods_content']);
				   				}
				   				myget_images($v_goods2['original_img']);
		   						$v_goods2['original_img'] = str_replace('/Public', $domain, $v_goods2['original_img']);
				   			}
				   				
				   				foreach($goods2 as $vz){
				   					// dump($vz);
				   					M('goods')->add($vz);//添加二级分类商品
				   				}
				   				
				   				
				   			
				   		}


		   			}

		   		}

		   	}
		   		
	   	}
	   	// $this->redirect('store/jump', array('store_id' => $store_id,'jump'=>'import3','html'=>'分类和商品复制成功'));
	   
		$domain = '/Public/temporary';
   		// 配置数据和幻灯图片
   		$yd_store = M('store','','DB_CONFIG1');
   		$store = $yd_store->where(array('store_id'=>$store_id))->field('store_logo,store_banner,seo_keywords,seo_description,store_zy,store_slide,store_slide_url,mb_slide,mb_slide_url,copyright,store_qq,store_phone,store_aliwangwang,city_id,district,store_address,store_products')->find();
   		if($store){
   			myget_images($store['store_logo']);
   			$store['store_logo'] ? $store['store_logo'] = str_replace('/Public', $domain, $store['store_logo']) : $store['store_logo'] = '';
   			// myget_images($store['store_banner']);
   			// $store['store_banner'] ? str_replace('/Public', $domain, $store['store_banner']) : $store['store_banner'] = '';
   			foreach(explode(',', $store['store_slide']) as $vv){
   				myget_images($vv);
   			}
   			$store['store_slide'] = str_replace('/Public', $domain, $store['store_slide']);
   			foreach(explode(',', $store['mb_slide']) as $vv2){
   				myget_images($vv2);
   			}
   			$store['mb_slide'] = str_replace('/Public', $domain, $store['mb_slide']);
   			M('store')->where(array('store_id'=>session('store_id')))->save($store);
   		}

   		//自定义模块
   		$store_mod = M('store_mod','','DB_CONFIG1');
   		$texts = $store_mod->where(array('store_id'=>$store_id))->field('content')->find();

   		if($texts){
   			$texts['content'] = unserialize($texts['content']);
   			foreach($texts['content'] as $con_v){
		   			foreach(sp_getcontent_imgs(htmlspecialchars_decode($con_v)) as $vv3){
		    								myget_images($vv3['src']);
		    		}
		    		if(!preg_match('/http:/',$con_v)){
		   				$con_v = str_replace('/Public', $domain, $con_v);
		   			}
			   		$texts['content'][] = $con_v;
			}

		   			$texts['content'] = serialize($texts['content']);

			if(M('store_mod')->where(array('store_id'=>session('store_id')))->find()){
			   			$res = M('store_mod')->where(array('store_id'=>session('store_id')))->save($texts);

	   		}else{
		   		$texts['store_id'] = session('store_id');
		   		$s = M('store_mod')->add($texts);
				   	
			 }
   		}

   			// $this->redirect('store/jump', array('store_id' => $store_id,'jump'=>'import4','html'=>'分类和商品复制成功'));
   			
   		$domain = '/Public/temporary';
   		//相册数据
   		$photo_m = M('photo','','DB_CONFIG1');
   		$photoimg_m = M('photoimg','','DB_CONFIG1');
   		$photo = $photo_m->where(array('store_id'=>$store_id))->select();
   		if($photo){

	   		foreach($photo as &$vv){
	   			$photoimg = $photoimg_m->where(array('photoid'=>$vv['id']))->select();
	   			$vv['store_id'] = session('store_id');
	   			unset($vv['id']);
	   			$res5 = M('photo')->add($vv);
	   			if($photoimg){
	   				foreach($photoimg as $vvv){
	   					unset($vvv['id']);
	   					$vvv['photoid'] = $res5;
	   					myget_images($vvv['img']);
	   					$vvv['img'] = str_replace('/Public', $domain, $vvv['img']);
	   					M('photoimg')->add($vvv);
	   				}
	   			}

	   		}

   		}

   		return true;
   }

   public function jump($store_id,$jump){
   		$this->assign('store_id',$store_id);
   		$this->assign('jump',$jump);
   		$this->display('jump');
   }

   	public function deletedata2()
   	{
			$this->deletedata(session('store_id'));//删除原有数据

   			$this->success("成功删除数据!!!");

   	}

   /**
    * 清空用户数据
    */
   public function deletedata($store_id)
   {

   		   	$nav = M('store_navigation');
   			$article = M('store_art');
   			$class = M('store_goods_class');
   			$goods_m = M('goods');
   			$yd_store = M('store');
   			$store_mod = M('store_mod');
   			$photo_m = M('photo');
   			$photoimg_m = M('photoimg');
   			$str = 'store_logo,store_banner,seo_keywords,seo_description,store_zy,store_slide,store_slide_url,mb_slide,mb_slide_url,copyright,store_qq,store_phone,store_aliwangwang,city_id,district,store_address';
   			$arr = explode(',',$str);
   			foreach($arr as $v){
   				$data2[$v] = '';
   			}
   			$yd_store->where(array('store_id'=>$store_id))->save($data2);
   			$data['content'] = '';
   			$store_mod->where(array('store_id'=>$store_id))->save($data);
   			$nav->where(array('sn_store_id'=>$store_id))->delete();
   			$article->where(array('store'=>$store_id))->delete();
   			$class->where(array('store_id'=>$store_id))->delete();
   			$goods_m->where(array('store_id'=>$store_id))->delete();
   			$photo_m->where(array('store_id'=>$store_id))->delete();
   			$photoimg_m->where(array('store_id'=>$store_id))->delete();

   			

   }


   /**
    * 绑定独立域名 周飞
    */
//    public function store_domain()
//    {
//    	if(IS_POST){
//    		exit('开发中！');
//             $store = M('store');
//             $data['domain'] = I('post.domain');
//             $domain = $store->where(array('store_id'=>session('store_id')))->field('domain')->find();
//             if($domain['domain']){
//                 echo "<script>alert('您已经绑定了独立域名，如要更换，请联系客服！');window.history.go(-1);</script>";exit;
//             }
//             $res = $store->where(array('store_id'=>session('store_id')))->save($data);


//             // $host2 = substr($data['domain'],4);

//             if($res){
// 	            	if(PHP_OS == 'Linux'){

// 	                	$filename = '/usr/local/apache2/etc/extra/httpd-vhosts.conf';
// 	            	}elseif(PHP_OS == 'WINNT'){

// 	                	echo "<script>alert('系统不支持！');window.history.go(-1);</script>";exit;
// 	            	}
//                 // 写入的字符
//                 $word = "
// <VirtualHost *:80>
// ServerName {$data['domain']}
// DocumentRoot /usr/local/apache2/htdocs/cdcms-websites/
// <Directory  '/usr/local/apache2/htdocs/cdcms-websites/'>
//   Options +Indexes +FollowSymLinks +MultiViews
//   AllowOverride All
//   Require local
// </Directory>
// </VirtualHost>
//                 ";

//                 $fh = fopen($filename, "a");
//                 $fwrite = fwrite($fh, $word); 
//                 fclose($fh);
//             }


//             if($fwrite){
//                   system("/usr/local/apache2/bin/apachectl -k graceful",$ress);//平滑重启apache
                 
//                   if($ress == 0){
//                     echo "<script>alert('绑定成功');</script>";
//                     $this->redirect('index/index');exit;
//                   }else{
//                     if($res){
//                     $data2['domain'] = null;
//                     $user->where('id = '.session('homeuser.id'))->save($data2);
//                     }
//                     echo "<script>alert('绑定失败！请联系客服！');window.history.go(-1);</script>";exit;
//                   }
//             }else{
//                 if($res){
//                     $data2['domain'] = null;
//                     $user->where('id = '.session('homeuser.id'))->save($data2);
//                 }
//                 echo "<script>alert('绑定失败！请重试！');window.history.go(-1);</script>";exit;
//             }

//         }else{
			
//             $this->display();
//         }
//    }




	public function store_info(){
		$store = M('store')->where("store_id=".STORE_ID)->find();
		$this->assign('store',$store);
		$apply = M('store_apply')->where("user_id=".$store['user_id'])->find();
		$this->assign('apply',$apply);
		
		$bind_class_list = M('store_bind_class')->where("store_id=".STORE_ID)->select();
		$goods_class = M('goods_category')->getField('id,name');
		for($i = 0, $j = count($bind_class_list); $i < $j; $i++) {
			$bind_class_list[$i]['class_1_name'] = $goods_class[$bind_class_list[$i]['class_1']];
			$bind_class_list[$i]['class_2_name'] = $goods_class[$bind_class_list[$i]['class_2']];
			$bind_class_list[$i]['class_3_name'] = $goods_class[$bind_class_list[$i]['class_3']];
		}
		
		$this->assign('bind_class_list',$bind_class_list);
		$this->display();
	}
	
	public function store_setting(){
		$store = M('store')->where("store_id=".STORE_ID)->find();
		$store['store_zy'] = explode(',',$store['store_zy']);
		$store['seo_keywords'] = explode(',',$store['seo_keywords']);
		if($store){
			$grade = M('store_grade')->where("sg_id=".$store['grade_id'])->find();
			$store['grade_name'] = $grade['sg_name'];
			$province = M('region')->where(array('parent_id'=>0))->select();
			$city =  M('region')->where(array('parent_id'=>$store['province_id']))->select();
			$area =  M('region')->where(array('parent_id'=>$store['city_id']))->select();
			$this->assign('province',$province);
			$this->assign('city',$city);
			$this->assign('area',$area);
		}
		$this->assign('store',$store);
		$this->display();
	}
	
	public function setting_save(){
		$store = M('store')->where(array('store_id'=>session('store_id')))->find();
		if ($store['store_name'] != $_POST['store_name']){
			$res = M('store')->where(array('store_name'=>$_POST['store_name']))->find();
			if ($res['store_name']){
				$this->error('官网名称已存在');
				return;
			}
		}
		$zyy = I('zyy');
		foreach ($zyy as $k => $v) {
			if($v == ''){
				unset($zyy[$k]); 
			}
			if ($v != ''){
				$vv .= $v.',';
			}
		}
		$seo = I('seo');
		foreach ($seo as $kk => $vo) {
			if($vo == ''){
				unset($seo[$kk]);
			}
			if ($vo != ''){
				$voo .= $vo.',';
			}
		}
		$_POST['seo_keywords'] = substr($voo,0,-1);
		$_POST['store_zy'] = substr($vv,0,-1);
		unset($_POST['seo']);
		unset($_POST['zyy']);
		$data = I('post.');
		if($data['act']=='update'){
			if(!file_exists('.'.$data['themepath'].'/style/'.$data['store_theme'].'/images/preview.jpg')){
				respose(array('stat'=>'fail','msg'=>'缺少模板文件'));
			}
			if(M('store')->where("store_id=".STORE_ID)->save(array('store_theme'=>$data['store_theme']))){
				respose(array('stat'=>'ok'));
			}else{
				respose(array('stat'=>'fail','msg'=>'没有修改模板'));
			}
		}else{
			if(M('store')->where("store_id=".STORE_ID)->save($data)){
				$this->success("操作成功",U('Store/store_setting'));
			}else{
				$this->error("没有修改数据",U('Store/store_setting'));
			}
		}
	}

	// 删除pc幻灯片
	public function del_slide(){
		$store = M('store')->where("store_id=".STORE_ID)->find();
		$store_slide = I('store_slide');
		$store_slide_url = I('store_slide_url');
		$data['store_slide'] = $store['store_slide'];
		$data['store_slide_url'] = $store['store_slide_url'];
		$arr  = explode(',',$data['store_slide']);
		$arr2 = explode(',',$data['store_slide_url']);
		foreach ($arr as $k=>$v) {
			if ($v == $store_slide) unset($arr[$k]);
		}
		foreach ($arr2 as $key => $value) {
			if ($value == $store_slide_url) unset($arr2[$k]);
		}
		$data['store_slide'] = implode(',', $arr);
		$data['store_slide_url'] = implode(',', $arr2);
		$res = M('store')->where("store_id=".STORE_ID)->save($data);
		if ($res){
			$this->success('删除成功');
		} else {
			$this->success('删除失败');
		}
	}
	
	public function store_slide(){
		$store = M('store')->where("store_id=".STORE_ID)->find();
		$store_slide = $store_slide_url = array();
		if(IS_POST){
			$store_slide = I('post.store_slide');
			$store_slide_url = I('post.store_slide_url');
			$store_slide = implode(',', $store_slide);
			$store_slide_url = implode(',', $store_slide_url);
			M('store')->where("store_id=".STORE_ID)->save(array('store_slide'=>$store_slide,'store_slide_url'=>$store_slide_url));
			$this->success("操作成功",U('Store/store_slide'));
			exit;
		}
		if($store['store_slide']){
			$store_slide = explode(',', $store['store_slide']);
			$store_slide_url = explode(',', $store['store_slide_url']);
		}
		$this->assign('store_slide',$store_slide);
		$this->assign('store_slide_url',$store_slide_url);
		$this->display();
	}
	
	public function mobile_slide(){
		$store = M('store')->where("store_id=".STORE_ID)->find();
		$store_slide = $store_slide_url = array();
		if(IS_POST){
			$store_slide = I('post.store_slide');
			$store_slide_url = I('post.store_slide_url');
			$store_slide = implode(',', $store_slide);
			$store_slide_url = implode(',', $store_slide_url);
			M('store')->where("store_id=".STORE_ID)->save(array('mb_slide'=>$store_slide,'mb_slide_url'=>$store_slide_url));
			$this->success("操作成功",U('Store/mobile_slide'));
			exit;
		}
		if($store['mb_slide']){
			$store_slide = explode(',', $store['mb_slide']);
			$store_slide_url = explode(',', $store['mb_slide_url']);
		}
		$this->assign('store_slide',$store_slide);
		$this->assign('store_slide_url',$store_slide_url);
		$this->display();
	}
	
	// 删除手机幻灯片
	public function del_mobile(){
		$store = M('store')->where("store_id=".STORE_ID)->find();
		$mb_slide = I('store_slide');
		$mb_slide_url = I('store_slide_url');
		$data['mb_slide'] = $store['mb_slide'];
		$data['mb_slide_url'] = $store['mb_slide_url'];
		$arr  = explode(',',$data['mb_slide']);
		$arr2 = explode(',',$data['mb_slide_url']);
		foreach ($arr as $k=>$v) {
			if ($v == $mb_slide) unset($arr[$k]);
		}
		foreach ($arr2 as $key => $value) {
			if ($value == $mb_slide_url) unset($arr2[$k]);
		}
		$data['mb_slide'] = implode(',', $arr);
		$data['mb_slide_url'] = implode(',', $arr2);
		$res = M('store')->where("store_id=".STORE_ID)->save($data);
		if ($res){
			$this->success('删除成功');
		} else {
			$this->success('删除失败');
		}
	}

	public function theme(){
		$template = include APP_PATH.'Common/Conf/style_inc.php';
		$theme = include APP_PATH.'/Conf/html.php';
		$this->assign('static_path',$theme['TMPL_PARSE_STRING']['__STATIC__']);
		$this->assign('template',$template);
		$store = M('store')->where("store_id=".STORE_ID)->find();
		$this->assign('store',$store);
		$this->display();
	}
	
	public function bind_class_list(){
		$bind_class_list = M('store_bind_class')->where("store_id=".STORE_ID)->select();
		$goods_class = M('goods_category')->getField('id,name');
		for($i = 0, $j = count($bind_class_list); $i < $j; $i++) {
			$bind_class_list[$i]['class_1_name'] = $goods_class[$bind_class_list[$i]['class_1']];
			$bind_class_list[$i]['class_2_name'] = $goods_class[$bind_class_list[$i]['class_2']];
			$bind_class_list[$i]['class_3_name'] = $goods_class[$bind_class_list[$i]['class_3']];
		}
		$this->assign('bind_class_list',$bind_class_list);
		$this->display();
	}
	
	public function get_bind_class(){
		$cat_list = M('goods_category')->where("parent_id = 0")->select();
		$this->assign('cat_list',$cat_list);
		if(IS_POST){
			$data = I('post.');
			$where = "class_3 =".$data['class_3']." and store_id=".STORE_ID;
			if(M('store_bind_class')->where($where)->count()>0){
				respose(array('stat'=>'fail','msg'=>'您已申请过该类目'));
			}
			$data['store_id'] = STORE_ID;
			// $data['commis_rate'] = M('goods_category')->where("id=".$data['class_3'])->getField('commission');
			if(M('store_bind_class')->add($data)){
				respose(array('stat'=>'ok'));
			}else{
				respose(array('stat'=>'fail','msg'=>'操作失败'));
			}			
		}
		$this->display();
	}
	
	public function ajax_category(){
		$category = I('category');
		// $category = '西装';
		$goods_category = M('goods_category');
		$arr = M('goods_category')->where('name like "%'.$category.'%"')->select();
		// dump($arr);
		$res = array();
		foreach ($arr as $key => $val) {
			if ($val['level'] != 1){
				$arr2 = M('goods_category')->where(array('id'=>$val['parent_id']))->select();
					// dump($arr2);
				foreach ($arr2 as $kk=>$vv){
					if ($vv['level'] != 1 && $val['level'] != 2){
						$arr3 = M('goods_category')->where(array('id'=>$vv['parent_id']))->select();
						foreach ($arr3 as $ko => $vo) {
							$res['name'][] = $vo['name'].'@'.$vv['name'].'@'.$val['name'];
							$res['id'][] = $vo['id'].'@'.$vv['id'].'@'.$val['id'];
						}
					} else {
						$res['name'][] = $vv['name'].'@'.$val['name'];
						$res['id'][] = $vv['id'].'@'.$val['id'];
					} 
				} 
			} else {
				$res['name'][] = $val['name'];
				$res['id'][] = $val['id'];
			}

		}
		// dump($res);
		// echo $category;
		// dump($res);
		$this->ajaxReturn($res);
		// $this->ajaxReturn('13123');

	}
	public function bind_class_del(){
		$data = I('post.');
		$r = M('store_bind_class')->where(array('bid'=>$data['bid']))->delete();
		if($r){
			$res = array('stat'=>'ok');
		}else{
			$res = array('stat'=>'fail','msg'=>'操作失败');
		}
		respose($res);
	}

	public function navigation_art_list(){
		$this->display();
	}

	
	public function navigation_list(){
		$Model =  M('store_navigation');
		$res = $Model->where("sn_store_id=".STORE_ID)->order('sn_sort')->page($_GET['p'].',10')->select();
		if(I('nav') != 'news'){
			$products = M('store')->where(array('store_id'=>STORE_ID))->getField('store_products');
			$products = unserialize($products);
			
		}
		if($res){
			foreach ($res as $val){
				$val['sn_new_open'] = $val['sn_new_open']>0 ? '开启' : '关闭';
				$val['sn_is_show'] = $val['sn_is_show']>0 ? '是' : '否';
				$list[] = $val;
			}
		}
		if(I('nav') == 'news'){
		$class = $Model->where(array('sn_store_id'=>STORE_ID,'sn_pid'=>0))->getField('sn_id,sn_title',true);
		}
		$this->assign('class',$class);
		$this->assign('products',$products);
		$this->assign('list',$list);


		/**
		*	@author 金龙
		*	2016/10/30
		*/
		$count = $Model->where("sn_store_id=".STORE_ID)->count();

		//$count = $Model->where('1=1')->count();
		$store_products = M('store')->where(array('store_id'=>STORE_ID))->find();
		$this->assign('store_products',$store_products);
		$Page = new \Think\Page($count,10);
		$show = $Page->show();
		$this->assign('page',$show);
		$this->display();
	}
	
	public function navigation(){
		$sn_id = I('sn_id',0);
		if($sn_id>0){
			$info = M('store_navigation')->where("sn_id=$sn_id")->find();
			$this->assign('info',$info);
		}
			$class = M('store_navigation')->where(array('sn_pid'=>0,'sn_store_id'=>STORE_ID))->select();
			$this->assign('class',$class);
		$this->initEditor();
		$this->display();
	}

	public function edit_products(){
		$store_products = M('store')->where(array('store_id'=>STORE_ID))->find();
		$store_products = unserialize($store_products['store_products']);
		$id = I('id');
		foreach ($store_products as $key => $val) {
			if ($id ==  $key) $temp = $val;
		}
		if (IS_POST){
			$id = I('id');
			$tmp = I('store_products');
			foreach ($store_products as $k => &$v) {
				if ($id == $k) $store_products[$k] = $tmp;
			}
			$data['store_products'] = serialize($store_products);
			$res = M('store')->where(array('store_id'=>STORE_ID))->save($data);
			if ($res){
				echo '<script>alert("修改成功");window.parent.location.reload();</script>';
			} else {
				echo '<script>alert("修改成功");window.parent.location.reload();</script>';
			}
		}
		$this->assign('id',$id);
		$this->assign('temp',$temp);
		$this->display();
	}
	
	public function navigationHandle(){
		$data = I('post.');
		// dump($data);

		if($data['act'] == 'del'){
		/**
		*	@author 金龙
		*	2016/10/30
		*/
			//$r = M('store_navigation')->where('sn_id='.$data['sn_id'])->delete();

			$r = M('store_navigation')->where("sn_store_id = ".STORE_ID.' and sn_id='.$data['sn_id'])->delete();
			M('store_art')->where("store = ".STORE_ID.' and sn_id = '.$data['sn_id'])->delete();

			if($r) exit(json_encode(1));
		}
		if(empty($data['sn_id'])){
			$data['sn_store_id'] = STORE_ID;
			$r = M('store_navigation')->add($data);
		}else{
			$r = M('store_navigation')->where('sn_id='.$data['sn_id'])->save($data);
		}
		if($r){
			$this->success("操作成功",U('Store/navigation_list'));
		}else{
			$this->error("操作失败",U('Store/navigation_list'));
		}
	}
	
	public function goods_class(){
		$Model =  M('store_goods_class');
		$res = $Model->where("store_id=".STORE_ID)->order('parent_id')->select();	
		$cat_list = $this->getTreeClassList(2,$res);
		$this->assign('cat_list',$cat_list);
		$this->display();
	}
	
	public function goods_class_info(){
		$cat_id = I('get.cat_id',0);
		$info['parent_id'] = I('get.parent_id',0);
		if($cat_id>0){
			$info = M('store_goods_class')->where("cat_id=$cat_id")->find();
		}
		$this->assign('info',$info);
		$parent = M('store_goods_class')->where("parent_id=0 and is_show=1 and store_id=".STORE_ID)->select();
		$this->assign('parent',$parent);
		$this->display();
	}
	
	public function goods_class_save(){
		$data = I('post.');
		if($data['act'] == 'del'){
			$r = M('store_goods_class')->where('cat_id='.$data['cat_id'].' or parent_id='.$data['cat_id'])->delete();
		}else{
			if(empty($data['cat_id'])){
				$data['store_id'] = STORE_ID;
				$r = M('store_goods_class')->add($data);
			}else{
				$r = M('store_goods_class')->where('cat_id='.$data['cat_id'])->save($data);
			}
		}
		if($r){
			$res = array('stat'=>'ok');
		}else{
			$res = array('stat'=>'fail','msg'=>'操作失败');
		}
		respose($res);
	}

	public function store_im(){
		$chat_msg = M('chat_msg')->select();
		$this->assign('chat_msg',$chat_msg);
		$this->display();
	}
	
	function store_collect(){
		$keywords = I('keywords');
		$map['store_id'] = STORE_ID;
		if(!empty($keywords)){
			$map['user_name'] = array('like',"%$keywords%");
		}
		$count = M('store_collect')->where($map)->count();
		$Page  = new \Think\Page($count,10);
		$show = $Page->show();
		$collect = M('store_collect')->where(array('store_id'=>STORE_ID))->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('page',$show);
		$this->assign('collect',$collect);
		$this->display();
	}
	
	public function store_decoration(){
		if(IS_POST){
			//店铺装修设置
			$data = I('post.');
			M('store')->where(array('store_id'=>STORE_ID))->save($data);
			$this->success("操作成功",U('Store/store_decoration'));
			exit;
		}
		$decoration = M('store_decoration')->where(array('store_id'=>STORE_ID))->find();
		if(empty($decoration)){
			$decoration = array('decoration_name'=>'默认装修','store_id'=>STORE_ID);
			$decoration['decoration_id'] = M('store_decoration')->add($decoration);
		}
		$this->assign('decoration',$decoration);
		$store = M('store')->where("store_id=".STORE_ID)->find();
		$this->assign('store',$store);
		$this->display();
	}
	
	/**
	 * 递归 整理分类
	 *
	 * @param int $show_deep 显示深度
	 * @param array $class_list 类别内容集合
	 * @param int $deep 深度
	 * @param int $parent_id 父类编号
	 * @param int $i 上次循环编号
	 * @return array $show_class 返回数组形式的查询结果
	 */
	private function getTreeClassList($show_deep=2,$class_list,$deep=1,$parent_id=0,$i=0){
		static $show_class = array();//树状的平行数组
		if(is_array($class_list) && !empty($class_list)) {
			$size = count($class_list);
			if($i == 0) $show_class = array();//从0开始时清空数组，防止多次调用后出现重复
			for ($i;$i < $size;$i++) {//$i为上次循环到的分类编号，避免重新从第一条开始
				$val = $class_list[$i];
				$cat_id = $val['cat_id'];
				$cat_parent_id	= $val['parent_id'];
				if($cat_parent_id == $parent_id) {
					$val['deep'] = $deep;
					$show_class[] = $val;
					if($deep < $show_deep && $deep < 2) {//本次深度小于显示深度时执行，避免取出的数据无用
						$this->getTreeClassList($show_deep,$class_list,$deep+1,$cat_id,$i+1);
					}
				}
				//if($cat_parent_id > $parent_id) break;//当前分类的父编号大于本次递归的时退出循环
			}
		}
		return $show_class;
	}
	
	private function initEditor()
	{
		$this->assign("URL_upload", U('Admin/Ueditor/imageUp',array('savepath'=>'decoration')));
		$this->assign("URL_fileUp", U('Admin/Ueditor/fileUp',array('savepath'=>'decoration')));
		$this->assign("URL_scrawlUp", U('Admin/Ueditor/scrawlUp',array('savepath'=>'decoration')));
		$this->assign("URL_getRemoteImage", U('Admin/Ueditor/getRemoteImage',array('savepath'=>'decoration')));
		$this->assign("URL_imageManager", U('Admin/Ueditor/imageManager',array('savepath'=>'decoration')));
		$this->assign("URL_imageUp", U('Admin/Ueditor/imageUp',array('savepath'=>'decoration')));
		$this->assign("URL_getMovie", U('Admin/Ueditor/getMovie',array('savepath'=>'decoration')));
		$this->assign("URL_Home", "");
	}
        
        /**
         * 三级分销设置
         */
	public function distribut(){
             // 每个店铺有一个分销 记录
            $store_distribut = M('store_distribut')->where("store_id=".STORE_ID)->find();
            if(IS_POST)
            {
                $Model = M('store_distribut');                
                if(!$Model->create())
                    $this->error("提交失败",U('Store/distribut'));
               
                $Model->store_id = STORE_ID;
                
                if($store_distribut)      
                    $Model->save();
                else
                    $Model->add();
                $this->success("操作成功",U('Store/distribut'));
                exit;
            }            
            $this->assign('config',$store_distribut);
            $this->display();
	}
	public function store_mod(){

		if(M('store_mod')->where('store_id = '.STORE_ID)->count()==0){
			M('store_mod')->add(array('store_id'=>STORE_ID));
		}

		$text = M('store_mod')->where('store_id = '.STORE_ID)->select();

		$tpls = M('store')->where('store_id = '.session('store_id'))->getField('tpl');
		$template_config = include "./Merchants_tpl/pc/$tpls/config.php";
		$template_config['mod']  = 5;

        $this->assign('template_config',$template_config);
        // dump($template_config);exit;



		$this->initEditor();
		$this->assign('text',unserialize($text[count($text)-1]['content']));
		$this->display();
	}
	public function modHandle(){
		$data = I('post.');
		$DataArray = array();
		foreach ($data as $key => $value) {
			if($key=='__hash__') continue;
			$DataArray[]=$value;
		}
		$data = array(
			'content' => serialize($DataArray)
			);

		$r = M('store_mod')->where('store_id = '.STORE_ID)->save($data);

		if($r){
			$this->success("操作成功",U('store/store_mod'));
		}else{
			$this->error("操作失败",U('store/store_mod'));
		}
	}    

	public function ajax_store(){
		$store_name = I('store_name');
		$res = M('store')->where(array('store_name'=>$store_name))->find();
		if ($res){
			$this->ajaxReturn(true); 
		} else {
			$this->ajaxReturn();
		}
	}

	public function pro_banner(){
		$store_id = session('store_id');
		$pro_banner = M('store')->where(array('store_id'=>$store_id))->getField('pro_banner');
		$this->assign('pro_banner',$pro_banner);
		$this->display();
	}

	public function edit_pro_banner(){
		$data['pro_banner'] = I('pro_banner');
		if (!$data['pro_banner']){
			$this->ajaxReturn(false);
		}
		$res = M('store')->where(array('store_id'=>session('store_id')))->save($data);
		if ($res){
			$this->ajaxReturn(true);
		} else {
			$this->ajaxReturn(false);
		}
	}

	public function delete_pro_banner(){
		$data['pro_banner'] = '';
		$res = M('store')->where(array('store_id'=>session('store_id')))->save($data);
		if ($res){
			$this->ajaxReturn(true);
		} else {
			$this->ajaxReturn(false);
		}
	}
}