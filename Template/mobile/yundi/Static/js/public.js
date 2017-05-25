
  $(window).scroll(function () {
  
    // var maxScroll=$(document).height()-$(window).height();

    // if ($(window).scrollTop() >= maxScroll) {
    //   $('.backtop').show(200);
    // }
   
  

    if ($(window).scrollTop() > 100) {
      $('.backtop').show(200);
    }
    else
    {
      $('.backtop').hide(200);
    }
  });
  $(function(){
    $('.y_nav_icon').click(function(){
      $('.slide_menu').fadeToggle('400');
    })

    $('.slide_menu').click(function(){
      $('.slide_menu').fadeOut('slow');
    })
    $(".backtop").click(function(){
      $('body,html').animate({scrollTop:0},300);
      return false;
    });


  //   $('.tabsbox .y_tabs a').click(function(){
  //   var bannerHei=$('.y_banner').height();
  //   var headerHei=$('.y_header').height();
  //   var scrollHei=headerHei+bannerHei;
  //   $("html,body").animate({scrollTop:scrollHei}, 100);

  //   var index=$(this).index();
  //   $('.tabsbox .y_tabs a').find('span').attr('class',' ');
  //   $(this).find('span').addClass('tab_on'+index);
  // });

  // $(window).scroll(function () {

  //     var maxScroll=$(document).height()-$(window).height();

  //     if ($(window).scrollTop() >= maxScroll) {
  //         if($('.tabsbox .y_tabs a span').hasClass('tab_on0')){
  //            alert(0)
  //         }
         
  //         if($('.tabsbox .y_tabs a span').hasClass('tab_on1')){
  //          alert(1)
  //         }
  //         if($('.tabsbox .y_tabs a span').hasClass('tab_on2')){
  //          alert(2)
  //         }
  //     }
      
  //   });

})
