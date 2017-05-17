<?php

namespace Mobile\Controller;
use Think\Page;
use Think\Verify;
use Think\model;
class InfoController extends MobileBaseController
{


    public function index() 
    { 

     $infolist = M('navigation')->where(array('id'=>6))->find();
     $this->assign('infolist',$infolist);
      $this->display(); 
    }

    public function ajaxgetinfo()
    {
        $ranking_m = M('ranking_art');
        $conut = $ranking_m->count();
        $page = new Page($count,10);
        $artid = $ranking_m->limit($page->firstRow,$page->listRows)->order('num desc,commerce_state desc,apply_state desc,id desc')->cache()->select();


        $art_id = '';
        foreach($artid as $v){
            $art_id .= $v['id'].',';
        }

        $articlelist = M('store_art')->where('id in('.substr($art_id,0,-1).')')->cache()->select();
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
        $this->display('ajaxinfo');
    }


	public function search()
	{
        if(!$_GET['p']){$_SERVER['REDIRECT_URL'] = '/'.$_SERVER['PATH_INFO'].'/p/1.html';}
        $info = I('info','');
        $where['a.is_show'] = array('eq',1);
        $where['a.title'] = array('like','%'.$info.'%');
        $model = M();
        $count =  $model->field('a.id,a.store,a.title,a.description,a.newsimg,a.content,a.sn_id,a.timer,s.user_id,s.store_logo')->table('__STORE_ART__ as a')->join('INNER JOIN __STORE__ as s ON a.store = s.store_id')->where(array('a.is_show'=>1))->order('a.id DESC')->count();
        $page = new Page($count,10);
        $articlelist = $model->field('a.id,a.store,a.title,a.description,a.newsimg,a.content,a.sn_id,a.timer,s.user_id,s.store_logo,s.domain,s.commerce_state,s.apply_state,IFNULL(s.commerce_state,0) + IFNULL(s.apply_state,0) as num')
        ->table('__STORE_ART__ as a')
        ->join('INNER JOIN __STORE__ as s ON a.store = s.store_id')
        ->where($where)
        ->order('num desc,s.commerce_state desc,s.apply_state desc,a.id desc')
        ->limit($page->firstRow,$page->listRows)
        ->select();
        $apply_state = M('store_apply')->getField('user_id,apply_state');

        foreach($articlelist as &$v){
            $v['certification'] = $apply_state[ $v['user_id'] ];
            $v['content'] = sp_getcontent_imgs(htmlspecialchars_decode($v['content']));
            if($v['domain']){
            $v['domain2'] = 'http://'.$v['domain'].'/Store/newscontent/text/'.$v['id'].'.html';
            }
        }
        $this->assign('info',$info);
        $this->assign('articlelist',$articlelist);
        $this->assign('page',$page);
        $this->assign('counts',$count);
        $this->assign('count',ceil($count / 10));
        if($_GET['p']){
          $this->display('ajaxgetinfo');
        }else{
          $this->display();
        }

	}

}