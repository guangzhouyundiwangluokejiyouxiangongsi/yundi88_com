//  // 输出文本框方法
// function sent(){

//     textarea=$.trim($('.chat-txt textarea').val())
//     if(textarea.length !==0){
//         var myDate = new Date();
//         var hour=myDate.getHours();
//         var minutes=myDate.getMinutes();
//         var seconds=myDate.getSeconds();
//         if(hour<10){
//             hour='0'+hour;
//         }
//         if(minutes<10){
//             minutes='0'+minutes;
//         }   
//         if(seconds<10){
//             seconds='0'+seconds;
//         }
//         var time = hour + ":" + minutes + ":" + seconds;
//         $('.message_box>div').append("<div class='user'><div class='title'><span class='time'>"+ time +"</span><span>我</span></div><div class='message'><div class='txt'><p>"+ textarea +"</p></div></div></div>")
//         // 清空输入框
//         $('.chat-txt textarea').val("");
//         // 滚动div
//         var height=$('.message_box>div').height();
//         $('.message_box').animate({scrollTop:height},600);
          

//     }
    
// }

// $(function(){
//     //发送信息
//     $('.sent').click(function(){
//         sent();
//         submitmsg(textarea);
//     })
//     //shift+enter换行
//     $('.chat-txt textarea').focus(function(){
//         $('.chat-txt  textarea').keyup(function(event){
            
//             if(event.shiftKey && event.which==13 || event.which == 10){
//                 sent();
//                 submitmsg(textarea);
//             }
//             else if(event.keyCode == 13){
                    
//             } 
//         })
//     })
// })