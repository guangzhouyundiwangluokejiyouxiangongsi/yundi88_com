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
 * Author: 当燃
 * 
 * 店铺装修模型
 *
 */
namespace Seller\Model;
use Think\Model;
class StoreDecorationModel extends Model {

    //装修块布局数组
    private $block_layout_array = array('block_1');
    //装修块类型数组
    private $block_type_array = array('html', 'slide', 'hot_area', 'goods');
	
    //protected $tablePrefix = 'tp_';
    
	/**
	 * 列表
     *
	 * @param array $condition 查询条件
     * @return array
	 */
    public function getStoreDecorationList($condition) {
        $list = $this->where($condition)->select();
        return $list;
    }

    /**
	 * 查询基本数据
     *
	 * @param array $condition 查询条件
     * @param int $store_id 店铺编号
     * @return array
	 */
    public function getStoreDecorationInfo($condition, $store_id = null) {
        $info = $this->where($condition)->find();
        //如果提供了$store_id，验证是否符合，不符合返回false
        if($store_id !== null) {
            if($info['store_id'] != $store_id) {
                return false;
            }
        }
        return $info;
    }

    /**
     * 获取完整装修数据
     *
	 * @param array $decoration_id 装修编号
     * @param int $store_id 店铺编号
     * @return array
     */
    public function getStoreDecorationInfoDetail($decoration_id, $store_id) {
        if($decoration_id <= 0) {
            return false;
        }

        $condition = array();
        $condition['decoration_id'] = $decoration_id;
        $condition['store_id'] = $store_id;
        $store_decoration_info = $this->getStoreDecorationInfo($condition);
        if(!empty($store_decoration_info)) {
            $data = array();
            //处理装修背景设置
            $decoration_setting = array();
            if(empty($store_decoration_info['decoration_setting'])) {
                $decoration_setting['background_color'] = '';
                $decoration_setting['background_image'] = '';
                $decoration_setting['background_image_url'] = '';
                $decoration_setting['background_image_repeat'] = '';
                $decoration_setting['background_position_x'] = '';
                $decoration_setting['background_position_y'] = '';
                $decoration_setting['background_attachment'] = '';
            } else {
                $setting = unserialize($store_decoration_info['decoration_setting']);
                $decoration_setting['background_color'] = $setting['background_color'];
                $decoration_setting['background_image'] = $setting['background_image'];
                $decoration_setting['background_image_url'] = $setting['background_image_url']; //getStoreDecorationImageUrl($setting['background_image'], $store_id);
                $decoration_setting['background_image_repeat'] = $setting['background_image_repeat'];
                $decoration_setting['background_position_x'] = $setting['background_position_x'];
                $decoration_setting['background_position_y'] = $setting['background_position_y'];
                $decoration_setting['background_attachment'] = $setting['background_attachment'];
            }
            $data['decoration_setting'] = $decoration_setting;

            //处理块列表
            $block_list = array();
            $block_list = $this->getStoreDecorationBlockList(array('decoration_id' => $decoration_id));
            $data['block_list'] = $block_list;
            //处理导航条样式
            $data['decoration_nav'] = unserialize($store_decoration_info['decoration_nav']);
            //处理banner
            $decoration_banner = unserialize($store_decoration_info['decoration_banner']);
            //$decoration_banner['image_url'] = getStoreDecorationImageUrl($decoration_banner['image'], $store_id);
            $data['decoration_banner'] = $decoration_banner;

            return $data;
        } else {
            return false;
        }
    }

    /**
     * 生成装修背景样式规则
     *
	 * @param array $decoration_setting 样式规则数组
	 * @return string 样式规则 
     */
    public function getDecorationBackgroundStyle($decoration_setting) {
        $decoration_background_style = '';
        if($decoration_setting['background_color'] != '') {
            $decoration_background_style .= 'background-color: ' . $decoration_setting['background_color'] . ';';
        }
        if($decoration_setting['background_image'] != '') {
            $decoration_background_style .= 'background-image: url(' . $decoration_setting['background_image_url'] . ');';
        }
        if($decoration_setting['background_image_repeat'] != '') {
            $decoration_background_style .= 'background-repeat: ' . $decoration_setting['background_image_repeat'] . ';';
        }
        if($decoration_setting['background_position_x'] != '' || $decoration_setting['background_position_y'] != '') {
            $decoration_background_style .= 'background-position: ' . $decoration_setting['background_position_x'] . ' ' . $decoration_setting['background_position_y'] . ';';
        }
        if($decoration_setting['background_attachment'] != '') {
            $decoration_background_style .= 'background-attachment: ' . $decoration_setting['background_attachment'] .';';
        }
        return $decoration_background_style;
    }

	/*
	 * 添加
     *
	 * @param array $param 信息
	 * @return bool
	 */
    public function addStoreDecoration($param){
        return M('store_decoration')->insert($param);
    }

	/*
	 * 编辑
     *
	 * @param array $update 更新信息
	 * @param array $condition 条件
	 * @return bool
	 */
    public function editStoreDecoration($update, $condition){
        return $this->where($condition)->save($update);
    }

    /**
	 * 查询装修块列表
     *
	 * @param array $condition 查询条件
     * @return array
	 */
    public function getStoreDecorationBlockList($condition) {
        $list = M('store_decoration_block')->where($condition)->order('block_sort asc')->select();
        foreach ($list as $key => $value) {
            $list[$key]['block_content'] = str_replace("\r", "", $value['block_content']);
            $list[$key]['block_content'] = str_replace("\n", "", $value['block_content']);
        }
        return $list;
    }

    /**
	 * 查询装修块信息
     *
	 * @param array $condition 查询条件
     * @param int $store_id 店铺编号
     * @return array
	 */
    public function getStoreDecorationBlockInfo($condition, $store_id = null) {
        $info = M('store_decoration_block')->where($condition)->find();
        //如果提供了$store_id，验证是否符合
        if($store_id !== null) {
            if($info['store_id'] != $store_id) {
                return false;
            }
        }
        return $info;
    }

	/*
	 * 添加装修块
     *
	 * @param array $param 信息
	 * @return bool
	 */
    public function addStoreDecorationBlock($param){
        return M('store_decoration_block')->add($param);
    }

	/*
	 * 编辑装修块
     *
	 * @param array $update 更新信息
	 * @param array $condition 条件
	 * @return bool
	 */
    public function editStoreDecorationBlock($update, $condition){
        return M('store_decoration_block')->where($condition)->save($update);
    }

	/*
	 * 删除装修块
     *
	 * @param array $condition 条件
	 * @return bool
	 */
    public function delStoreDecorationBlock($condition){
        return M('store_decoration_block')->where($condition)->delete();
    }

    /**
     * 返回装修块布局数组
     */
    public function getStoreDecorationBlockLayoutArray() {
        return $this->block_layout_array;
    }

    /**
     * 返回装修块模块类型数组
     */
    public function getStoreDecorationBlockTypeArray() {
        return $this->block_type_array;
    }
}
