$(function(){
$('.xiuxian').removeClass('infors');
$('.xiuxian').addClass('');

	$('.informationr >ul li').bind('click',function(){

			$('.informationr > ul li').each(function(){				
				$($(this).attr('i')).css('display','none');				
				$($(this).attr('i')).hide('infors');	
				$(this).removeClass('infors');
				$(this).addClass('');			

			})

		$(this).removeClass('');
		$(this).addClass('infors');
		$($(this).attr('i')).show('');
		});
})