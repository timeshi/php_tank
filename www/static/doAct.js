function login()
{
    let userName = $("#userName").val();
    if (userName.length < 3) {
        alert('姓名不能小于3个字符');
        return ;
    }

    let isJoin = document.getElementById("joinQueue").checked;
    doLogin(userName, isJoin);
    $("#readMe").remove();
    $("#loginBar").remove();
}

function chatSend()
{
    let event = window.event||event;
    let keyCode = event.which;
    if (keyCode !== 13) { //非回车键，返回
        return ;
    }

    let content = $("#chatContent").val();
    if (!content) {
        alert('请输入聊天内容');
        return ;
    }

    $("#chatContent").val('');
    doChat(content);
}

function doQueueJoin()
{
    let data = {
        act : 'doQueueJoin'
    }
    wsClient.doAct(data);
}

function doQueueExit()
{
    let data = {
        act : 'doQueueExit'
    }
    wsClient.doAct(data);
}

function doLogin(name, isJoin)
{
    if (myUserId > 0) {
        return;
    }

    let data = {
        name : name,
        token : getUserToken(),
        isJoin : isJoin ? 1 : 0,
        act : 'doLogin'
    }
    wsClient.doAct(data);
}

//用户移动
function doUserMove(dir)
{
    let data = {
        dir : dir,
        act : 'doUserMove'
    }
    wsClient.doAct(data);
}

//用户开火
function doUserFire()
{
    let data = {
        act : 'doUserFire'
    }
    wsClient.doAct(data);
}

//用户聊天
function doChat(content)
{
    let data = {
        content : content,
        act : 'doChat'
    }
    wsClient.doAct(data);
}

//用户死亡
function doTankDeath()
{

}

//用户
function doUserLevel()
{

}