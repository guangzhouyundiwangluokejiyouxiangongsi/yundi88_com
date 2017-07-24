<?php

namespace Seller\Controller;
use Think\AjaxPage;
use Seller\Logic\GoodsLogic;

class SellerstoreController extends BaseController{

	public function addpr()
	{
		if (IS_POST) {
			// 处理表单数据
			$keywords = '';
			for ($i=0; $i < count(I('post.keywords')); $i++) {
				$keywords .= I('post.keywords')[$i].',';
			}

            // 正则验证
            // if (!$this->match('\/^1[34578]\d{9}$\/', I('post.phone'))) {
            //     $this->error('手机号码不正确');
            // }

			$value1 = array(
                'store_id' => session('store_id'),
				'goods_name' => I('post.title'),
				'keywords' => $keywords,
				'shop_price' => I('post.price'),
				'cat_id1' => I('post.cat_id1'),
				'cat_id2' => I('post.cat_id2'),
				'cat_id3' => I('post.cat_id3'),
				'goods_remark' => I('post.description'),
				'original_img' => I('post.newsimg'),
				'goods_content' => I('post.content'),
			);
			$res = M('goods')->add($value1);
			if ($res) {
				// 处理相册
				for ($j=0; $j < count(I('post.goods_images')); $j++) {
					$imagesData[$j]['goods_id'] = $res;
					$imagesData[$j]['image_url'] = I('post.goods_images')[$j];
				}
				$res3 = M('goods_images')->addAll($imagesData);
				if (!$res3) {
					$this->error('图片上传失败');
					exit;
				}

				$value2 = array(
					'sid' => session('store_id'),
					'name' => I('post.company_name'),
					'person' => I('post.connect_person'),
					'phone' => I('post.person_phone'),
					'wechat' => I('post.person_wechat'),
                    'privince' => I('post.privince'),
                    'city' => I('post.city'),
                    'area' => I('post.area'),
					'address' => I('post.company_address'),
				);
                // 判断是否填写过公司信息
                $companyInfo = M('goods_contact')->field('id')->where(array('sid' => session('store_id')))->find();
                if ($companyInfo) {
                    $value2['update_time'] = time();
                    $res2 = M('goods_contact')->where(array('sid' => session('store_id')))->save($value2);
                } else {
                    $res2 = M('goods_contact')->add($value2);
                }
				if ($res2) {
					$this->success('操作成功');
				} else {
					$this->error('操作失败，请重试');
				}
			} else {
				$this->error('操作失败，请重试');
			}
		} else {
            // 如果存在公司信息，直接查询遍历
            $companyInfo = M('goods_contact')->field('name, person, phone, wechat, privince, city, area, address')->where(array('sid' => session('store_id')))->find();
            if ($companyInfo) {
                $this->assign('companyInfo', $companyInfo);
                $cityData = M('region')->field('id, name')->where(array('level' => 2, 'parent_id'=>$companyInfo['privince']))->select();
                $areaData = M('region')->field('id, name')->where(array('level' => 3, 'parent_id'=>$companyInfo['city']))->select();
                $this->assign('cityData', $cityData);
                $this->assign('areaData', $areaData);
            }

            // dump($companyInfo);
            // dump($cityData);exit;

			$privinceData = M('region')->field('id, name')->where(array('level' => 1))->select();
            $goodsTypeData = M('goods_category')->field('id, name')->where(array('level' => 1))->select();
            $this->assign('goodsTypeData', $goodsTypeData);
			$this->assign('privinceData', $privinceData);
			$this->display();
		}
	}

    // 正则验证
    public function match($match, $v)
    {
        return preg_match($match, $v);
    }

	public function getCitys()
	{
        $this->ajaxGetData('region');
	}

    public function categoryList()
    {
        $this->ajaxGetData('goods_category');
    }

    public function ajaxGetData($table)
    {
        if (IS_AJAX) {
			$level = I('post.level');
			$id = I('post.id');
			$data = M($table)->field('id, name')->where(array('level' => $level, 'parent_id' => $id))->select();
			$this->ajaxReturn($data);
		}
    }

	public function addinfo()
	{
		if (IS_POST) {

			// dump(I('post.'));exit;
			// 处理表单数据
			$keywords = '';
			for ($i=0; $i < count(I('post.keywords')); $i++) {
				$keywords .= I('post.keywords')[$i].',';
			}

			$value1 = array(
                'store' => session('store_id'),
				'title' => I('post.title'),
				'keyword' => $keywords,
				'description' => I('post.description'),
				'content' => I('post.content'),
				'newsimg' => I('post.newsimg'),
			);
			$res = M('store_art')->add($value1);


			if ($res) {
				// 处理相册
				for ($j=0; $j < count(I('post.goods_images')); $j++) {
					$imagesData[$j]['goods_id'] = $res;
					$imagesData[$j]['image_url'] = I('post.goods_images')[$j];
				}
				$res3 = M('goods_images')->addAll($imagesData);
				if (!$res3) {
					$this->error('图片上传失败');
					exit;
				}

				$value2 = array(
					'sid' => session('store_id'),
					'name' => I('post.company_name'),
					'person' => I('post.connect_person'),
					'phone' => I('post.person_phone'),
					'wechat' => I('post.person_wechat'),
                    'privince' => I('post.privince'),
                    'city' => I('post.city'),
                    'area' => I('post.area'),
					'address' => I('post.company_address'),
				);
                // 判断是否填写过公司信息
                $companyInfo = M('goods_contact')->field('id')->where(array('sid' => session('store_id')))->find();
                if ($companyInfo) {
                    $value2['update_time'] = time();
                    $res2 = M('goods_contact')->where(array('sid' => session('store_id')))->save($value2);
                } else {
                    $res2 = M('goods_contact')->add($value2);
                }

                if ($res2) {
					$this->success('操作成功');
				} else {
					$this->error('操作失败，请重试');
				}
			} else {
				$this->error('操作失败，请重试');
			}
		} else {
			// 如果存在公司信息，直接查询遍历
            $companyInfo = M('goods_contact')->field('name, person, phone, wechat, privince, city, area, address')->where(array('sid' => session('store_id')))->find();
            if ($companyInfo) {
            	// dump($companyInfo);exit;
                $this->assign('companyInfo', $companyInfo);
                $cityData = M('region')->field('id, name')->where(array('level' => 2, 'parent_id'=>$companyInfo['privince']))->select();
                $areaData = M('region')->field('id, name')->where(array('level' => 3, 'parent_id'=>$companyInfo['city']))->select();
                $this->assign('cityData', $cityData);
                $this->assign('areaData', $areaData);
            }

			$privinceData = M('region')->field('id, name')->where(array('level' => 1))->select();
			// dump($privinceData);dump($cityData);dump($areaData);exit;
			$this->assign('privinceData', $privinceData);
			$this->display();
		}
	}

	public function prList()
	{
		checkIsBack();
        $store_goods_class_list = M('store_goods_class')->where("parent_id = 0 and store_id = ".STORE_ID)->select();
        // dump($store_goods_class_list);exit;
        $this->assign('store_goods_class_list',$store_goods_class_list);
        $this->display();
	}

	/**
     *  商品列表
     */
    public function ajaxPrList()
    {

        $where = "store_id = ".STORE_ID; // 搜索条件
        // 关键词搜索
        $key_word = I('key_word') ? trim(I('key_word')) : '';
        if($key_word)
        {
            $where = "$where and (goods_name like '%$key_word%' or goods_sn like '%$key_word%')" ;
        }

        $model = M('Goods');
        $count = $model->where($where)->count();
        $Page  = new AjaxPage($count,10);

        $order_str = "`{$_POST['orderby1']}` {$_POST['orderby2']}";

        //是否从缓存中获取Page
        if(session('is_back')==1){
            $Page = getPageFromCache();
            //重置获取条件
            delIsBack();
        }
        // $where += 'and __STORE__.status=2';
        $goodsList = M('goods')->where(array('store_id'=>session('store_id')))->order('sort,on_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        // dump(M('goods'));exit;
        cachePage($Page);
        $show = $Page->show();
        $catList = D('goods_category')->select();
        $catList = convert_arr_key($catList, 'id');
        $this->assign('catList',$catList);
        $this->assign('goodsList',$goodsList);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();
    }

    /**
     *  添加修改商品
     */
    public function addEditGoods()
    {
        $GoodsLogic = new GoodsLogic();
        $Goods = D('Admin/Goods'); //
        $goods_id = I('goods_id',0);
        $type = $goods_id > 0 ? 2 : 1; // 标识自动验证时的 场景 1 表示插入 2 表示更新
        //
        if($goods_id > 0)
        {
            $c = M('goods')->where("goods_id = $goods_id and store_id = ".STORE_ID)->count();
            if($c == 0)
                $this->error("非法操作",U('Sellerstore/prList'));
        }

        //
        //ajax提交验证
        if(($_GET['is_ajax'] == 1) && IS_POST)
        {
            C('TOKEN_ON',false);
            if(!$Goods->create(NULL,$type))// 根据表单提交的POST数据创建数据对象
            {
                //  编辑
                $error = $Goods->getError();
                $error_msg = array_values($error);
                $return_arr = array(
                    'status' => -1,
                    'msg' => $error_msg[0],
                    'data' => $error,
                );
                $this->ajaxReturn(json_encode($return_arr));
            }else {
               // form表单提交
               // C('TOKEN_ON',true);
                $Goods->on_time = time(); // 上架时间
                $cat_id3 = I('cat_id3',0);
                $_POST['extend_cat_id_2'] && ($Goods->extend_cat_id = I('extend_cat_id_2'));
                $_POST['extend_cat_id_3'] && ($Goods->extend_cat_id = I('extend_cat_id_3'));
                $Goods->shipping_area_ids = implode(',',$_POST['shipping_area_ids']);
                $Goods->shipping_area_ids = $Goods->shipping_area_ids ? $Goods->shipping_area_ids : '';

                $type_id = M('goods_category')->where("id = $cat_id3")->getField('type_id'); // 找到这个分类对应的type_id
                // dump($type_id);exit;
                $store_goods_examine = M('store')->where(array('store_id'=>STORE_ID))->getField('goods_examine');
                $Goods->goods_type = $type_id ? $type_id : 0;
                $Goods->store_id = STORE_ID; // 店家id
                if($store_goods_examine){
                    $Goods->goods_state = 0; // 待审核
                }else{
                    $Goods->goods_state = 1; // 出售中
                }

                if($Goods->distribut > ($Goods->shop_price / 2))
                    $this->ajaxReturn(json_encode(array('status' => -1,'msg'=> '分销的分成金额不得超过商品金额的50%','data'  =>'')));

                if ($type == 2)
                {
                	if(M('Goods')->where(array('goods_id'=>$goods_id,'store_id'=>STORE_ID))->count()>0){
                        // 修改商品后购物车的商品价格也修改一下
                        M('cart')->where("goods_id = $goods_id and spec_key = ''")->save(array(
                                'market_price'=>$_POST['market_price'], //市场价
                                'goods_price'=>$_POST['shop_price'], // 本店价
                                'member_goods_price'=>$_POST['shop_price'], // 会员折扣价
                                ));
                		$Goods->save(); // 编辑数据到数据库
                	}else{
                		$this->ajaxReturn(array('status' => -1,'msg'=> '非法操作'),'JSON');
                	}
                }
                else
                {
                    $goods_id = $Goods->add(); // 新增数据到数据库
                }
                $Goods->afterSave($goods_id,STORE_ID);
                $GoodsLogic->saveGoodsAttr($goods_id,$type_id,STORE_ID); // 处理商品 属性
                $return_arr = array(
                    'status' => 1,
                    'msg'   => '操作成功',
                    'data'  => array('url'=>U('Sellerstore/prList')),
                );
               //重定向, 调整之前URL是设置参数获取方式
               session("is_back" , 1);
               $this->ajaxReturn(json_encode($return_arr));

            }
        }

        $goodsInfo =M('Goods')->where('goods_id='.I('GET.goods_id',0))->find();
        $store = M('store')->where(array('store_id'=>STORE_ID))->find();
        if($store['bind_all_gc'] == 1){
        	$cat_list = M('goods_category')->where("parent_id = 0")->select();//自营店已绑定所有分类
        }else{
        	$cat_list = M('goods_category')->where("parent_id = 0 and id in(select class_1 from ".C('DB_PREFIX')."store_bind_class  where store_id = ".STORE_ID." and state = 1 )")->select();//自营店已绑定所有分类
        }
        $store_goods_class_list = M('store_goods_class')->where("parent_id = 0 and store_id = ".STORE_ID)->select(); //店铺内部分类
        $brandList = $GoodsLogic->getSortBrands();
        $goodsType = M("GoodsType")->select();
        $suppliersList = M("suppliers")->select();
        $plugin_shipping = M('plugin')->where(array('type'=>array('eq','shipping')))->select();//插件物流


        // 查询公司信息
        $companyInfo = M('goods_contact')->where(array('sid' => $store['store_id']))->find();
        $this->assign('companyInfo', $companyInfo);
        // 处理省市数据
        $privinceData = M('region')->field('id, name')->where(array('level' => 1))->select();

        $cityData = M('region')->field('id, name')->where(array('level' => 2, 'parent_id'=>$companyInfo['privince']))->select();
        $areaData = M('region')->field('id, name')->where(array('level' => 3, 'parent_id'=>$companyInfo['city']))->select();
        $this->assign('privinceData', $privinceData);
        $this->assign('cityData', $cityData);
        $this->assign('areaData', $areaData);
        // dump($areaData);exit;

        // 图片集合
        $goodsImages = M('goods_images')->field('image_url')->where(array('goods_id' => $goodsInfo['goods_id']))->select();
        $this->assign('goodsImages', $goodsImages);

        // dump($companyInfo);exit;
        // dump($store_goods_class_list);
        // dump($store);
        // dump($cat_list);
        // dump($brandList);
        // dump($goodsInfo);exit;
        $goodsTypeData = M('goods_category')->field('id, name')->where(array('level' => 1))->select();
        $this->assign('goodsTypeData', $goodsTypeData);
        $shipping_area = D('shipping_area')->getShippingArea(STORE_ID);//配送区域
        $goods_shipping_area_ids = explode(',',$goodsInfo['shipping_area_ids']);
        $this->assign('goods_shipping_area_ids',$goods_shipping_area_ids);
        $this->assign('shipping_area',$shipping_area);
        $this->assign('plugin_shipping',$plugin_shipping);
        $this->assign('cat_list',$cat_list);
        $this->assign('store_goods_class_list',$store_goods_class_list);
        $this->assign('brandList',$brandList);
        $this->assign('goodsType',$goodsType);
        $this->assign('suppliersList',$suppliersList);
        $this->assign('goodsInfo',$goodsInfo);  // 商品详情
        $this->initEditor(); // 编辑器
        $this->display('_goods');
    }
    // 信息管理
    public function infolist()
    {
        $id = (!empty($_GET['type']))?$_GET['type']:0;
        $_GET['p'] = (empty($_GET['p']))?0:$_GET['p'];
        $where = 'sn_id = '.$id.' and store = '.STORE_ID;
        if($id==0)$where = 'store = '.STORE_ID;
        $list= M('store_art as a')->where(array('store'=>session('store_id')))->page($_GET['p'].',10')->order('timer desc')->select();
        $count = M('store_art')->where($where)->count();
        $Page = new \Think\Page($count,10);
        $show = $Page->show();
        $this->assign('page',$show);
        $this->assign('list',$list);
        $this->display();
    }

    public function uddinfo()
    {
        $nav = M('store_navigation')->where("sn_is_list = 1 and sn_store_id=".STORE_ID)->select();

        $info = M('store_art')->where('id = '.$_GET['id'].' and store = '.STORE_ID)->select();

        // dump($info);dump($nav);exit;

        // 查询公司信息
        $companyInfo = M('goods_contact')->where(array('sid' => $info[0]['store']))->find();
        $this->assign('companyInfo', $companyInfo);
        // 处理省市数据
        $privinceData = M('region')->field('id, name')->where(array('level' => 1))->select();
        $cityData = M('region')->field('id, name')->where(array('level' => 2, 'parent_id'=>$companyInfo['privince']))->select();
        $areaData = M('region')->field('id, name')->where(array('level' => 3, 'parent_id'=>$companyInfo['city']))->select();
        $this->assign('privinceData', $privinceData);
        $this->assign('cityData', $cityData);
        $this->assign('areaData', $areaData);

        // 图片集合
        $goodsImages = M('goods_images')->field('image_url')->where(array('goods_id' => $goodsInfo['goods_id']))->select();
        $this->assign('goodsImages', $goodsImages);

        $this->initEditor();
        $this->assign('info',$info[0]);
        $this->assign('nav',$nav);
        $this->display();
    }

    public function infoHandle()
    {
        $data = I('post.');
        $data['timer'] = time();
        if($data['act'] == 'del'){
            $r = M('store_art')->where('id='.$data['id'])->delete();
            if($r) exit(json_encode(1));
        }
        if(empty($data['id'])){
            $data['store'] = STORE_ID;
            $r = M('store_art')->add($data);
        }else{
            $r = M('store_art')->where('id='.$data['id'])->save($data);
        }
        if($r){
            $this->success("操作成功",U('News/newslist'));
        }else{
            $this->error("操作失败",U('News/newslist'));
        }
    }

    /**
    *	2016/12/1
    *	富文本框参数
    */
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

    public function ajax_re_news()
    {
        $news_id = I('news_id');
        if (!$news_id){
            $this->ajaxReturn();
        }

        $time = time()-86400;

        $where['time'] = array('gt',$time);
        $where['news_id'] = $news_id;
        $data['time'] = time();
        $data['news_id'] = $news_id;
        $data3['timer'] = time();
        $res2 = M('rest_news')->where($where)->find();
        if (!$res2){
            $res = M('rest_news')->add($data);
            M('store_art')->where(array('id'=>$news_id))->save($data3);
            $this->ajaxReturn(true);
        }
        if ($res2['num'] == 5){
            $this->ajaxReturn();
        }

        $data2['num'] = $res2['num']+1;
        $res = M('rest_news')->where(array('id'=>$res2['id']))->save($data2);
        if ($res){
            M('store_art')->where(array('id'=>$news_id))->save($data3);
            $this->ajaxReturn(true);
        }
        $this->ajaxReturn();
    }


}
