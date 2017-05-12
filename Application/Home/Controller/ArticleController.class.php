<?php
 
namespace Home\Controller;
use Home\Logic\ArticleLogic;
use Think\Verify;


class ArticleController extends BaseController {
    
    public function index(){       
        $article_id = I('article_id',38);
    	$article = D('article')->where("article_id=$article_id")->find();
    	$this->assign('article',$article);
        $this->display();
    }

    //商会加入多少商户
   
    public function promote()
    {

        if(IS_AJAX){//前端请求
            $num = M('config')->where(array('id'=>88))->getField('value');
            $this->ajaxReturn($num);

        }elseif(IS_POST){//操作系统增加
        $num = rand(1,3);
          M('config')->where(array('id'=>88))->setInc('value',$num);  exit;

        }else{
            $num = M('config')->where(array('id'=>88))->getField('value');
            $this->assign('num',$num);
            $this->display();
            
        }
    }
 
    /**
     * 文章内列表页
     */
    public function articleList(){
        $article_cat = M('ArticleCat')->where("parent_id  = 0")->select();
        $this->assign('article_cat',$article_cat);        
        $this->display();
    }    
    /**
     * 文章内容页
     */
    public function detail(){
    	$article_id = I('article_id',1);
    	$article = D('article')->where(array('article_id'=>$article_id))->find();
        if ($article_id == 228 || $article_id ==229){
            $this->redirect('/index.php/article/promote_school');
            exit;
        }
        if ($article['cat_id'] == 40){
            $this->redirect('/article/lists1/id/lists1.html?id='.$article_id.'#xiangxixingxi');
            exit;
        }
        if ($article['cat_id'] == 41){
            $this->redirect('/article/lists1/id/lists1.html/id/listsht1.html?id='.$article_id.'#xiangxixingxi');
            exit;
        }
        if ($article['cat_id'] == 32 || $article['cat_id'] == 33){
            $this->redirect('/Article/detail_news/article_id/'.$article_id.'.html');
            exit;
        }
        if ($article['cat_id'] == 34 || $article['cat_id'] == 35 || $article['cat_id'] == 36){
            $this->redirect('/article/news?new_id='.$article_id);
            exit;
        }
        if ($article['cat_id'] == 38){
            $this->redirect('/article/yun_shop');
            exit;
        }
    	if($article){
            $data['link_num'] = $article['link_num']+1;
            M('article')->where(array('article_id'=>$article_id))->save($data);
    		$parent = D('article_cat')->where("cat_id=".$article['cat_id'])->find();
            $map['cat_id'] = $parent['cat_id'];
            $map['is_open'] = 1;
            $parentss = D('article')->where($map)->order('add_time desc')->limit(15)->select();
            if($parent['parent_id'] != 0){
                $parents = D('article_cat')->where("cat_id=".$parent['parent_id'])->find();
            }
            $this->assign('parentss',$parentss);
            $this->assign('parents',$parents['cat_name']);
    		$this->assign('cat_name',$parent['cat_name']);
    		$this->assign('article',$article);
    	}
        $this->display();
    } 

    public function detail_news(){
        $article_id = I('article_id',1);
        $article = D('article')->where(array('article_id'=>$article_id))->find();
        if($article){
            if ($article['cat_id'] == 32 || $article['cat_id'] == 33){
                $data['link_num'] = $article['link_num']+1;
                M('article')->where(array('article_id'=>$article_id))->save($data);
            }
            
            $parent = D('article_cat')->where("cat_id=".$article['cat_id'])->find();
            $map['cat_id'] = $parent['cat_id'];
            $map['is_open'] = 1;
            $parentss = D('article')->where($map)->order('add_time desc')->limit(15)->select();
            if($parent['parent_id'] != 0){
                $parents = D('article_cat')->where("cat_id=".$parent['parent_id'])->find();
            }
            $this->assign('parentss',$parentss);
            $this->assign('parents',$parents['cat_name']);
            $this->assign('cat_name',$parent['cat_name']);
            $this->assign('article',$article);
        }
        $this->display();
    } 
    

   // 用户体验
    public function experience()
    {   
        if ($_POST) {
            $data = I('post.');
            $data['addtime'] = time();
            $yzm = $data['yzm'];
            $verify = new \Think\Verify();
            if (!$verify->check($yzm))
            {
                echo '<script>alert("验证码错误");javascript:history.go(-1);</script>';
                exit;
            }
            $res = M('experience')->add($data);
            if ($res) {
                $this->display();
                echo "<script>success();</script>";
                return;
            } else {
                echo '<script>alert("提交失败，请您刷新页面重新提交");javascript:history.go(-1);</script>';
                return;
            }
        }
        $lunbo = M('lunbo')->where(array('id'=>'1'))->Field('tiyan_lunbo,tiyan_jump')->find();
        $lunbo['tiyan_lunbo'] = explode(',', $lunbo['tiyan_lunbo']);
        $lunbo['tiyan_jump'] = explode(',', $lunbo['tiyan_jump']);
        $copyright = M('config')->where(array('id'=>80))->getField('value');
        $record_no = M('config')->where(array('id'=>2))->getField('value');
        $phone = M('config')->where(array('id'=>9))->getField('value');
        $this->assign('phone',$phone);
        $this->assign('copyright',$copyright);
        $this->assign('record_no',$record_no);
        $this->assign('lunbo',$lunbo);
        $this->display();

    }

     
    public function yzm()
    {
        $Verify = new \Think\Verify();
        $Verify->fontSize = 30;
        $Verify->length   = 4;
        $Verify->useNoise = false;
        $Verify->entry();
    }

    public function yun_shop()
    {   
        $store_apply = M('store_apply')->where(array('apply_state'=>1))->order('add_time desc')->limit(11)->getField('id,user_id');
        foreach ($store_apply as $key => $val) {
            $store_logo[] = M('store')->where(array('user_id'=>$val))->getField('store_id');
        }
        // dump($store_logo);
        $shop_link = M('shop_link')->where(array('is_show'=>1))->order('orderby')->select();
        $this->assign('shop_link',$shop_link);
        $this->assign('store_logo',$store_logo);
        $this->display();
    }

    public function news(){
        $article_id = I('new_id',0); 
        if (!$article_id){
            $article = M('article')->where(array('cat_id'=>35,'is_open'=>1))->order('add_time desc')->limit(1)->find();
        } else {
            $article = M('article')->where(array('article_id'=>$article_id,'is_open'=>1))->find();
        }
        if (!$article) $this->error('文章不存在');
        $publish_time = ceil((time()-$article['publish_time'])/60);
        if ($publish_time < 0) {
            $data['publish_time'] = time();
            M('article')->where(array('article_id'=>$article_id))->save($data);
            $article['publish_time'] = '刚刚';
        } else {
            if ($publish_time > 1440){
                $article['publish_time'] = floor((time()-$article['publish_time'])/(60*60*24)).'天前';
            } else if ($publish_time > 60){
                    $article['publish_time'] = floor($publish_time/60).'小时前';
            } else {
                $article['publish_time'] = $publish_time.'分钟前';
            }
        }
        $data['link_num'] = $article['link_num']+1;
        M('article')->where(array('article_id'=>$article['article_id']))->save($data);
        $article2 = M('article')->where(array('cat_id'=>$article['cat_id'],'is_open'=>1))->select();
        foreach ($article2 as $key => $val) {
            if ($val['article_id'] == $article['article_id']){
                $last = $article2[$key-1];
                $next = $article2[$key+1];
            }
        }
        $this->assign('last',$last);
        $this->assign('next',$next);
        $this->assign('article2',$article2);
        $this->assign('article',$article);
        $this->display();
    }

    public function elegant(){
        $article_id = I('new_id',0); 
        if (!$article_id){
           $article = M('article')->where(array('cat_id'=>37,'is_open'=>1))->order('add_time desc')->limit(1)->find();
        } else {
            $article = M('article')->where(array('article_id'=>$article_id,'is_open'=>1))->find();
        }
        if (!$article) $this->error('文章不存在');
        $publish_time = ceil((time()-$article['publish_time'])/60);
        if ($publish_time < 0) {
            $data['publish_time'] = time();
            M('article')->where(array('article_id'=>$article_id))->save($data);
            $article['publish_time'] = '刚刚';
        } else {
            if ($publish_time > 1440){
                $article['publish_time'] = floor((time()-$article['publish_time'])/(60*60*24)).'天前';
            } else if ($publish_time > 60){
                    $article['publish_time'] = floor($publish_time/60).'小时前';
            } else {
                $article['publish_time'] = $publish_time.'分钟前';
            }
        }
        $data['link_num'] = $article['link_num']+1;
        M('article')->where(array('article_id'=>$article['article_id']))->save($data);
        $article2 = M('article')->where(array('cat_id'=>$article['cat_id'],'is_open'=>1))->select();
        foreach ($article2 as $key => $val) {
            if ($val['article_id'] == $article['article_id']){
                $last = $article2[$key-1];
                $next = $article2[$key+1];
            }
        }
        $this->assign('last',$last);
        $this->assign('next',$next);
        $this->assign('article2',$article2);
        $this->assign('article',$article);
        $this->display();
    }

    public function gonggao(){
        $article = M('article'); // 实例化User对象
        $count      = $article->where(array('cat_id'=>36,'is_open'=>1))->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出
         $gonggao = M('article')->where(array('cat_id'=>36,'is_open'=>1))->order('add_time desc')->limit($Page->firstRow.','.$Page->listRows)->select(); //公告
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('gonggao',$gonggao);
        $this->display();
    }

    //最新资讯
    public function demeanour(){
        $article = M('article'); // 实例化User对象
        $count      = $article->where(array('cat_id'=>34,'is_open'=>1))->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出
        $demeanour = M('article')->where(array('cat_id'=>34,'is_open'=>1))->order('add_time desc')->limit($Page->firstRow.','.$Page->listRows)->select(); 
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('demeanour',$demeanour);
        $this->display();
    }

    //最新动态
    public function demeanourmoll(){
        $article = M('article'); // 实例化User对象
        $count      = $article->where(array('cat_id'=>35,'is_open'=>1))->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出
        $demeanourmoll = M('article')->where(array('cat_id'=>35,'is_open'=>1))->order('add_time desc')->limit($Page->firstRow.','.$Page->listRows)->select(); //公告
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('demeanourmoll',$demeanourmoll);
        $this->display();
    }

    public function newsmember(){
        $store_id = I('store_id');
        if (!$store_id) $this->error('您访问的会员不存在');
        $store = M('store')->where(array('store_id'=>$store_id))->find();
        if (!$store) $this->error('您访问的会员不存在');
        $this->assign('store',$store);
        $this->display();
    }

    public function addnbsp(){
        $a = S(array(    'type'=>'memcache',    'host'=>'47.90.81.73',    'port'=>'13689',    'prefix'=>'',    'expire'=>60));
        $ab = S('aaa','123');
        dump($ab);
        exit;
    }

    public function promote_school(){
        $article = M('article')->where(array('cat_id'=>40,'is_open'=>1))->select();//免费建设网站
        $article2 = M('article')->where(array('cat_id'=>41,'is_open'=>1))->select();//后台优化
        $this->assign('article',$article);
        $this->assign('article2',$article2);
        $this->display();
    }

    public function lists1(){
        $article_id = I('id');
        if (!$article_id) $this->error('文章不存在');
        $article = M('article')->where(array('article_id'=>$article_id,'is_open'=>1))->find();
        $data['link_num'] = $article['link_num']+1;
        M('article')->where(array('article_id'=>$article_id))->save($data);
        $article_cat = M('article')->where(array('cat_id'=>$article['cat_id'],'is_open'=>1))->select();
        foreach($article_cat as $key=>$val){
            if ($val['article_id'] == $article['article_id']){
                $last = $article_cat[$key-1];
                $next = $article_cat[$key+1];
                $this->assign('last',$last);
                $this->assign('next',$next);
            }
        }
        $this->assign('article',$article);
        $this->display();
    }

    public function listsht1(){
        $article_id = I('id');
        if (!$article_id) $this->error('文章不存在');
        $article = M('article')->where(array('article_id'=>$article_id,'is_open'=>1))->find();
        $data['link_num'] = $article['link_num']+1;
        M('article')->where(array('article_id'=>$article_id))->save($data);
        $article_cat = M('article')->where(array('cat_id'=>$article['cat_id'],'is_open'=>1))->select();
        foreach($article_cat as $key=>$val){
            if ($val['article_id'] == $article['article_id']){
                $last = $article_cat[$key-1];
                $next = $article_cat[$key+1];
                $this->assign('last',$last);
                $this->assign('next',$next);
            }
        }
        $this->assign('article',$article);
        $this->display();
    }

    public function tuiguang_common(){
        $article = M('article')->where(array('cat_id'=>40,'is_open'=>1))->select();//免费建设网站
        $article2 = M('article')->where(array('cat_id'=>41,'is_open'=>1))->select();//后台优化
        $this->assign('article',$article);
        $this->assign('article2',$article2);
        $this->display();
    }

}