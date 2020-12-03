<?php
/**
 * @example  https://china-news.net/game/play/1001-php-tank
 * @author  一曲小诗 (QQ:81769876)
 * @contact  81769876@qq.com
 * @copyright your code
 */

/**
 * 用户（也是tank）
 */
class User extends Actor
{
    /**
     *
     */
    const HP_MAX = 3;

    /**
     * @var int
     */
    public $fd = 0;

    /**
     * @var int
     */
    public $userId = 0;

    /**
     * @var string
     */
    public $name = '';

    /**
     * 总的击杀数量
     * @var int
     */
    public $killNumAll = 0;

    /**
     * 单局的击杀总梳理
     * @var int
     */
    public $killNumOne = 0;

    /**
     * 临时击杀数量
     * @var int
     */
    public $killNumTemp = 0;


    /**
     * 加入游戏的时间帧，前5秒是无敌状态，不会被击打
     * @var int
     */
    public $enterTimerIndex = 0;

    /**
     * 最后开火时间
     * @var int
     */
    public $lastFireTime = 0;

    /**
     * 最后移动时间
     * @var int
     */
    public $lastMoveTime = 0;


    /**
     * User constructor.
     * @param $userId
     * @param $fd
     * @param $name
     */
    public function __construct($userId)
    {
        $this->id = $userId;
        $this->userId = $userId;
    }

    /**
     * @return bool|void
     */
    public function onEvent()
    {
        if ($this->isDeath) {
            unset($this->room->actorList[$this->id]);
            unset($this->room->userList[$this->id]);
            Host::pushToAllUser('ActorDeath', ['id' => $this->id]);
            return;
        }
    }


    /**
     * 玩家移动位置
     * @param $dir
     */
    public function move($dir)
    {
        //防止客户端捣乱
        if (Timer::$index - $this->lastMoveTime < 2) {
            return;
        }

        $this->lastMoveTime = Timer::$index;

        $this->moveDis($dir);

        $moveData = [
            'id' => $this->id,
            'x' => $this->x,
            'y' => $this->y,
            'dir' => $dir,
        ];
        Host::pushToAllUser('UserMove', $moveData);
    }

    /**
     * 玩家开火
     */
    public function fire()
    {
        //防止客户端捣乱
        if (Timer::$index - $this->lastFireTime < 10) {
            return;
        }

        $this->lastFireTime = Timer::$index;

        $bullet = Bullet::initByUser($this);
        $bullet->room->actorList[$bullet->id] = $bullet;

        $bulletData = $bullet->getInitData();
        Host::pushToAllUser('BulletInit', $bulletData);
    }

    /**
     * 当进入房间前，初始化角色的数据
     */
    public function beforeEnter()
    {
        $this->x = mt_rand(0, Room::MAP_WEIGHT);
        $this->y = mt_rand(0, Room::MAP_HEIGHT);
        $this->enterTimerIndex = Timer::$index;
        $this->killNumTemp = 0; //单局击杀数量重置为0

        $this->hp = self::HP_MAX;
        $this->hpMax = self::HP_MAX;
        $this->isDeath = false; //未死亡状态
    }

    /**
     * @return array
     */
    public function getPositionData()
    {
        return [
            'id' => $this->userId,
            'name' => $this->name,
            'x' => $this->x,
            'y' => $this->y,
            'dir' => $this->dir,
            'hp' => $this->hp,
            'hpMax' => $this->hpMax,
        ];
    }

    /**
     * 当用户掉线
     */
    public function onFdClose()
    {
        //退出房间
        if ($this->room) {
            unset($this->room->userList[$this->id]);
            unset($this->room->userWaitQueue[$this->id]);
        }

        //退出大厅
        unset(Host::$fdList[$this->fd]);
        unset(Host::$loginLockList[$this->fd]);
        //unset(Host::$userList[$this->id]); //不能删除userList,需要N天后再删除冷用户

        //连接句柄归零
        $this->fd = 0;

        //推送消息
        Host::pushToAllUser('UserExit', ['id'=>$this->id]);
    }

    /**
     * @throws E
     */
    public function queueJoin()
    {
        if (isset(Host::$room->userWaitQueue[$this->userId]) || isset(Host::$room->userList[$this->userId])) {
            E::out('你已经加入了过队列或正在战斗');
        }

        Host::$room->userWaitQueue[$this->userId] = $this;
        $data = [
            'id' => $this->id,
            'name' => $this->name,
        ];
        Host::pushToAllUser('QueueJoin', $data);

        $this->sendChatMsg('{name}加入了战斗排队');
    }

    /**
     * 当被子弹击中
     * @param Bullet $bullet
     */
    public function onCollision($bullet)
    {
        //自己键一分
        $this->hpDecr(1);

        if ($this->isDeath) {
            //击杀者得一分
            $bullet->owner->hpIncr(1);

            //击杀了一个
            $bullet->owner->onKillOne();

            //公告
            $this->sendChatMsg('【' . $bullet->owner->name . '】击杀了{name}，增加了1点血。');
        }
    }

    /**
     * @throws E
     */
    public function queueExit()
    {
        if (!isset(Host::$room->userWaitQueue[$this->userId])) {
            E::out('你已经退出了队列');
        }

        unset(Host::$room->userWaitQueue[$this->userId]);
        $data = [
            'id' => $this->id,
        ];
        Host::pushToAllUser('QueueExit', $data);

        $this->sendChatMsg('{name}退出了战斗排队');
    }

    /**
     * 发送关于当前用户的聊天信息
     * @param $content
     */
    public function sendChatMsg($content)
    {
        $pushData = [
            'name' => '系统',
            'content' =>  date('[H:i:s]') . str_replace('{name}', '【' . $this->name . '】', $content),
        ];
        Host::pushToAllUser('Chat', $pushData);
    }

    /**
     *
     */
    public function onKillOne()
    {
        $this->killNumAll ++;
        $this->killNumTemp ++;
        if ($this->killNumTemp > $this->killNumOne) {
            $this->killNumOne = $this->killNumTemp;
        }
    }
}