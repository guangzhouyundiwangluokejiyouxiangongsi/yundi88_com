     //获取对象
        var one = document.getElementById('one');
        var two = document.getElementById('two');

        var oneLi = one.getElementsByTagName('li');
        var twoLi = two.getElementsByTagName('li');

        var timer = setInterval(fun, 2000);

        var a = 0;

        var num = 0; //对应关系变量


         //遍历li
        for (var i = 0; i < oneLi.length; i++) {

            oneLi[i].onmouseover = function () {

                num = this.innerHTML - 1;

                //停止定时器
                clearInterval(timer);

                //初始化颜色
                for (var j = 0; j < oneLi.length; j++) {
                    oneLi[j].style.backgroundColor = '#999999';
                }

                this.style.backgroundColor = '#eb3436';

                //遍历要显示的轮播图
                for (var k = 0; k < twoLi.length; k++) {

                    //判断
                    k == num ? twoLi[num].style.display = 'block' : twoLi[k].style.display = 'none';

                }

            }

            //功能区鼠标移出事件
            oneLi[i].onmouseout = function () {
                a = num;
                timer = null;
                timer = setInterval(fun, 1000);

            }

        }

        function fun() {

            //初始化功能区
            for (var i = 0; i < oneLi.length; i++) {
                oneLi[i].style.backgroundColor = '#999999';
            }

            //遍历轮播图
            for (var j = 0; j < twoLi.length; j++) {

                //判断
                if (j == a) {
                    twoLi[a].style.display = 'block';
                } else {
                    twoLi[j].style.display = 'none';
                }

            }

            //让对应的功能区颜色变色

            oneLi[a].style.backgroundColor = '#eb3436';

            a++;

            if (a % oneLi.length == 0) {
                a = 0;
            }
           // console.log(a);
        }
