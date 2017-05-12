
$(function(){
	// alert('adsaas');
	var $v = document.getElementById('J_NavCommonRowItems_0');
	var $a = $v.getElementByTagName('a');
		//产品一
		$('.mollch').bind('click',function(){
			$('.bginput').css('display','block');
			$('.btns').css('display','block');
			$('.back').css('display','block');
			$('.mollch').css('display','none');
			for(var i = 0;i<$a.length;i++){
					$a[i].click = function(){
						console.log('asasa00');
					}
			}		
		});

		$('.cancel').bind('click',function(){
			$('.bginput').css('display','none');
			$('.btns').css('display','none');
			$('.mollch').css('display','');
			$('.back').css('display','none');
		});

		$('.back').bind('click',function(){
			$('.bginput').css('display','none');
			$('.btns').css('display','none');
			$('.mollch').css('display','');
			$('.back').css('display','none');
		});

		//产品二
		$('.mollch1').bind('click',function(){
			$('.bginput1').css('display','block');
			$('.btns1').css('display','block');
			$('.mollch1').css('display','none');
			$('.back1').css('display','block');
		});
		
		$('.cancel1').bind('click',function(){
			$('.bginput1').css('display','none');
			$('.btns1').css('display','none');
			$('.mollch1').css('display','');
			$('.back1').css('display','none');
		});
		$('.back1').bind('click',function(){
			$('.bginput1').css('display','none');
			$('.btns1').css('display','none');
			$('.mollch1').css('display','');
			$('.back1').css('display','none');
		});
		//产品三
		$('.mollch2').bind('click',function(){
			$('.bginput2').css('display','block');
			$('.btns2').css('display','block');
			$('.mollch2').css('display','none');
			$('.back2').css('display','block');
		});

		$('.cancel2').bind('click',function(){
			$('.bginput2').css('display','none');
			$('.btns2').css('display','none');
			$('.mollch2').css('display','');
			$('.back2').css('display','none');
		});
		$('.back2').bind('click',function(){
			$('.bginput2').css('display','none');
			$('.btns2').css('display','none');
			$('.mollch2').css('display','');
			$('.back2').css('display','none');
		});
		//产品四
		$('.mollch3').bind('click',function(){
			$('.bginput3').css('display','block');
			$('.btns3').css('display','block');
			$('.mollch3').css('display','none');
			$('.back3').css('display','block');
		});
		$('.cancel3').bind('click',function(){
			$('.bginput3').css('display','none');
			$('.btns3').css('display','none');
			$('.mollch3').css('display','');
			$('.back3').css('display','none');
		});
		$('.back3').bind('click',function(){
			$('.bginput3').css('display','none');
			$('.btns3').css('display','none');
			$('.mollch3').css('display','');
			$('.back3').css('display','none');
		});
		//产品五
		$('.mollch4').bind('click',function(){
			$('.bginput4').css('display','block');
			$('.btns4').css('display','block');
			$('.mollch4').css('display','none');
			$('.back4').css('display','block');
		});
		$('.cancel4').bind('click',function(){
			$('.bginput4').css('display','none');
			$('.btns4').css('display','none');
			$('.mollch4').css('display','');
			$('.back4').css('display','none');
		});
		$('.back4').bind('click',function(){
			$('.bginput4').css('display','none');
			$('.btns4').css('display','none');
			$('.mollch4').css('display','');
			$('.back4').css('display','none');
		});






});