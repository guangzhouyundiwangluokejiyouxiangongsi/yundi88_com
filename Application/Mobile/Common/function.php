<?php
 /**
 *获取html文本里的img
 * @param string $content
 * @return array
 */
// function sp_getcontent_imgs($content){
//     import("Org.phpQuery.phpQuery");
//     \phpQuery::newDocumentHTML($content);
//     $pq=pq();
//     $imgs=$pq->find("img");
//     $imgs_data=array();
//     if($imgs->length()){
//         foreach ($imgs as $img){
//             $img=pq($img);
//             $im['src']=$img->attr("src");
//             // $im['title']=$img->attr("title");
//             // $im['alt']=$img->attr("alt");
//             $imgs_data[]=$im;
//         }
//     }
//     \phpQuery::$documents=null;
//     return $imgs_data;
// }

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

function tempimg($name,$width,$height)
{
    if(empty($name)) return '';
    $arr = explode('/', $name);
    // $name = $arr[ count($arr)-1 ];
    //判断缩略图是否存在
    $path = "Public/upload/info/thumb/".$arr[ count($arr)-1 ]."/";
    $thumb_name =$arr[ count($arr)-1 ]."{$width}_{$height}";
  
    // 这个商品 已经生成过这个比例的图片就直接返回了
    if(file_exists($path.$thumb_name.'.jpg'))  return '/'.$path.$thumb_name.'.jpg'; 
    if(file_exists($path.$thumb_name.'.jpeg')) return '/'.$path.$thumb_name.'.jpeg'; 
    if(file_exists($path.$thumb_name.'.gif'))  return '/'.$path.$thumb_name.'.gif'; 
    if(file_exists($path.$thumb_name.'.png'))  return '/'.$path.$thumb_name.'.png'; 
        
    
    $original_img = '.'.$name; // 相对路径
    if(!file_exists($original_img)) return $name;
    
    
        $image = new \Think\Image();
        $image->open($original_img);        
        $thumb_name = $thumb_name. '.'.$image->type();
        // 生成缩略图
        if(!is_dir($path)) mkdir($path,0777,true);      
        // 参考文章 http://www.mb5u.com/biancheng/php/php_84533.html  改动参考 http://www.thinkphp.cn/topic/13542.html
        $image->thumb($width, $height,2)->save($path.$thumb_name,NULL,100); //按照原图的比例生成一个最大为$width*$height的缩略图并保存
        return '/'.$path.$thumb_name;
   
        // return '/'.$path.$thumb_name;

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