<?php
/**
 * @example  https://china-news.net/game/play/1001-php-tank
 * @author  一曲小诗 (QQ:81769876)
 * @contact  81769876@qq.com
 * @copyright your code
 */

/**
 * 启动的服务主机，就是一个游戏大厅，一个游戏大厅内的玩家能实时互动，跨大厅的玩家只能异步互动
 * (一个进程就是一个主机，如果进程挂了，所有的信息都会丢失)
 */
class Host
{
    /**
     * @var Swoole\WebSocket\Server
     */
    public static $ws = null;

    /**
     * 登录锁列表
     * @var array
     */
    public static $loginLockList = [];

    /**
     * 当前登录用户列表，用户id是下标
     * @var User[]
     */
    public static $userList = [];

    /**
     * 网络连接符号与用户列表，下标是网络连接fd
     * @var User[]
     */
    public static $fdList = [];


    /**
     * 登录校验与用户id对照关系
     * @var array [$token => $userId]
     */
    public static $tokenList = [];

    /**
     * 当前角色id
     * @var int
     */
    public static $autoUserId = 1000001;

    /**
     * 全局唯一的room
     * （本游戏就一个大厅一个房间，如果想要多个房间，可以自己扩展）
     * @var Room
     */
    public static $room = null;

    /**
     * 初始化环境
     */
    public static function initEnv()
    {
        //1.初始化一个空房间
        self::$room = new Room();
        Timer::add(self::$room);
    }

    /**
     * 获取自增角色id
     * @return int
     */
    public static function getAutoUserId()
    {
        self::$autoUserId++;
        return self::$autoUserId;
    }

    /**
     * 主动关闭连接
     * @param $fd
     */
    public static function close($fd)
    {
        unset(self::$loginLockList[$fd]);
        self::$ws->close($fd);
    }

    /**
     * 当客户端主动断开连接
     * @param $fd
     */
    public static function onFdClose($fd)
    {
    }

    /**
     * 把消息推送给客户端
     * @param $fd
     * @param $data
     */
    public static function pushByFd($fd, $data)
    {
        if (!Host::$ws->isEstablished($fd)) {
            Logger::info(__METHOD__, "fd:{$fd} is not Established");
            if (isset(self::$fdList[$fd])) {
                self::$fdList[$fd]->fd = 0; //把关联用户的设置成0
                unset(self::$fdList[$fd]); //删除已经失效的连接
            }
            return;
        }

        if (is_array($data)) {
            $data = Json::encode($data);
        }

        Host::$ws->push($fd, $data);
        Logger::debug(__METHOD__, $fd, $data);
    }

    /**
     * 把消息推送给某个用户id
     * @param $userId
     * @param $act
     * @param array $data
     */
    public static function pushByUserId($userId, $act, $data = [])
    {
        if (isset(Host::$userList[$userId]) && Host::$userList[$userId]->fd) {
            $data['act'] = $act;
            self::pushByFd(Host::$userList[$userId]->fd, $data);
        }
    }

    /**
     * 把消息推送给所有用户
     * @param $act
     * @param array $data
     */
    public static function pushToAllUser($act, $data = [])
    {
        $data['act'] = $act;
        $data = Json::encode($data);

        foreach (Host::$fdList as $fd => $user) {
            if (!Host::$ws->isEstablished($fd)) {
                Logger::info(__METHOD__, "fd:{$fd} is not Established");
                $user->fd = 0;
                unset(self::$fdList[$fd]); //删除已经失效的连接
                continue;
            }

            //推送具体信息
            Host::$ws->push($fd, $data);
            Logger::debug(__METHOD__, $fd, $data);
        }
    }

    /**
     * 把消息推送给用户列表
     * @param User[] $userList
     * @param string $act
     * @param array $data
     */
    public static function pushToUserList($userList, $act, $data = [])
    {
        $data['act'] = $act;
        $data = Json::encode($data);

        foreach ($userList as $user) {
            $fd = $user->fd;
            if ($fd < 1) {
                continue;
            }

            if (!Host::$ws->isEstablished($fd)) {
                Logger::info(__METHOD__, "fd:{$fd} is not Established");
                continue;
            }

            //推送具体信息
            Host::$ws->push($fd, $data);
            Logger::debug(__METHOD__, $fd, $data);
        }
    }
}