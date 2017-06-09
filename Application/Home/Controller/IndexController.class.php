<?php
/**
 * tpshop
 * ============================================================================
 * * 版权所有 2015-2027 深圳搜豹网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tp-shop.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: IT宇宙人 2015-08-10 $
 */ 
namespace Home\Controller;
use Home\Logic\StoreLogic;
use Think\Page;
use Think\Verify;
class IndexController extends BaseController {
    
    /* 
    public function test(){
        $goods_id_arr = M('goods')->where("goods_id >= 140")->getField('goods_id',true);
        foreach($goods_id_arr as $key => $val)
        {
            $goods_image_url = M('goods_images')->where("goods_id = $val")->getField('image_url',3);
            
            $data = array(                    
                    'goods_id'=>$val,
                    'email'=>'www.99soubao.com',
                    'username'=>'淑女',
                    'content'=>'买回来用了一段时间, 真心感觉bucuo  ....',
                    'deliver_rank'=>rand(0, 5),
                    'add_time'=>time(),
                    'ip_address'=>'127.0.0.1',
                    'is_show'=>1,
                    'parent_id'=>0,
                    'user_id'=>1,
                    //'img'=>serialize($goods_image_url),
                    'order_id'=>1,
                    'goods_rank'=>rand(0, 5),
                    'service_rank'=>rand(0, 5),
            );
            M('comment')->add($data);
            
           // M('goods_consult')->execute("insert into tp_goods_consult (goods_id,username,add_time,consult_type,content,parent_id) (
//select $val,username,add_time,consult_type,content,parent_id from `tp_goods_consult`  where goods_id = 95)");
            //M('comment')->where("goods_id = $val")->save(array('img'=>serialize($goods_image_url)));
        }
    }
    */ 

    public function certification()
    {
        $store_id = I('store_id');
        $user_id = M('store')->where(array('store_id'=>$store_id))->getField('user_id');
        if($user_id){
            $res = M('store_apply')->where(array('user_id'=>$user_id))->find();
        }
        if($res && $res['apply_state'] == 1){
            $this->assign('res',$res);

            //查询产品
            $sql = "select * from __GOODS__ inner join __STORE__ ON __STORE__.store_id = __GOODS__.store_id where __GOODS__.store_id = {$store_id} and __GOODS__.goods_state = 1 order by goods_id desc limit 5";

            $goods = M()->query($sql);
             $apply_state = M('store_apply')->getField('user_id,apply_state');
                    foreach($goods as &$vv){
                        $a = array_values(unserialize($vv['store_presales']));
                        foreach($a as $k=>$v){
                            if($v['type'] == 'qq'){
                                $data_['qq'][] = $v['account'];
                            }elseif($v['type'] == 'ww'){
                                $data_['ww'][] = $v['account'];
                            }
                        }
                            $vv['store_presales'] = $data_;
                            unset($data_);

                   
                   }
            $this->assign('apply_state',$apply_state);
            $this->assign('goods',$goods);


            if($res['apply_type'] == 0){


                $this->display('company');


            }elseif($res['apply_type'] == 1){

              
                $this->display('user');
            }else{
               $this->display(); 
            }
        }else{

            $this->display();
        }
       
    }

    public function sethome()
    {
        $this->display();
    }


    
    public function index(){
        //$aa = order_settlement(316);
        //print_r($aa);
        // 如果是手机跳转到 手机模块
        if(true == isMobile()){
            header("Location: ".U('Mobile/Index/index'));
        }
        
        $hot_goods = $hot_cate = $cateList = array();
        $sql = "select a.goods_name,a.goods_id,a.shop_price,a.market_price,a.cat_id1,b.parent_id_path,b.name from __PREFIX__goods as a left join ";
        $sql .= " __PREFIX__goods_category as b on a.cat_id1=b.id where a.is_hot=1 and a.is_on_sale=1 and a.goods_state = 1 order by rand() limit 0,10";//二级分类下热卖商品       
        $index_hot_goods = M()->query($sql);//首页热卖商品
		if($index_hot_goods){
			foreach($index_hot_goods as $val){
				$cat_path = explode('_', $val['parent_id_path']);
				$hot_goods[$cat_path[1]][] = $val;
			}
		}
        $record_no = M('config')->where(array('name' => 'record_no'))->find();
        $copyright = M('config')->where(array('name'=> 'copyright'))->find();
        $article = M('article')->where('cat_id = 27 and is_open = 1')->order('add_time DESC')->limit(2)->select();
        //文章
        // dump($article);exit;
        $this->assign('record_no',$record_no);
        $this->assign('copyright',$copyright);
        $this->assign('cateList',$index_hot_goods); 
        $this->assign('article',$article);
        $this->display();
    }
 
    /**
     *  公告详情页
     */
    public function notice(){
        $this->display();
    }
    
    // 二维码
    public function qr_code(){        
        // 导入Vendor类库包 Library/Vendor/Zend/Server.class.php
        //http://www.tp-shop.cn/Home/Index/erweima/data/www.99soubao.com
         require_once 'ThinkPHP/Library/Vendor/phpqrcode/phpqrcode.php';
          //import('Vendor.phpqrcode.phpqrcode');
            error_reporting(E_ERROR);            
            $url = urldecode($_GET["data"]);
            \QRcode::png($url);          
    }
    
    // 验证码
    public function verify()
    {
        //验证码类型
        $type = I('get.type') ? I('get.type') : '';
        $fontSize = I('get.fontSize') ? I('get.fontSize') : '40';
        $length = I('get.length') ? I('get.length') : '4';
        
        $config = array(
            'fontSize' => $fontSize,
            'length' => $length,
            'useCurve' => true,
            'useNoise' => false,
        );
        $Verify = new Verify($config);
        $Verify->entry($type);        
    }
    
    // 促销活动页面
    public function promoteList()
    {                          
        $model = M('');
        $db_prefix = C('DB_PREFIX');
        $goods_where['start_time']  = array('lt',time());
        $goods_where['end_time']  = array('gt',time());
        $goods_where['status']  = 1;
        $goods_where['is_end']  = 0;
        $goodsList = $model
            ->table($db_prefix . 'goods g')
            ->join('INNER JOIN ' . $db_prefix . 'flash_sale AS f ON g.goods_id = f.goods_id')
            ->where($goods_where)
            ->select();
        $brandList = M('brand')->getField("id,name,logo");
        $this->assign('brandList',$brandList);
        $this->assign('goodsList',$goodsList);
        $this->display();
    }

    /**
     * 店铺街
     * @author dyr
     * @time 2016/08/26
     */
    public function street()
    {
        $sc_id = I('get.sc_id');
        $store_class = M('store_class')->field('sc_id,sc_name')->where('')->select();
        $store_logic = new StoreLogic();

        $store_list = $store_logic->getStoreList($sc_id,10);
        foreach ($store_list['result'] as &$v) {
                $v['store_presales'] = unserialize($v['store_presales']);
                $v['store_aftersales'] = unserialize($v['store_aftersales']);
                if(!$v['domain']){
                    $v['domain'] = U('/Store/index',array('store_id'=>$v['store_id']));$v['nofollow'] = '';
                }else{
                    $v['nofollow'] = 'nofollow';
                    $v['domain'] = 'http://'.$v['domain'];

                }
        }
        $this->assign('page', $store_list['show']);// 赋值分页输出
        $this->assign('count', $store_list['num']);// 页数
        $this->assign('store_list', $store_list['result']);
        $this->assign('store_class', $store_class);//店铺分类
        $this->display();
    }


    //公司搜索
    public function searchstreet(){
        $company = I('company');
        if(!$company){$this->error();}
        $m = M();
        $store_id = M('store')->where("store_name like '%{$company}%'")->getField('store_id',true);
        if($store_id){
            $p = I('p',1);
            if ($p == 1) search($company,1,1,0);

             $store_logic = new StoreLogic();
            $store_list = $store_logic->getStoreList2($store_id);
            foreach ($store_list['result'] as &$v) {
                    $v['store_presales'] = unserialize($v['store_presales']);
            }
            $this->assign('page', $store_list['show']);// 赋值分页输出
            $this->assign('count', $store_list['num']);// 页数
            $this->assign('store_list', $store_list['result']);
            $this->assign('store_class', $store_class);//店铺分类
        }

        $this->display('street');

    }
 public function ajaxdata()
    {
      if(IS_AJAX){
        $name = trim(I('val'));
        $data = M('store')->where("store_name like '%{$name}%'")->getField('store_id,store_name');
        if($data){
            $this->ajaxReturn($data);
            
        }else{
            $data[0] = '';
            $this->ajaxReturn($data);
        }
      }else{


      }
    }
    
    public function store_qrcode(){
    	require_once 'ThinkPHP/Library/Vendor/phpqrcode/phpqrcode.php';
    	error_reporting(E_ERROR);
    	$store_id = I('store_id',1);
    	\QRcode::png(U('Store/index',array('store_id'=>$store_id)));
    }
    
    function truncate_tables (){
        $model = new \Think\Model(); // 实例化一个model对象 没有对应任何数据表
        $tables = $model->query("show tables");
        //print_r($tables);
        $table = array('tp_admin','tp_config','tp_region','tp_system_module','tp_admin_role','tp_system_menu','tp_store_grade','tp_article_cat');
        foreach($tables as $key => $val)
        {                                    
           // if(!in_array($val['tables_in_tpshop_bbc'], $table))                             
             //   echo "truncate table ".$val['tables_in_tpshop_bbc'].' ; ';
             //   echo "<br/>";         
        }                
    }    

    public function certification2()
    {
        if (session('user')) redirect('/seller/index/index');
        $renzheng_lunbo = M('lunbo')->where(array('id'=>'1'))->Field('renzheng_lunbo,renzheng_jump')->find();
        $renzheng_lunbo['renzheng_lunbo'] = explode(',', $renzheng_lunbo['renzheng_lunbo']);
        $renzheng_lunbo['renzheng_jump'] = explode(',', $renzheng_lunbo['renzheng_jump']);
        $this->assign('renzheng_lunbo',$renzheng_lunbo);
        $this->display();
    }

    public function index_nav()
    {   
        $nav = M('article')->getField('article_id,title,link');
        foreach ($nav as $key => $val) {
           if ($val['article_id'] == 143) $arr[0] = $val;
           if ($val['article_id'] == 144) $arr[1] = $val;
           if ($val['article_id'] == 142) $arr[2] = $val;
           if ($val['article_id'] == 146) $arr[3] = $val;
           if ($val['article_id'] == 149) $arr[4] = $val;
           if ($val['article_id'] == 145) $arr[5] = $val;
           if ($val['article_id'] == 34) $arr[6] = $val;
           if ($val['article_id'] == 36) $arr[7] = $val;
           if ($val['article_id'] == 54) $arr[8] = $val;
        }
        
        $this->assign('arr',$arr);
        $this->display();
    }


//     public function sendMail($to, $title, $content) {
//         //导入vender\PHPMailer\classphpmailer.php
//         //注意：用vender函数导入的是.php的文件！！！！
//         Vendor('PHPMailer2.classphpmailer');

//         $mail = new \PHPMailer(); /*实例化*/
//         $mail->IsSMTP(); /*启用SMTP*/
//         $mail->Host         =   'smtp.163.com'; /*smtp服务器的名称*/
//         $mail->SMTPDebug    =   TRUE; /*开启调试模式，显示信息*/
//         $mail->SMTPAuth     =   TRUE; /*启用smtp认证*/
//         $mail->Username     =   'vzhoufei@163.com'; /*你的邮箱名*/
//         $mail->Password     =   '12456' ; /*邮箱密码*/
//         $mail->From         =   'vzhoufei@163.com'; 发件人地址（也就是你的邮箱地址）
//         $mail->FromName     =   '广州云狄网络科技有限公司'; /*发件人姓名*/
//         $mail->AddAddress($to);
//         $mail->WordWrap     =   50; /*设置每行字符长度*/
//         $mail->IsHTML(TRUE); /* 是否HTML格式邮件*/
//         $mail->CharSet      =   'utf-8'; /*设置邮件编码*/
//         $mail->Subject      =   $title; /*邮件主题*/
//         $mail->Body         =   $content; /*邮件内容*/
//         $mail->AltBody      =   "This is the body in plain text for non-HTML mail clients"; /*邮件正文不支持HTML的备用显示*/
//         return $mail->Send();
//     }


// public function test(){

//     $this->sendMail('vzhoufei@qq.com','云狄建站提醒您，您有新的留言。','ssssssssssssss');
// }
    public function logout(){
        session(null);
        cookie('referurl',null);
    }

}