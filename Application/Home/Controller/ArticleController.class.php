<?php

namespace Home\Controller;
use Home\Logic\ArticleLogic;
use Think\Verify;
use Home\Controller\TperrorController;


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
        if(!$article){
                C('VIEW_PATH','./Template/pc/');
                C('DEFAULT_THEME','yundi');
                $error = new TperrorController();
                $error->tp404();exit;

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
            $location = '<a href="http://'.$_SERVER[HTTP_HOST].'">首页</a>&nbsp;>&nbsp;';
            foreach($this->getlocation($article['cat_id']) as $v){

                $location .= '<a href="'.U('/Article/details',array('cat_id'=>$v['cat_id'])).'">'.$v['cat_name'].'</a>&nbsp;>&nbsp;';
            }
                $location .= '<a href="'.U('/Article/detail_news',array('article_id'=>$article_id)).'">正文</a>';
                // dump($article_id);exit;

            if ($article_id == 26){
                $p['article_id'] = $article_id;
                $p['title'] = '没有了';
                $this->assign('p',$p);

                $n['article_id'] = $article_id;
                $n['title'] = '没有了';
                $this->assign('n',$n);
            }else{
                //上一篇
                $where['article_id']=array('lt',$article['article_id']);
                $where['cat_id'] = $article['cat_id'];
                $p = M('article')->where($where)->find();
                if(!$p){
                $p['article_id'] = $article['article_id'];
                $p['title'] = '没有了';
                }
                $this->assign('p',$p);


                //下一篇
                $where['article_id']=array('gt',$article['article_id']);
                $n = M('article')->where($where)->find();
                if(!$n){
                $n['article_id'] = $article['article_id'];
                $n['title'] = '没有了';
                }
                $this->assign('n',$n);
            }
            $this->assign('location',$location);
            $this->assign('parentss',$parentss);
            $this->assign('parents',$parents['cat_name']);
    		$this->assign('cat_name',$parent['cat_name']);
    		$this->assign('article',$article);
    	}
        $this->display('detail_news');
    }

    public function detail_news(){
        $article_id = I('article_id',1);
        $article = D('article')->where(array('article_id'=>$article_id))->find();
        if(!$article){
            C('VIEW_PATH','./Template/pc/');
            C('DEFAULT_THEME','yundi');
            $error = new TperrorController();
            $error->tp404();exit;

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

            $location = '<a href="http://'.$_SERVER[HTTP_HOST].'">首页</a>&nbsp;>&nbsp;';
            foreach($this->getlocation($article['cat_id']) as $v){

                $location .= '<a href="'.U('/Article/details',array('cat_id'=>$v['cat_id'])).'">'.$v['cat_name'].'</a>&nbsp;>&nbsp;';
            }
                $location .= '<a href="'.U('/Article/detail_news',array('article_id'=>$article_id)).'">正文</a>';
                // dump($article_id);exit;

            if ($article_id == 26){
                $p['article_id'] = $article_id;
                $p['title'] = '没有了';
                $this->assign('p',$p);

                $n['article_id'] = $article_id;
                $n['title'] = '没有了';
                $this->assign('n',$n);
            }else{
                //上一篇
                $where['article_id']=array('lt',$article['article_id']);
                $where['cat_id'] = $article['cat_id'];
                $p = M('article')->where($where)->find();
                if(!$p){
                $p['article_id'] = $article['article_id'];
                $p['title'] = '没有了';
                }
                $this->assign('p',$p);


                //下一篇
                $where['article_id']=array('gt',$article['article_id']);
                $n = M('article')->where($where)->find();
                if(!$n){
                $n['article_id'] = $article['article_id'];
                $n['title'] = '没有了';
                }
                $this->assign('n',$n);
            }



            $this->assign('location',$location);
            $this->assign('parentss',$parentss);
            $this->assign('parents',$parents['cat_name']);
            $this->assign('cat_name',$parent['cat_name']);
            $this->assign('article',$article);
        }
        $this->display();
    }

    public function details()
    {
        $cat_id = I('cat_id',1);
        $count      = M('article')->where(array('cat_id'=>$cat_id))->count();
        $Page       = new \Think\Page($count,10);
        $show       = $Page->show();
        $articleList = M('article')->where(array('cat_id'=>$cat_id))->order('add_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        if ($cat_id == 22) $articleList = M('article')->where(array('article_id'=>26))->select();
        foreach($articleList as &$vv){
            $vv['miaoshu'] = mb_substr(strip_tags(htmlspecialchars_decode($vv['content'])),0,250);
        }
        $article_cat = M('article_cat')->where(array('cat_id'=>$cat_id))->find();
        $this->assign('show',$show);
        $this->assign('article_cat',$article_cat);
        $this->assign('articleList',$articleList);
        $this->display();
    }

    public $location = array();
    public function getlocation($pid){
        if(!(int)$pid){return;}
        $pid_ = M('article_cat')->where(array('cat_id'=>$pid))->find();
        $this->location[] = array('cat_id'=>$pid_['cat_id'],'cat_name'=>$pid_['cat_name']);
        if($pid_['parent_id'] != 0){
            $this->getlocation($pid_['parent_id']);
        }
        sort($this->location);
        return $this->location;
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
        header('location:http://association.yundi88.com');
    }

    public function promote_school(){
        $article = M('article')->where(array('cat_id'=>40,'is_open'=>1))->select();//免费建设网站
        $article2 = M('article')->where(array('cat_id'=>41,'is_open'=>1))->select();//后台优化
        $this->assign('article',$article);
        $this->assign('article2',$article2);
        $this->display();
    }

    public function newsmember(){
        $store_id = I('store_id');
        header('location:http://association.yundi88.com/Home/newsmember?store_id='.$store_id);
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

    //公告栏
    public function noticeList()
    {
        $count      = M('notice')->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出
        $noticeList = M('notice')->order('addtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($noticeList as &$vv){
            $vv['miaoshu'] = mb_substr(strip_tags(htmlspecialchars_decode($vv['text'])),0,250);
        }
        $this->assign('show',$show);
        $this->assign('noticeList',$noticeList);
        $this->display();
    }

    //公告页面
    public function notice(){
        $id = I('id',1);
        $notice = M('notice')->where(array('id'=>$id))->find();
        $where['id'] = array('lt',$id);
        $where2['id'] = array('gt',$id);
        $last = M('notice')->where($where)->order('addtime desc')->find();
        $next = M('notice')->where($where2)->order('addtime')->find();
        $this->assign('last',$last);
        $this->assign('next',$next);
        $this->assign('notice',$notice);
        $this->display();
    }

    public function information_list_details()
    {
        $id = I('id');
        $store_art = M('store_art')->where(array('id'=>$id))->find();
        $rand = $store_art['pc_click']+rand(1,9);
        M('store_art')->where(array('id'=>$id))->save(array('pc_click'=>$rand));
        $store = M('store')->field('store_name,store_id,store_logo,people,store_phone,mobile,email,store_zy')->where(array('store_id'=>$store_art['store']))->find();
        $where['timer'] = array('lt',$store_art['timer']);
        $where['store'] = $store['store_id'];
        $next = M('store_art')->where($where)->order('timer desc')->find();
        $where['timer'] = array('gt',$store_art['timer']);
        $last = M('store_art')->where($where)->order('timer')->find();
        $this->assign('store_art',$store_art);
        $this->assign('last',$last);
        $this->assign('next',$next);
        $this->assign('store',$store);
        $this->display();
    }

    public function information_list()
    {   
        $store_id = I('store_id');
        $count = M('store_art')->where(array('store'=>$store_id))->count();
        $Page       = new \Think\Page($count,16);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $store_art = M('store_art')->where(array('store'=>$store_id))->order('timer desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $store = M('store')->field('store_name,store_id,store_logo,people,store_phone,mobile,email,store_zy')->where(array('store_id'=>$store_id))->find();
        $this->assign('store',$store);
        $this->assign('page',$Page->show());
        $this->assign('store_art',$store_art);
        $this->display();
    }

}
