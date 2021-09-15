<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1 ,user-scalable=no" ">
    <title>免责声明</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        *:after,
        *:before {
            box-sizing: border-box;
        }

        body {
            font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif;
            font-size: 16px;
            line-height: 24px;
            color:#fff;
        }

        #wrap {
            position: fixed;
            width: 100%;
            height: 100%;
            background: #000 url(/uploads/assets/dev-tip/bgPC.png) center/cover no-repeat;
        }
        .content{
            margin-left:auto;
            margin-right:auto;
            width:1000px;
        }
        .center-block{
            display: block;
            margin-left:auto;
            margin-right:auto;
        }

        .text-center{
            text-align:center;
        }

        .text-right{
            text-align: right;
        }


        article{
            font-size:14px;
            line-height: 24px;
            margin-bottom:4%;
        }

        h1{
            font-size:36px;
            margin-top: 4%;
            margin-bottom: 4%;
        }
        section.main{
            margin-bottom:2%;
        }
        p strong{
            font-size:16px;
        }
        .tip-content{
            margin-bottom:4%;
        }
        .tip-main {
            width: 150px;
            border-bottom: 1px solid #fff;
            margin-left: auto;
            margin-right: auto;
        }

        #timer span {
            color: #ff0012;
            font-size: 30px;
            margin-right: 2px;
        }
        .logo{
            width:181px;
            height:46px;
            background:url(/uploads/assets/dev-tip/logo.png) center/cover no-repeat;
        }
        .idea{
            margin-bottom: 30px;
        }
        .idea p{
            font-size: 18px;
            line-height:24px;
            letter-spacing: 1px;
            margin-top:12px;
        }

        .code{
            width:110px;
            height:110px;
        }
        .code img{
            width:100%;
            height:100%;
        }
        @media screen and (max-width:767px) {
            #wrap {
                background: #000 url(/uploads/assets/dev-tip/bgMobile.png) center/cover no-repeat;
            }
            .content{
                width:100%;
                padding-left:15px;
                padding-right:15px;
            }

            h1{
                font-size:18px;
            }
            p strong{
                font-size:16px;
            }
            .tip-main{
                width:130px;
                font-size:14px
            }
            #timer span{
                font-size:20px;
            }
            .logo{
                width:120px;
                height:30px;
            }
            .idea{
                margin-bottom:15px;
            }
            .idea p{
                font-size: 14px;
                line-height:20px;
                letter-spacing: 1px;
                margin-top:6px;
            }

        }
        @media screen and (max-width:360px){
            article{
                font-size:13px;
                line-height:18px;
            }
            p strong{
                font-size:15px;
                font-weight: normal;
            }
        }
    </style>
</head>
<body>
<div id="wrap">
    <div class="content">
        <!--免责声明-->
        <article>
            <h1 class="text-center ">免责声明</h1>
            <section class="main ">
                <p>尊敬的用户：</p>
                <p>您好，欢迎您在测试过程中浏览和体验我们的网站。为了测试网站的功能和体验效果，我们在测试环境下展示的所有信息，仅是为了测试网站的可靠性而存在的，并不是真实有效的版权方信息内容。我们对现阶段网站展示的任何信息的真实性不作保证，请您不要相信任何包括联系方式在内的所有信息。如果您通过使用现有信息内容所造成的结果，版权方将不承担任何责任。</p>
                <p><strong> 如果您已经理解和认可本声明，请进入测试网站体验。</strong></p>
            </section>
        </article>
        <!--倒计时-->
        <div class="tip-content text-center ">
            <div class="tip-main ">
                <div id='timer'><span>10</span>S</div>
                <a id="text " href="javascript:void(history.go(0));" style="color:#fff;text-decoration: none;">后自动进入测试网站</a>
            </div>
        </div>
    </div> <!--content结束-->
</div> <!--wrap结束-->
<script>
    var timer = document.getElementById('timer').getElementsByTagName('span')[0];
    var sec = 10;
    timer.textContent = sec;
    function timerInterval() {
        var interval;
        sec -= 1;
        if (sec > 0) {
            timer.textContent = sec;
            interval = setTimeout(timerInterval, 1000)
        } else {
            clearTimeout(interval);
            history.go(0);
        }
    }
    setTimeout(timerInterval, 1000);
</script>
</body>
</html>