<?php
/**
 * 验证码发送
 * @param $mobile 手机号码
 * @param $content 发送内容
 * @param $type 验证码类型
 */
function send_sms($mobile,$content,$type=''){

}


/**
 * 管理员操作记录
 * @param $log_url 操作URL
 * @param $log_info 记录信息
 * @param $log_type 日志类别
 */function adminLog($log_info,$log_type=0){
    $add['log_time'] = time();
    $add['admin_id'] = session('admin_id');
    $add['log_info'] = $log_info;
    $add['log_ip'] = getIP();
    $add['log_url'] = __ACTION__;
    $add['log_type'] = $log_type;
    M('admin_log')->add($add);
}

/**
 * 面包屑导航  用于前台用户中心
 * 根据当前的控制器名称 和 action 方法
 */
function navigate_user()
{    
    $navigate = include APP_PATH.'Common/Conf/navigate.php';    
    $location = strtolower('/'.CONTROLLER_NAME);
    $arr = array(
        '首页'=>'/',
        $navigate[$location]['name']=>U('/'.CONTROLLER_NAME),
        $navigate[$location]['action'][ACTION_NAME]=>'javascript:void();',
    );
    return $arr;
}

/**
*  面包屑导航  用于前台商品
 * @param type $id 商品id  或者是 商品分类id
 * @param type $type 默认0是传递商品分类id  id 也可以传递 商品id type则为1
 */
function navigate_goods($id,$type = 0)
{
    $cat_id = $id; //
    // 如果传递过来的是
    if($type == 1){
        $cat_id = M('goods')->where("goods_id = $id")->getField('cat_id3');
    }
    $categoryList = M('GoodsCategory')->getField("id,name,parent_id");

    // 第一个先装起来
    $arr[$cat_id] = $categoryList[$cat_id]['name'];
    while (true)
    {
        $cat_id = $categoryList[$cat_id]['parent_id'];
        if($cat_id > 0)
            $arr[$cat_id] = $categoryList[$cat_id]['name'];
        else
            break;
    }
    $arr = array_reverse($arr,true);
    return $arr;
}


function photoimg($id,$width,$height)
{
    if(!$id || !is_numeric($id)){return '';}
    $path = "/Public/upload/photoimg/thumb/".$id;
    $thumb_name ="/photoimg_{$id}_{$width}_{$height}";
    $store_id = $_GET['store_id'];
    $str = <<<zhoufei
" onclick="window.open('/Store/photolist/store_id/{$store_id}.html')" style="cursor:pointer;
zhoufei;
    if(file_exists('.'.$path.$thumb_name.'.jpg'))  return $path.$thumb_name.'.jpg'.$str; 
    if(file_exists('.'.$path.$thumb_name.'.jpeg')) return $path.$thumb_name.'.jpeg'.$str; 
    if(file_exists('.'.$path.$thumb_name.'.gif'))  return $path.$thumb_name.'.gif'.$str; 
    if(file_exists('.'.$path.$thumb_name.'.png'))  return $path.$thumb_name.'.png'.$str; 
    if(!is_dir('.'.$path)){mkdir('.'.$path,0777,true);}
    $productimg = M("photoimg")->where(array('id'=>$id))->field('img')->find();
    if(!file_exists('.'.$productimg['img'])){$productimg['img'] = '/Public/images/linfei.png'.$str;}
    // return $productimg['img'];
    $image = new \Think\Image(); 
    $image->open('.'.$productimg['img']);
    $type = $image->type(); 
    $image->thumb($width, $height,2)->save('.'.$path.$thumb_name.'.'.$type);
    //如果是产品或文章添加水印
   // echo $path.$thumb_name.'.'.$type
    return $path.$thumb_name.'.'.$type.$str;
}



   



    function mosaic($user_id,$width,$height,$field,$x1,$y1,$x2,$y2)
    {

        if(empty($user_id)) return '';
        //判断缩略图是否存在
        $path = "Public/upload/mosaic/thumb/$user_id/";
        $mosaic_thumb_name ="mosaic_thumb_{$user_id}_{$width}_{$height}_{}";
      
        // 这个商品 已经生成过这个比例的图片就直接返回了
        if(file_exists($path.$mosaic_thumb_name.'.jpg'))  return '/'.$path.$mosaic_thumb_name.'.jpg'; 
        if(file_exists($path.$mosaic_thumb_name.'.jpeg')) return '/'.$path.$mosaic_thumb_name.'.jpeg'; 
        if(file_exists($path.$mosaic_thumb_name.'.gif'))  return '/'.$path.$mosaic_thumb_name.'.gif'; 
        if(file_exists($path.$mosaic_thumb_name.'.png'))  return '/'.$path.$mosaic_thumb_name.'.png'; 

         $original_img = M('store_apply')->where(array('user_id'=>$user_id))->getField($field);
         if(empty($original_img)) return '';
         $mosaic_img = '.'.$original_img;
         if(!file_exists($mosaic_img)) return '';
         if(!is_dir($path)) mkdir($path,0777,true);

         $image = new \Think\Image(); 
         $image->open($mosaic_img);//将图片裁剪为440x440并保存为corp.jpg
          $type = $image->type(); 
         $image->thumb($width, $height)->save($path.$mosaic_thumb_name.'.'.$type,NULL,100);//缩放
         $image->water('./Public/images/mosaic.jpg',array($x1,$y1),100)->save($path.$mosaic_thumb_name.'.'.$type);
         $image->water('./Public/images/mosaic.jpg',array($x2,$y2),100)->save($path.$mosaic_thumb_name.'.'.$type);

         return '/'.$path.$mosaic_thumb_name.'.'.$type;

    }



function goodsorderby($goods_category_id)
 {
    if(!$goods_category_id){return;}
    $goods_category = M('goods_category');
    //查询商品分类层级
    $level =  $goods_category->where(array('id'=>$goods_category_id))->getField('level');

    //查询此分类下的所有商品
    $goodslist = M('goods')->where("cat_id{$level} = {$goods_category_id}")->field('goods_id,commerce_state,apply_state,IFNULL(commerce_state,0) + IFNULL(apply_state,0) as num')->join('INNER JOIN __STORE__ ON __STORE__.store_id = __GOODS__.store_id')->select();
    
    foreach($goodslist as $v){
            if($v['num'] == 2){
                $num[] = $v['goods_id'];
            }elseif($v['commerce_state']){
                $commerce_state[] = $v['goods_id'];
            }elseif($v['apply_state']){
                $apply_state[] = $v['goods_id'];
            }else{

                $other[] = $v['goods_id'];
            }
    }       

            shuffle($num);
            shuffle($commerce_state);
            shuffle($apply_state);
            shuffle($other);
            $str .= implode(',',$num);
            $str  .= implode(',',$commerce_state);
            $str .= implode(',',$apply_state);
            $str .= implode(',',$other);
        return $str;


 }
