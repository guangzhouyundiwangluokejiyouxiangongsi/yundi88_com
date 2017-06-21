<?php

namespace Seller\Controller;

use Think\Page;

class PurchaseController extends BaseController {

	public function purchase()
	{
        if (IS_POST) {
            $data2['tab2'] = $_POST['tab2'];
            $data2['tab2']['store_id'] = session('store_id');
            $res2 = M('purchase')->add($data2['tab2']);

            //转换post提交过来的数据为想要的格式
            for($i = 0;$i < count($_POST['tab1']['gid']);$i++){
                $n = 0;
                foreach($_POST['tab1'] as $k=>$v){
                    $data[$i][$k] = $_POST['tab1'][$k][$i];
                }
                $data[$i]['purpic'] = $_POST['img']['purpic'];
                $data[$i]['purcontract'] = $_POST['img']['purcontract'];
                $data[$i]['purelse'] = $_POST['img']['purelse'];
                $data[$i]['store_id'] = session('store_id');
                $data[$i]['pid'] = $res2;
                $n++;
            }

            $m = M('purchase_detail');
            // $data['__hash__'] = $_POST['__hash__'];
            // $data = $m->create($data,1);

            // 表单令牌验证
            if ($m->autoCheckToken($_POST['__hash__'])) {
                echo $m->getError();
            }

            if(!$data){
                echo $m->getError();
            }
            $m->startTrans();
            $res1 = $m->addAll($data);
            // dump($m->getLastSQL());exit;
            if (!$res1) {
                $m->rollback();
                $this->error('添加失败');
            }

            if (!$res2) {
                $m->rollback();
                $this->error('添加失败');
            } else {
                $m->commit();
                $this->success('添加成功');
            }
        } else {
    		$this->display();
        }
	}

    public function _empty()
    {
        $this->display('404');
    }

    public function index()
    {
        $store_id =session('store_id');
        $list = M('purchase')->where(array('store_id'=>$store_id))->getField('id,person,phone,qq,email,supplier,addtime');
        $this->assign('list',$list);
        $this->display();
    }

    public function details()
    {
        $pid = I('get.id');
        // $store_id =session('store_id');
        $list = M('purchase_detail')->where(array('pid'=>$pid))->field('id,gid,gname,model,num,unit,price,money,remark,purpic,purcontract,purelse')->select();
        for ($i=0; $i < count($list); $i++) {
            $list[$i]['purpic'] = explode(',', $list[$i]['purpic']);
            $list[$i]['purcontract'] = explode(',', $list[$i]['purcontract']);
            $list[$i]['purelse'] = explode(',', $list[$i]['purelse']);
        }
        $this->assign('list',$list);
        $this->display();
    }

    public function editPurchase()
    {

        if (IS_POST) {


            $data2['tab2'] = $_POST['tab2'];
            $res2 = M('purchase')->where(array('id'=>I('post.id')))->save($data2['tab2']);
            // dump(M()->getLastSQL());

            //转换post提交过来的数据为想要的格式
            for($i = 0;$i < count($_POST['tab1']['gid']);$i++){
                $n = 0;
                foreach($_POST['tab1'] as $k=>$v){
                    $data[$i][$k] = $_POST['tab1'][$k][$i];
                }
                $data[$i]['purpic'] = implode(',',$_POST['img']['purpic']);
                $data[$i]['purcontract'] = implode(',',$_POST['img']['purcontract']);
                $data[$i]['purelse'] = implode(',',$_POST['img']['purelse']);
                $n++;
            }
            foreach($data as $v){
                // dump($v);exit;
                $id = $data[$v]['id'];
                $m = M('purchase_detail');
                $res3[] = $m->where(array('id'=>$id))->save($v);

            }

            for ($j=0; $j < count($data); $j++) {
                $id = $data[$j]['id'];
                unset($data[$j]['id']);
                $m = M('purchase_detail');

                // 表单令牌验证
                if ($m->autoCheckToken($_POST['__hash__'])) {
                    echo $m->getError();
                }

                if(!$data){
                    echo $m->getError();
                }
                $m->startTrans();
                $res1[] = $m->where(array('id'=>$id))->save($data[$j]);
            }

            for ($k=0; $k < count($res1); $k++) {
                if (!$res1[$k] && $res1[$k] === false) {
                    $m->rollback();
                    $this->error('修改失败');
                }
            }

            if (!$res2 && $res2 === false) {
                $m->rollback();
                $this->error('修改失败');
            } else {
                $m->commit();
                $this->success('修改成功');
            }
        } else {
            $data1 = M('purchase')->field('person,phone,qq,email,supplier,addtime')->where(array('id'=>I('get.id')))->find();
            $data2 = M('purchase_detail')->field('id,gid,gname,model,num,unit,price,money,remark,purpic,purcontract,purelse')->where(array('pid'=>I('get.id')))->select();
            for ($i=0; $i < count($data2); $i++) {
                $data2[$i]['purpic'] = explode(',', $data2[$i]['purpic']);
                $data2[$i]['purcontract'] = explode(',', $data2[$i]['purcontract']);
                $data2[$i]['purelse'] = explode(',', $data2[$i]['purelse']);
            }

            $this->assign('data1',$data1);
            $this->assign('data2',$data2);
            $this->assign('id', I('get.id'));
            $this->display();
        }
    }

    public function del()
    {
        $id = I('post.id');
        // dump($id);exit;
        $res1 = M('purchase')->where(array('id'=>$id))->delete();
        $res2 = M('purchase_detail')->where(array('pid'=>$id))->delete();
        if ($res1 && $res2) {
            echo 1;
        } else {
            echo 0;
        }
    }

    public function dels()
    {
        $id = I('post.id');
        // dump($id);exit;
        $res = M('purchase_detail')->where(array('id'=>$id))->delete();
        if ($res) {
            echo 1;
        } else {
            echo 0;
        }
    }

    public function showMyPurchase()
    {
        M('purchase')->where(array('store_id'=>session('store_id')))->select();
    }
}
