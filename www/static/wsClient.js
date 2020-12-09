// 连接服务端
function wsConnect() {
    // 创建websocket
    wsClient = new WebSocket(wsUrl);

    // 当socket连接打开时，输入用户名
    wsClient.onopen = function () {
        console.log("连接打开");
    };
    // 当有消息时根据消息类型显示不同信息
    wsClient.onmessage = function (event) {
        console.log(event.data);
        let json = JSON.parse(event.data);

        //执行调用
        try {
            let act = json.act;
            eval(act + '(json)');
        } catch (e) {
            console.log(e);
            console.log("未定义ps:" + act);
        }
    };

    wsClient.onclose = function() {
        console.log("连接关闭");
    };

    wsClient.onerror = function() {
        console.log("出现错误");
    };

    wsClient.doAct = function(data)
    {
        let act = data['act'];
        if (act.substr(0, 2) === 'do') { //删除前两个字符可能是do的字符
            act = act.substr(2);
        }
        data['act'] = act;
        this.send(JSON.stringify(data));
    };
}

//获取用户token
function getUserToken()
{
    let token = window.localStorage.getItem('token');
    if (!token) {
        token = userPhpToken;
        window.localStorage.setItem('token', userPhpToken)
    }
    return token;
}

//获取当前毫秒数
function getMsTime()
{
    return (new Date()).getTime()
}

$(function(){
    wsConnect();
    let keyMap = {
        68 : 1, //d
        100 : 1, //D

        83 : 2, //s
        115 : 2, //S

        65 : 3, //a
        97 : 3, //A

        87 : 4, //w
        119 : 4, //w
    }

    let keyDownMap = {}
    onkeydown = onkeyup = function (event) {
        let keyCode = event.which;
        if (event.type == 'keydown') {
            keyDownMap[keyCode] = true;

            //直接发射炮弹
            if (myUserId > 0  && (keyCode==74 || keyCode == 106)) {
                let nowTime = getMsTime();
                if (nowTime - lastFireMs >= 1000) { //一秒才允许发一个子弹
                    lastFireMs = nowTime;
                    doUserFire();
                }
            }
        } else {
            delete keyDownMap[keyCode];
        }
    }

    //100秒执行一次
    setInterval(function(){
        if (myUserId < 1) { //用户未登录，不触发控制
            return ;
        }

        //移动
        for (keyCode in keyDownMap) {
            if (typeof keyMap[keyCode] !== 'undefined') {
                let nowTime = getMsTime();
                if (nowTime - lastMoveMs >= 200) {
                    lastMoveMs = nowTime;
                    doUserMove(keyMap[keyCode]);
                }
            }
        }
    }, 100);
});