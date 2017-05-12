function codes(url){
  pattern = /^1[3|4|5|7|8]\d{9}$/;
  var phone = $('#username').val();
  if(!pattern.test(phone)){ layer.msg('手机号不合法！',{icon:2,time:2000});return false;}
  if (!mobile2){
    layer.msg('手机号码已被使用!',{icon:2,time:2000});
    return false;
  }
  $.post(url,{'mobile':phone},function(res){
    var obj = eval('('+res+')');
      times();
      alert(obj['msg']);
    })
}

//验证码时间 周飞
function times(){
	i = 60;
	var time = setInterval(function(){
		 $('#button').val( i +'秒中后重新获取');
	i--;
	if(i < 0){clearInterval(time); $('#button').val('重新获取');$('#button').removeAttr('disabled');}else{$('#button').attr({'disabled':"disabled"});}
	},1000);
}

//验证账号
      var seller = false;
      var mobile2 = false;
      var password3  = false;
      var storenames = true;
    function checkstore(){
      var storename = document.getElementById("storename").value;
      var zh_cn  =  /^[\u4e00-\u9fff\w]{2,16}$/;
      if (storename == '') {
        return false;
      }
      if (!zh_cn.test(storename)){
        storename = false;
        layer.msg('店铺名字格式错误',{icon:2,time:2000});
      } else {
        $.ajax({
          type:'post',
          url:"/User/ajax_store",
          data:'storename='+storename,
          dataType:'',
          success:function(res){
            if (res){
              storenames = false;
              layer.msg('店铺名已存在',{icon:2,time:2000});
            } else {
              storenames = true;
            }
          }
        })
      }
    }

    function checkseller(){
      var sellername = document.getElementById("sellername").value;
      var yonghu = /^[a-zA-Z][a-zA-Z0-9_]{5,16}/;
      if(sellername == ''){
          return false;
      }
      if(!yonghu.test(sellername)){
        layer.msg('请正确填写账号',{icon:2,time:2000});
        seller = false;
        return false;
      } else {
        $.ajax({
          type:'post',
          url:"/User/ajax_seller",
          data:'sellername='+sellername,
          success:function(res){
            if(res){
              seller = false;
              layer.msg('账号已存在',{icon:2,time:2000});
            } else {
              seller = true;
            }
          }
        })
      }
    }

// 验证手机
    function checkusername(){
      var username = document.getElementById("username").value;
      var mobile = /^1[3|4|5|7|8]\d{9}$/;
      if(username == ''){
        return false;
      }
      if(!mobile.test(username)){
        layer.msg('手机号码格式错误',{icon:2,time:2000});
        mobile2 = false;
        return false;
      } else {
        $.ajax({
          type:'post',
          url:"/User/ajax_mobile",
          data:'mobile='+username,
          success:function(res){
            if(res){
              mobile2 = false;
              layer.msg('手机号已被注册',{icon:2,time:2000});
            } else {
              mobile2 = true;
            }
          }
        })
      }
    }

    function checkpassword(){
      var password = document.getElementById("password").value;
      var pass   = /^[A-Za-z0-9]{6,20}/;
      if(password == ''){
      	password3 = false;
        return false;
      }
      if (!pass.test(password)) {
        password3 = false;
        layer.msg('密码格式错误',{icon:2,time:2000});
      } else {
        password3 = true;
      }
    }

    function checksubmit(){
      var ids = document.getElementById("ids");
      var code = document.getElementById("code").value;
      if (storenames !== true){
        layer.msg('店铺名格式错误',{icon:2,time:2000});
        return false;
      }

      if( code === ''){
        layer.msg('请输入验证码',{icon:2,time:2000});
        return false;
      }

      if(seller !== true){
        layer.msg('账号格式错误或已被注册',{icon:2,time:2000});
        return false;
      }

      if(mobile2 !== true){
        layer.msg('手机格式错误或已被注册',{icon:2,time:2000});
        return false;
      }

      if(password3 != true){
        layer.msg('密码格式错误',{icon:2,time:2000});
        return false;
      }

      if (storenames !== true){
        layer.msg('店铺名字格式错误或已被注册',{icon:2,time:2000});
        return false;
      }
      if (ids.checked != true){
        layer.msg('请同意服务协议',{icon:2,time:2000});
        return false;
      }
    }








    function codesv(url){
  pattern = /^1[3|4|5|7|8]\d{9}$/;
  var phonev = $('#usernamev').val();
  if(!pattern.test(phonev)){ layer.msg('手机号不合法！',{icon:2,time:2000});return false;}
  if (!mobile2v){
    layer.msg('手机号码已被使用!',{icon:2,time:2000});
    return false;
  }
  $.post(url,{'mobile':phonev},function(res){
    var obj = eval('('+res+')');
      timesv();
      alert(obj['msg']);
    })
}

//验证码时间 周飞
function timesv(){
  i = 60;
  var time = setInterval(function(){
     $('#buttonv').val( i +'秒中后重新获取');
  i--;
  if(i < 0){clearInterval(time); $('#buttonv').val('重新获取');$('#buttonv').removeAttr('disabled');}else{$('#buttonv').attr({'disabled':"disabled"});}
  },1000);
}

//验证账号
      var sellerv = false;
      var mobile2v= false;
      var password3v  = false;
      var storenamesv = false;
    function checkstorev(){
      var storenamev = document.getElementById("storenamev").value;
      var zh_cn  =  /^[\u4e00-\u9fff\w]{2,16}$/;
      if (storenamev == '') {
        return false;
      }
      if (!zh_cn.test(storenamev)){
        storenamev = false;
        layer.msg('店铺名字格式错误',{icon:2,time:2000});
      } else {
        $.ajax({
          type:'post',
          url:"/User/ajax_store",
          data:'storename='+storenamev,
          success:function(res){
            if (res){
              storenamesv = false;
              layer.msg('店铺名已存在',{icon:2,time:2000});
            } else {
              storenamesv = true;
            }
          }
        })
      }
    }

    function checksellerv(){
      var sellernamev = document.getElementById("sellernamev").value;
      var yonghu = /^[a-zA-Z][a-zA-Z0-9_]{5,16}/;
      if(sellernamev == ''){
          return false;
      }
      if(!yonghu.test(sellernamev)){
        layer.msg('请正确填写账号',{icon:2,time:2000});
        sellerv = false;
        return false;
      } else {
        $.ajax({
          type:'post',
          url:"/User/ajax_seller",
          data:'sellername='+sellernamev,
          success:function(res){
            if(res){
              sellerv = false;
              layer.msg('账号已存在',{icon:2,time:2000});
            } else {
              sellerv = true;
            }
          }
        })
      }
    }

// 验证手机
    function checkusernamev(){
      var usernamev = document.getElementById("usernamev").value;
      var mobile = /^1[3|4|5|7|8]\d{9}$/;
      if(usernamev == ''){
        return false;
      }
      if(!mobile.test(usernamev)){
        layer.msg('手机号码格式错误',{icon:2,time:2000});
        mobile2v = false;
        return false;
      } else {
        $.ajax({
          type:'post',
          url:"/User/ajax_mobile",
          data:'mobile='+usernamev,
          success:function(res){
            if(res){
              mobile2v = false;
              layer.msg('手机号已被注册',{icon:2,time:2000});
            } else {
              mobile2v = true;
            }
          }
        })
      }
    }

    function checkpasswordv(){
      var passwordv = document.getElementById("passwordv").value;
      var pass   = /^[A-Za-z0-9]{6,20}/;
      if(passwordv == ''){
        password3v = false;
        return false;
      }
      if (!pass.test(passwordv)) {
        password3v = false;
        layer.msg('密码格式错误',{icon:2,time:2000});
      } else {
        password3v = true;
      }
    }