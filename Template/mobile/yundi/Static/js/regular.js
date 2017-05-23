	function feature(name,des){
		name.fadeIn();
		name.text(des);
		setTimeout(function(){
			name.stop(false,true).fadeOut();
		},1000);
		return false;
	};

	//login
	//手机号
	function isTel(obj){	
		l_telpro = $('.d_login form>label:nth-child(1)>i');
		regtel = /^1[3|4|5|7|8][0-9]{9}$/;
		if(obj == ""){
			feature(l_telpro,'手机号不能为空');
		}else if(!regtel.test(obj)){
			feature(l_telpro,'手机号不正确');
		}else{
			obj;
		}		
	};
	
	//密码
	function isPass(obj) {
		l_passpro = $(".d_login form>label:nth-child(2)>i");
		regpass = /(?!^\\d+$)(?!^[a-zA-Z]+$)(?!^[_#@]+$).{6,16}/;
		if(obj == ""){		
			feature(l_passpro,'密码不能为空');
		}else if(!regpass.test(obj)){			
			feature(l_passpro,'密码格式错误!');
		}else{
			obj;
		}		
	};
	
	function btn_log(){
		l_telpro = $('.d_login form>label:nth-child(1)>i');
		l_passpro = $(".d_login form>label:nth-child(2)>i");
		if($(".d_login form>label:nth-child(1)>input").val() == ""){			
			feature(l_telpro,'请输入账号');
		}else if($(".d_login form>label:nth-child(2)>input").val() == ""){		
			feature(l_passpro,'请输入密码');
		}else{
			$.post('/Mobile/User/do_login',$('#form').serialize(),function(res){
				if (res.status == 1){
					window.location.href="/Mobile/User/index";
				}else{
					feature(l_passpro,res.msg);
				}
			})
		}
	};
	
	//register个人
	//账号
	function p_isaccount(obj) {
		rp_accpro = $('.d_register .formone>label:nth-child(1)>i');
		regacc = /^[a-zA-Z][a-zA-Z_\$0-9\#]{5,15}/;
		if(obj == ""){		
			feature(rp_accpro,'账号不能为空');
		}else if(!regacc.test(obj)){		
			feature(rp_accpro,'账号格式错误!');
		}else{
			$.post('/Mobile/User/ajax_seller',{'sellername':obj},function(res){
				if (res){
					feature(rp_accpro,'账号已存在');
				}else{
					rp_accpro.html('');
				}
			})
		}
	};
	
	//手机号
	function p_isTel(obj){	
		rp_telpro = $('.d_register .formone>label:nth-child(2)>i');
		regtel = /^1[3|4|5|7|8][0-9]{9}$/;
		if(obj == ""){		
			feature(rp_telpro,'手机号不能为空');
		}else if(!regtel.test(obj)){			
			feature(rp_telpro,'手机号格式错误!');
		}else{
			$.post('/Mobile/User/ajax_mobile',{'mobile':obj},function(res){
				if (res){
					feature(rp_telpro,'手机号已被注册!');
				}else{
					rp_telpro.html('');
				}
			});
		}		
	};

	// 个人获取验证码
	var i;
	function code(){
		var mobile = /^1[3|4|5|7|8][0-9]{9}$/;
		var phone = $('#mobile').val();
		var rp_telpro = $('.d_register .formone>label:nth-child(2)>i');
		if (i > 0){
			feature(rp_telpro,'60秒内不能重新获取!');
			return false;
		}
		if (!mobile.test(phone)){
			feature(rp_telpro,'手机号格式错误!');
			return false;
		}
		$.post('/Mobile/User/ajax_mobile',{'mobile':phone},function(res){
			if (res){
				feature(rp_telpro,'手机号已被注册!');
			}else{
				$.post('/Mobile/User/send_sms_reg_code',{mobile:phone},function(res){
					if (res.status == 1){
						i = 60;
						var time = setInterval(function(){
							$('.gaincode').html( i +'秒中后重新获取');
							i--;
							if (i == 0){
								clearInterval(time);
								$('.gaincode').html('获取验证码');
							}
						},1000)
						
					}
				})
			}
		});
	}

	//企业获取验证码
	function codes(){
		var mobile = /^1[3|4|5|7|8][0-9]{9}$/;
		var phone = $('#mobile2').val();
		var rp_telpro = $('.d_register .formtwo>label:nth-child(4)>i');
		if (i > 0){
			feature(rp_telpro,'60秒内不能重新获取!');
			return false;
		}
		if (!mobile.test(phone)){
			feature(rp_telpro,'手机号格式错误!');
			return false;
		}
		$.post('/Mobile/User/ajax_mobile',{'mobile':phone},function(res){
			if (res){
				feature(rp_telpro,'手机号已被注册!');
			}else{
				$.post('/Mobile/User/send_sms_reg_code',{mobile:phone},function(res){
					if (res.status == 1){
						i = 60;
						var time = setInterval(function(){
							$('.gaincode').html( i +'秒中后重新获取');
							i--;
							if (i == 0){
								clearInterval(time);
								$('.gaincode').html('获取验证码');
							}
						},1000)
						
					}
				})
			}
		});
	}
	//密码
	function p_isPass(obj) {
		rp_passpro = $(".d_register .formone>label:nth-child(3)>i");
		regpass = /(?!^\\d+$)(?!^[a-zA-Z]+$)(?!^[_#@]+$).{6,16}/;
		if(obj == ""){			
			feature(rp_passpro,'密码不能为空');
		}else if(!regpass.test(obj)){			
			feature(rp_passpro,'密码格式错误!');
		}else{
			rp_passpro.html('');
		}		
	};
	
	//验证码
	function iscode(obj){
		r_code = $(".d_register form>label:last-of-type>i");
		regcode = /^[A-Za-z0-9]+$/;
		if(obj == ""){			
			feature(r_code,'验证码不能为空');
		}else if(!regcode.test(obj)){			
			feature(r_code,'验证码不正确');
		}else{
			r_code.html('');
		}
	};
	
	function btn_reg(){
		rp_accpro = $('.d_register .formone>label:nth-child(1)>i');
		rp_telpro = $('.d_register .formone>label:nth-child(2)>i');
		rp_passpro = $(".d_register .formone>label:nth-child(3)>i");
		r_code = $(".d_register form>label:last-of-type>i");
		if($(".d_register .formone>label:nth-child(1)>input").val() == ""){
			feature(rp_accpro,'请输入账号');
		}else if($(".d_register .formone>label:nth-child(2)>input").val() == ""){
			feature(rp_telpro,'请输入手机号');
		}else if($(".d_register .formone>label:nth-child(3)>input").val() == ""){
			feature(rp_passpro,'请输入密码');
		}else if($(".d_register .formone>label:nth-child(4)>input").val() == ""){
			feature(r_code,'请输入验证码');
		}else if(rp_accpro.html()){
			feature(rp_accpro,'账号已存在');
		}else if(rp_telpro.html()){
			feature(rp_telpro,'手机号已存在!');
		}else if(rp_passpro.html()){
			feature(rp_passpro,'密码格式错误!');
		}else if(r_code.html()){
			feature(r_code,'验证码不正确');
		}else{
			$.post('/Mobile/User/register',$('#form1').serialize(),function(res){
				if (res){
					if (res != '注册成功'){
						alert(res);
					}else{
						window.location.href="/Mobile/User/index";
					}
				}else{
					alert('网络故障!');
				}
			})
		}
	};
	
	//register企业
	//企业名
	function c_iscorporation(obj) {
		rc_corpro = $(".d_register .formtwo>label:nth-child(2)>i");
		regcorp = /^[a-zA-Z\u4E00-\u9FA5]{4,16}$/;
		if(obj == ""){		
			feature(rc_corpro,'企业名不能为空');
		}else if(!regcorp.test(obj)){
			feature(rc_corpro,'企业名不正确');
		}else{
			$.post('/Mobile/User/ajax_store',{'storename':obj},function(res){
				if (res){
					feature(rc_corpro,'企业名已存在!');
				}else{
					rc_corpro.html('');
				}
			})
		}
	};
	
	//账号
	function c_isaccount(obj) {
		rc_accpro = $(".d_register .formtwo>label:nth-child(3)>i");
		regacc = /^[a-zA-Z][a-zA-Z_\$0-9\#]{5,15}/;
		if(obj == ""){
			feature(rc_accpro,'账号不能为空');
		}else if(!regacc.test(obj)){			
			feature(rc_accpro,'账号不正确');
		}else{
			$.post('/Mobile/User/ajax_seller',{'sellername':obj},function(res){
				if (res){
					feature(rc_accpro,'账号已被注册!');
				}else{
					rc_accpro.html('');
				}
			})
		}
	};
	
	//手机号
	function c_isTel(obj){	
		rc_telpro = $('.d_register .formtwo>label:nth-child(4)>i');
		regtel = /^1[3|4|5|7|8][0-9]{9}$/;
		if(obj == ""){
			feature(rc_telpro,'手机号不能为空');
		}else if(!regtel.test(obj)){			
			feature(rc_telpro,'手机号不正确');
		}else{
			$.post('/Mobile/User/ajax_mobile',{'mobile':obj},function(res){
				if (res){
					feature(rc_telpro,'手机号已被注册!');
				}else{
					rc_telpro.html('');
				}
			});
		}		
	};
	
	//密码
	function c_isPass(obj) {
		rc_passpro = $(".d_register .formtwo>label:nth-child(5)>i");
		regpass = /(?!^\\d+$)(?!^[a-zA-Z]+$)(?!^[_#@]+$).{6,}/;
		if(obj == ""){
			feature(rc_passpro,'密码不能为空');
		}else if(!regpass.test(obj)){			
			feature(rc_passpro,'密码格式错误!');
		}else{
			rc_passpro.html('');
		}		
	};

	function btn_regg(){
		rc_corpro = $(".d_register .formtwo>label:nth-child(2)>i");
		rc_accpro = $(".d_register .formtwo>label:nth-child(3)>i");
		rc_telpro = $('.d_register .formtwo>label:nth-child(4)>i');
		rc_passpro = $(".d_register .formtwo>label:nth-child(5)>i");
		r_code = $(".d_register form>label:last-of-type>i");
		if($(".d_register .formtwo>label:nth-child(2)>input").val() == ""){			
			feature(rc_corpro,'请输入企业名');
		}else if($(".d_register .formtwo>label:nth-child(3)>input").val() == ""){
			feature(rc_accpro,'请输入账号');
		}else if($(".d_register .formtwo>label:nth-child(4)>input").val() == ""){
			feature(rc_telpro,'请输入手机号');
		}else if($(".d_register .formtwo>label:nth-child(5)>input").val() == ""){
			feature(rp_passpro,'请输入密码');
		}else if($(".d_register .formtwo>label:nth-child(6)>input").val() == ""){
			feature(r_code,'请输入验证码');
		}else if(rc_corpro.html()){
			feature(rc_corpro,'企业名已存在!');
		}else if(rc_accpro.html()){
			feature(rc_accpro,'账号已被注册!');
		}else if(rc_telpro.html()){
			feature(rc_telpro,'手机号已存在!');
		}else if(rc_passpro.html()){
			feature(rc_passpro,'密码格式错误!');
		}else if(r_code.html()){		
			feature(r_code,'验证码不正确');
		}else{
			$.post('/Mobile/User/register',$('#form2').serialize(),function(res){
				if (res){
					if (res != '注册成功'){
						alert(res);
					}else{
						window.location.href="/Mobile/User/index";
					}
				}else{
					alert('网络故障!');
				}
			})
		}
	};
	
	
	//找回密码
	//手机
	function e_isTel(obj){	
		e_telpro = $('.d_retrieve form>label:nth-child(1)>i');
		regtel = /^1[3|4|5|7|8][0-9]{9}$/;
		if(obj == ""){
			feature(e_telpro,'手机号不能为空');
		}else if(!regtel.test(obj)){
			feature(e_telpro,'手机号不正确');
		}else{
			$.post('/Mobile/User/ajax_mobile',{'mobile':obj},function(res){
				if (res){
					e_telpro.html('');
				}else{
					feature(e_telpro,'手机号码不存在!');
				}
			});
		}		
	};
	
	function codess(){
		var mobile = /^1[3|4|5|7|8][0-9]{9}$/;
		var phone = $('#mobile').val();
		var rp_telpro = $(".d_retrieve form>label:nth-child(1)>i");
		if (i > 0){
			feature(rp_telpro,'60秒内不能重新获取!');
			return false;
		}
		if (!mobile.test(phone)){
			feature(rp_telpro,'手机号格式错误!');
			return false;
		}
		$.post('/Mobile/User/ajax_mobile',{'mobile':phone},function(res){
			if (!res){
				feature(rp_telpro,'手机号未注册!');
			}else{
				$.post('/Mobile/User/send_sms_reg_code',{mobile:phone},function(res){
					if (res.status == 1){
						i = 60;
						var time = setInterval(function(){
							$('.gaincode').html( i +'秒中后重新获取');
							i--;
							if (i == 0){
								clearInterval(time);
								$('.gaincode').html('获取验证码');
							}
						},1000)
						
					}
				})
			}
		});
	}

	//验证码
	function e_iscode(obj){
		e_code = $(".d_retrieve form>label:nth-child(2)>i");
		regcode = /^[A-Za-z0-9]+$/;
		if(obj == ""){			
			feature(e_code,'验证码不能为空');
		}else if(!regcode.test(obj)){			
			feature(e_code,'验证码不正确');
		}else{
			e_code.html('');
		}
	};
	
	//密码
	function e_isPass(obj) {
		e_passpro = $(".d_retrieve form>label:nth-child(3)>i");
		regpass = /(?!^\\d+$)(?!^[a-zA-Z]+$)(?!^[_#@]+$).{6,16}/;
		if(obj == ""){		
			feature(e_passpro,'密码不能为空');
		}else if(!regpass.test(obj)){			
			feature(e_passpro,'密码格式错误');
		}else{
			e_passpro.html('');
		}		
	};
	
	function btn_ret(){
		e_telpro = $('.d_retrieve form>label:nth-child(1)>i');
		e_code = $(".d_retrieve form>label:nth-child(2)>i");
		e_passpro = $(".d_retrieve form>label:nth-child(3)>i");
		if($(".d_retrieve form>label:nth-child(1)>input").val() == ""){			
			feature(e_telpro,'手机号不能为空');
		}else if($(".d_retrieve form>label:nth-child(2)>input").val() == ""){		
			feature(e_code,'验证码不能为空');
		}else if($(".d_retrieve form>label:nth-child(3)>input").val() == ""){		
			feature(e_passpro,'密码不能为空');
		}else if(!regtel.test($(".d_retrieve form>label:nth-child(1)>input").val())){			
			feature(e_telpro,'手机号不正确');
		}else if(!regcode.test($(".d_retrieve form>label:nth-child(2)>input").val())){			
			feature(e_code,'验证码不正确');
		}else if(!regpass.test($(".d_retrieve form>label:nth-child(3)>input").val())){			
			feature(e_passpro,'密码格式错误');
		}else{
			$.post('/Mobile/User/retrieve',$('#formtwo').serialize(),function(res){
				if (res.status == 1){
					feature(e_telpro,res.msg);
				}else{
					feature(e_telpro,res.msg);
				}
			})
		}
	};
	
