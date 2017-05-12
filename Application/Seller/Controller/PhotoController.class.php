<?php

namespace Seller\Controller;
class PhotoController extends BaseController
{

	public function index()
	{	
		if (IS_POST){
			// $old_pic = $store['photo_banner'];
			$data['photo_banner'] = I('photo_banner','');
			if ($data['photo_banner'] == ''){
				 $this->error('您未选择图片,请重新操作');
				 return;
			}
			$res = M('store')->where(array('store_id'=>session('store_id')))->save($data);
			if ($res){
				$this->success('图片保存成功');
				return;
			} else {
				$this->error('图片保存失败');
				return;
			}
		}
		$store = M('store')->where(array('store_id'=>session('store_id')))->find();
		$photo = M('photo')->where(array('store_id'=>session('store_id')))->select();
		foreach($photo as &$v){
			$v['photoimg'] = M('photoimg')->where(array('photoid'=>$v['id']))->select();
		}
		// dump($photo);
		$this->assign('photo_banner',$store['photo_banner']);
		$this->assign('photo',$photo);
		$this->display();
	}



	public function addphoto()
	{	
		if(IS_POST){
				$id = I('id',0);
				$data['title'] = I('title','');
				$data['store_id'] = session('store_id');
				$data['status'] = I('status',1);
				$data['home'] = I('home',0);
				$data['time'] = time();
				if(!I('title')){$this->error('标题不能为空！！！');}
			if($id){

				$photo = M('photo')->where(array('id'=>$id))->save($data);
				if($_POST['img']){
					foreach($_POST['img'] as &$v){
					$data2[] = array('photoid' => $id,'img'=>$v);
					}
					$res = M('photoimg')->addAll($data2);
				}

				if($res || $photo){

				$this->success('操作成功',U('Photo/index'));
				}else{
					$this->error('操作失败！');
				}

			}else{

				$photo = M('photo')->add($data);
				foreach($_POST['img'] as &$v){
				$data2[] = array('photoid' => $photo,'img'=>$v);
				}
				$res = M('photoimg')->addAll($data2);
				if($photo){

				$this->success('操作成功',U('Photo/index'));
				}else{
					$this->error('操作失败！');
				}

			}
			
		}else{
			$id = I('id',0);
			$photo = M('photo')->where(array('store_id'=>session('store_id'),'id'=>$id))->find();
			$photo['photoimg'] = M('photoimg')->where(array('photoid'=>$id))->select();
			$this->assign('photo',$photo);
			$this->display();
		}
	}




	public function status()
	{
		if(IS_AJAX){

			$val = I('val',0);
			if($val){$v = 0;}else{$v = 1;}
			$data[ I('field') ] = $v;
			$id = I('id',0);
			$res['res'] = M('photo')->where(array('id'=>$id))->save($data);
			$res['msg'] = ($v)?'是':'否';
			$res['status'] = $v;
			$this->ajaxReturn($res);
		}else{
			$this->error('非法操作！');
		}
	}



	public function delete()
	{

		$id = I('id',0);
		$res = M('photo')->where(array('id'=>$id,'store_id'=>session('store_id')))->delete();

		if($res){
			// $img = M('photoimg')->where(array('photoid'=>$id))->getField('img',true);
			// foreach($img as $v){
			// 	@unlink('.'.$v);
			// }
			$res = M('photoimg')->where(array('photoid'=>$id))->delete();

			if($res){
				$this->success('删除成功');
			}else{
				$this->error('删除失败！！！');
			}
		}
	}

	public function delete_banner()
	{
		$data['photo_banner'] = '';
		$res = M('store')->where(array('store_id'=>session('store_id')))->save($data);
		if ($res){
			$this->ajaxReturn(true);
		} else {
			$this->ajaxReturn(false);
		}
	}


	public function deleteimg()
	{
		if(IS_AJAX){
			$imgid = I('imgid',0);
			$pid = I('pid',0);
			$imgurl = I('imgurl');
			$photo = M('photo')->where(array('store_id'=>session('store_id'),'id'=>$pid))->find();
			if($photo){
				$res = M('photoimg')->where(array('id'=>$imgid))->delete();
				if($res){
					// @unlink('.'.$imgurl);
					$this->ajaxReturn($res);
				}else{
					$this->ajaxReturn('删除失败！！！');
				}
			}else{

				$this->ajaxReturn('非法操作1！');
			}
		}else{
			$this->error('非法操作2！');
		}
	}

	public function pho_banner(){
		$store = M('store')->where(array('store_id'=>session('store_id')))->find();
		$this->assign('photo_banner',$store['photo_banner']);
		$this->display();
	}

}