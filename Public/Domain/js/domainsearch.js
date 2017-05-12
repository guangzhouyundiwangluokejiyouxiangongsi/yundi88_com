$(function(){
	$(".panel-body-company").show();
	// $(".panel-heading .panel-title span a").eq(1).addClass("btn-primary");
	$(".panel-heading .panel-title span a").click(function(){
		$(".panel-heading .panel-title span a").css('background','#fff');
		$(this).css('background','#337AB7');
	})
	
		
});

