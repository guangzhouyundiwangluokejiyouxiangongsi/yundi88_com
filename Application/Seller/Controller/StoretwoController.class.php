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
 * Date: 2016-06-09
 */

namespace Seller\Controller;
use Think\Controller;
class StoretwoController extends Controller {

    
    /*
     * 初始化操作
     */
    public function _initialize() 
    {   
        $this->assign('action',ACTION_NAME);
        //过滤不需要登陆的行为
        if(in_array(ACTION_NAME,array('login','logout','vertify')) || in_array(CONTROLLER_NAME,array('Ueditor','Uploadify'))){ 
        	return;
        }else{
        	if(session('seller_id') > 0 && session('user') != '' && session('store_id') > 0 && session('seller') !=''){
                define('STORE_ID',session('store_id')); //将当前的session_id保存为常量，供其它方法调用
        		// $this->check_priv();//检查管理员菜单操作权限
        	}else{
                echo "<script>top.location.href='http://".$_SERVER['HTTP_HOST']."/User/login.html'</script>";
                exit;
        	}
        }
        $store_art = M('store')->where(array('store_id'=>session('store_id')))->getField('status');
        if ($store_art) $this->redirect('/seller/index/index');
        $this->assign('store_id',session('store_id'));
        $this->public_assign();
    }
    
    public function public_assign()
    {
        
       $tpshop_config = array();
       $tp_config = M('config')->cache(true,TPSHOP_CACHE_TIME)->select();       
       foreach($tp_config as $k => $v)
       {
          if($v['name'] == 'hot_keywords'){
             $tpshop_config['hot_keywords'] = explode('|', $v['value']);
          }           
          $tpshop_config[$v['inc_type'].'_'.$v['name']] = $v['value'];
       }                        
       $goods_category_tree = get_goods_category_tree();    
       $this->cateTrre = $goods_category_tree;
       $this->assign('goods_category_tree', $goods_category_tree);                     
       $brand_list = M('brand')->cache(true,TPSHOP_CACHE_TIME)->field('id,cat_id1,logo,is_hot')->where("cat_id1>0")->select();              
       $this->assign('brand_list', $brand_list);
       $this->assign('tpshop_config', $tpshop_config);

    }  

    public function index()
    {
        
        $this->display();
    }

    public function company_information()
    {
        $store = M('store')->where(array('store_id'=>session('store_id')))->find();
        $store['store_zy'] = explode(',',$store['store_zy']);
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
    
    /*
     *ajax提交修改
     */
    public function company_save()
    {   
        if (IS_AJAX){
            $store = M('store')->where(array('store_id'=>session('store_id')))->find();
            if ($store['store_name'] != $_POST['store_name']){
                $res = M('store')->where(array('store_name'=>$_POST['store_name']))->find();
                if ($res['store_name']){
                    $this->ajaxReturn(array('status'=>-1,'msg'=>'官网名称已存在'));
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
            $_POST['store_zy'] = substr($vv,0,-1);
            unset($_POST['zyy']);
            $data = I('post.');
            $res = M('store')->where(array('store_id'=>session('store_id')))->save($data);
            if ($res){
                $this->ajaxReturn(array('status'=>1,'msg'=>'提交成功'));
            }else{
                $this->ajaxReturn(array('status'=>-1,'msg'=>'提交失败'));
            }
        }else{
            $this->error('页面不存在');
        }
    }

    //立即建站
    public function company_status()
    {   
        $model = M();
        $model->query('update __STORE__ set status = 1 where store_id = '.session('store_id'));
        $this->success('恭喜您建站成功','/seller/index/index');
    }

    public function information_release()
    {   
        $id = I('id');
        $info = M('store_art')->where(array('id'=>$id))->find();
        $this->assign('info',$info);
        $this->display();
    }

    public function add_news()
    {
        if (IS_AJAX){
            $data = I('post.');
            $data['timer'] = time();
            if ($data['id']){
                $res = M('store_art')->where(array('id'=>$data['id']))->save($data);
            }else{
                $res = M('store_art')->add($data);
            }
            $this->ajaxReturn($res);
        }
    }

    public function del_news()
    {
        if (IS_AJAX){
            $id = I('id');
            $res = M('store_art')->where(array('id'=>$id))->delete();
            $this->ajaxReturn($res);
        }
    }
    public function information_management()
    {   
        $count = M('store_art')->where(array('store'=>session('store_id')))->count();
        $Page       = new \Think\Page($count,10);
        $link = M('store_art')->where(array('store'=>session('store_id')))->order('timer desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('page',$Page->show());
        $this->assign('link',$link);
        $this->display();
    }
    
    
}