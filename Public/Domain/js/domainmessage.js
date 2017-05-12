
	function dj(obj,s){
		$('.panel-title a').removeClass("on");
		$(".panel-body >div").hide();
		$('.panel-body-'+s).show();
		$(obj).addClass("on");
	}