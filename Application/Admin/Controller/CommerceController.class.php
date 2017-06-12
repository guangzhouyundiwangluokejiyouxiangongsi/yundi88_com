<?php
	namespace Admin\Controller;

	use Admin\Logic\GoodsLogic;

	use Think\AjaxPage;

	use Think\Page;

	class CommerceController extends BaseController {

		public function notice()
		{
			$data = M('notice')->field('id, content, addtime')->select();
			for ($i=0; $i < count($data); $i++) {
				$data[$i]['addtime'] = date('Y-m-d H:i:s', $data[$i]['addtime']);
			}
			$this->assign('data', $data);
			$this->display();
		}

		// 添加公告
		public function notice_add()
		{
			$this->display();
		}

		public function addData()
		{
			$data['content'] = I('post.content');
			$data['addtime'] = time();
			$res = M('notice')->data($data)->add();
			if ($res) {
				$this->success('成功', 'notice');
			} else {
				$this->error('失败');
			}
		}

		// 修改公告
		public function notice_edit()
		{
            if(IS_POST){
                $cont = M('notice')->field('content')->where(array('id'=>I('post.id')))->find();
                if ($cont['content'] == I('post.content')) {
                    $this->success('修改成功', 'notice');
                    exit;
                }
                $data['content'] = I('post.content');
                $result = M('notice')->where(array('id'=>I('post.id')))->data($data)->save();
                if($result){
                    $this->success('修改成功', 'notice');exit;
                }else{
                    $this->error('修改失败');
                }
            }

            $id = I('get.id');
            $res = M('notice')->field('id,content')->where(array('id'=>$id))->find();
            $this->assign('id', $id);
            $this->assign('res', $res);
	        $this->display();
		}
		// 删除公告
		public function notice_del()
		{
			$id = I('get.id');
			$res = M('notice')->where(array('id'=>$id))->delete();
            if ($res) {
                $this->success('删除成功', 'notice');exit;
            } else {
                $this->error('删除失败');
            }
		}


	}
