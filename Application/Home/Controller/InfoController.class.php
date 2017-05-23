<?php

namespace Home\Controller;
use Home\Logic\StoreLogic;
use Think\Page;
use Think\Verify;
class InfoController extends BaseController 
{
    


	// public function index()
	// {

 //        if(!$_GET['p']){$_SERVER['REDIRECT_URL'] = '/'.$_SERVER['PATH_INFO'].'/p/1.html';}
	// 	$model = M();
	// 	$count =  $model->field('a.id,a.store,a.title,a.description,a.newsimg,a.content,a.sn_id,a.timer,a.pc_click,s.user_id,s.store_logo')->table('__STORE_ART__ as a')->join('INNER JOIN __STORE__ as s ON a.store = s.store_id')->where(array('a.is_show'=>1,'a.home_is_show'=>1))->order('a.timer DESC')->cache()->count();
	// 	$page = new Page($count,10);
	// 	$articlelist = $model->field('a.id,a.store,a.title,a.description,a.newsimg,a.content,a.sn_id,a.timer,a.pc_click,s.user_id,s.store_logo,s.domain,s.commerce_state,s.apply_state,IFNULL(s.commerce_state,0) + IFNULL(s.apply_state,0) as num')
 //        ->table('__STORE_ART__ as a')
 //        ->join('INNER JOIN __STORE__ as s ON a.store = s.store_id')
 //        ->where(array('a.is_show'=>1,'a.home_is_show'=>1))
 //        ->order('num desc,s.commerce_state desc,s.apply_state desc,a.timer desc')
 //        ->limit($page->firstRow,$page->listRows)
 //        ->cache()->select();

 //            // dump($model);exit;
 //        $apply_state = M('store_apply')->cache()->getField('user_id,apply_state');

 //        foreach($articlelist as &$v){
 //        	$v['certification'] = $apply_state[ $v['user_id'] ];
 //        	$v['content'] = sp_getcontent_imgs(htmlspecialchars_decode($v['content']));
 //            if($v['domain']){
 //        	$v['domain2'] = 'http://'.$v['domain'].'/Store/newscontent/text/'.$v['id'].'.html';
                
 //            }
 //        }
 //        // dump($articlelist);
 //        $this->assign('articlelist',$articlelist);
 //        $this->assign('page',$page);// 赋值分页输出
 //        $this->assign('counts',$count);
 //        $this->assign('count',ceil($count / 10));

	// 	$this->display();
	// }

    public function index()
    {

        $key = md5(__SELF__);
        $html = S($key);
        if(!empty($html))
        {
            exit($html);
        }
        if(!$_GET['p']){$_SERVER['REDIRECT_URL'] = '/'.$_SERVER['PATH_INFO'].'/p/1.html';}
        $ranking_m = M('ranking_art');
        $count = $ranking_m->cache()->count();
        $page = new Page($count,10);
        $artid = $ranking_m->limit($page->firstRow,$page->listRows)->order('num desc,commerce_state desc,apply_state desc,id desc')->cache()->select();

        $art_id = '';
        foreach($artid as $v){
            $art_id .= $v['id'].',';
        }

        $articlelist = M('store_art')->where('id in('.substr($art_id,0,-1).')')->order("field(id,".substr($art_id,0,-1).")")->cache()->select();
        // dump($artid);exit;
        foreach($articlelist as &$v){
            $v['content'] = sp_getcontent_imgs(htmlspecialchars_decode($v['content']));
            foreach($artid as $vv){
                if($v['store'] == $vv['store']){
                    $v['commerce_state'] = $vv['commerce_state'];
                    $v['apply_state'] = $vv['apply_state'];
                }
            }
        }


       
        $this->assign('articlelist',$articlelist);
        $this->assign('page',$page);// 赋值分页输出
        $this->assign('counts',$count);
        $this->assign('count',ceil($count / 10));

        // C('TOKEN_ON',false);
        $html = $this->fetch();
        S($key,$html);
        echo $html;
    }



	public function search()
	{
        if(!$_GET['p']){$_SERVER['REDIRECT_URL'] = '/'.$_SERVER['PATH_INFO'].'/p/1.html';}

		$info = I('info');
		if(!$info){$this->error();}
        $apply_state = M('store_apply')->getField('user_id,apply_state');

		//竞价排名 bidding
		$where = "a.title like '%{$info}%' or a.description like '%{$info}%'";
		$biddinglist = M()->field('a.id,a.store,a.title,a.description,a.newsimg,a.content,a.sn_id,a.timer,a.pc_click,a.bidding,s.user_id,s.domain')->table('__STORE_ART__ as a')->join('INNER JOIN __STORE__ as s ON a.store = s.store_id')->where($where)->order('a.bidding DESC')->limit(5)
            ->select();
        foreach($biddinglist as $vvv){
             if((int)$vvv['bidding'] > 0){
                $biddinglist2[] = $vvv;
            }
        }
        foreach($biddinglist2 as &$v){
        	$v['certification'] = $apply_state[ $v['user_id'] ];
        	$v['content'] = sp_getcontent_imgs(htmlspecialchars_decode($v['content']));
             if($v['domain']){
            $v['domain2'] = 'http://'.$v['domain'].'/Store/newscontent/text/'.$v['id'].'.html';
                
            }
           
        }
        $this->assign('biddinglist',$biddinglist2);

		$map = "a.title like '%{$info}%' or a.description like '%{$info}%' and a.is_show = 1";
		$count =  M()
        ->field('a.id,a.store,a.title,a.description,a.newsimg,a.content,a.sn_id,a.timer,s.user_id,a.pc_click,s.store_logo
			')->table('__STORE_ART__ as a')->join('INNER JOIN __STORE__ as s ON a.store = s.store_id')->where($map)->order('a.timer DESC')->count();
		$page = new Page($count,10);
		$articlelist = M()->field('a.id,a.store,a.title,a.description,a.newsimg,a.content,a.sn_id,a.timer,a.pc_click,s.user_id,s.domain,s.commerce_state,s.apply_state,IFNULL(s.commerce_state,0) + IFNULL(s.apply_state,0) as num')->table('__STORE_ART__ as a')->join('INNER JOIN __STORE__ as s ON a.store = s.store_id')->where($map)->order('num desc,s.commerce_state desc,s.apply_state desc,a.timer desc')->limit($page->firstRow,$page->listRows)
            ->select();

        foreach($articlelist as &$v){
        	$v['certification'] = $apply_state[ $v['user_id'] ];
            $v['content'] = sp_getcontent_imgs(htmlspecialchars_decode($v['content']));
            if($v['domain']){
            $v['domain2'] = 'http://'.$v['domain'].'/Store/newscontent/text/'.$v['id'].'.html';
                
            }
        }
        // DUMP($articlelist);EXIT;

        //优质商家
        $sql = "select * from __STORE__ where store_zy like '%{$info}%'  and store_state = 1";
        $quality = M()->query($sql);
        foreach($quality as &$vc){
            $vc['store_zy2'] = str_replace($info, "<zhoufei>{$info}</zhoufei>", $vc['store_zy']);
        }
        $this->assign('quality',$quality);

        $this->assign('articlelist',$articlelist);
        $this->assign('page',$page);// 赋值分页输出
        $this->assign('count',ceil($count / 10));
        $this->assign('counts',$count);
        $this->display();
	}

}