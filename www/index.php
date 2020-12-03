<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>90坦克大乱斗 - php_tank 演示页</title>
    <script src="/static/jquery.min.js"></script>
    <script src="/static/wsClient.js"></script>
    <script src="/static/doAct.js"></script>
    <script src="/static/doEvent.js"></script>
    <link rel="stylesheet" href="/static/css.css"/>
    <style type="text/css">
        #page { width: 90%; margin: 0 auto;}
    </style>
    <script>
        let userPhpToken = '<?= md5(uniqid('https://china-news.net/', true));?>'; //php生成一个唯一的token给客户端使用
        let myUserId = 0; //当前登录的用户id
        let wsClient;
        let wsUrl = window.location.href.replace('http', 'ws');
        let lastMoveMs = 0; //最后移动事件
        let lastFireMs = 0; //最后发射时间
    </script>
</head>

<body>
<div id="battleGround">
    <div id="userLayer">
    </div>
    <div id="bulletLayer">
    </div>
</div>

<div id="readMe">
    <div style="text-align: right;"><a href="javascript:;" id="closeReadMe" onclick="$('#readMe').remove()">关闭</a>&nbsp;</div>
   <?= file_get_contents(ROOT . 'readme.txt'); ?>
</div>

<div id="queueBox">
    <div class="queueBoxTitle">战斗排队列表</div>
    <div class="queueBoxButton"><input type="button" value="加入" onclick="doQueueJoin()">&nbsp;&nbsp;<input type="button" value="离开" onclick="doQueueExit()"></div>
    <div class="queueBoxRank">
        <a href="rank.php?type=1" target="_blank">全局排名</a><br/>
        <a href="rank.php?type=2" target="_blank">单次排名</a>
    </div>
    <ul id="queueList">
    </ul>
</div>

<div id="loginBar">
    请为你起一个高大威猛昵称，登录后开始游戏<br/>
    <input type="text" size="20" value="" id="userName" placeholder="请输入你的游戏昵称" maxlength="12" autocomplete="off"><br/>
    直接加入战斗排队<input type="checkbox" id="joinQueue" checked><br/>
    <input type="button" value=" 登录 " onclick="login()"><br/>
</div>

<div id="chatBox">
</div>
<div id="chatBoxInput">
    <input id="chatContent" type="text" size="120" placeholder="请输入聊天内容,回车发送消息" maxlength="96" onkeyup="chatSend()">
</div>
</body>
</html>
