<?php
    namespace Admin\Controller;

    class PageController extends BaseController {
        public function floor()
        {
            header('location:'.__APP__.'/Index/yun_commerce/edit/1');
        }

        public function editInfo()
        {
            if (IS_POST) {
                if ( !empty($_FILES['pic']['name']) ) {
                     $fileInfo = $this->upload($_FILES['pic']);
                    if ( $fileInfo['code'] ==404 ) {
                        $this->error('上传文件失败');
                        exit;
                    }
                    $data['pic']  = $fileInfo['msg'];
                }
                $data['title'] = I('post.title');
                $data['des'] = I('post.des');
                $data['align1'] = I('post.align1');
                $data['align2'] = I('post.align2');
                $data['color1'] = I('post.color1');
                $data['color2'] = I('post.color2');
                $data['link'] = U(I('post.link'));
                $result = M('floor')->where(array('id'=>I('post.id')))->save($data);
                if ($result) {
                    $this->success('修改成功', 'floor');
                } else {
                    $this->error('修改失败');
                }
            } else {
                if (I('get.type') == 1) {
                    $res = M('floor')->where(array('id'=>I('get.id')))->find();
                    if ($res) {
                        $this->assign('res', $res);
                        $this->display();
                    } else {
                        $this->assign('id', I('get.id'));
                        $this->display('addInfo');
                    }
                } else {
                    $res = M('slide_pic')->where(array('board'=>I('get.id')))->find();
                    if ($res) {
                        $this->assign('res', $res);
                        $this->display();
                    } else {
                        $this->assign('id', I('get.id'));
                        $this->display('addInfo');
                    }
                }
            }
        }

        public function addInfo()
        {
            if ( !empty($_FILES) ) {
                 $fileInfo = $this->upload($_FILES['pic']);
                if ( $fileInfo['code'] ==404 ) {
                    $this->error('上传文件失败');
                    exit;
                }
                $data['pic']  = $fileInfo['msg'];
            }
            $data['id'] = I('post.id');
            $data['title'] = I('post.title');
            $data['des'] = I('post.des');
            $data['align1'] = I('post.align1');
            $data['align2'] = I('post.align2');
            $data['color1'] = I('post.color1');
            $data['color2'] = I('post.color2');
            $data['link'] = U(I('post.link'));
            $res = M('floor')->add($data);
            if ($res) {
                $this->success('添加成功', 'floor');
            } else {
                $this->error('添加失败');
            }
        }

        public function addPic()
        {
            for ($i=0; $i < 3; $i++) {
                if ( !empty($_FILES) ) {
                     $fileInfo = $this->upload($_FILES['pic'.($i + 1)]);
                    if ( $fileInfo['code'] ==404 ) {
                        $this->error('上传文件失败');
                        exit;
                    }
                    $data['pic']  = $fileInfo['msg'];
                }
                $data['board'] = I('post.id');
                $data['title'] = I('post.title'.($i + 1));
                $data['url'] = U(I('post.url'.($i + 1)));
                $res[] = M('slide_pic')->add($data);
            }
            for ($j=0; $j < count($res); $j++) {
                if (!$res[$j]) {
                    $this->error('添加失败');
                }
            }
            $this->success('添加成功', 'floor');
        }

        public function editPic()
        {
            if (IS_POST) {
                for ($i=0; $i < 3; $i++) {
                    if ( !empty($_FILES['pic'.($i + 1)]['name']) ) {
                         $fileInfo = $this->upload($_FILES['pic'.($i + 1)]);
                        if ( $fileInfo['code'] ==404 ) {
                            $this->error('上传文件失败');
                            exit;
                        }
                        $data['pic']  = $fileInfo['msg'];
                    }
                    $data['title'] = I('post.title'.($i + 1));
                    $data['url'] = U(I('post.url'.($i + 1)));
                    $result[] = M('floor')->where(array('board'=>I('post.id')))->save($data);
                }
                for ($j=0; $j < count($res); $j++) {
                    if (!$res[$j]) {
                        $this->error('修改失败');
                    }
                }
                $this->success('修改成功', 'floor');
            } else {
                $res = M('slide_pic')->where(array('board'=>I('get.id')))->select();
                if ($res) {
                    $this->assign('res', $res);
                    $this->display();
                } else {
                    $this->assign('id', I('get.id'));
                    $this->display('addPic');
                }
            }
        }

        public function upload($file){
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize   =     3145728 ;// 设置附件上传大小
            $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->rootPath  =      './Public/Floor/Upload/'; // 设置附件上传根目录

            // 上传单个文件
            $info   =   $upload->uploadOne($file);

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
