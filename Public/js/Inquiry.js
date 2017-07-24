var iqn=document.getElementById('iqn');
var navss=document.getElementsByClassName('navss')[0];
iqn.onmouseover=function(){
	navss.style.display="block";
}
iqn.onmouseout=function(){
	navss.style.display="none";
}
var InqMl=document.getElementsByClassName('Inq-ml')[0];
var cal1=document.getElementById('cal1');
var cal2=document.getElementById('cal2');
var contBtnf=document.getElementsByClassName('cont-btnf')[0];
var warnt=document.getElementsByClassName('warn-t')[0];
var warnt1=document.getElementsByClassName('warn-t')[1];
var warnt2=document.getElementsByClassName('warn-t')[2];
var Inqxp=document.getElementsByClassName('Inqxp');
var num1=document.getElementById('num1');
var tel1=document.getElementById('tel1');
var I1=document.getElementsByClassName('I1')[0];
var I2=document.getElementsByClassName('I2')[0];
var I3=document.getElementsByClassName('I3')[0];
var I4=document.getElementsByClassName('I4')[0];
var submit=document.getElementById('submit');

function close(){
	InqMl.style.display="none";
}
function open1(){
	InqMl.style.display="block";
}



contBtnf.onclick=function(){
	InqMl.style.display="block";
}
cal1.onclick=function(){
	InqMl.style.display="none";
}
cal2.onclick=function(){
	InqMl.style.display="none";
}
I1.onclick=function(){
	InqMl.style.display="none";
}

I2.onclick=function(){
	InqMl.style.display="none";
}

I3.onclick=function(){
	InqMl.style.display="none";
}

I4.onclick=function(){
	InqMl.style.display="none";
}


  function checkName(){
  var str1 = document.getElementById('name1').value.trim();
  var obj1 = document.getElementById("name1").value;
     if(str1.length==0){
      	warnt.innerHTML="联系人不能为空";
        warnt.style.display="block";
		submit.onclick=function check(){

       return false;
       }
     }

    if(str1.length>0){
    if((/^[0-9]*$/.test(obj1))){

     		warnt.innerHTML="联系人不能为数字";
     		warnt.style.display="block";
    }

     submit.onclick=function check(){

          return false;
       }


    }

   if(!(/^[0-9]*$/.test(obj1))){
    warnt.style.display="none";
		submit.onclick=function check(){

           InqMl.style.display="none";
       }
   }



}






    function checknum1(){

     var str2 = document.getElementById('num1').value.trim();

     if(str2.length==0){

       warnt1.style.display="block";
     submit.onclick=function check(){

       return false;
       }

     }
     else{

     submit.onclick=function check(){

          InqMl.style.display="none";
       }

     	   warnt1.style.display="none";


     }
 }



function checktel2(){
     var obj3 = document.getElementById("tel1").value;
     var str3 = document.getElementById('tel1').value.trim();

     if(str3.length==0){
       warnt2.style.display="block";
      submit.onclick=function check(){

       return false;
       }

     }


  if(!( /^1[34578]\d{9}$/.test(obj3))){
   warnt2.innerHTML="请填写正确的手机号码";
   warnt2.style.display="block";
    submit.onclick=function check(){

      return false;
       }
  }

  if(( /^1[34578]\d{9}$/.test(obj3))){

   warnt2.style.display="none";
    submit.onclick=function check(){

      InqMl.style.display="none";
       }
  }
 }


  for (var i = Inqxp.length - 1; i >= 0; i--) {
	Inqxp[i].onclick=function(){
        goods_id = this.getAttribute("data-id");
        goods_name = this.getAttribute("data-name");
        store_name = this.getAttribute("data-store-name");
        people = this.getAttribute("data-people");
        mobile = this.getAttribute("data-mobile");
        store_id = this.getAttribute("data-store-id");
        document.getElementById("data-id-input").setAttribute("value", goods_id);
        document.getElementById("data-name-input").setAttribute("value", goods_name);
        document.getElementById("data-store-id-input").setAttribute("value", store_id);
        document.getElementById("store-name").innerHTML = store_name;
        document.getElementById("people").innerHTML = people;
        document.getElementById("people-phone").innerHTML = mobile;
		InqMl.style.display="block";

	}
}
