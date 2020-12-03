<?php
require __DIR__ . '/init.php';

define('START_TIME', time()); //系统启动时间
define('IS_DEBUG', in_array('debug', $argv)); //是否调试模式
define('HOST_PORT', 8084); //启动端口
define('MAX_BATTLE_ROLE', 6); //最多6人参与战斗
define('MAX_CONN', 300); //最多连接数



//预先加载常用的数据类
require ROOT_LIB . 'E.php';

//新建websocket服务器,使用基本模式，单个进程服务
$ws = new Swoole\WebSocket\Server('0.0.0.0', HOST_PORT, SWOOLE_BASE);
$ws->set([
    'worker_num' => 1, //工作worker
    'daemonize' => IS_DEBUG ? 0 : 1, //debug模式下不开启守护进程
    'log_file' => ROOT_LOG . '.log', //记录日志
    'pid_file' => ROOT_LOG . '.pid', //主进程pid文件
]);

//当子进程几区
$ws->on("workerStart", function(Swoole\Http\Server $server) {
    Logger::info("onWorkerStart", "worker_id:{$server->worker_id}", "worker_pid:{$server->worker_pid}");

    //初始化定时器
    Timer::initEnv();

    //初始化主机环境
    Host::initEnv();
});

//处理http请求
$ws->on("request", function (Swoole\Http\Request $request, Swoole\Http\Response $response) {
    //请求url
    $uri = $request->server['request_uri'];
    $uri = trim($uri, '/');
    if ($uri == '') {
        $uri = 'index.php';
    }

    //获取文件的真实路径
    $file = ROOT_WWW . $uri;
    $file = realpath($file);

    //获取文章扩展名
    $fileExt = substr(strrchr($file, '.'), 1);
    $contentTypeList = [
        'php' => 'text/html; charset=utf-8',
        'css' => 'text/css; charset=utf-8',
        'js' => 'application/x-javascript; charset=utf-8',
        'jpg' => 'image/jpg',
        'png' => 'image/png',
        'gif' => 'image/gif',
    ];

    //文件不存在，文件网站根目录，文件扩展不存在，都返回404
    if (!is_file($file) || strpos($file, ROOT_WWW) !== 0 || !isset($contentTypeList[$fileExt])) {
        $response->status(404);
        $response->end('no file find!');
    } else {
        //加载文件php文件
        if ($fileExt == 'php') {
            ob_start();
            $_GET = $request->get;
            include $file; //加载PHP模板
            $html = ob_get_contents();
            ob_end_clean();
        } else {
            $html = file_get_contents($file);
        }

        //输出数据
        $response->header("Content-Type", $contentTypeList[$fileExt]);
        $response->end($html);
    }
});

//当链接打开
$ws->on('open', function(Swoole\WebSocket\Server $server, $frame) {
    Logger::info("onOpen", "fd={$frame->fd}");
});

//当链接关闭
$ws->on('close', function(Swoole\WebSocket\Server $server, $fd) {
    Logger::info("onClose", "fd={$fd}");

    //客户端断开，通知用户关闭连接，
    if (isset(Host::$fdList[$fd])) {
        Host::$fdList[$fd]->onFdClose();
    }
});

//当接收到信息
$ws->on("message", function (Swoole\WebSocket\Server $server, $frame){
    $fd = $frame->fd;
    $data = $frame->data;
    Logger::info("onMessage", $fd, $data, "worker_id:" . $server->worker_id, "worker_pid:" . $server->worker_pid);

    try {
        $data = Json::decode($data);
        Action::doAction($fd, $data);
    } catch (Throwable $e) {
        $msg = $e->getMessage();
        $json = [
            'act' => 'Error',
            'msg' => $msg,
        ];
        Host::pushByFd($fd, $json);
        Logger::error('onMessageError', $fd, $data, $msg);

        //记录非法异常
        if (!($e instanceof E)) {
            Logger::logException($e);
        }
    }
});

//绑定全局变量
Host::$ws = $ws;

//启动进程
$ws->start();