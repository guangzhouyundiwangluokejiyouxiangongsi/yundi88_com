$(window).load(function(){
	//banner
	var swiper = new Swiper('.swiper-container', {
		pagination : '.pager',
		loop:true,
		grabCursor: true,	
		autoplay: 2000,
		autoplayDisableOnInteraction : false
	});
    $('.pager .swiper-pagination-switch').click(function(){
    	swiper.swipeTo($(this).index())
    });
    
    //header
    $('.d_header .d_nav li').click(function(){
    	$(this).addClass('active').siblings().removeClass('active');   	
    });
      
    // //弹窗
    // $('.d_prodetails .d_style a').click(function(){
    // 	setTimeout(function(){
    //         $(".d_cpm .f_prompt").fadeOut(200);
    //     },1000)
    // 	$('.d_cpm').animate({'bottom':'0%'},200);
    // 	$('.d_shade').animate({'bottom':'0%'},100); 
    // 	$('.d_cpm').css('display','block');
    // 	$('.d_shade').css('display','block');
    // });   
    // $('.d_footer .d_addshop').click(function(){  
    // 	setTimeout(function(){
    //         $(".d_cpm .f_prompt").fadeOut(200);
    //     },1000)
    // 	$('.d_cpm').animate({'bottom':'0%'},200);
    // 	$('.d_shade').animate({'bottom':'0%'},100);  
    // 	$('.d_cpm').css('display','block');
    // 	$('.d_shade').css('display','block');
    // });
    // $('.d_cpm .d_miss').click(function(){
    // 	$('.d_cpm').animate({'bottom':'-100%'},200);
    // 	$('.d_shade').animate({'bottom':'100%'},100);
    // });
    // $('.d_shade').click(function(){
    // 	$('.d_cpm').animate({'bottom':'-100%'},200);
    // 	$(this).animate({'bottom':'100%'},100);
    // });
    
    //选择款式  
    // $(".d_cpm .d_numA span").click(function(){
    // 	txtA = $(this).text();
    //     $(".choose").hide();
    //     $(".chosen").show();
    //     $(".chosen .color").text('"' + txtA + '"');
    //     $(this).addClass("active").siblings().removeClass("active");
    //     if($(".chosen .color").text() == "" || $(".chosen .size").text() == ""){
    //         $(".chosen .hov").hide();
    //     }else{
    //         $(".chosen .hov").show();
    //     }
    // })
    // $(".d_cpm .d_numB span").click(function(){
    // 	txtB = $(this).text();
    //     $(".choose").hide();
    //     $(".chosen").show();
    //     $(".chosen .size").text('"' + txtB + '"');
    //     $(this).addClass("active").siblings().removeClass('active');
    //     if($(".chosen .color").text() == "" || $(".chosen .size").text() == ""){
    //         $(".chosen .hov").hide();
    //     }else{
    //         $(".chosen .hov").show();
    //     }
    // })
    
    //计算
    $('.d_cpm .d_shopcar .reduct').click(function(){   	
    	numberA = $('.d_cpm .d_shopcar input');
    	count = parseInt(numberA.val());  
    	if(count<=2){
    		count = 2;
    		$(this).removeClass('in');
    	}else{
    		$(this).addClass('in').siblings('b').removeClass('in');
    	}
    	numberA.val(--count);
    	
    });
    $('.d_cpm .d_shopcar .add').click(function(){
    	numberA = $('.d_cpm .d_shopcar input');
    	count = parseInt(numberA.val());
    	if(count>100){
    		count = 99;
    		$(this).removeClass('in');
    	}else{
    		$(this).addClass('in').siblings('b').removeClass('in');
    	}
    	numberA.val(++count);
    });
    
    //详情确认
    // $(".d_cpm .confirm").click(function(){
    //     if($(".chosen .color").text() == "" && $(".chosen .size").text() == ""){
    //         $(".d_cpm .f_prompt").text("请选择商品颜色和尺码");
    //         $(".d_cpm .f_prompt").fadeIn(300);
    //         setTimeout(function(){
    //             $(".d_cpm .f_prompt").fadeOut(500);
    //         },1000)
    //         return false;
    //     }else if($(".chosen .color").text() == "" && $(".chosen .size").text() != ""){
    //         $(".d_cpm .f_prompt").text("请选择商品颜色");
    //         $(".d_cpm .f_prompt").fadeIn(300);
    //         setTimeout(function(){
    //             $(".d_cpm .f_prompt").fadeOut(500);
    //         },1000)
    //         return false;
    //     }else if($(".color").text() != "" && $(".size").text() == ""){
    //         $(".d_cpm .f_prompt").text("请选择商品尺码");
    //         $(".d_cpm .f_prompt").fadeIn(300);
    //         setTimeout(function(){
    //             $(".d_cpm .f_prompt").fadeOut(500);
    //         },1000)
    //         return false;
    //     }else{
    //     	numberA = $('.d_cpm .d_shopcar input');
    //     	count = parseInt(numberA.val());      	
    //     	$('.d_cpm').animate({'bottom':'-100%'},200);
    // 		$('.d_shade').animate({'bottom':'100%'},100);
    // 		$('.d_header .d_shop i').css('display','block');
    // 		$('.d_prodetails .d_style span').text('已选择' + '"' + txtA + '"' + ',' + '"' + txtB + '"' + ',' + '"' +count + '"');
    // 		$('.d_footer .s_prompt').fadeIn(300);   		
    //         return true;
    //     }
    // })
 	
	//register选项卡
	$('.d_register ul li').click(function(){
		$(this).addClass('active').siblings().removeClass('active');
		$('.d_register .show').eq($(this).index()).stop(false,true).slideDown(300).siblings().slideUp(300);
	});
	
	//search下拉   
	$('.d_header .d_seek>ul>li').click(function(){
		$('.d_header .d_seek .drop').stop(false,true).toggle();
	});
	$('.d_header .d_seek .drop li').click(function(){
		$('.d_header .d_seek>ul>li>a>span').text($(this).text());
	});	
	
});

	
	
