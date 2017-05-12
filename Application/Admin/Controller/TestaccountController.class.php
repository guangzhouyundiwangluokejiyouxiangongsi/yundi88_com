<?php

namespace Admin\Controller;
use Admin\Model;
use Think\AjaxPage;
use Think\Page;

class TestaccountController extends BaseController 
{
    public function index()
    {
    	$shouji = M('shouji');
    	$data = $shouji->select();
    	$this->assign('data',$data);
        $this->display();
    }


    public function add()
    {
    	if(IS_POST){
    		$data['userid'] = I('userid');
    		if(!I('userid')){$this->error('不能为空！');}
    		$res = M('shouji')->add($data);
    		if($res){
    			$this->success('添加成功');
    		}else{
    			$this->error('添加失败！');
    		}
    	}else{


    	}
    }
    
   
}