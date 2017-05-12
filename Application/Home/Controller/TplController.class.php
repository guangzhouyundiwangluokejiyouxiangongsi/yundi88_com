<?php

namespace Home\Controller;
use Think\Controller;
use Think\Page;

class TplController extends Controller
{	

	//前端启动模板
	public function homestarttpl()
	{	
		$id = I('tplid',38);
		$store = M('store');
		$data['tpl'] = M('store','','DB_CONFIG1')->where(array('store_id'=>$id))->getField('tpl');
		if($data['tpl']){
			if(!session('store_id')){
				echo "<script>alert('请先登录！');window.location.href='".U('/User/login')."'</script>";exit;
				// $this->error('请先登录！','/Seller/Admin/login');
			}
			$res = $store->where(array('store_id'=>session('store_id')))->save($data);
			if($res){
				echo "<script>alert('操作成功！');window.location.href='".U('/Seller/Index/index',array('hometpl'=>1))."'</script>";
				// $this->success('操作成功！','Seller/Index/index');
			}else{
				echo "<script>alert('您正在使用当前模板！');window.close();</script>";
				// $this->error('操作失败！');
			}
		}
	}

	/**
	 * 选择模板周飞
	 */
	public function template()
	{


		$self = ($_SERVER['QUERY_STRING'] != '')?$_SERVER['QUERY_STRING']:'t=pc&tpl=fuzhuang&layer=1';
		$tpl = M('store')->where(array('store_id'=>session('store_id')))->getField('tpl');
		$tpl = str_replace('/','',$tpl);
		$curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, "template.yundi88.com/tpl/template.html?".$self);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
		// dump(__SELF__);

        $str = "<script>$('#{$tpl}').css('color','#FFF');$('#{$tpl}').css('background-color','#00a65a');</script>";
        $this->show($data.$str);
		

	}

}