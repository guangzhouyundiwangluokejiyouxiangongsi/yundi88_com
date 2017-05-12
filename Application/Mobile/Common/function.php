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

function templogo($store_id,$width,$height)
{
    if(empty($store_id)) return '/Public/images/hemplogo.png';
    //判断缩略图是否存在
    $path = "Public/upload/logo/thumb/$store_id/";
    $logo_thumb_name ="logo_thumb_{$store_id}_{$width}_{$height}";
  
    // 这个商品 已经生成过这个比例的图片就直接返回了
    if(file_exists($path.$logo_thumb_name.'.jpg'))  return '/'.$path.$logo_thumb_name.'.jpg'; 
    if(file_exists($path.$logo_thumb_name.'.jpeg')) return '/'.$path.$logo_thumb_name.'.jpeg'; 
    if(file_exists($path.$logo_thumb_name.'.gif'))  return '/'.$path.$logo_thumb_name.'.gif'; 
    if(file_exists($path.$logo_thumb_name.'.png'))  return '/'.$path.$logo_thumb_name.'.png'; 
        
    $original_img = M('store')->where(array('store_id'=>$store_id))->getField('store_logo');
    if(empty($original_img)) return '/Public/images/hemplogo.png';
    
    $original_img = '.'.$original_img; // 相对路径
    if(!file_exists($original_img)) return '/Public/images/hemplogo.png';
    
    try{
        $image = new \Think\Image();
        $image->open($original_img);        
        $logo_thumb_name = $logo_thumb_name. '.'.$image->type();
        // 生成缩略图
        if(!is_dir($path)) mkdir($path,0777,true);      
        // 参考文章 http://www.mb5u.com/biancheng/php/php_84533.html  改动参考 http://www.thinkphp.cn/topic/13542.html
        $image->thumb($width, $height,2)->save($path.$logo_thumb_name,NULL,100); //按照原图的比例生成一个最大为$width*$height的缩略图并保存
       
    }catch (Think\Exception $e){
        return $original_img;
    }

}
