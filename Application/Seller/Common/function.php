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
 * Date: 2015-09-09
 */

/**
 * 管理员操作记录
 * @param $log_url 操作URL
 * @param $log_info 记录信息
 */
function sellerLog($log_info) {
	$seller = session('seller');
	$add['log_time'] = time();
	$add['log_seller_id'] = $seller['seller_id'];
	$add['log_seller_name'] = $seller['seller_name'];
	$add['log_content'] = $log_info;
	$add['log_seller_ip'] = getIP();
	$add['log_store_id'] = $seller['store_id'];
	$add['log_url'] = __ACTION__;
	M('seller_log')->add($add);
}

function getAdminInfo($admin_id) {
	return D('admin')->where("admin_id=$admin_id")->find();
}

function tpversion() {
	if (!empty($_SESSION['isset_push'])) {
		return false;
	}

	$_SESSION['isset_push'] = 1;
	error_reporting(0); //关闭所有错误报告
	$app_path = dirname($_SERVER['SCRIPT_FILENAME']) . '/';
	$version_txt_path = $app_path . '/Application/Admin/Conf/version.txt';
	$curent_version = file_get_contents($version_txt_path);

	$vaules = array(
		'domain' => $_SERVER['HTTP_HOST'],
		'last_domain' => $_SERVER['HTTP_HOST'],
		'key_num' => $curent_version,
		'install_time' => INSTALL_DATE,
		'cpu' => '0001',
		'mac' => '0002',
		'serial_number' => SERIALNUMBER,
	);
	$url = "http://service.tp-shop.cn/index.php?m=Home&c=Index&a=user_push&" . http_build_query($vaules); // 检测版本升级
	stream_context_set_default(array('http' => array('timeout' => 3)));
	file_get_contents($url);
}

/**
 * 面包屑导航  用于后台管理
 * 根据当前的控制器名称 和 action 方法
 */
function navigate_admin() {
	$navigate = include APP_PATH . 'Common/Conf/navigate.php';
	$location = strtolower('Seller/' . CONTROLLER_NAME);
	$arr = array(
		'后台首页' => 'javascript:void();',
		$navigate[$location]['name'] => 'javascript:void();',
		$navigate[$location]['action'][ACTION_NAME] => 'javascript:void();',
	);
	return $arr;
}

/**
 * 导出excel
 * @param $strTable	表格内容
 * @param $filename 文件名
 */
function downloadExcel($strTable, $filename) {
	header("Content-type: application/vnd.ms-excel");
	header("Content-Type: application/force-download");
	header("Content-Disposition: attachment; filename=" . $filename . "_" . date('Y-m-d') . ".xls");
	header('Expires:0');
	header('Pragma:public');
	echo '<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . $strTable . '</html>';
}

/**
 * 格式化字节大小
 * @param  number $size      字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 */
function format_bytes($size, $delimiter = '') {
	$units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
	for ($i = 0; $size >= 1024 && $i < 5; $i++) {
		$size /= 1024;
	}

	return round($size, 2) . $delimiter . $units[$i];
}

/**
 * 根据id获取地区名字
 * @param $regionId id
 */
function getRegionName($regionId) {
	$data = M('region')->where(array('id' => $regionId))->field('name')->find();
	return $data['name'];
}

function respose($res) {
	header('Content-type:text/json');
	exit(json_encode($res));
}

function getMenuList() {
	$menu_list = array(
		'userinfo' => array('name' => '会员中心', 'icon' => 'fa-user','child' => array(
			array('name' => '个人信息','act' => 'info' ,'op'=>'Userinfo'),
			array('name' => '地址管理','act' => 'address_list','op'=>'Userinfo'),
			array('name' => '安全中心','act' => 'safety_settings','op'=>'Userinfo'),
			array('name' => '我的订单','act' => 'order_list','op'=>'Userinfo'),
			)),
		'storee' => array('name' => '网站模板', 'icon' => 'fa-gift','child' => array(
			array('name' => '手机模板', 'act' => 'store_mtpl', 'op' => 'Store'),
			array('name' => '官网模板', 'act' => 'store_tpl?t=pc&tpl=fuzhuang&layer=1', 'op' => 'Store'),
			array('name' => '微商模板', 'act' => 'store_weixin', 'op' => 'Store'),
			)),
		'Newjoin' =>array('name' => '真实认证', 'icon' => 'fa-bell','child' => array(
			array('name' => '企业认证', 'act' => 'basic_info','op' => 'Newjoin'),
			array('name' => '个体认证', 'act' => 'basic','op' => 'Newjoin'),
			)),
		'store' => array('name' => '网站建设', 'icon' => 'fa-cog', 'child' => array(
			array('name' => '网站设置', 'act' => 'store_setting', 'op' => 'Store'),
			/*array('name' => '网站装修', 'act'=>'store_decoration', 'op'=>'Store'),*/
			array('name' => '首页编辑', 'act' => 'store_mod', 'op' => 'Store'),
			// array('name' => '导航分类', 'act' => 'navigation_art_list', 'op' => 'Store'),
			array('name' => '导航设置', 'act' => 'navigation_list', 'op' => 'Store'),
			array('name' => '经营类目', 'act'=>'bind_class_list', 'op'=>'Store'),
			// array('name' => '网站信息', 'act' => 'store_info', 'op' => 'Store'),
			// array('name' => '网站关注', 'act' => 'store_collect', 'op' => 'Store'),
			array('name' => '绑定域名', 'act' => 'domain_whois', 'op' => 'Domain'),
		)),
		'news' => array('name' => '新闻推广', 'icon' => 'fa-file', 'child' => array(
			array('name' => '新闻发布', 'act' => 'addNews', 'op' => 'News'), ///index.php/Seller/goods/addEditGoods.html'
			array('name' => '新闻管理', 'act' => 'newslist', 'op' => 'News'),
		)),
		'goods' => array('name' => '产品推广', 'icon' => 'fa-tasks', 'child' => array(
			array('name' => '产品发布', 'act' => 'addEditGoods', 'op' => 'Goods'), ///index.php/Seller/goods/addEditGoods.html'
			//array('name' => '淘宝导入', 'act'=>'import', 'op'=>'index'),             //临时屏蔽淘宝商品导入
			array('name' => '产品分类', 'act' => 'goods_class', 'op' => 'Store'),
			array('name' => '产品管理', 'act' => 'goodsList?goods_state=1', 'op' => 'Goods'),
			array('name' => '供应产品banner图', 'act' => 'pro_banner', 'op' => 'store'),
			// array('name' => '商品规格', 'act' => 'specList', 'op' => 'Goods'),
			// array('name' => '仓库中的商品', 'act' => 'goodsList?goods_state=0,2,3', 'op' => 'Goods'),
			//array('name' => '关联版式', 'act'=>'store_plate', 'op'=>'index'),
			// array('name' => '品牌申请', 'act'=>'brandList', 'op'=>'Goods'),
			//array('name' => '图片空间', 'act'=>'store_album', 'op'=>'album_cate'),
		)),

		'photo' => array('name' => '相册管理', 'icon' => 'fa-camera', 'child' => array(
			array('name' => '相册发布', 'act' => 'addphoto', 'op' => 'Photo'), ///index.php/Seller/goods/addEditGoods.html'
			array('name' => '相册查看', 'act' => 'index', 'op' => 'Photo'),
			array('name' => '相册banner图', 'act' => 'pho_banner', 'op' => 'Photo'),
		)),
		'order' => array('name' => '订单物流', 'icon' => 'fa-money', 'child' => array(
			array('name' => '订单列表', 'act' => 'index', 'op' => 'Order'),
			array('name' => '发货', 'act' => 'delivery_list', 'op' => 'Order'),
			array('name' => '发货设置', 'act' => 'index', 'op' => 'Plugin'),
			//array('name' => '运单模板', 'act'=>'store_waybill', 'op'=>'waybill_manage'),
			// array('name' => '商品评论', 'act' => 'index', 'op' => 'Comment'),
			// array('name' => '商品咨询', 'act' => 'ask_list', 'op' => 'Comment'),
		)),
		'friend_link' => array('name' => '友情链接', 'icon' => 'fa-glass', 'act' => '/seller/article/store_linkList'),
		// 'promotion' => array('name' => '促销管理', 'icon' => 'fa-bell', 'child' => array(
		// 	array('name' => '抢购管理', 'act' => 'flash_sale', 'op' => 'Promotion'),
		// 	array('name' => '团购管理', 'act' => 'group_buy_list', 'op' => 'Promotion'),
		// 	array('name' => '商品促销', 'act' => 'prom_goods_list', 'op' => 'Promotion'),
		// 	array('name' => '订单促销', 'act' => 'prom_order_list', 'op' => 'Promotion'),
		// 	array('name' => '代金券管理', 'act' => 'index', 'op' => 'Coupon'),
		// 	//array('name' => '分销管理', 'act'=>'store_activity', 'op'=>'promotion'),
		// )),
		'message' => array('name' => '客服消息', 'icon' => 'fa-comments', 'child' => array(
			array('name' => '客服设置', 'act' => 'store_service', 'op' => 'Index'),
			// array('name' => '系统消息', 'act' => 'store_msg', 'op' => 'Index'),
			array('name' => '客户留言', 'act' => 'store_email', 'op' => 'Index'),
			array('name' => '留言banner图', 'act' => 'store_mes', 'op' => 'Index'),
			//array('name' => '聊天记录查询', 'act'=>'store_im', 'op'=>'store'),
		)),

		// 'statistics' => array('name' => '统计报表', 'icon' => 'fa-signal', 'child' => array(
		// 	array('name' => '官网概况', 'act' => 'index', 'op' => 'Report'),
		// 	array('name' => '商品分析', 'act' => 'saleTop', 'op' => 'Report'),
		// 	array('name' => '运营报告', 'act' => 'finance', 'op' => 'Report'),
		// 	array('name' => '销售排行', 'act' => 'saleTop', 'op' => 'Report'),
		// 	array('name' => '流量统计', 'act' => 'visit', 'op' => 'Report'),
		// )),
		'consult' => array('name' => '售后服务', 'icon' => 'fa-flag', 'child' => array(
			// array('name' => '咨询管理', 'act' => 'ask_list', 'op' => 'Comment'),
			//array('name' => '退款记录', 'act'=>'store_refund', 'op'=>'Order'),
			array('name' => '退款换货', 'act' => 'return_list', 'op' => 'Order'),
			// array('name' => '投诉管理', 'act' => 'complain_list', 'op' => 'Comment'),
		)),
		'account' => array('name' => '账号管理', 'icon' => 'fa-home', 'child' => array(
			array('name' => '账号列表', 'act' => 'index', 'op' => 'Admin'),
			array('name' => '账号组', 'act' => 'role', 'op' => 'Admin'),
			array('name' => '账号日志', 'act' => 'log', 'op' => 'Admin'),
			//array('name' => '官网消费', 'act'=>'store_cost', 'op'=>'cost_list'),
		)),
		'account' => array('name' => '企业云谱', 'icon' => 'fa-book', 'child' => array(
			array('name' => '我的云谱', 'act' => 'baike', 'op' => 'Finance'),
		)),
		'account' => array('name' => '采购管理', 'icon' => 'fa-book', 'child' => array(
			array('name' => '发布我的需求', 'act' => 'purchase', 'op' => 'Purchase'),
			array('name' => '我的需求管理', 'act' => 'index', 'op' => 'Purchase'),
			// array('name' => '推送采购单', 'act' => 'push', 'op' => 'Purchase'),
			array('name' => '采购需求通知', 'act' => 'purMsg', 'op' => 'Purchase'),
			array('name' => '我已接单', 'act' => 'myPurchase', 'op' => 'Purchase'),
		)),
		// http://www.tpshop.com/Admin/Distribut/remittance
		// 'finance' => array('name' => '财务管理', 'icon' => 'fa-book', 'child' => array(
		// 	array('name' => '提现申请', 'act' => 'withdrawals', 'op' => 'Finance'),
		// 	array('name' => '汇款记录', 'act' => 'remittance', 'op' => 'Finance'),
		// 	array('name' => '商家结算记录', 'act' => 'order_statis', 'op' => 'Finance'),
		// 	array('name' => '未结算订单', 'act' => 'order_no_statis', 'op' => 'Finance'),
		// 	array('name' => '我的余额','act' => 'recharge','op'=>'Userinfo'),
		// )),
		/*
			                        // http://www.tp-shop.cn/Admin/Distribut/rebate_log     /index.php/Seller/Store/distribut
						'distribut' => array('name' => '分销管理', 'icon'=>'fa-cubes', 'child' => array(
								array('name' => '分销商品', 'act'=>'goods_list', 'op'=>'Distribut'),
								array('name' => '分销设置', 'act'=>'distribut', 'op'=>'Store'),
			                                        array('name' => '分成记录', 'act'=>'rebate_log', 'op'=>'Distribut'),
						)),
		*/
	);
	return $menu_list;
}



function permissions()
{
	return array(
		array('name'=>'个人中心',
			'child'=>array(
							array('name'=>'个人信息','f'=>'Userinfo@info'),
							array('name'=>'安全中心','f'=>'Userinfo@safety_settings,Userinfo@password,Userinfo@mobile_validate,Userinfo@email_validate'),
							array('name'=>'个人认证','f'=>'Newjoin@basic,Newjoin@info_edit'),
				),
		),
		array('name'=>'网站模板',
			'child'=>array(
							array('name'=>'商城模板','f'=>'Store@store_tpl,Store@changeTemplate'),
							array('name'=>'手机模板','f'=>'Store@store_mtpl,Store@configmobile'),
							// array('name'=>'微商城模板','f'=>'Newjoin@basic,Newjoin@info_edit'),
				),
		),
		array('name'=>'企业认证',
			'child'=>array(
							array('name'=>'企业信息','f'=>'Newjoin@basic_info'),
				),
		),
		array('name'=>'网站建设',
			'child'=>array(
							array('name'=>'网站设置','f'=>'Store@store_setting,Store@setting_save'),
							array('name'=>'首页编辑','f'=>'Store@store_mod,store@modHandle'),
							array('name'=>'导航设置','f'=>'Store@navigation_list,Store@navigation,Store@navigationHandle'),
							array('name'=>'经营类目','f'=>'Store@bind_class_list,Store@store_info,'),
							array('name'=>'绑定域名','f'=>'Domain@domain_whois,Domain@store_domain'),
				),
		),
		array('name'=>'新闻推广',
			'child'=>array(
							array('name'=>'新闻发布','f'=>'News@addNews,News@newsHandle'),
							array('name'=>'新闻管理','f'=>'News@newslist,News@uddNews'),
				),
		),
		array('name'=>'产品推广',
			'child'=>array(
							array('name'=>'产品发布','f'=>'Goods@addEditGoods'),
							array('name'=>'产品分类','f'=>'Store@goods_class'),
							array('name'=>'产品管理','f'=>'Goods@goodsList,Goods@addEditGoods'),
							array('name'=>'产品规格','f'=>'Goods@specList'),
				),
		),
		array('name'=>'相册管理',
			'child'=>array(
							array('name'=>'相册发布','f'=>'Photo@addphoto'),
							array('name'=>'查看相册','f'=>'Photo@index,Photo@addphoto'),
				),
		),
		array('name'=>'订单管理',
			'child'=>array(
							array('name'=>'订单列表','f'=>'Order@index'),
							array('name'=>'订单查看','f'=>'order@detail'),
							array('name'=>'订单查看','f'=>'order@detail'),
							array('name'=>'订单修改','f'=>'order@edit_order'),
							array('name'=>'打印订单','f'=>'Order@order_print'),
							array('name'=>'发货','f'=>'Order@delivery_list,Order@delivery_info,Order@deliveryHandle'),
							array('name'=>'发货设置','f'=>'Seller@Plugin/index,Plugin@shipping_list,Plugin@shipping_list_edit'),
							array('name'=>'商品评论','f'=>'Comment@index'),
							array('name'=>'商品咨询','f'=>'Comment@ask_list'),
				),
		),
		array('name'=>'促销管理',
			'child'=>array(
							array('name'=>'抢购管理','f'=>'Promotion@flash_sale,Promotion@flash_sale_info'),
							array('name'=>'团购管理','f'=>'Promotion@group_buy_list,Promotion@group_buy'),
							array('name'=>'商品促销','f'=>'Promotion@prom_goods_list,Promotion@prom_goods_info'),
							array('name'=>'订单促销','f'=>'Promotion@prom_order_list,Promotion@prom_order_info,Promotion@prom_order_save'),
							array('name'=>'代金卷管理','f'=>'Coupon@index,Coupon@coupon_info'),
				),
		),
		array('name'=>'客服消息',
			'child'=>array(
							array('name'=>'客服设置','f'=>'Index@store_service,Index@store_service'),
							array('name'=>'系统消息','f'=>'Index@store_msg'),
							array('name'=>'客户留言','f'=>'Index@store_email'),
							array('name'=>'留言banner图','f'=>'Index@store_mes'),
				),
		),
		array('name'=>'统计报表',
			'child'=>array(
							array('name'=>'官网概况','f'=>'Report@index'),
							array('name'=>'商品分析','f'=>'Report@saleTop'),
							array('name'=>'运营报告','f'=>'Report@finance'),
							array('name'=>'销售排行','f'=>'Report@saleTop'),
							array('name'=>'流量统计','f'=>'Report@visit'),

				),
		),
		array('name'=>'售后服务',
			'child'=>array(
							array('name'=>'咨询管理','f'=>'Comment@ask_list'),
							array('name'=>'退换货','f'=>'Order@return_list'),
							array('name'=>'投诉管理','f'=>'Comment@complain_list'),

				),
		),
		array('name'=>'账号管理',
			'child'=>array(
							array('name'=>'账号列表','f'=>'Admin@index'),
							array('name'=>'账号组','f'=>'Admin@role,Admin@role_info'),
							array('name'=>'账号日志','f'=>'Admin@log'),

				),
		),

	);
}



function alipayconfig()
{
		$paymentPlugin = M('Plugin')->where("code='alipay' and  type = 'payment' ")->find(); // 找到支付插件的配置
        $config_value = unserialize($paymentPlugin['config_value']); // 配置反序列化
	return array(

        'alipay_pay_method'=> $config_value['alipay_pay_method'], // 1 使用担保交易接口  2 使用即时到帐交易接口s
        'partner'       => $config_value['alipay_partner'],//合作身份者id，以2088开头的16位纯数字
        'seller_email'  => $config_value['alipay_account'],//收款支付宝账号，一般情况下收款账号就是签约账号
        'key'	      => $config_value['alipay_key'],//安全检验码，以数字和字母组成的32位字符
        // 服务器异步通知页面路径  需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
		'notify_url' => "http://{$_SERVER['HTTP_HOST']}/Seller/alipay/notifyurl.php",

		// 页面跳转同步通知页面路径 需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
		'return_url' => "http://{$_SERVER['HTTP_HOST']}/Seller/alipay/returnurl.php",

        'sign_type'    => strtoupper('MD5'),//签名方式 不需修改
        'input_charset' => strtolower('utf-8'),//字符编码格式 目前支持 gbk 或 utf-8
        'cacert'        => getcwd().'\\cacert.pem', //ca证书路径地址，用于curl中ssl校验 //请保证cacert.pem文件在当前文件夹目录中
        'transport'     => 'http',//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
       	'payment_type' => 1,

		// 产品类型，无需修改
		'service' => "create_direct_pay_by_user",
		// 防钓鱼时间戳  若要使用请调用类文件submit中的query_timestamp函数
		'anti_phishing_key' => "",

		// 客户端的IP地址 非局域网的外网IP地址，如：221.0.0.1
		'exter_invoke_ip' => "",

		);
}



function pinyin($_String, $_Code='UTF-8')
{
$_DataKey = "a|ai|an|ang|ao|ba|bai|ban|bang|bao|bei|ben|beng|bi|bian|biao|bie|bin|bing|bo|bu|ca|cai|can|cang|cao|ce|ceng|cha".
"|chai|chan|chang|chao|che|chen|cheng|chi|chong|chou|chu|chuai|chuan|chuang|chui|chun|chuo|ci|cong|cou|cu|".
"cuan|cui|cun|cuo|da|dai|dan|dang|dao|de|deng|di|dian|diao|die|ding|diu|dong|dou|du|duan|dui|dun|duo|e|en|er".
"|fa|fan|fang|fei|fen|feng|fo|fou|fu|ga|gai|gan|gang|gao|ge|gei|gen|geng|gong|gou|gu|gua|guai|guan|guang|gui".
"|gun|guo|ha|hai|han|hang|hao|he|hei|hen|heng|hong|hou|hu|hua|huai|huan|huang|hui|hun|huo|ji|jia|jian|jiang".
"|jiao|jie|jin|jing|jiong|jiu|ju|juan|jue|jun|ka|kai|kan|kang|kao|ke|ken|keng|kong|kou|ku|kua|kuai|kuan|kuang".
"|kui|kun|kuo|la|lai|lan|lang|lao|le|lei|leng|li|lia|lian|liang|liao|lie|lin|ling|liu|long|lou|lu|lv|luan|lue".
"|lun|luo|ma|mai|man|mang|mao|me|mei|men|meng|mi|mian|miao|mie|min|ming|miu|mo|mou|mu|na|nai|nan|nang|nao|ne".
"|nei|nen|neng|ni|nian|niang|niao|nie|nin|ning|niu|nong|nu|nv|nuan|nue|nuo|o|ou|pa|pai|pan|pang|pao|pei|pen".
"|peng|pi|pian|piao|pie|pin|ping|po|pu|qi|qia|qian|qiang|qiao|qie|qin|qing|qiong|qiu|qu|quan|que|qun|ran|rang".
"|rao|re|ren|reng|ri|rong|rou|ru|ruan|rui|run|ruo|sa|sai|san|sang|sao|se|sen|seng|sha|shai|shan|shang|shao|".
"she|shen|sheng|shi|shou|shu|shua|shuai|shuan|shuang|shui|shun|shuo|si|song|sou|su|suan|sui|sun|suo|ta|tai|".
"tan|tang|tao|te|teng|ti|tian|tiao|tie|ting|tong|tou|tu|tuan|tui|tun|tuo|wa|wai|wan|wang|wei|wen|weng|wo|wu".
"|xi|xia|xian|xiang|xiao|xie|xin|xing|xiong|xiu|xu|xuan|xue|xun|ya|yan|yang|yao|ye|yi|yin|ying|yo|yong|you".
"|yu|yuan|yue|yun|za|zai|zan|zang|zao|ze|zei|zen|zeng|zha|zhai|zhan|zhang|zhao|zhe|zhen|zheng|zhi|zhong|".
"zhou|zhu|zhua|zhuai|zhuan|zhuang|zhui|zhun|zhuo|zi|zong|zou|zu|zuan|zui|zun|zuo";
$_DataValue = "-20319|-20317|-20304|-20295|-20292|-20283|-20265|-20257|-20242|-20230|-20051|-20036|-20032|-20026|-20002|-19990".
"|-19986|-19982|-19976|-19805|-19784|-19775|-19774|-19763|-19756|-19751|-19746|-19741|-19739|-19728|-19725".
"|-19715|-19540|-19531|-19525|-19515|-19500|-19484|-19479|-19467|-19289|-19288|-19281|-19275|-19270|-19263".
"|-19261|-19249|-19243|-19242|-19238|-19235|-19227|-19224|-19218|-19212|-19038|-19023|-19018|-19006|-19003".
"|-18996|-18977|-18961|-18952|-18783|-18774|-18773|-18763|-18756|-18741|-18735|-18731|-18722|-18710|-18697".
"|-18696|-18526|-18518|-18501|-18490|-18478|-18463|-18448|-18447|-18446|-18239|-18237|-18231|-18220|-18211".
"|-18201|-18184|-18183|-18181|-18012|-17997|-17988|-17970|-17964|-17961|-17950|-17947|-17931|-17928|-17922".
"|-17759|-17752|-17733|-17730|-17721|-17703|-17701|-17697|-17692|-17683|-17676|-17496|-17487|-17482|-17468".
"|-17454|-17433|-17427|-17417|-17202|-17185|-16983|-16970|-16942|-16915|-16733|-16708|-16706|-16689|-16664".
"|-16657|-16647|-16474|-16470|-16465|-16459|-16452|-16448|-16433|-16429|-16427|-16423|-16419|-16412|-16407".
"|-16403|-16401|-16393|-16220|-16216|-16212|-16205|-16202|-16187|-16180|-16171|-16169|-16158|-16155|-15959".
"|-15958|-15944|-15933|-15920|-15915|-15903|-15889|-15878|-15707|-15701|-15681|-15667|-15661|-15659|-15652".
"|-15640|-15631|-15625|-15454|-15448|-15436|-15435|-15419|-15416|-15408|-15394|-15385|-15377|-15375|-15369".
"|-15363|-15362|-15183|-15180|-15165|-15158|-15153|-15150|-15149|-15144|-15143|-15141|-15140|-15139|-15128".
"|-15121|-15119|-15117|-15110|-15109|-14941|-14937|-14933|-14930|-14929|-14928|-14926|-14922|-14921|-14914".
"|-14908|-14902|-14894|-14889|-14882|-14873|-14871|-14857|-14678|-14674|-14670|-14668|-14663|-14654|-14645".
"|-14630|-14594|-14429|-14407|-14399|-14384|-14379|-14368|-14355|-14353|-14345|-14170|-14159|-14151|-14149".
"|-14145|-14140|-14137|-14135|-14125|-14123|-14122|-14112|-14109|-14099|-14097|-14094|-14092|-14090|-14087".
"|-14083|-13917|-13914|-13910|-13907|-13906|-13905|-13896|-13894|-13878|-13870|-13859|-13847|-13831|-13658".
"|-13611|-13601|-13406|-13404|-13400|-13398|-13395|-13391|-13387|-13383|-13367|-13359|-13356|-13343|-13340".
"|-13329|-13326|-13318|-13147|-13138|-13120|-13107|-13096|-13095|-13091|-13076|-13068|-13063|-13060|-12888".
"|-12875|-12871|-12860|-12858|-12852|-12849|-12838|-12831|-12829|-12812|-12802|-12607|-12597|-12594|-12585".
"|-12556|-12359|-12346|-12320|-12300|-12120|-12099|-12089|-12074|-12067|-12058|-12039|-11867|-11861|-11847".
"|-11831|-11798|-11781|-11604|-11589|-11536|-11358|-11340|-11339|-11324|-11303|-11097|-11077|-11067|-11055".
"|-11052|-11045|-11041|-11038|-11024|-11020|-11019|-11018|-11014|-10838|-10832|-10815|-10800|-10790|-10780".
"|-10764|-10587|-10544|-10533|-10519|-10331|-10329|-10328|-10322|-10315|-10309|-10307|-10296|-10281|-10274".
"|-10270|-10262|-10260|-10256|-10254";
$_TDataKey = explode('|', $_DataKey);
$_TDataValue = explode('|', $_DataValue);
$_Data = (PHP_VERSION>='5.0') ? array_combine($_TDataKey, $_TDataValue) : _Array_Combine($_TDataKey, $_TDataValue);
arsort($_Data);
reset($_Data);
if($_Code != 'gb2312') $_String = _U2_Utf8_Gb($_String);
$_Res = '';
for($i=0; $i<strlen($_String); $i++)
{
$_P = ord(substr($_String, $i, 1));
if($_P>160) { $_Q = ord(substr($_String, ++$i, 1)); $_P = $_P*256 + $_Q - 65536; }
$_Res .= _Pinyin($_P, $_Data);
}
return preg_replace("/[^a-z0-9]*/", '', $_Res);
}
function _Pinyin($_Num, $_Data)
{
if ($_Num>0 && $_Num<160 ) return chr($_Num);
elseif($_Num<-20319 || $_Num>-10247) return '';
else {
foreach($_Data as $k=>$v){ if($v<=$_Num) break; }
return $k;
}
}
function _U2_Utf8_Gb($_C)
{
$_String = '';
if($_C < 0x80) $_String .= $_C;
elseif($_C < 0x800)
{
$_String .= chr(0xC0 | $_C>>6);
$_String .= chr(0x80 | $_C & 0x3F);
}elseif($_C < 0x10000){
$_String .= chr(0xE0 | $_C>>12);
$_String .= chr(0x80 | $_C>>6 & 0x3F);
$_String .= chr(0x80 | $_C & 0x3F);
} elseif($_C < 0x200000) {
$_String .= chr(0xF0 | $_C>>18);
$_String .= chr(0x80 | $_C>>12 & 0x3F);
$_String .= chr(0x80 | $_C>>6 & 0x3F);
$_String .= chr(0x80 | $_C & 0x3F);
}
return iconv('UTF-8', 'GB2312', $_String);
}
function _Array_Combine($_Arr1, $_Arr2)
{
for($i=0; $i<count($_Arr1); $i++) $_Res[$_Arr1[$i]] = $_Arr2[$i];
return $_Res;
}
