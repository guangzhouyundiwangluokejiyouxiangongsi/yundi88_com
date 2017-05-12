<?php
/**
 * tpshop
 * ============================================================================
 * 版权所有 2015-2027 深圳搜豹网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tp-shop.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * Author: dyr
 * Date: 2016-08-09
 */

namespace Home\Logic;

use Think\Model\RelationModel;

/**
 * 回复
 * Class CatsLogic
 * @package Home\Logic
 */
class ReplyLogic extends RelationModel{
    /**
     * 把回复树状数组转换成二维数组
     * @param $comment_id 回复id
     * @param int $item_num 条数
     * @return array
     */
    public function getReplyList($comment_id, $item_num = 0)
    {
        $reply_tree = D('reply')->getReplyList($comment_id);
        if(empty($reply_tree)){
            return $reply_tree;
        }
        $reply_flat_list = $this->treeToArray($reply_tree);
        if ($item_num == 0 || count($reply_flat_list) <= $item_num) {
            $res =  $reply_flat_list;
        } else {
            $res =  array_slice($reply_flat_list, $item_num);
        }
        return $res;
    }

    /**
     * 回复分页
     * @param $comment_id
     * @param int $page
     * @param int $item_num
     * @return mixed
     */
    public function getRaplyPage($comment_id, $page = 0, $item_num = 20)
    {
        $reply_tree = D('reply')->getReplyList($comment_id);
        $reply_flat_list = $this->treeToArray($reply_tree);
        $count = count($reply_flat_list);
        $list['list'] = array_slice($reply_flat_list, $page * $item_num, $item_num);
        $list['count'] = $count;
        return $list;
    }

    /**
     * 将树状数组转换二维数组
     * @param $tree
     * @return array
     */
    public function treeToArray($tree) {
        $list = array();
        foreach($tree as $key) {
            $node = $$key['children'];
            unset($key['children']);
            $list[] = $key;
            if($node) $list = array_merge($list, $this->treeToArray($node));
        }
        return $list;
    }
}