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

            $location = '<a href="http://'.$_SERVER[HTTP_HOST].'">首页</a>&nbsp;>&nbsp;';
            foreach($this->getlocation($article['cat_id']) as $v){

                $location .= '<a href="">'.$v['cat_name'].'</a>&nbsp;>&nbsp;';
            }
                $location .= '<a >正文</a>';

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



            $this->assign('location',$location);
            $this->assign('parentss',$parentss);
            $this->assign('parents',$parents['cat_name']);
            $this->assign('cat_name',$parent['cat_name']);
            $this->assign('article',$article);
        }
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

}