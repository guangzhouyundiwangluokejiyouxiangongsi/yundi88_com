<?php

namespace Seller\Controller;

use Think\Page;

class PurchaseController extends BaseController {

    public function preg($match, $str, $board)
    {
        if (preg_match($match, $str)) {
            $this->error($board.'格式错误');
        }
    }
	public function purchase()
	{
        if (IS_POST) {
            $types = I('post.tab1');
            for ($z=0; $z < count($types['type']); $z++) {
                if (is_null($types['type'][$z])) {
                    $this->error('请选择产品分类');
                }
            }
            
            $this->preg("\/^1[34578]\d{9}$\/", $_POST['tab2']['phone'], '手机号');
            $this->preg("\/^(\w)+(\.\w+)*@(\w)+((\.\w+)+)$\/", $_POST['tab2']['email'], '邮箱');
            if ($_POST['tab2']['qq']) {
                $this->preg('\/^[1-9]*[1-9][0-9]*$\/', $_POST['tab2']['qq'], 'QQ');
            }
            $data2['tab2'] = $_POST['tab2'];
            $data2['tab2']['store_id'] = session('store_id');
            $data2['tab2']['addtime'] = time();
            $res2 = M('purchase')->add($data2['tab2']);

            //转换post提交过来的数据为想要的格式
            for($i = 0;$i < count($_POST['tab1']['gname']);$i++){
                $n = 0;
                foreach($_POST['tab1'] as $k=>$v){
                    $data[$i]['gid'] = 'WP'.time().$i;
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
                // 推送
                for ($k=0; $k < count($data); $k++) {
                    $storeRes[] = M('store_bind_class')->field('store_id')->where(array('class_3'=>$data[$k]['type'], 'state'=>1))->select();
                }

                for ($l=0; $l < count($storeRes); $l++) {
                    for ($m=0; $m < count($storeRes[$l]); $m++) {
                        $no = $l+$m;
                        $storeData[$no]['cid'] = $res2;
                        $storeData[$no]['sid'] = $storeRes[$l][$m]['store_id'];
                        $storeData[$no]['addtime'] = time();
                        M('pur_status')->add($storeData[$no]);
                    }
                }
                

                // $m->commit();
                $this->success('添加成功');
            }
        } else {
            $class1 = M('goods_category')->field('id, name')->where(array('level'=>1))->select();
            $this->assign('class1', $class1);
    		$this->display();
        }
	}

    public function getData()
    {
        $class2 = M('goods_category')->field('id, name')->where(array('level'=>I('post.level'), 'parent_id'=>I('post.id')))->select();
        $this->ajaxReturn($class2);
    }


    public function _empty()
    {
        $this->display('404');
    }

    public function index()
    {
        $store_id =session('store_id');
        $list = M('purchase')->where(array('store_id'=>$store_id))->getField('id,person,phone,qq,email,addtime');
        $this->assign('list',$list);
        $this->display();
    }

    public function details()
    {
        $pid = I('get.id');
        // $store_id =session('store_id');
        $list = M('purchase_detail')->where(array('pid'=>$pid))->field('id,gid,gname,model,num,unit,price,money,remark,type,purpic,purcontract,purelse')->select();
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
                $id = $data[$v]['id'];
                $m = M('purchase_detail');
                $res3[] = $m->where(array('id'=>$id))->save($v);

            }

            for ($j=0; $j < count($data); $j++) {
                $id = $data[$j]['id'];
                unset($data[$j]['id']);
                // $m = M('purchase_detail');

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
                // $m->commit();
                $this->success('修改成功');
            }
        } else {
             $class1 = M('goods_category')->field('id, name')->where(array('level'=>1))->select();
            $this->assign('class1', $class1);
            $data1 = M('purchase')->field('person,phone,qq,email,addtime')->where(array('id'=>I('get.id')))->find();
            $data2 = M('purchase_detail')->field('id,gid,gname,model,num,unit,price,money,remark,purpic,purcontract,purelse,type')->where(array('pid'=>I('get.id')))->select();
            for ($i=0; $i < count($data2); $i++) {
                $data2[$i]['purpic'] = explode(',', $data2[$i]['purpic']);
                $data2[$i]['purcontract'] = explode(',', $data2[$i]['purcontract']);
                $data2[$i]['purelse'] = explode(',', $data2[$i]['purelse']);
            }
            for ($y=0; $y < count($data2); $y++) { 
                $nameRes = M('goods_category')->field('name')->where(array('id'=>$data2[$y]['type']))->find();
                $data2[$y]['type'] = $nameRes['name'];
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

    public function purMsg()
    {
        if (IS_AJAX) {
            $id = I('post.id');
            $detailRes = M('purchase_detail')->field('id,gname,model,num,unit,price,money,remark,purpic,purcontract,purelse')->where(array('pid'=>$id))->select();
            for ($i=0; $i < count($detailRes); $i++) {
                $detailRes[$i]['purpic'] = explode(',', $detailRes[$i]['purpic']);
                $detailRes[$i]['purcontract'] = explode(',', $detailRes[$i]['purcontract']);
                $detailRes[$i]['purelse'] = explode(',', $detailRes[$i]['purelse']);
            }
            $this->ajaxReturn($detailRes);
        } else {
            $purRes = M('pur_status')->field('cid')->where(array('sid'=>session('store_id'),'status'=>1))->select();
            for ($i=0; $i < count($purRes); $i++) {
                $purRes2[] = M('purchase')->field('id,person,phone,addtime')->where(array('id'=>$purRes[$i]['cid']))->find();
            }

            for ($j=0; $j < count($purRes2); $j++) {
                $purRes2[$j]['addtime'] = date('Y-m-d H:i:s', $purRes2[$j]['addtime']);
            }
            $this->assign('purRes', $purRes2);
            $this->display();
        }
    }

    public function purDetails()
    {
        $id = I('get.id');
        $detailRes = M('purchase_detail')->field('id,gid,gname,model,num,unit,price,money,remark,type,purpic,purcontract,purelse')->where(array('pid'=>$id))->select();
        for ($i=0; $i < count($detailRes); $i++) {
            $detailRes[$i]['purpic'] = explode(',', $detailRes[$i]['purpic']);
            $detailRes[$i]['purcontract'] = explode(',', $detailRes[$i]['purcontract']);
            $detailRes[$i]['purelse'] = explode(',', $detailRes[$i]['purelse']);
        }

        for ($y=0; $y < count($detailRes); $y++) { 
                $nameRes = M('goods_category')->field('name')->where(array('id'=>$detailRes[$y]['type']))->find();
                $detailRes[$y]['type'] = $nameRes['name'];
            }
        $this->assign('detailRes', $detailRes);
        $this->assign('pid', $id);
        $this->display();
    }

    public function haveRead()
    {
        $id = I('post.id');
        $data['status'] = 2;
        $res = M('pur_status')->where(array('cid'=>$id))->save($data);
        $this->ajaxReturn($res);
    }

    public function changeSta()
    {
        $id = I('post.id');
        $status = I('post.status');
        $data['status'] = $status;
        $res = M('pur_status')->where(array('cid'=>$id,'sid'=>session('store_id')))->save($data);
        $this->ajaxReturn($res);
    }

    public function myPurchase()
    {
        $purRes = M('pur_status')->field('cid')->where(array('sid'=>session('store_id'),'status'=>3))->select();
        for ($i=0; $i < count($purRes); $i++) {
            $purRes2[] = M('purchase')->field('id,person,phone,addtime')->where(array('id'=>$purRes[$i]['cid']))->find();
        }
        for ($j=0; $j < count($purRes2); $j++) {
            $purRes2[$j]['addtime'] = date('Y-m-d H:i:s', $purRes2[$j]['addtime']);
        }
        $this->assign('purRes', $purRes2);
        $this->display();
    }

    public function myDetails()
    {
        $id = I('get.id');
        $detailRes = M('purchase_detail')->field('id,gid,gname,model,num,type,unit,price,money,remark,purpic,purcontract,purelse')->where(array('pid'=>$id))->select();
        for ($i=0; $i < count($detailRes); $i++) {
            $detailRes[$i]['purpic'] = explode(',', $detailRes[$i]['purpic']);
            $detailRes[$i]['purcontract'] = explode(',', $detailRes[$i]['purcontract']);
            $detailRes[$i]['purelse'] = explode(',', $detailRes[$i]['purelse']);
        }

        for ($y=0; $y < count($detailRes); $y++) { 
                $nameRes = M('goods_category')->field('name')->where(array('id'=>$detailRes[$y]['type']))->find();
                $detailRes[$y]['type'] = $nameRes['name'];
            }
        $this->assign('detailRes', $detailRes);
        $this->display();
    }
}
