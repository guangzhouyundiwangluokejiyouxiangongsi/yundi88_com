window.onload = function() {
    var oDiv = document.getElementById("tab");
    var oLi = oDiv.getElementsByTagName("div")[0].getElementsByTagName("li");
    var aCon = oDiv.getElementsByTagName("div")[1].getElementsByTagName("div");

    var timer = null;
    for (var i = 0; i < oLi.length; i++) {
        oLi[i].index = i;
        oLi[i].onclick = function() {
            show(this.index);
        }
    }
    function show(a) {
        index = a;
        var alpha = 0;
        for (var j = 0; j < oLi.length; j++) {
            oLi[j].className = "";
            aCon[j].className = "";
            aCon[j].style.display="none";
        }
        oLi[index].className = "cur";
        clearInterval(timer);
        timer = setInterval(function() {
            alpha += 2;
            alpha > 100 && (alpha = 100);
            aCon[index].style.display="block";
            alpha == 100 && clearInterval(timer);
        },
        5)
    }


    //详情切换
    var clickA = document.getElementById('clickAAA');
    if(clickA){
        clickA.onclick =function(){
           var curOne = document.getElementById('curOne');
             curOne.style.display="block";

           var curTwo =  document.getElementById('curTwo');
            curTwo.style.display="none";      
            clickB.className = "curTwo";
            clickA.className = "cur";

        }
    }

     var clickB = document.getElementById('clickBBB');
     if(clickB){
    clickB.onclick =function(){
       var curTwo = document.getElementById('curTwo');
         curTwo.style.display="block";
       var curOne =  document.getElementById('curOne');
        curOne.style.display="none";      
         clickA.className = "curTwo";
         clickB.className = "cur"; 
    }
     }





}