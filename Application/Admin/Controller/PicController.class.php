<?php
	namespace Admin\Controller;

	use Admin\Logic\GoodsLogic;

	use Think\AjaxPage;

	use Think\Page;

	class PicController extends BaseController {
		public function pic()
		{
            $list = D('pic')->findPic();
            // 进行图片的URL处理
            if (!$list) {
                $list['link'] = '/Admin/img/a1.jpg';
            }
            $this->assign('list', $list);
            $data = D('pic')->selectPic();
            $this->assign('data', $data);
            $this->display('Commerce/pic');
		}

		public function changePic()
        {
            $id = I('post.id');
            $obj = D('pic');
            $res = $obj->chaPic($id);
            if ($res) {
                echo 1;
            } else {
                echo 0;
            }
        }

        public function upImg()
        {
            if (IS_POST) {
                if ( !empty($_FILES) ) {
                     $fileInfo = $this->upload($_FILES['path']);
                    if ( $fileInfo['code'] ==404 ) {
                        // dump($fileInfo);
                        $this->error('上传文件失败');
                        exit;
                    }
                    $data['link']  = $fileInfo['msg'];
                }
                $res = D('pic')->addPic($data);
                if ($res) {
                    header('location:'.$_SERVER["HTTP_REFERER"]);
                }
            }
        }

        protected function upload($files)
        {
            $upload = new \Think\Upload();
            $upload->maxSize = 3145728;
            // 设置附件上传类型
            $upload->exts   =   array('jpg', 'gif', 'png', 'jpeg', 'JPG');
            //设置根目录
            $upload->rootPath = './Public/Pic/';
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
	}
