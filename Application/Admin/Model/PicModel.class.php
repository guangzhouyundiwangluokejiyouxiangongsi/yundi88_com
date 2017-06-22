<?php

    namespace Admin\Model;

    use Think\Model;

    class PicModel extends Model
    {
        public function findPic()
        {
            $list = $this->field('id,link')->where(array('status'=>'1'))->find();
            return $list;
        }

        public function selectPic()
        {
            $list = $this->field('id,link')->where(array('status'=>'0'))->order('addtime desc')->limit(3)->select();
            return $list;
        }

        public function addPic($data)
        {
            $this->editSta();
            $data['addtime'] = time();
            $data['status'] = 1;
            $res = $this->add($data);
            if ($res) {
                return ture;
            } else {
                return false;
            }
        }

        public function chaPic($id)
        {
            $this->editSta();
            $data['status'] = 1;
            $res = $this->where('id='.$id)->save($data);
            if ($res) {
                return ture;
            } else {
                return false;
            }
        }

        public function editSta()
        {
            $sta['status'] = 0;
            $this->where('1=1')->save($sta);
        }
    }
