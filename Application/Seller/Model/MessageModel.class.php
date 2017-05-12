<?php


namespace Seller\Model;
use Think\Model;

class MessageModel extends Model
{
 	
    public function message_data($datas)
    {
    	$data['userid'] 	= $userid['id'];//用户id
    	$data['username'] 	= $datas['username'];//留言者姓名
    	$data['tel'] 		= $datas['tel'];//留言者电话
    	$data['url'] 		= $datas['url'];//留言页面
    	$data['email']		= $datas['email'];//留言者邮箱
    	$data['qq'] 		= $datas['qq'];//留言者qq
    	$data['text'] 		= $datas['text'];//留言内容
    	$data['addtime'] 	= time();
    	return $data;
    }


    public function message_conversion()
    {
        $messages = $this->where('message_userid ='.session('homeuser.id'))->select();
        foreach($messages as &$v){
            $v['message_addtime'] = date('Y-m-d H:i:s',$v['message_addtime']);
        }
        return $messages;
    }
}

 
