

var browser = {
versions: function () {
var u = navigator.userAgent, app = navigator.appVersion;
return { //移动终端浏览器版本信息 
ios: !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), //ios终端 
android: u.indexOf('Android') > -1 || u.indexOf('Linux') > -1, //android终端或uc浏览器 
iPhone: u.indexOf('iPhone') > -1, //是否为iPhone或者QQHD浏览器 
iPad: u.indexOf('iPad') > -1, //是否iPad 
};
}(),
};
if (browser.versions.iPhone || browser.versions.iPad || browser.versions.ios) {
    // document.write('<h1>苹果</h1>');  
    document.write('<link rel="stylesheet" type="text/css" href="/Template/mobile/yundi/Static/css/ios.css"/>');  
}
if (browser.versions.android) {
// document.write('<h2>安卓</h2>');  
// document.write('<link rel="stylesheet" type="text/css" href=""/>');  
}

// var winW = document.documentElement.clientWidth;
// var higH = document.documentElement.clientHeight;
// console.log("宽" + winW)  
// console.log("高" + higH)  


$(function(){

	// home
	$(".w_order_btn").click(function(){
		event.stopPropagation()
		if($(".w_order").css("display") == "none"){
			$(".w_order").stop(true,true).slideDown(300);
	        $(".w_order_btn").css("background","#f5f5f5");
		}else{
			$(".w_order").stop(true,true).hide();
	        $(".w_order_btn").css("background","#ffffff");
		}
	})
	$(document).bind("touchstart",function(e){
		$(".w_order").stop(true,true).slideUp(300);
		$(".w_order_btn").css("background","#ffffff");
    });

	$(".w_more_pro").click(function(){
		$(".w_peer_pro_c ul li").addClass("hov");
	})
	// home

	// classify
	$(".w_con_tit ul li:first-child").addClass("act");
	$(".w_poster>ul").eq(0).show();
	$(".w_list>ul").eq(0).show();

	$(".w_con_tit ul li").click(function(){
		var onli = $(this).index();
		$(this).addClass("act").siblings("li").removeClass("act");
		$(".w_poster>ul").eq(onli).show().siblings("ul").hide();
		$(".w_list>ul").eq(onli).show().siblings("ul").hide();
	})
	$(".w_change>a").click(function(){
		$(".w_con_c>div").toggle();

	})
	// classify

	// info
	// $(".w_info_li").each(function(lis){


	// 	// var lis = $(".w_info>ul>li").length;
	// 	// alert(lis)
	// 	for(i=0;i<lis;i++){
	// 		var hig = parseFloat($(".w_info>ul>li").eq(i).find(".w_info_text").find("p").css('line-height'));
	// 		var hig = 3*hig;
	// 		if($(".w_info>ul>li").eq(i).find(".w_info_text").find("p").height() > hig){
	// 			$(".w_info>ul>li").eq(i).find(".w_info_text").find(".w_info_btn").addClass("on");
	// 			$(".w_info>ul>li").eq(i).find(".w_info_text").find("p").addClass("default");
	// 		}
	// 	}
	// 	$(".w_info_btn").click(function(){
	// 		var _text = $(this).html();
	// 		if(_text == "点击展开"){
	// 			$(this).siblings('p').removeClass("default");
	// 			$(this).siblings('p').addClass("hov");
	// 			$(this).html("收起");
	// 		}else{
	// 			$(this).siblings('p').removeClass("hov");
	// 			$(this).siblings('p').addClass("default");
	// 			$(this).html("点击展开");
	// 		}
			
	// 	})
	// })

	// info




// 回到顶部
  $(window).scroll(function() {
    if ($(window).scrollTop() > 100) {
      $("#w_top").fadeIn(500);
    } else {
      $("#w_top").fadeOut(500);
    }
  });
  //当点击跳转链接后，回到页面顶部位置
  $("#w_top").click(function() {
    $('body,html').animate({
      scrollTop: 0
    },
    300);
    return false;
  });

})


function getsubstr(kk)
{
	var str = $('#str_'+kk).html();
	$('#str_'+kk).html(str.substr(0,50));
	$('#str_'+kk+'s').css('display','');
	$('#str_'+kk+'_s').css('display','none');
}