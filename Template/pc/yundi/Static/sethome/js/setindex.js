// 左边栏目选择
$(function(){
  	$('.main2l >ul li').bind('click',function(){
      $('.main2l > ul li').each(function(){        
        $($(this).attr('i')).css('display','none');       
        $($(this).attr('i')).hide(); 
        $(this).removeClass('main2la');
        $(this).addClass('main2laaa');     
      })
    $(this).removeClass('main2laaa');
    $(this).addClass('main2la');
    $($(this).attr('i')).show();
    });
})
 
  
// 谷歌图片切换
$(function(){
 function DY_scroll(wraper,prev,next,img,speed,or)
 { 
  var wraper = $(wraper);
  var prev = $(prev);
  var next = $(next);
  var img = $(img).find('ul');
  var w = img.find('li').outerWidth(true);
  var s = speed;
  next.click(function() 
       {
        img.animate({'margin-left':-w},function() 
                  {
                   img.find('li').eq(0).appendTo(img); 
                   img.css({'margin-left':0}); 
                   });
        }); 
  prev.click(function() 
       { 
        img.find('li:last').prependTo(img); 
        img.css({'margin-left':-w});
        img.animate({'margin-left':0});
 
        });
  if (or == true)
  { 
   ad = setInterval(function() { next.click();},s*1000);
 
   wraper.hover(function(){clearInterval(ad);},function(){ad = setInterval(function() { next.click();},s*1000);});
  }
 }
 
 DY_scroll('.img-scroll','.preva','.nexta','.lista',3,false);// true为自动播放，不加此参数或false就默认不自动

})

// ie图片切换
$(function(){
   function DY_scroll(wraper,prev,next,img,speed,or)
   { 
    var wraper = $(wraper);
    var prev = $(prev);
    var next = $(next);
    var img = $(img).find('ul');
    var w = img.find('li').outerWidth(true);
    var s = speed;
    next.click(function() 
         {
          img.animate({'margin-left':-w},function() 
                    {
                     img.find('li').eq(0).appendTo(img); 
                     img.css({'margin-left':0}); 
                     });
          }); 
    prev.click(function() 
         { 
          img.find('li:last').prependTo(img); 
          img.css({'margin-left':-w});
          img.animate({'margin-left':0});
   
          });
    if (or == true)
    { 
     ad = setInterval(function() { next.click();},s*1000);
   
     wraper.hover(function(){clearInterval(ad);},function(){ad = setInterval(function() { next.click();},s*1000);});
    }
   }
   
   DY_scroll('.img-scroll','.prevb','.nextb','.listb',3,false);// true为自动播放，不加此参数或false就默认不自动

})

// 360浏览器
$(function(){
   function DY_scroll(wraper,prev,next,img,speed,or)
   { 
    var wraper = $(wraper);
    var prev = $(prev);
    var next = $(next);
    var img = $(img).find('ul');
    var w = img.find('li').outerWidth(true);
    var s = speed;
    next.click(function() 
         {
          img.animate({'margin-left':-w},function() 
                    {
                     img.find('li').eq(0).appendTo(img); 
                     img.css({'margin-left':0}); 
                     });
          }); 
    prev.click(function() 
         { 
          img.find('li:last').prependTo(img); 
          img.css({'margin-left':-w});
          img.animate({'margin-left':0});
   
          });
    if (or == true)
    { 
     ad = setInterval(function() { next.click();},s*1000);
   
     wraper.hover(function(){clearInterval(ad);},function(){ad = setInterval(function() { next.click();},s*1000);});
    }
   }
   
   DY_scroll('.img-scroll','.prevc','.nextc','.listc',3,false);// true为自动播放，不加此参数或false就默认不自动

})

// 傲游浏览器
$(function(){
   function DY_scroll(wraper,prev,next,img,speed,or)
   { 
    var wraper = $(wraper);
    var prev = $(prev);
    var next = $(next);
    var img = $(img).find('ul');
    var w = img.find('li').outerWidth(true);
    var s = speed;
    next.click(function() 
         {
          img.animate({'margin-left':-w},function() 
                    {
                     img.find('li').eq(0).appendTo(img); 
                     img.css({'margin-left':0}); 
                     });
          }); 
    prev.click(function() 
         { 
          img.find('li:last').prependTo(img); 
          img.css({'margin-left':-w});
          img.animate({'margin-left':0});
   
          });
    if (or == true)
    { 
     ad = setInterval(function() { next.click();},s*1000);
   
     wraper.hover(function(){clearInterval(ad);},function(){ad = setInterval(function() { next.click();},s*1000);});
    }
   }
   
   DY_scroll('.img-scroll','.prevd','.nextd','.listd',3,false);// true为自动播放，不加此参数或false就默认不自动

})

// 搜狗浏览器
$(function(){
   function DY_scroll(wraper,prev,next,img,speed,or)
   { 
    var wraper = $(wraper);
    var prev = $(prev);
    var next = $(next);
    var img = $(img).find('ul');
    var w = img.find('li').outerWidth(true);
    var s = speed;
    next.click(function() 
         {
          img.animate({'margin-left':-w},function() 
                    {
                     img.find('li').eq(0).appendTo(img); 
                     img.css({'margin-left':0}); 
                     });
          }); 
    prev.click(function() 
         { 
          img.find('li:last').prependTo(img); 
          img.css({'margin-left':-w});
          img.animate({'margin-left':0});
   
          });
    if (or == true)
    { 
     ad = setInterval(function() { next.click();},s*1000);
   
     wraper.hover(function(){clearInterval(ad);},function(){ad = setInterval(function() { next.click();},s*1000);});
    }
   }
   
   DY_scroll('.img-scroll','.preve','.nexte','.liste',3,false);// true为自动播放，不加此参数或false就默认不自动

})

// QQ浏览器
$(function(){
   function DY_scroll(wraper,prev,next,img,speed,or)
   { 
    var wraper = $(wraper);
    var prev = $(prev);
    var next = $(next);
    var img = $(img).find('ul');
    var w = img.find('li').outerWidth(true);
    var s = speed;
    next.click(function() 
         {
          img.animate({'margin-left':-w},function() 
                    {
                     img.find('li').eq(0).appendTo(img); 
                     img.css({'margin-left':0}); 
                     });
          }); 
    prev.click(function() 
         { 
          img.find('li:last').prependTo(img); 
          img.css({'margin-left':-w});
          img.animate({'margin-left':0});
   
          });
    if (or == true)
    { 
     ad = setInterval(function() { next.click();},s*1000);
   
     wraper.hover(function(){clearInterval(ad);},function(){ad = setInterval(function() { next.click();},s*1000);});
    }
   }
   
   DY_scroll('.img-scroll','.prevf','.nextf','.listf',3,false);// true为自动播放，不加此参数或false就默认不自动

})

// TT浏览器
$(function(){
   function DY_scroll(wraper,prev,next,img,speed,or)
   { 
    var wraper = $(wraper);
    var prev = $(prev);
    var next = $(next);
    var img = $(img).find('ul');
    var w = img.find('li').outerWidth(true);
    var s = speed;
    next.click(function() 
         {
          img.animate({'margin-left':-w},function() 
                    {
                     img.find('li').eq(0).appendTo(img); 
                     img.css({'margin-left':0}); 
                     });
          }); 
    prev.click(function() 
         { 
          img.find('li:last').prependTo(img); 
          img.css({'margin-left':-w});
          img.animate({'margin-left':0});
   
          });
    if (or == true)
    { 
     ad = setInterval(function() { next.click();},s*1000);
   
     wraper.hover(function(){clearInterval(ad);},function(){ad = setInterval(function() { next.click();},s*1000);});
    }
   }
   
   DY_scroll('.img-scroll','.prevg','.nextg','.listg',3,false);// true为自动播放，不加此参数或false就默认不自动

})

// Firefox浏览器
$(function(){
   function DY_scroll(wraper,prev,next,img,speed,or)
   { 
    var wraper = $(wraper);
    var prev = $(prev);
    var next = $(next);
    var img = $(img).find('ul');
    var w = img.find('li').outerWidth(true);
    var s = speed;
    next.click(function() 
         {
          img.animate({'margin-left':-w},function() 
                    {
                     img.find('li').eq(0).appendTo(img); 
                     img.css({'margin-left':0}); 
                     });
          }); 
    prev.click(function() 
         { 
          img.find('li:last').prependTo(img); 
          img.css({'margin-left':-w});
          img.animate({'margin-left':0});
   
          });
    if (or == true)
    { 
     ad = setInterval(function() { next.click();},s*1000);
   
     wraper.hover(function(){clearInterval(ad);},function(){ad = setInterval(function() { next.click();},s*1000);});
    }
   }
   
   DY_scroll('.img-scroll','.prev1','.next1','.list1',3,false);// true为自动播放，不加此参数或false就默认不自动

})

// 360极速浏览器
$(function(){
   function DY_scroll(wraper,prev,next,img,speed,or)
   { 
    var wraper = $(wraper);
    var prev = $(prev);
    var next = $(next);
    var img = $(img).find('ul');
    var w = img.find('li').outerWidth(true);
    var s = speed;
    next.click(function() 
         {
          img.animate({'margin-left':-w},function() 
                    {
                     img.find('li').eq(0).appendTo(img); 
                     img.css({'margin-left':0}); 
                     });
          }); 
    prev.click(function() 
         { 
          img.find('li:last').prependTo(img); 
          img.css({'margin-left':-w});
          img.animate({'margin-left':0});
   
          });
    if (or == true)
    { 
     ad = setInterval(function() { next.click();},s*1000);
   
     wraper.hover(function(){clearInterval(ad);},function(){ad = setInterval(function() { next.click();},s*1000);});
    }
   }
   
   DY_scroll('.img-scroll','.prev2','.next2','.list2',3,false);// true为自动播放，不加此参数或false就默认不自动

})

// 世界之窗极速版浏览器
$(function(){
   function DY_scroll(wraper,prev,next,img,speed,or)
   { 
    var wraper = $(wraper);
    var prev = $(prev);
    var next = $(next);
    var img = $(img).find('ul');
    var w = img.find('li').outerWidth(true);
    var s = speed;
    next.click(function() 
         {
          img.animate({'margin-left':-w},function() 
                    {
                     img.find('li').eq(0).appendTo(img); 
                     img.css({'margin-left':0}); 
                     });
          }); 
    prev.click(function() 
         { 
          img.find('li:last').prependTo(img); 
          img.css({'margin-left':-w});
          img.animate({'margin-left':0});
   
          });
    if (or == true)
    { 
     ad = setInterval(function() { next.click();},s*1000);
   
     wraper.hover(function(){clearInterval(ad);},function(){ad = setInterval(function() { next.click();},s*1000);});
    }
   }
   
   DY_scroll('.img-scroll','.prev3','.next3','.list3',3,false);// true为自动播放，不加此参数或false就默认不自动

})

// Opera浏览器
$(function(){
   function DY_scroll(wraper,prev,next,img,speed,or)
   { 
    var wraper = $(wraper);
    var prev = $(prev);
    var next = $(next);
    var img = $(img).find('ul');
    var w = img.find('li').outerWidth(true);
    var s = speed;
    next.click(function() 
         {
          img.animate({'margin-left':-w},function() 
                    {
                     img.find('li').eq(0).appendTo(img); 
                     img.css({'margin-left':0}); 
                     });
          }); 
    prev.click(function() 
         { 
          img.find('li:last').prependTo(img); 
          img.css({'margin-left':-w});
          img.animate({'margin-left':0});
   
          });
    if (or == true)
    { 
     ad = setInterval(function() { next.click();},s*1000);
   
     wraper.hover(function(){clearInterval(ad);},function(){ad = setInterval(function() { next.click();},s*1000);});
    }
   }
   
   DY_scroll('.img-scroll','.prev4','.next4','.list4',3,false);// true为自动播放，不加此参数或false就默认不自动

})

// 世界之窗浏览器
$(function(){
   function DY_scroll(wraper,prev,next,img,speed,or)
   { 
    var wraper = $(wraper);
    var prev = $(prev);
    var next = $(next);
    var img = $(img).find('ul');
    var w = img.find('li').outerWidth(true);
    var s = speed;
    next.click(function() 
         {
          img.animate({'margin-left':-w},function() 
                    {
                     img.find('li').eq(0).appendTo(img); 
                     img.css({'margin-left':0}); 
                     });
          }); 
    prev.click(function() 
         { 
          img.find('li:last').prependTo(img); 
          img.css({'margin-left':-w});
          img.animate({'margin-left':0});
   
          });
    if (or == true)
    { 
     ad = setInterval(function() { next.click();},s*1000);
   
     wraper.hover(function(){clearInterval(ad);},function(){ad = setInterval(function() { next.click();},s*1000);});
    }
   }
   
   DY_scroll('.img-scroll','.prev5','.next5','.list5',3,false);// true为自动播放，不加此参数或false就默认不自动

})

// Safari浏览器
$(function(){
   function DY_scroll(wraper,prev,next,img,speed,or)
   { 
    var wraper = $(wraper);
    var prev = $(prev);
    var next = $(next);
    var img = $(img).find('ul');
    var w = img.find('li').outerWidth(true);
    var s = speed;
    next.click(function() 
         {
          img.animate({'margin-left':-w},function() 
                    {
                     img.find('li').eq(0).appendTo(img); 
                     img.css({'margin-left':0}); 
                     });
          }); 
    prev.click(function() 
         { 
          img.find('li:last').prependTo(img); 
          img.css({'margin-left':-w});
          img.animate({'margin-left':0});
   
          });
    if (or == true)
    { 
     ad = setInterval(function() { next.click();},s*1000);
   
     wraper.hover(function(){clearInterval(ad);},function(){ad = setInterval(function() { next.click();},s*1000);});
    }
   }
   
   DY_scroll('.img-scroll','.prev6','.next6','.list6',3,false);// true为自动播放，不加此参数或false就默认不自动

})

// 绿色浏览器
$(function(){
   function DY_scroll(wraper,prev,next,img,speed,or)
   { 
    var wraper = $(wraper);
    var prev = $(prev);
    var next = $(next);
    var img = $(img).find('ul');
    var w = img.find('li').outerWidth(true);
    var s = speed;
    next.click(function() 
         {
          img.animate({'margin-left':-w},function() 
                    {
                     img.find('li').eq(0).appendTo(img); 
                     img.css({'margin-left':0}); 
                     });
          }); 
    prev.click(function() 
         { 
          img.find('li:last').prependTo(img); 
          img.css({'margin-left':-w});
          img.animate({'margin-left':0});
   
          });
    if (or == true)
    { 
     ad = setInterval(function() { next.click();},s*1000);
   
     wraper.hover(function(){clearInterval(ad);},function(){ad = setInterval(function() { next.click();},s*1000);});
    }
   }
   
   DY_scroll('.img-scroll','.prev7','.next7','.list7',3,false);// true为自动播放，不加此参数或false就默认不自动

})

// KR浏览器
$(function(){
   function DY_scroll(wraper,prev,next,img,speed,or)
   { 
    var wraper = $(wraper);
    var prev = $(prev);
    var next = $(next);
    var img = $(img).find('ul');
    var w = img.find('li').outerWidth(true);
    var s = speed;
    next.click(function() 
         {
          img.animate({'margin-left':-w},function() 
                    {
                     img.find('li').eq(0).appendTo(img); 
                     img.css({'margin-left':0}); 
                     });
          }); 
    prev.click(function() 
         { 
          img.find('li:last').prependTo(img); 
          img.css({'margin-left':-w});
          img.animate({'margin-left':0});
   
          });
    if (or == true)
    { 
     ad = setInterval(function() { next.click();},s*1000);
   
     wraper.hover(function(){clearInterval(ad);},function(){ad = setInterval(function() { next.click();},s*1000);});
    }
   }
   
   DY_scroll('.img-scroll','.prev8','.next8','.list8',3,false);// true为自动播放，不加此参数或false就默认不自动

})

// 百度浏览器
$(function(){
   function DY_scroll(wraper,prev,next,img,speed,or)
   { 
    var wraper = $(wraper);
    var prev = $(prev);
    var next = $(next);
    var img = $(img).find('ul');
    var w = img.find('li').outerWidth(true);
    var s = speed;
    next.click(function() 
         {
          img.animate({'margin-left':-w},function() 
                    {
                     img.find('li').eq(0).appendTo(img); 
                     img.css({'margin-left':0}); 
                     });
          }); 
    prev.click(function() 
         { 
          img.find('li:last').prependTo(img); 
          img.css({'margin-left':-w});
          img.animate({'margin-left':0});
   
          });
    if (or == true)
    { 
     ad = setInterval(function() { next.click();},s*1000);
   
     wraper.hover(function(){clearInterval(ad);},function(){ad = setInterval(function() { next.click();},s*1000);});
    }
   }
   
   DY_scroll('.img-scroll','.prev9','.next9','.list9',3,false);// true为自动播放，不加此参数或false就默认不自动

})

