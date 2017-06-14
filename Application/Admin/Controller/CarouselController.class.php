<?php
	namespace Admin\Controller;

	use Admin\Logic\GoodsLogic;

	use Think\AjaxPage;

	use Think\Page;

	class CarouselController extends BaseController {
		// 添加轮播图
        public function addCarousel()
        {
            if ($_POST) {
                if ($_FILES['link']['name']) {
                    $res = $this->upload($_FILES['link']);
                    if ($res['status']==404) {
                        $this->error($data['msg']);
                    }
                    $data['link'] = $res['msg'];
                } else {
                    $this->error('请上传一张图片');
                }
                $data['url'] = I('post.url');
                $data['order'] = I('post.order');
                $data['addtime'] = time();
                $data['bgcolor'] = I('post.bgcolor');
                $list = M('carousel')->data($data)->add();
                if ($list) {
                    $this->success('成功', U('carousel'));
                } else {
                    $this->error('失败');
                }
            } else {
                $this->display();
            }

        }

        // 显示
        public function carousel()
        {
            $data = M('carousel')->field('id,link,order,url,bgcolor,addtime')->select();
            // dump($data);
            for ($i=0; $i < count($data); $i++) {
                $data[$i]['addtime'] = date('Y-m-d H:i:s', $data[$i]['addtime']);
            }
            $this->assign('data',$data);
            $this->display();
        }

        protected function upload($files)
        {
            $upload = new \Think\Upload();
            $upload->maxSize = 3145728;
            // 设置附件上传类型
            $upload->exts   =   array('jpg', 'gif', 'png', 'jpeg');
            //设置根目录
            $upload->rootPath = './Public/Carousel/';
            // 设置附件上传目录
            $upload->savePath  = '';
            $info   =   $upload->uploadOne($files);
            if (!$info) {
                //上传失败
                return array(
                    'msg' => $upload->getError(),
                    'code' => 404
                );
            } else {
                return array(
                    'msg' => $info['savepath'].$info['savename'],
                    'code' => 200
                );
            }
        }

        // 删除轮播图
        public function delCarousel()
        {
            $id = I('get.id');
            // dump($id);
            $res = M('carousel')->where(array('id' => $id))->delete();
            if ($res) {
                $this->success('成功', U('carousel'));
            } else {
                $this->error('失败');
            }
        }

        // 编辑轮播图
        public function editCarousel()
        {
            if (IS_POST) {
                if ( $_FILES['link']['name'] != '' ) {
                     $fileInfo = $this->upload($_FILES['link']);
                    if ( $fileInfo['code'] ==404 ) {
                        $this->error('上传文件失败');
                        exit;
                    }
                    $data['link']  = $fileInfo['msg'];
                }
                $data['order'] = I('post.order');
                $data['url'] = I('post.url');
                $data['bgcolor'] =I('post.bgcolor');
                $res = M('carousel')->where(array('id'=>I('post.id')))->save($data);
                if ($res) {
                    $this->success('更新成功', U('carousel'));
                    exit;
                } else {
                    $this->error('更新失败，请重试');
                    exit;
                }
            } else {
                $id = I('get.id');
                $res = M('carousel')->where(array('id'=>$id))->find();
                if ($res) {
                    $this->assign('res', $res);
                    $this->display();
                } else {
                    $this->error('非法访问', U('Index/index'));
                }
            }
        }
	}
