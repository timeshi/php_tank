<?php
/**
 * @example  https://china-news.net/game/play/1001-php-tank
 * @author  一曲小诗 (QQ:81769876)
 * @contact  81769876@qq.com
 * @copyright your code
 */

/**
 * 游戏入口页，控制所有ws请求
 */
class Action
{
    /**
     * 执行入口
     * @param int $fd
     * @param array $data
     */
    public static function doAction($fd, $data)
    {
        $act = $data['act'];

        //用户登录
        if ($act == 'Login') { //非登录角色
            self::doLogin($fd, $data);
            return;
        }

        //正常用户请求
        $user = Host::$fdList[$fd] ?? null;
        if (!$user) {
            E::out('请先登录');
        }

        //防止客户端传入action，
        $method = 'do' . $act;
        //Logger::info(__METHOD__, $method);
        self::$method($user, $data);
    }

    /**
     * 用户登录，一个用户可以重复登录，如果用户中途掉线，则再次登录后击杀数量保持不变
     * @param $fd
     * @param $data
     */
    public static function doLogin($fd, $data)
    {
        //检测登录锁
        if (isset(Host::$loginLockList[$fd]) && (Timer::$index - Host::$loginLockList[$fd]) < 100) {
            E::out('正在登录中，请稍后');
        }

        //增加登录锁
        Host::$loginLockList[$fd] = Timer::$index;

        //判断是否重复登录
        if (isset(Host::$fdList[$fd])) { //用户已登录，重复发送了登录，强制退出
            return;
        }

        $token = $data['token'];
        $userId = Host::$tokenList[$token] ?? 0;

        //创建新用户
        if (!isset(Host::$userList[$userId])) {
            $userId = Host::getAutoUserId();
            Host::$userList[$userId] = new User($userId);
            Host::$tokenList[$token] = $userId; //token与用户关联，同一设备的用户用户登录保持一致
        }

        //关联用户与fd的关系
        $user = Host::$userList[$userId];
        $user->fd = $fd;
        $user->name = htmlspecialchars(mb_substr($data['name'], 0, 12)); //防止非法字符串
        $user->room = Host::$room; //默认绑定第一个room

        //关联fd与user的关系
        Host::$fdList[$fd] = $user;

        //推送全量战场数据与排队列表
        $queueList = [];
        foreach (Host::$room->userWaitQueue as $item) {
            $queueList[] = [
                'id' => $item->id,
                'name' => $item->name,
            ];
        }

        $tankList = [];
        foreach (Host::$room->userList as $item) {
            $tankList[] = $item->getPositionData();
        }

        $pushData = [
            'act' => 'GameInit',
            'userId' => $userId,
            'queueList' => $queueList,
            'userList' => $tankList,
        ];
        Host::pushByFd($fd, $pushData);

        //欢迎进入游戏
        $user->sendChatMsg('欢迎{name}进入了游戏,当前在线总人数:' . count(Host::$fdList));

        //是否加入战斗
        if (isset($data['isJoin']) && $data['isJoin']) {
            $user->queueJoin();
        }

        //登录成功，删除登录锁
        unset(Host::$loginLockList[$fd]);
    }

    /**
     * 用户发送一个聊天
     * @param User $user
     * @param array $data
     */
    public static function doChat($user, $data)
    {
        $pushData = [
            'userId' => $user->userId,
            'name' => $user->name,
            'content' => date('[H:i:s]') . mb_substr(htmlspecialchars($data['content']), 0, 96),
        ];
        Host::pushToAllUser('Chat', $pushData);
    }

    /**
     * 用户发送一个聊天
     * @param User $user
     */
    public static function doQueueJoin($user, $data)
    {
        $user->queueJoin();
    }

    /**
     * 用户发送一个聊天
     * @param User $user
     */
    public static function doQueueExit($user)
    {
        $user->queueExit();
    }

    /**
     * 用户发送一个聊天
     * @param User $user
     */
    public static function doUserMove($user, $data)
    {
        if ($user->isDeath) {
            return;
        }
        $user->move($data['dir']);
    }

    /**
     * 用户发送一个聊天
     * @param User $user
     */
    public static function doUserFire($user)
    {
        if ($user->isDeath) {
            return;
        }
        $user->fire();
    }
}