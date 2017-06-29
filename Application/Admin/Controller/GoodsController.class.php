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
 * Author: IT宇宙人
 * Date: 2015-09-09
 */
namespace Admin\Controller;
use Admin\Logic\GoodsLogic;
use Think\AjaxPage;
use Think\Page;

class GoodsController extends BaseController {

    /**
     *  商品分类列表
     */
    public function categoryList(){
        $GoodsLogic = new GoodsLogic();
        $cat_list = $GoodsLogic->goods_cat_list();
        $goods_type = M('goods_type')->getField('id,name');
        $this->assign('goods_type',$goods_type);
        $this->assign('cat_list',$cat_list);
        $this->display();
    }

    /**
     * 添加修改商品分类
     * 手动拷贝分类正则 ([\u4e00-\u9fa5/\w]+)  ('393','$1'),
     * select * from tp_goods_category where id = 393
        select * from tp_goods_category where parent_id = 393
        update tp_goods_category  set parent_id_path = concat_ws('_','0_76_393',id),`level` = 3 where parent_id = 393
        insert into `tp_goods_category` (`parent_id`,`name`) values
        ('393','时尚饰品'),
     */
    public function addEditCategory(){

            $GoodsLogic = new GoodsLogic();
            $db_prefix = C('DB_PREFIX');
            if(IS_GET)
            {
                $goods_category_info = D('GoodsCategory')->where('id='.I('GET.id',0))->find();
                $level_cat = $GoodsLogic->find_parent_cat($goods_category_info['id']); // 获取分类默认选中的下拉框

                $cat_list = M('goods_category')->where("parent_id = 0")->select(); // 已经改成联动菜单
                $this->assign('level_cat',$level_cat);
                $this->assign('cat_list',$cat_list);
                $this->assign('goods_category_info',$goods_category_info);

                $goods_category_list = M('goods_category')->where("id in(select cat_id1 from {$db_prefix}goods_type) ")->getField("id,name,parent_id");
                $goods_category_list[0] = array('id'=>0, 'name'=>'默认');
                asort($goods_category_list);
                $this->assign('goods_category_list',$goods_category_list);
                $goods_type_list = M('goods_type')->select(); // 所有类型id
                $this->assign('goods_type_list',$goods_type_list);
                $this->display('_category');
                exit;
            }

            $GoodsCategory = D('GoodsCategory'); //

            $type = $_POST['id'] > 0 ? 2 : 1; // 标识自动验证时的 场景 1 表示插入 2 表示更新
            //ajax提交验证
            if($_GET['is_ajax'] == 1)
            {
                C('TOKEN_ON',false);

                if(!$GoodsCategory->create(NULL,$type))// 根据表单提交的POST数据创建数据对象
                {
                    //  编辑
                    $return_arr = array(
                        'status' => -1,
                        'msg'   => '操作失败!',
                        'data'  => $GoodsCategory->getError(),
                    );
                    $this->ajaxReturn(json_encode($return_arr));
                }else {
                    //  form表单提交
                    C('TOKEN_ON',true);

                    $GoodsCategory->parent_id = $_POST['parent_id_1'];
                    $_POST['parent_id_2'] && ($GoodsCategory->parent_id = $_POST['parent_id_2']);

                    if($GoodsCategory->id > 0 && $GoodsCategory->parent_id == $GoodsCategory->id)
                    {
                        //  编辑
                        $return_arr = array(
                            'status' => -1,
                            'msg'   => '上级分类不能为自己',
                            'data'  => '',
                        );
                        $this->ajaxReturn(json_encode($return_arr));
                    }
                    // 平台抽成比例
                    if($GoodsCategory->commission > 100)
                    {
                        //  编辑
                        $return_arr = array(
                            'status' => -1,
                            'msg'   => '抽成比例不得超过100%',
                            'data'  => '',
                        );
                        $this->ajaxReturn(json_encode($return_arr));
                    }
                    if ($type == 2)
                        $GoodsCategory->save(); // 写入数据到数据库
                    else
                        $_POST['id'] = $GoodsCategory->add(); // 写入数据到数据库

                        $GoodsLogic->refresh_cat($_POST['id']);
                     // 修改它下面的所有分类的 type_id 等于它的type_id
                        $category = M('goods_category')->where("id = {$_POST['id']}")->find();
                        M('goods_category')->where("parent_id_path like '{$category['parent_id_path']}\_%'")->save(array('type_id'=>$_POST['type_id'],'commission'=>$_POST['commission']));

                    $return_arr = array(
                        'status' => 1,
                        'msg'   => '操作成功',
                        'data'  => array('url'=>U('Admin/Goods/categoryList')),
                    );
                    $this->ajaxReturn(json_encode($return_arr));

                }
            }

    }

    /**
     * 获取商品分类 的帅选规格 复选框
     */
    public function ajaxGetSpecList(){
        $GoodsLogic = new GoodsLogic();
        $_REQUEST['category_id'] = $_REQUEST['category_id'] ? $_REQUEST['category_id'] : 0;
        $filter_spec = M('GoodsCategory')->where("id = ".$_REQUEST['category_id'])->getField('filter_spec');
        $filter_spec_arr = explode(',',$filter_spec);
        $str = $GoodsLogic->GetSpecCheckboxList($_REQUEST['type_id'],$filter_spec_arr);
        $str = $str ? $str : '没有可帅选的商品规格';
        exit($str);
    }

    /**
     * 获取商品分类 的帅选属性 复选框
     */
    public function ajaxGetAttrList(){
        $GoodsLogic = new GoodsLogic();
        $_REQUEST['category_id'] = $_REQUEST['category_id'] ? $_REQUEST['category_id'] : 0;
        $filter_attr = M('GoodsCategory')->where("id = ".$_REQUEST['category_id'])->getField('filter_attr');
        $filter_attr_arr = explode(',',$filter_attr);
        $str = $GoodsLogic->GetAttrCheckboxList($_REQUEST['type_id'],$filter_attr_arr);
        $str = $str ? $str : '没有可帅选的商品属性';
        exit($str);
    }

    /**
     * 删除分类
     */
    public function delGoodsCategory(){
        // 判断子分类
        $GoodsCategory = M("GoodsCategory");
        $count = $GoodsCategory->where("parent_id = {$_GET['id']}")->count("id");
        $count > 0 && $this->error('该分类下还有分类不得删除!',U('Admin/Goods/categoryList'));
        // 判断是否存在商品
        $goods_count = M('Goods')->where("cat_id1 = {$_GET['id']} or cat_id2 = {$_GET['id']} or  cat_id3 = {$_GET['id']}")->count('1');
        $goods_count > 0 && $this->error('该分类下有商品不得删除!',U('Admin/Goods/categoryList'));
        // 删除分类
        $GoodsCategory->where("id = {$_GET['id']}")->delete();
        $this->success("操作成功!!!",U('Admin/Goods/categoryList'));
    }


    /**
     *  商品列表
     */
    public function goodsList(){
        $GoodsLogic = new GoodsLogic();
        $brandList = $GoodsLogic->getSortBrands();
        $categoryList = $GoodsLogic->getSortCategory();
        $this->assign('categoryList',$categoryList);
        $this->assign('brandList',$brandList);
        $this->display();
    }


   



    /**
     *  商品列表
     */
    public function ajaxGoodsList(){
        $where = ' 1 = 1 '; // 搜索条件
       
        I('intro')    && $where = "$where and ".I('intro')." = 1" ;
        I('board') && $where = "$where and ".I('field')." = ".I('board');
        I('brand_id') && $where = "$where and brand_id = ".I('brand_id') ;
        (I('goods_state') !== '') && $where = "$where and goods_state = ".I('goods_state') ;
        $cat_id = I('cat_id');
        // 关键词搜索
        $key_word = I('key_word') ? trim(I('key_word')) : '';
        if($key_word)
        {
            $where = "$where and (goods_name like '%$key_word%' or goods_sn like '%$key_word%')" ;
        }

        // 商品id搜索
        $goods_id = I('goods_id');
        if($goods_id)
        {
            $where = "$where and (goods_id like '%$goods_id%' or goods_id like '%$goods_id%')" ;
        }

        if($cat_id > 0)
        {
            $where .= " and (cat_id1 = $cat_id or cat_id2 = $cat_id or cat_id3 = $cat_id ) "; // 初始化搜索条件
        }

        $model = M('Goods');
        $count = $model->where($where)->count();
        $Page  = new AjaxPage($count,10);
        /**  搜索条件下 分页赋值
        foreach($condition as $key=>$val) {
            $Page->parameter[$key]   =   urlencode($val);
        }
        */
        $show = $Page->show();
        // $order_str = "`{$_POST['orderby1']}` {$_POST['orderby2']}";
        $goodsList = $model->where($where)->order($order_str)->limit($Page->firstRow.','.$Page->listRows)->select();
        // dump($where);exit;

        $catList = D('goods_category')->select();
        $catList = convert_arr_key($catList, 'id');
        $goods_state = C('goods_state');
        $this->assign('catList',$catList);
        $this->assign('goodsList',$goodsList);
        $this->assign('goods_state',$goods_state);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();
    }


    /**
     * 添加修改商品
     */
    public function addEditGoods(){

            $GoodsLogic = new GoodsLogic();
            $Goods = D('Goods'); //
            $type = $_POST['goods_id'] > 0 ? 2 : 1; // 标识自动验证时的 场景 1 表示插入 2 表示更新
            //ajax提交验证
            if(($_GET['is_ajax'] == 1) && IS_POST)
            {
                C('TOKEN_ON',false);
                if(!$Goods->create(NULL,$type))// 根据表单提交的POST数据创建数据对象
                {
                    //  编辑
                    $return_arr = array(
                        'status' => -1,
                        'msg'   => '操作失败',
                        'data'  => $Goods->getError(),
                    );
                    $this->ajaxReturn(json_encode($return_arr));
                }else {
                    //  form表单提交
                   // C('TOKEN_ON',true);
                    $Goods->on_time = time(); // 上架时间
                    //$Goods->cat_id = $_POST['cat_id_1'];
                    //$_POST['cat_id_2'] && ($Goods->cat_id = $_POST['cat_id_2']);
                    //$_POST['cat_id_3'] && ($Goods->cat_id = $_POST['cat_id_3']);

                    $_POST['extend_cat_id_2'] && ($Goods->extend_cat_id = $_POST['extend_cat_id_2']);
                    $_POST['extend_cat_id_3'] && ($Goods->extend_cat_id = $_POST['extend_cat_id_3']);

                    if ($type == 2)
                    {
                        $goods_id = $_POST['goods_id'];
                        $Goods->save(); // 写入数据到数据库
                        $Goods->afterSave($goods_id);
                    }
                    else
                    {
                        $goods_id = $insert_id = $Goods->add(); // 写入数据到数据库
                        $Goods->afterSave($goods_id);
                    }

                    $GoodsLogic->saveGoodsAttr($goods_id, $_POST['goods_type']); // 处理商品 属性

                    $return_arr = array(
                        'status' => 1,
                        'msg'   => '操作成功',
                        'data'  => array('url'=>U('Admin/Goods/goodsList')),
                    );
                    $this->ajaxReturn(json_encode($return_arr));
                }
            }

            $goodsInfo = D('Goods')->where('goods_id='.I('GET.id',0))->find();
            //$cat_list = $GoodsLogic->goods_cat_list(); // 已经改成联动菜单
            $level_cat2 = $GoodsLogic->find_parent_cat($goodsInfo['extend_cat_id']); // 获取分类默认选中的下拉框
            $cat_list = M('goods_category')->where("parent_id = 0")->select(); // 已经改成联动菜单
            $brandList = $GoodsLogic->getSortBrands();
            $goodsType = M("GoodsType")->select();
            $this->assign('level_cat',$level_cat);
            $this->assign('level_cat2',$level_cat2);
            $this->assign('cat_list',$cat_list);
            $this->assign('brandList',$brandList);
            $this->assign('goodsType',$goodsType);
            $this->assign('goodsInfo',$goodsInfo);  // 商品详情
            $goodsImages = M("GoodsImages")->where('goods_id ='.I('GET.id',0))->select();
            $this->assign('goodsImages',$goodsImages);  // 商品相册
            $this->initEditor(); // 编辑器
            $this->display('_goods');
    }

    /**
     * 商品类型  用于设置商品的属性
     */
    public function goodsTypeList(){
        $model = M("GoodsType");
        $count = $model->count();
        $Page  = new Page($count,100);
        $show  = $Page->show();
        $goodsTypeList = $model->order("id desc")->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('show',$show);
        $this->assign('goodsTypeList',$goodsTypeList);
        $this->display('goodsTypeList');
    }


    /**
     * 添加修改编辑  商品属性类型
     */
    public  function addEditGoodsType(){
            $id = $_GET['id'] ? $_GET['id'] : 0;
            $model = M("GoodsType");
            if(IS_POST)
            {
                    $model->create();
                    // 编辑操作
                    if($id){
                        $model->save();
                    }
                    else // 添加操作
                    {
                        $id = $model->add();
                    }

                    // 类型规格对应关系表
                    if($id && !empty($_POST['spec_id']))
                    {
                        $dataList = array();
                        foreach($_POST['spec_id'] as $k => $v)
                          $dataList[] = array('type_id'=>$id,'spec_id'=>$v);

                        M('spec_type')->where("type_id = $id")->delete(); // 先把类型规格 表对应的 删除掉 然后再重新添加
                        M('spec_type')->addAll($dataList);
                    }

                    // 类型品牌对应关系表
                    if($id && !empty($_POST['brand_id']))
                    {
                        $dataList = array();
                        foreach($_POST['brand_id'] as $k => $v)
                          $dataList[] = array('type_id'=>$id,'brand_id'=>$v);

                        M('brand_type')->where("type_id = $id")->delete(); // 先把类型规格 表对应的 删除掉 然后再重新添加
                        M('brand_type')->addAll($dataList);
                    }
                    // 处理商品属性
                    if($id && !empty($_POST['attr_name']))
                    {
                        $dataList = array();
                        foreach($_POST['attr_name'] as $k => $v)
                        {
                            $_POST['attr_values'][$k] = str_replace('_', '', $_POST['attr_values'][$k]); // 替换特殊字符
                            $_POST['attr_values'][$k] = str_replace('@', '', $_POST['attr_values'][$k]); // 替换特殊字符
                            $_POST['attr_values'][$k] = trim($_POST['attr_values'][$k]);
                            $_POST['attr_index'][$k] = $_POST['attr_index'][$k] ? $_POST['attr_index'][$k] : 0; // 是否显示
                           $attribute  = array(
                                'attr_name'=>$v,
                                'type_id'=>$id,
                                'attr_index'=>$_POST['attr_index'][$k],
                                'attr_values'=>$_POST['attr_values'][$k],
                                'attr_input_type'=>'1',
                                'order'=>$_POST['order'][$k],
                                );
                            if(empty($_POST['attr_id'][$k]))
                                $dataList[] = $attribute;
                            else{
                                $attribute['attr_id'] = $_POST['attr_id'][$k];
                                M('goods_attribute')->save($attribute);
                            }
                        }
                        // 插入属性
                       if($dataList)
                           M('goods_attribute')->addAll($dataList);
                    }

                    $this->success("操作成功!!!",U('Admin/Goods/goodsTypeList'));
                    exit;
            }
           $goodsType = $model->where("id = $id")->find();
           $GoodsLogic = new GoodsLogic();
           $cat_list = M('goods_category')->where("parent_id = 0")->select(); // 已经改成联动菜单
           $attributeList = M('goods_attribute')->where("type_id = $id")->select();
           $this->assign('attributeList',$attributeList);
           $this->assign('cat_list',$cat_list);
           $this->assign('level_cat',$level_cat);
           $this->assign('goodsType',$goodsType);
           $this->display('_goodsType');
    }

    /**
     * 商品属性列表
     */
    public function goodsAttributeList(){
        $goodsTypeList = M("GoodsType")->select();
        $this->assign('goodsTypeList',$goodsTypeList);
        $this->display();
    }

    /**
     *  商品属性列表
     */
    public function ajaxGoodsAttributeList(){
        //ob_start('ob_gzhandler'); // 页面压缩输出
        $where = ' 1 = 1 '; // 搜索条件
        I('type_id')   && $where = "$where and type_id = ".I('type_id') ;
        // 关键词搜索
        $model = M('GoodsAttribute');
        $count = $model->where($where)->count();
        $Page       = new AjaxPage($count,13);
        $show = $Page->show();
        $goodsAttributeList = $model->where($where)->order('`order` desc,attr_id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
        $goodsTypeList = M("GoodsType")->getField('id,name');
        $attr_input_type = array(0=>'手工录入',1=>' 从列表中选择',2=>' 多行文本框');
        $this->assign('attr_input_type',$attr_input_type);
        $this->assign('goodsTypeList',$goodsTypeList);
        $this->assign('goodsAttributeList',$goodsAttributeList);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();
    }

    /**
     * 添加修改编辑  商品属性
     */
    public  function addEditGoodsAttribute(){

            $model = D("GoodsAttribute");
            $type = $_POST['attr_id'] > 0 ? 2 : 1; // 标识自动验证时的 场景 1 表示插入 2 表示更新
            $_POST['attr_values'] = str_replace('_', '', $_POST['attr_values']); // 替换特殊字符
            $_POST['attr_values'] = str_replace('@', '', $_POST['attr_values']); // 替换特殊字符
            $_POST['attr_values'] = trim($_POST['attr_values']);

            if(($_GET['is_ajax'] == 1) && IS_POST)//ajax提交验证
            {
                C('TOKEN_ON',false);
                if(!$model->create(NULL,$type))// 根据表单提交的POST数据创建数据对象
                {
                    //  编辑
                    $return_arr = array(
                        'status' => -1,
                        'msg'   => '',
                        'data'  => $model->getError(),
                    );
                    $this->ajaxReturn(json_encode($return_arr));
                }else {
                   // C('TOKEN_ON',true); //  form表单提交
                    if ($type == 2)
                    {
                        $model->save(); // 写入数据到数据库
                    }
                    else
                    {
                        $insert_id = $model->add(); // 写入数据到数据库
                    }
                    $return_arr = array(
                        'status' => 1,
                        'msg'   => '操作成功',
                        'data'  => array('url'=>U('Admin/Goods/goodsAttributeList')),
                    );
                    $this->ajaxReturn(json_encode($return_arr));
                }
            }
           // 点击过来编辑时
           $_GET['attr_id'] = $_GET['attr_id'] ? $_GET['attr_id'] : 0;
           $goodsTypeList = M("GoodsType")->select();
           $goodsAttribute = $model->find($_GET['attr_id']);
           $this->assign('goodsTypeList',$goodsTypeList);
           $this->assign('goodsAttribute',$goodsAttribute);
           $this->display('_goodsAttribute');
    }

    /**
     * 更改指定表的指定字段
     */
    public function updateField(){
        $primary = array(
                'goods' => 'goods_id',
                'goods_category' => 'id',
                'brand' => 'id',
                'goods_attribute' => 'attr_id',
        		'ad' =>'ad_id',
        );
        $model = D($_POST['table']);
        $model->$primary[$_POST['table']] = $_POST['id'];
        $model->$_POST['field'] = $_POST['value'];
        $model->save();
        $return_arr = array(
            'status' => 1,
            'msg'   => '操作成功',
            'data'  => array('url'=>U('Admin/Goods/goodsAttributeList')),
        );
        $this->ajaxReturn(json_encode($return_arr));
    }
    /**
     * 动态获取商品属性输入框 根据不同的数据返回不同的输入框类型
     */
    public function ajaxGetAttrInput(){
        $GoodsLogic = new GoodsLogic();
        $str = $GoodsLogic->getAttrInput($_REQUEST['goods_id'],$_REQUEST['type_id']);
        exit($str);
    }

    /**
     * 删除商品
     */
    public function delGoods()
    {
        $goods_id = $_GET['id'];
        $error = '';

        // 判断此商品是否有订单
        $c1 = M('OrderGoods')->where("goods_id = $goods_id")->count('1');
        $c1 && $error .= '此商品有订单,不得删除! <br/>';


         // 商品团购
        $c1 = M('group_buy')->where("goods_id = $goods_id")->count('1');
        $c1 && $error .= '此商品有团购,不得删除! <br/>';

         // 商品退货记录
        $c1 = M('return_goods')->where("goods_id = $goods_id")->count('1');
        $c1 && $error .= '此商品有退货记录,不得删除! <br/>';

        if($error)
        {
            $return_arr = array('status' => -1,'msg' =>$error,'data'  =>'',);   //$return_arr = array('status' => -1,'msg' => '删除失败','data'  =>'',);
            $this->ajaxReturn(json_encode($return_arr));
        }

        // 删除此商品
        M("Goods")->where('goods_id ='.$goods_id)->delete();  //商品表
        M("cart")->where('goods_id ='.$goods_id)->delete();  // 购物车
        M("comment")->where('goods_id ='.$goods_id)->delete();  //商品评论
        M("goods_consult")->where('goods_id ='.$goods_id)->delete();  //商品咨询
        M("goods_images")->where('goods_id ='.$goods_id)->delete();  //商品相册
        M("spec_goods_price")->where('goods_id ='.$goods_id)->delete();  //商品规格
        M("spec_image")->where('goods_id ='.$goods_id)->delete();  //商品规格图片
        M("goods_attr")->where('goods_id ='.$goods_id)->delete();  //商品属性
        M("goods_collect")->where('goods_id ='.$goods_id)->delete();  //商品收藏

        $return_arr = array('status' => 1,'msg' => '操作成功','data'  =>'',);   //$return_arr = array('status' => -1,'msg' => '删除失败','data'  =>'',);
        $this->ajaxReturn(json_encode($return_arr));
    }

    /**
     * 删除商品类型
     */
    public function delGoodsType()
    {
        // 判断 商品规格        `tp_spec_type`   `tp_brand_type`
        $count = M("spec_type")->where("type_id = {$_GET['id']}")->count("1");
        $count > 0 && $this->error('该类型下有商品规格不得删除!',U('Admin/Goods/goodsTypeList'));

        $count = M("brand_type")->where("type_id = {$_GET['id']}")->count("1");
        $count > 0 && $this->error('该类型下有管理品牌不得删除!',U('Admin/Goods/goodsTypeList'));

        // 判断 商品属性
        $count = M("GoodsAttribute")->where("type_id = {$_GET['id']}")->count("1");
        $count > 0 && $this->error('该类型下有商品属性不得删除!',U('Admin/Goods/goodsTypeList'));
        // 删除分类
        M('GoodsType')->where("id = {$_GET['id']}")->delete();
        $this->success("操作成功!!!",U('Admin/Goods/goodsTypeList'));
    }

    /**
     * 删除商品属性
     */
    public function delGoodsAttribute()
    {
        $id = I('id');
        if(empty($id))  return;
        // 删除 属性
        M("GoodsAttr")->where("attr_id = $id")->delete();
        M('GoodsAttribute')->where("attr_id = $id")->delete();
    }

    /**
     * 删除商品规格
     */
    public function delGoodsSpec()
    {
        // 判断 商品规格项
        $count = M("SpecItem")->where("spec_id = {$_GET['id']}")->count("1");
        $count > 0 && $this->error('此规格有规格值不得删除!',U('Admin/Goods/specList'));
        // 删除分类
        M('Spec')->where("id = {$_GET['id']}")->delete();
        $this->success("操作成功!!!",U('Admin/Goods/specList'));
    }

    /**
     * 品牌列表
     */
    public function brandList(){
        $model = M("Brand");
        $where = " 1 = 1 ";
        $status = I('status');
        $keyword = I('keyword');
        $status && $where .= " and status = $status ";
        $keyword && $where .= " and name like '%$keyword%' ";
        $count = $model->where($where)->count();
        $Page  = new Page($count,10);
        $brandList = $model->where($where)->order("`sort` desc, FIELD(`status`, 1, 2, 0)")->limit($Page->firstRow.','.$Page->listRows)->select();
        $show  = $Page->show();
        $cat_list = M('goods_category')->where("parent_id = 0")->getField('id,name'); // 已经改成联动菜单
        $this->assign('cat_list',$cat_list);
        $this->assign('show',$show);
        $this->assign('brandList',$brandList);
        $this->display('brandList');
    }

    /**
     * ajax 获取 品牌列表
     */
    public function getBrandByCat(){
        $db_prefix = C('DB_PREFIX');
        $cat_id = I('cat_id');
        $level = I('l');
        $type_id = I('type_id');

        if($type_id)
            $list = M('brand')->join("left join {$db_prefix}brand_type on {$db_prefix}brand.id = {$db_prefix}brand_type.brand_id and  type_id = $type_id")->order('id')->select();
        else
            $list = M('brand')->order('id')->select();

        $goods_category_list = M('goods_category')->where("id in(select cat_id1 from {$db_prefix}brand) ")->getField("id,name,parent_id");
        $goods_category_list[0] = array('id'=>0, 'name'=>'默认');
        asort($goods_category_list);
        $this->assign('goods_category_list',$goods_category_list);
        $this->assign('type_id',$type_id);
        $this->assign('list',$list);
        $this->display();
    }


    /**
     * ajax 获取 规格列表
     */
    public function getSpecByCat(){

        $db_prefix = C('DB_PREFIX');
        $cat_id = I('cat_id');
        $level = I('l');
        $type_id = I('type_id');

        if($type_id)
            $list = M('spec')->join("left join {$db_prefix}spec_type on {$db_prefix}spec.id = {$db_prefix}spec_type.spec_id  and  type_id = $type_id")->order('id')->select();
        else
            $list = M('spec')->order('id')->select();

        $goods_category_list = M('goods_category')->where("id in(select cat_id1 from {$db_prefix}spec) ")->getField("id,name,parent_id");
        $goods_category_list[0] = array('id'=>0, 'name'=>'默认');
        asort($goods_category_list);
        $this->assign('goods_category_list',$goods_category_list);
        $this->assign('type_id',$type_id);
        $this->assign('list',$list);
        $this->display();
    }

    /**
     * 添加修改编辑  商品品牌
     */
    public  function addEditBrand(){
            $id = I('id',0);
            $model = M("Brand");
            if(IS_POST)
            {
                    $model->create();
                    if($id)
                        $model->save();
                    else
                        $id = $model->add();

                    $this->success("操作成功!!!",U('Admin/Goods/brandList',array('p'=>$_GET['p'])));
                    exit;
            }
           $cat_list = M('goods_category')->where("parent_id = 0")->select(); // 已经改成联动菜单
           $this->assign('cat_list',$cat_list);
           $brand = $model->where("id = $id")->find();
           $this->assign('brand',$brand);
           $this->display('_brand');
    }

    /**
     * 删除品牌
     */
    public function delBrand()
    {
        // 判断此品牌是否有商品在使用
        $goods_count = M('Goods')->where("brand_id = {$_GET['id']}")->count('1');
        if($goods_count)
        {
            $return_arr = array('status' => -1,'msg' => '此品牌有商品在用不得删除!','data'  =>'',);   //$return_arr = array('status' => -1,'msg' => '删除失败','data'  =>'',);
            $this->ajaxReturn(json_encode($return_arr));
        }

        $model = M("Brand");
        $model->where('id ='.$_GET['id'])->delete();
        $return_arr = array('status' => 1,'msg' => '操作成功','data'  =>'',);   //$return_arr = array('status' => -1,'msg' => '删除失败','data'  =>'',);
        $this->ajaxReturn(json_encode($return_arr));
    }

    /**
     * 初始化编辑器链接
     * 本编辑器参考 地址 http://fex.baidu.com/ueditor/
     */
    private function initEditor()
    {
        $this->assign("URL_upload", U('Admin/Ueditor/imageUp',array('savepath'=>'goods'))); // 图片上传目录
        $this->assign("URL_imageUp", U('Admin/Ueditor/imageUp',array('savepath'=>'article'))); //  不知道啥图片
        $this->assign("URL_fileUp", U('Admin/Ueditor/fileUp',array('savepath'=>'article'))); // 文件上传s
        $this->assign("URL_scrawlUp", U('Admin/Ueditor/scrawlUp',array('savepath'=>'article')));  //  图片流
        $this->assign("URL_getRemoteImage", U('Admin/Ueditor/getRemoteImage',array('savepath'=>'article'))); // 远程图片管理
        $this->assign("URL_imageManager", U('Admin/Ueditor/imageManager',array('savepath'=>'article'))); // 图片管理
        $this->assign("URL_getMovie", U('Admin/Ueditor/getMovie',array('savepath'=>'article'))); // 视频上传
        $this->assign("URL_Home", "");
    }



    /**
     * 商品规格列表
     */
    public function specList(){
        $cat_list = M('goods_category')->where("parent_id = 0")->getField('id,name,parent_id'); // 已经改成联动菜单
        $this->assign('cat_list',$cat_list);
        $this->display();
    }


    /**
     *  商品规格列表
     */
    public function ajaxSpecList(){
        //ob_start('ob_gzhandler'); // 页面压缩输出
        $where = ' 1 = 1 '; // 搜索条件
        I('cat_id1')   && $where = "$where and cat_id1 = ".I('cat_id1') ;
        // 关键词搜索
        $model = D('spec');
        $count = $model->where($where)->count();
        $Page       = new AjaxPage($count,13);
        $show = $Page->show();

        $cat_list = M('goods_category')->where("parent_id = 0")->getField('id,name'); // 已经改成联动菜单
        $specList = $model->where($where)->order('`cat_id1` desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('cat_list',$cat_list);
        $this->assign('specList',$specList);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();
    }
    /**
     * 添加修改编辑  商品规格
     */
    public  function addEditSpec(){

            $model = D("spec");
            $type = $_POST['id'] > 0 ? 2 : 1; // 标识自动验证时的 场景 1 表示插入 2 表示更新
            if(($_GET['is_ajax'] == 1) && IS_POST)//ajax提交验证
            {
                C('TOKEN_ON',false);
                if(!$model->create(NULL,$type))// 根据表单提交的POST数据创建数据对象
                {
                    //  编辑
                    $return_arr = array(
                        'status' => -1,
                        'msg'   => '操作失败',
                        'data'  => $model->getError(),
                    );
                    $this->ajaxReturn(json_encode($return_arr));
                }else {
                   // C('TOKEN_ON',true); //  form表单提交
                    if ($type == 2)
                    {
                        $model->save(); // 写入数据到数据库
                    }
                    else
                    {
                        $insert_id = $model->add(); // 写入数据到数据库
                    }
                    $return_arr = array(
                        'status' => 1,
                        'msg'   => '操作成功',
                        'data'  => array('url'=>U('Admin/Goods/specList')),
                    );
                    $this->ajaxReturn(json_encode($return_arr));
                }
            }
           // 点击过来编辑时
           $id = $_GET['id'] ? $_GET['id'] : 0;
           $spec = $model->where("id = $id")->find();
           $cat_list = M('goods_category')->where("parent_id = 0")->getField('id,name,parent_id'); // 已经改成联动菜单
           $this->assign('cat_list',$cat_list);
           $this->assign('spec',$spec);
           $this->display('_spec');
    }


    /**
     * 动态获取商品规格选择框 根据不同的数据返回不同的选择框
     */
    public function ajaxGetSpecSelect(){
        $goods_id = $_GET['goods_id'] ? $_GET['goods_id'] : 0;
        $GoodsLogic = new GoodsLogic();
        //$_GET['spec_type'] =  13;
        $specList = D('Spec')->where("type_id = ".$_GET['spec_type'])->order('`order` desc')->select();
        foreach($specList as $k => $v)
            $specList[$k]['spec_item'] = D('SpecItem')->where("spec_id = ".$v['id'])->getField('id,item'); // 获取规格项

        $items_id = M('SpecGoodsPrice')->where('goods_id = '.$goods_id)->getField("GROUP_CONCAT(`key` SEPARATOR '_') AS items_id");
        $items_ids = explode('_', $items_id);

        // 获取商品规格图片
        if($goods_id)
        {
           $specImageList = M('SpecImage')->where("goods_id = $goods_id")->getField('spec_image_id,src');
        }
        $this->assign('specImageList',$specImageList);

        $this->assign('items_ids',$items_ids);
        $this->assign('specList',$specList);
        $this->display('ajax_spec_select');
    }

    /**
     * 动态获取商品规格输入框 根据不同的数据返回不同的输入框
     */
    public function ajaxGetSpecInput(){
         $GoodsLogic = new GoodsLogic();
         $goods_id = $_REQUEST['goods_id'] ? $_REQUEST['goods_id'] : 0;
         $str = $GoodsLogic->getSpecInput($goods_id ,$_POST['spec_arr']);
         exit($str);
    }

    /**
     * 商品批量操作
     */
    public function act()
    {
        $act = I('post.act', '');
        $goods_ids = I('post.goods_ids');
        $goods_state = I('post.goods_state');
        $reason = I('post.reason');

        $return_success = array('status' => 1, 'msg' => '操作成功', 'data' => '');
        if ($act == 'hot') {
            $hot_condition['goods_id'] = array('in', $goods_ids);
            M('goods')->where($hot_condition)->save(array('is_hot' => 1));
            $this->ajaxReturn($return_success);
        }
        if ($act == 'recommend') {
            $recommend_condition['goods_id'] = array('in', $goods_ids);
            M('goods')->where($recommend_condition)->save(array('is_recommend' => 1));
            $this->ajaxReturn($return_success);
        }
        if ($act == 'new') {
            $new_condition['goods_id'] = array('in', $goods_ids);
            M('goods')->where($new_condition)->save(array('is_new' => 1));
            $this->ajaxReturn($return_success);
        }
        if ($act = 'examine') {
            $goods_array = explode(',', $goods_ids);
            $goods_state_cg = C('goods_state');
            if (!array_key_exists($goods_state, $goods_state_cg)) {
                $return_success = array('status' => -1, 'msg' => '操作失败，商品没有这种属性', 'data' => '');
                $this->ajaxReturn($return_success);
            }
            foreach ($goods_array as $key => $val) {
                $update_goods_state = M('goods')->where("goods_id = $val")->save(array('goods_state' => $goods_state));
                if ($update_goods_state) {
                    $update_goods = M('goods')->where(array('goods_id' => $val))->find();
                    // 给商家发站内消息 告诉商家商品被批量操作
                    $store_msg = array(
                        'store_id' => $update_goods['store_id'],
                        'content' => "您的商品\"{$update_goods[goods_name]}\"被{$goods_state_cg[$goods_state]},原因:{$reason}",
                        'addtime' => time(),
                    );
                    M('store_msg')->add($store_msg);
                }
            }
            $this->ajaxReturn($return_success);
        }
        $return_fail = array('status' => -1, 'msg' => '没有找到该批量操作', 'data' => '');
        $this->ajaxReturn($return_fail);
    }
}
