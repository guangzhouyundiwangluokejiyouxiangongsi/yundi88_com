$(function(){
    $('.w_tabs a').eq(0).addClass('tabson');
     $('.w_message>div').eq(0).show();
    $('.w_tabs a').click(function(){
      var i=$(this).index();
      $(this).addClass('tabson').siblings('.w_tabs a').removeClass('tabson')
      $('.w_message>div').eq(i).show().siblings('.w_message>div').hide();
    })

    $(window).scroll(function () {
    	var banHeight=$('.w_banner').height();
    	var headerHeight=$('.w_header').height();
	    if ($(window).scrollTop() > banHeight) {
	     	$('.w_tabs').css({
	     		'position':'fixed',
	     		'z-index':2,
	     		'top':headerHeight
	     	})
	    }
	    else
	    {
	      $('.w_tabs').css({
	     		'position':'',
	     		'z-index':0,
	     		'top':''
	     	})
	    }
	});
})