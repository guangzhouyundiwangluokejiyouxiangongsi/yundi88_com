$(function() {
  //配置变量
  var config = {
   showNum : 6, //设置滚动的显示个数
   autoScroll : false, //是否自动滚动，默认为 false
   autoScrollInterval : 3000 //自动滚动间隔，默认为 3000 毫秒，autoScroll = true 时才有效
  }
  
  var scrollUlWidth = $('.scroll_ul li').outerWidth(true), //单个 li 的宽度
   scrollUlLeft = 0, //.scroll_ul 初使化时的 left 值为 0
   prevAllow = true, //为了防止连续点击上一页按钮
   nextAllow = true; //为了防止连续点击下一页按钮
   
  //计算父容量限宽
  $('.scroll_list').width(config.showNum * scrollUlWidth);
  //点击上一页
  $('#prev').click(function() {
   if (prevAllow) {
    prevAllow = false;
    scrollUlLeft = scrollUlLeft - scrollUlWidth;
    $('.scroll_ul').css('left', scrollUlLeft);
    //复制最后一个 li 并插入到 ul 的最前面
    $('.scroll_ul li:last').clone().prependTo('.scroll_ul');
    //删除最后一个 li
    $('.scroll_ul li:last').remove();
    $('.scroll_ul').animate({
     left : scrollUlLeft + scrollUlWidth
    }, 300, function() {
     scrollUlLeft = parseInt($('.scroll_ul').css('left'), 10);
     prevAllow = true;
    })
   }
  });
  
  //点击下一页
  $('#next').click(function() {
   if (nextAllow) {
    nextAllow = false;
    $('.scroll_ul').animate({
     left : scrollUlLeft - scrollUlWidth
    }, 300, function() {
     scrollUlLeft = parseInt($('.scroll_ul').css('left'), 10);
     scrollUlLeft = scrollUlLeft + scrollUlWidth;
     $('.scroll_ul').css('left', scrollUlLeft);
     //复制第一个 li 并插入到 ul 的最后面
     $('.scroll_ul li:first').clone().appendTo('.scroll_ul');
     //删除第一个 li
     $('.scroll_ul li:first').remove();
     nextAllow = true;
    })
   }
  });
  
  //自动滚动
  if (config.autoScroll) {
   setInterval(function() {
    $('#next').trigger('click');
   }, config.autoScrollInterval)
  }
 })


$(function() {
  //配置变量
  var config = {
   showNum : 6, //设置滚动的显示个数
   autoScroll : false, //是否自动滚动，默认为 false
   autoScrollInterval : 3000 //自动滚动间隔，默认为 3000 毫秒，autoScroll = true 时才有效
  }
  
  var scrollUlWidth = $('.scroll_uls li').outerWidth(true), //单个 li 的宽度
   scrollUlLeft = 0, //.scroll_ul 初使化时的 left 值为 0
   prevAllow = true, //为了防止连续点击上一页按钮
   nextAllow = true; //为了防止连续点击下一页按钮
   
  //计算父容量限宽
  $('.scroll_list').width(config.showNum * scrollUlWidth);
  //点击上一页
  $('#prevs').click(function() {
   if (prevAllow) {
    prevAllow = false;
    scrollUlLeft = scrollUlLeft - scrollUlWidth;
    $('.scroll_uls').css('left', scrollUlLeft);
    //复制最后一个 li 并插入到 ul 的最前面
    $('.scroll_uls li:last').clone().prependTo('.scroll_uls');
    //删除最后一个 li
    $('.scroll_uls li:last').remove();
    $('.scroll_uls').animate({
     left : scrollUlLeft + scrollUlWidth
    }, 300, function() {
     scrollUlLeft = parseInt($('.scroll_uls').css('left'), 10);
     prevAllow = true;
    })
   }
  });
  
  //点击下一页
  $('#nexts').click(function() {
   if (nextAllow) {
    nextAllow = false;
    $('.scroll_uls').animate({
     left : scrollUlLeft - scrollUlWidth
    }, 300, function() {
     scrollUlLeft = parseInt($('.scroll_uls').css('left'), 10);
     scrollUlLeft = scrollUlLeft + scrollUlWidth;
     $('.scroll_uls').css('left', scrollUlLeft);
     //复制第一个 li 并插入到 ul 的最后面
     $('.scroll_uls li:first').clone().appendTo('.scroll_uls');
     //删除第一个 li
     $('.scroll_uls li:first').remove();
     nextAllow = true;
    })
   }
  });
  
  //自动滚动
  if (config.autoScroll) {
   setInterval(function() {
    $('#nexts').trigger('click');
   }, config.autoScrollInterval)
  }
 })

function ddd(obj, sType) { 
var oDiv = document.getElementById(obj); 
if (sType == 'show') { oDiv.style.display = 'block';} 
if (sType == 'hide') { oDiv.style.display = 'none';} 
} 


$(function(){

// $('.xiuxian').removeClass('s2mainAAA');
// $('.xiuxian').addClass('s2mainAA');

  $('.scroll_lists >ul li').bind('mouseover',function(){

      $('.scroll_lists > ul li').each(function(){        

        $($(this).attr('i')).css('display','none');       
        $($(this).attr('i')).hide('s2mainAAA'); 
        // $(this).removeClass('s2mainAAA');
        // $(this).addClass('s2mainAA');      

      })

    // $(this).removeClass('s2mainAA');
    // $(this).addClass('s2mainAAA');
    $($(this).attr('i')).show('s2mainAA');
    });
})


