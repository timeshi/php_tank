function Error(data)
{
    if (data['code'] < 0){
        return ;
    }


    alert(data['msg']);
}


function QueueJoin(data)
{
    let html = '<li id="q' + data.id + '">' + data.name + '</li>';
    $("#queueList").append(html);
}

function QueueExit(data)
{
    let id = 'q' + data.id;
    $("#" + id).remove();
}

function UserExit(data)
{
    QueueExit(data); //从排队里删除
    $("#" + data.id).remove(); //从战场里删除
}

function GameInit(data)
{
    //设置当前用户的id
    myUserId = data.userId;

    let queueList = data.queueList;
    for (let i=0; i<queueList.length; i++) {
        QueueJoin(queueList[i]);
    }

    let userList = data.userList;
    for (let i=0; i<userList.length; i++) {
        UserInit(userList[i]);
    }
}

function Move(data)
{

}

function Chat(data)
{
    let chatBox = document.getElementById("chatBox");
    let line = '<strong>' + data.name + '</strong>: ' + data.content + '<br/>' + "\n";
    chatBox.innerHTML += line
    chatBox.scrollTop = 99999999; //滚动最底层
}

function JoinQueue(userId, data)
{
}

function JoinBattle(userId, data)
{
}

function TankMove(userId, data)
{
    let dir = data['dir'];
    let x = data['x'];
    let y = data['y'];
    $("#tank" + userId).css({left:data.x+'px', top:data.y+'px'});
}

function BulletMove()
{

}

function UserInit(data)
{
    let html = '<tank id="' + data.id + '" class="' + (data.id === myUserId ? 'my' : '') + '" style="left:' + data.x + 'px; top:' + data.y + 'px;"><name>' + data.name + '</name><bg style="transform:' + 'rotate(' + (data.dir * 90) + 'deg)' + '"></bg><hp>' + data.hp + '</hp></tank>';
    let me = $(html);
    me.appendTo("#userLayer");
}

function BulletInit(data)
{
    let html = '<bullet id="' + data.id + '" style="left:' + data.x + 'px; top:' + data.y + 'px;"><bulletType' + data.type + '></bulletType' + data.type + '></bullet>';
    let me = $(html);
    me.appendTo("#bulletLayer");
    me.animate({left:data.toX + "px", top:data.toY + "px"}, data.toTime, 'linear', function(){
        //me.remove();
    });
}

//用户移动
function UserMove(data)
{
    let user = $("#" + data.id);
    user.find("bg").css({'transform' : 'rotate(' + (data.dir * 90) + 'deg)'});


    user.stop().animate({left:data.x + "px", top:data.y + "px"}, 300, 'linear');
}

function ActorHpDecr(data)
{
    $("#" + data.id).find('hp').html(data.hp);
}

function ActorHpIncr(data)
{
    $("#" + data.id).find('hp').html(data.hp);
}

function ActorDeath(data)
{
    $("#" + data.id).remove();

    if (data.id === myUserId) {
        alert('你已经牺牲，可以点击右边的队列再次加入游戏');
    }
}