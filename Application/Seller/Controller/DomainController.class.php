<?php

namespace Seller\Controller;

use Seller\Logic\GoodsLogic;
use Think\AjaxPage;
use Think\Page;


set_time_limit(0);

class DomainController  extends IndexController

{

	public function index()
	{
		$this->pushVersion();
        $seller = session('seller');
        $menu_list = $this->getSellerMenuList($seller['is_admin'],$seller['act_limits']);
        $this->assign('menu_list',$menu_list['seller_menu']);
        $this->assign('seller',$seller);

		$this->display();
	}


	//配置文件
	protected $userid = 43627;//代理用户id
	protected $email = "446249654@qq.com";//代理用户账户
	protected $password = "123456";//新一代给的密钥
	protected $array = array('com'=>88,'cn'=>58,'net'=>88,'top'=>5,'org'=>130,'com_cn'=>58,'net_cn'=>58,'org_cn'=>58,'biz'=>140,'info'=>140,'cc'=>88,'tv'=>600,'name'=>100,'mobi'=>130,'me'=>280,'co'=>250,'so'=>900);

	public function domain_register($order=null)
	{
		if($order){
			$strAction = "Domain.Register";
			// 查询域名信息
			$data = M('domain')->field('r_organization,r_organization1,r_manager,r_industry,r_name,r_name1,r_country,r_province,r_province1,r_city,r_city1,r_street,r_street1,r_postcode,r_phone,r_phone_ext,r_fax,r_fax_ext,r_email,a_organization,a_organization1,a_manager,a_industry,a_name,a_name1,a_country,a_province,a_province1,a_city,a_city1,a_street,a_street1,a_postcode,a_phone,a_phone_ext,a_fax,a_fax_ext,a_email,t_organization,t_organization1,t_manager,t_industry,t_name,t_name1,t_country,t_province,t_province1,t_city,t_city1,t_street,t_street1,t_postcode,t_phone,t_phone_ext,t_fax,t_fax_ext,t_email,t_organization,t_organization1,t_manager,t_industry,t_name,t_name1,t_country,t_province,t_province1,t_city,t_city1,t_street,t_street1,t_postcode,t_phone,t_phone_ext,t_fax,t_fax_ext,t_email,dns1,dns2,dns3,dns4,domain,year,tld,lang,encoding')->where(array('order'=>$order,'status'=>1))->select();
			if(!$data){return '订单不存在！';}

	foreach($data as $vv){

		$strResult = $this->doPost($vv, $this->userid, $this->email, $this->password, $strAction);

		$isMatched = preg_match_all('/\s/', $strResult, $matches);//匹配空白符
			//将空白符替换成点
			foreach($matches as $v){
				$s = str_replace($v, '.', $strResult);
			}
			//用点切割字符串
			$resarray = explode('.', $s);
			//用等号切割转换成索引数组
			foreach($resarray as $v){
				$r = explode('=', $v);
				$res[ $r[0] ] = @$r[1];
			}

			return $res['code'];
			// if($res['code'] == '200'){
			// 	M('domain')->where(array('order'=>$order))->save(array('status'=>2));
			// 	return true;
			// }else{
			// 	$str = file_get_contents('./domain.md');
			// 	file_put_contents('./domain.md', $str.$vv['domain'].'注册失败！\n');
			// 	return false;
			// }
			
	}

		}else{
			unset($_GET['__hash__']);
			$this->assign('domain',$_GET);

			$this->display();
		}



	}




	public function info()
	{	
		if (I('order')){
			session('domaincart'.session('store_id'),null);
			include_once(PATH.'/plugins/payment/alipay/alipay2.class.php');
			$pay = new \alipay();
			$order['order_sn'] = I('order');
			$price = M()->query("select sum(price) as price from __DOMAIN__ where `order` = '{$order['order_sn']}' and userid = ".session('store_id'));
			$order['order_amount'] = $price[0]['price'];//订单总价
			$config_value['bank_code'] = '1';
			$ss = $pay->get_code($order,$config_value);
			echo $ss;
			exit;
		}

		if(IS_POST){
			$_POST['phone'] = '+81.'.$_POST['phone'];//电话
			$_POST['fax'] = '+81.'.$_POST['fax'];//传真
		$strAction = "Domain.Register";
		// 域名注册信息
		$aDomainRegInfo = array
		(
			// 所有人
			'r_organization'	=>	$_POST['organization']?$_POST['organization']:$_POST['name'],
			'r_organization1'	=>	$_POST['organization1']?$_POST['organization1']:$_POST['name'],
			'r_manager'			=>	$_POST['name'],//单位负责人
			'r_industry'		=>	"s16",//所属行业
			'r_name'			=>	$_POST['name'],
			'r_name1'			=>	$_POST['name1'],
			'r_country'			=>	"CN",
			'r_province'		=>	$_POST['province'],
			'r_province1'		=>	pinyin($_POST['province'],'utf-8'),
			'r_city'			=>	$_POST['city'],
			'r_city1'			=>	pinyin($_POST['city'],'utf-8'),
			'r_street'			=>	$_POST['street'],
			'r_street1'			=>	$_POST['street1'],
			'r_postcode'		=>	$_POST['postcode'],
			'r_phone'			=>	$_POST['phone'],
			'r_phone_ext'		=>	"123",
			'r_fax'				=>	$_POST['fax'],
			'r_fax_ext'			=>	"123",
			'r_email'			=>	$_POST['email'],
			// 管理联系人
			'a_organization'	=>	$_POST['organization']?$_POST['organization']:$_POST['name'],
			'a_organization1'	=>	$_POST['organization1']?$_POST['organization1']:$_POST['name'],
			'a_manager'			=>	$_POST['name'],//单位负责人
			'a_industry'		=>	"",//所属行业
			'a_name'			=>	$_POST['name'],
			'a_name1'			=>	$_POST['name1'],
			'a_country'			=>	"CN",
			'a_province'		=>	$_POST['province'],
			'a_province1'		=>	pinyin($_POST['province'],'utf-8'),
			'a_city'			=>	$_POST['city'],
			'a_city1'			=>	pinyin($_POST['city'],'utf-8'),
			'a_street'			=>	$_POST['street'],
			'a_street1'			=>	$_POST['street1'],
			'a_postcode'		=>	$_POST['postcode'],
			'a_phone'			=>	$_POST['phone'],
			'a_phone_ext'		=>	"123",
			'a_fax'				=>	$_POST['fax'],
			'a_fax_ext'			=>	"123",
			'a_email'			=>	$_POST['email'],
			// 技术联系人
			't_organization'	=>	$_POST['organization']?$_POST['organization']:$_POST['name'],
			't_organization1'	=>	$_POST['organization1']?$_POST['organization1']:$_POST['name'],
			't_manager'			=>	$_POST['name'],//单位负责人
			't_industry'		=>	"",//所属行业
			't_name'			=>	$_POST['name'],
			't_name1'			=>	$_POST['name1'],
			't_country'			=>	"CN",
			't_province'		=>	$_POST['province'],
			't_province1'		=>	pinyin($_POST['province'],'utf-8'),
			't_city'			=>	$_POST['city'],
			't_city1'			=>	pinyin($_POST['city'],'utf-8'),
			't_street'			=>	$_POST['street'],
			't_street1'			=>	$_POST['street1'],
			't_postcode'		=>	$_POST['postcode'],
			't_phone'			=>	$_POST['phone'],
			't_phone_ext'		=>	"123",
			't_fax'				=>	$_POST['fax'],
			't_fax_ext'			=>	"123",
			't_email'			=>	$_POST['email'],
			// 付费联系人
			't_organization'	=>	$_POST['organization']?$_POST['organization']:$_POST['name'],
			't_organization1'	=>	$_POST['organization1']?$_POST['organization1']:$_POST['name'],
			't_manager'			=>	$_POST['name'],//单位负责人
			't_industry'		=>	"",//所属行业
			't_name'			=>	$_POST['name'],
			't_name1'			=>	$_POST['name1'],
			't_country'			=>	"CN",
			't_province'		=>	$_POST['province'],
			't_province1'		=>	pinyin($_POST['province'],'utf-8'),
			't_city'			=>	$_POST['city'],
			't_city1'			=>	pinyin($_POST['city'],'utf-8'),
			't_street'			=>	$_POST['street'],
			't_street1'			=>	$_POST['street1'],
			't_postcode'		=>	$_POST['postcode'],
			't_phone'			=>	$_POST['phone'],
			't_phone_ext'		=>	"123",
			't_fax'				=>	$_POST['fax'],
			't_fax_ext'			=>	"123",
			't_email'			=>	$_POST['email'],
			// Name server 可不填，系统会默认为最新的DNS服务器
			'dns1'				=>	"",
			'dns2'				=>	"",
			'dns3'				=>	"",
			'dns4'				=>	"",
			// 域名信息
			'domain'			=>	"",
			'year'				=>	"",
			'tld'				=>	"",
			'lang'				=>	'ENG',
			'encoding'			=>	"ASCII",
		);

		foreach($_POST['d'] as $k=>$v){
			$arr = explode('=', $v);//
			$d = str_replace('_', '.', $arr[0]);
			$array = explode('.', $d);
			if(count($array) == 2){
				$aDomainRegInfo['tld'] = '.'.$array[1];//后缀
				$price1 = $this->array[ $array[1] ] * $arr[1];//价格
			}elseif(count($array) == 3){
				$aDomainRegInfo['tld'] = '.'.$array[1].'.'.$array[2];//后缀
				$price1 = $this->array[ $array[1].'_'.$array[2] ] * $arr[1];//价格
			}
			$aDomainRegInfo['domain'] = $d;
			$aDomainRegInfo['year'] = $arr[1];
			$aDomainRegInfo['time'] = time();
			$aDomainRegInfo['price'] = $price1;//价格
			$aDomainRegInfo['userid'] = session('store_id');
			$aDomainRegInfo['status'] = 0; 
			$aDomainRegInfo['order'] = date('YmdHis',time()).rand(111111,999999); 
			$data[] = $aDomainRegInfo;
		}

			$ress = M('domain')->addAll($data);
			if($ress){
				session('domaincart'.session('store_id'),null);
				include_once(PATH.'/plugins/payment/alipay/alipay2.class.php');
				$pay = new \alipay();
				$order['order_sn'] = $aDomainRegInfo['order'];
				$price = M()->query("select sum(price) as price from __DOMAIN__ where `order` = '{$order['order_sn']}' and userid = ".session('store_id'));
				$order['order_amount'] = $price[0]['price'];//订单总价
				$config_value['bank_code'] = '1';
				$ss = $pay->get_code($order,$config_value);
				echo $ss;


			}
			

		}else{
			unset($_GET['__hash__']);
			$this->assign('domain',$_GET);

			$this->display();
		}



	}


	// 服务器点对点 // http://www.tp-shop.cn/index.php/Home/Payment/notifyUrl        
    public function notifyUrl(){  
    include_once(PATH.'/plugins/payment/alipay/alipay2.class.php');
	$pay = new \alipay();          
    $pay->response();            
         exit();
    }

    // 页面跳转 // http://www.tp-shop.cn/index.php/Home/Payment/returnUrl        
    public function returnUrl(){
    	 include_once(PATH.'/plugins/payment/alipay/alipay2.class.php');
		 $pay = new \alipay();  
         $result = $pay->respond2(); // $result['order_sn'] = '201512241425288593'; 

         if($result['status'] == 1){
         	$res = $this->domain_register($result['order_sn']);//提交注册域名
         	if($res['code'] == '200'){
				M('domain')->where(array('order'=>$result['order_sn']))->save(array('status'=>2));//注册成功
			}else{
				M('domain')->where(array('order'=>$result['order_sn']))->save(array('status'=>3));//注册失败

			}
         	$this->success('购买成功','/Seller/Domain/index');
         }else{

         	$this->error('支付失败！','/Seller/Domain/index');
         }



         if(stripos($result['order_sn'],'recharge') !== false)
         {
         	$order = M('recharge')->where("order_sn = '{$result['order_sn']}'")->find();
         	$this->assign('order', $order);
         	if($result['status'] == 1)
         		$this->display('recharge_success');
         	else
         		$this->display('recharge_error');
         	exit();
         }
        
    }                


	/**
	 * 购物车
	 */
	public function cart()
	{
		if(IS_AJAX){
			$domain = I('v');
			$d = str_replace('.', '_', $domain);
			session('domaincart'.session('store_id').'.'.$d,$domain);
			$this->ajaxReturn(1);
		}else{


			// $domain_m = M('domain');
			// $domain = $domain_m->where(array('userid'=>session('store_id'),'status'=>1))->select();
			// $this->assign('cart',$domain);
			foreach(session('domaincart'.session('store_id')) as $v){
				$arr = explode('.', $v);
				if(count($arr) == 2){
					$domaincart[$v] = $this->array[$arr[1]];
				}elseif(count($arr) == 3){
					$domaincart[$v] = $this->array[$arr[1].'_'.$arr[2]];
				}
			}
			$this->assign('cart2',$domaincart);
			$this->display();
		}
	}


	public function delcart()
	{
		if(IS_AJAX){
			$domain = str_replace('.', '_',I('k'));
			session('domaincart'.session('store_id').'.'.$domain,null);
			$this->ajaxReturn($domain);
		}else{

		}
	}





	/**
	 * 域名信息查询
	 */
	public function whois()
	{
		$domain = $_GET['d'];
		 $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, "http://whois.263.tw/weixinindex.php?domain={$domain}");
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        //显示获得的数据
        $this->assign('whois',$data);
        $this->display();
		
	}






	//域名查询
	public function domain_whois()
	{
		//查询购物车中的商品个数
		// $domain = M('domain')->where(array('userid'=>session('store_id'),'status'=>1))->count();
		$count = count(session('domaincart'.session('store_id')));
		$this->assign('count',$count);
		if($_GET['domain']){

			
			$domain = I('domain').'.'.str_replace('_', '.', I('suffix'));
			$d = explode('.', $domain);
			$count = (count($d) - 1);
			$strAction = "Domain.Whois";
			// 域名注册信息
			$aDomainRegInfo = array
			(
				// 所有人
				'domain'			=>	$domain,
				'year'				=>	"1",
				'tld'				=>	'.'.$d[ $count ],
				'lang'				=>	'ENG',
				'encoding'			=>	"ASCII",
			);
			$strResult = $this->doPost($aDomainRegInfo, $this->userid, $this->email, $this->password, $strAction);//调用接口查询
			$isMatched = preg_match_all('/\s/', $strResult, $matches);//匹配空白符
			//将空白符替换成点
			foreach($matches as $v){
				$s = str_replace($v, '.', $strResult);
			}
			//用点切割字符串
			$resarray = explode('.', $s);
			//用等号切割转换成索引数组
			foreach($resarray as $v){
				$r = explode('=', $v);
				$res[ $r[0] ] = @$r[1];
			}
			$res['domain'] = $domain;
			$res['price'] = $this->array[ I('suffix') ];
			if(IS_AJAX){
				$this->ajaxReturn($res);
			}
			$this->assign('res',$res);
			$this->display();

		}else{
			$domain = M('store')->where(array('store_id'=>session('store_id')))->field('domain')->find();
			$this->assign('domain',$domain);
			$this->display();
		}
	}



		// 接口API提交函数
		/*
		 * $args 接口信息提交数组
		 * $userid 用户API ID
		 * $email 用户登陆邮箱
		 * $password 用户API接口密码(非平台登陆密码)
		 * $action 接口动作
		*/
		protected function doPost($args, $userid, $email, $password, $action)
		{
			$strPostToUrl = "http://www.gzidc.com/api.php";

			// 构造要Post的字符串
			$postData = "";
			
			// 提交时间
			$ptime = date("YmdHis", time());
			$postData = "c_vtime=".$ptime;
			$postData .= "&c_agentid=".$userid;
			$postData .= "&c_action=".$action;
			$postData .= "&c_email=".$email;
			$postData .= "&c_validatestr=".md5($userid.$password.$email.$ptime);

			foreach ($args as $key => $value)
			{
				// 参数需要用urlencode进行URL编码
				$postData .= "&".$key."=".urlencode($value);
			}
			//echo $postData;

			// post字符串长度
			$postDataLength = strlen($postData);

			$url_info = parse_url($strPostToUrl);
			$apiHost = $url_info['host'];
			$apiPath = $url_info['path'];
			$apiPort = (isset($url_info['port'])) ? $url_info['port'] : 80;

			$fp = fsockopen($apiHost, $apiPort, $errno, $errstr, 30);
			if (!$fp)
			{
				die($errno." : ".$errstr);
			}

			$header = "POST ". $apiPath . " HTTP/1.1\r\n";
			$header .= "Host:" . $apiHost . "\r\n";
			$header .= "Content-Type: application/x-www-form-urlencoded\r\n"; 
			$header .= "Content-Length: ".$postDataLength."\r\n"; 
			$header .= "Connection: Close\r\n\r\n";

			//添加post的字符串 
			$header .= $postData."\r\n"; 

			//发送post的数据 
			fputs($fp,$header); 

			$inheader = 1; 
			$ret = "";
			
			while (! feof($fp))
			{
				$line = fgets($fp,1024); //去除请求包的头只显示页面的返回数据
				if ($inheader && ($line == "\n" || $line == "\r\n"))
				{
					$inheader = 0;
					continue;
				}
				if ($inheader == 0)
				{
					$ret.=$line;
				}
			}

			fclose($fp); 
			return $ret;
		}

   /**
    * 绑定独立域名 
    */
   public function store_domain()
   {
   	if(IS_POST){
// //    		$str = file_get_contents('/usr/local/apache2/etc/extra/httpd-vhosts.conf');
// //    		$str2 = str_replace("<VirtualHost *:80>
// //    ServerAdmin yd@admin.com
// //   DocumentRoot '/usr/local/apache2/htdocs/seller'
// //     ServerName zf.ydolm.com
// //    ErrorLog 'logs/dummy-host2.example.com-error_log'
// //   CustomLog 'logs/dummy-host2.example.com-access_log' common
// // </VirtualHost>", '11', $str);

// //    		dump($str2);exit;
//             $store = M('store');
//             $data['domain'] = I('post.domain');
//             if ($data['domain'] == '') {
//             	echo "<script>alert('请输入域名！');window.history.go(-1);</script>";exit;
//             }

//             system("nslookup  {$data['domain']}");
//             $str = ob_get_contents();
//             if(!preg_match('/47.90.81.73/', $str)){
//             	echo "<script>alert('请先把你的域名解析到www.yundi88.com,在绑定你的域名！');window.history.go(-1);</script>";exit;
//             }

//             if(M('store')->where(array('domain'=>$data['domain']))->find()){
//             	echo "<script>alert('此域名已经绑定！');window.history.go(-1);</script>";exit;
//             }


//             $domain = $store->where(array('store_id'=>session('store_id')))->field('domain')->find();
//             if($domain['domain']){
//                 echo "<script>alert('您已经绑定了独立域名，如要更换，请联系客服！');window.history.go(-1);</script>";exit;
//             }
//             $res = $store->where(array('store_id'=>session('store_id')))->save($data);


//             // $host2 = substr($data['domain'],4);

//             if($res){
// 	            	if(PHP_OS == 'Linux'){

// 	                	$filename = '/usr/local/apache2/etc/extra/httpd-vhosts.conf';
// 	            	}elseif(PHP_OS == 'WINNT'){

// 	                	echo "<script>alert('系统不支持！');window.history.go(-1);</script>";exit;
// 	            	}
//                 // 写入的字符
//                 $word = "
// <VirtualHost *:80>
//    ServerAdmin yd@admin.com
//   DocumentRoot '/usr/local/apache2/htdocs/seller'
//     ServerName {$data['domain']}
//    ErrorLog 'logs/dummy-host2.example.com-error_log'
//   CustomLog 'logs/dummy-host2.example.com-access_log' common
// </VirtualHost>

//                 ";

//                 $fh = fopen($filename, "a");
//                 $fwrite = fwrite($fh, $word); 
//                 fclose($fh);
//             }

//             if($fwrite){
//                   system("/usr/local/apache2/bin/apachectl -k graceful",$ress);//平滑重启apache
                 
//                   if($ress == 0){
//                     echo "<script>alert('绑定成功');window.history.go(-1);</script>";
//                     // $this->redirect('index/index');exit;
//                   }else{
//                     if($res){
//                     $data2['domain'] = null;
//                     $user->where('id = '.session('homeuser.id'))->save($data2);
//                     }
//                     echo "<script>alert('绑定失败！请联系客服！');window.history.go(-1);</script>";exit;
//                   }
//             }else{
//                 if($res){
//                     $data2['domain'] = null;
//                     $user->where('id = '.session('homeuser.id'))->save($data2);
//                 }
//                 echo "<script>alert('绑定失败！请重试！');window.history.go(-1);</script>";exit;
//             }




 			$store = M('store');
            $data['domain'] = trim(I('post.domain'));
            if ($data['domain'] == '') {
            	echo "<script>alert('请输入域名！');window.history.go(-1);</script>";exit;
            }

            // system("nslookup  {$data['domain']}");
            // $str = ob_get_contents();
            // exit($str);
            // if(!preg_match('/47.90.81.73/', $str)){
            // 	echo "<script>alert('请先把你的域名解析到customer.yundi88.com,再绑定你的域名！');window.history.go(-1);</script>";exit;
            // }

            if(M('store')->where(array('domain'=>$data['domain']))->find()){
            	echo "<script>alert('此域名已经绑定！');window.history.go(-1);</script>";exit;
            }


            $domain = $store->where(array('store_id'=>session('store_id')))->field('domain')->find();
            if($domain['domain']){
                echo "<script>alert('您已经绑定了独立域名，如要更换，请联系客服！');window.history.go(-1);</script>";exit;
            }

 			// $res = $store->where(array('store_id'=>session('store_id')))->save($data);

 			// if($res){
echo "<script>window.location.href='http://customer.yundi88.com/Domain/store_domain.html?domain=".$data['domain']."&store_id=".session('store_id')."'</script>";
 				// header('http://customer.yundi88.com/Domain/store_domain.thml?domain='.$data['domain']);
 			// }

   		

        }else{
			
            $this->display();
        }
   }



}
