<?php
/**
 * @example  https://china-news.net/game/play/1001-php-tank
 * @author  一曲小诗 (QQ:81769876)
 * @contact  81769876@qq.com
 * @copyright your code
 */

/**
 * 游戏房间，小游戏战斗使用
 */
class Room extends Space
{
    /**
     * 房间地图宽度
     */
    const MAP_WEIGHT = 800 - 1;

    /**
     * 房间地图高度
     */
    const MAP_HEIGHT = 800 - 1;

    /**
     * 单局最大玩家数量
     */
    const USER_MAX = 6;

    /**
     * 当前房间内活跃的用户(tank列表)
     * @var User[]
     */
    public $userList = [];

    /**
     * 正在排队的用户
     * @var User[]
     */
    public $userWaitQueue = [];

    /**
     * 当前房间内的机器人列表，用于陪玩
     * @var Robot[]
     */
    public $robotList = [];

    /**
     * 初始化room对象
     * Room constructor.
     */
    public function __construct()
    {
        $this->initEnv();
    }

    /**
     * 初始化环境，增加机器人
     */
    public function initEnv()
    {
        for ($i=1; $i<=2; $i++) {
            $userId = Host::getAutoUserId();
            $robot = new Robot($userId);
            $robot->name = "陪玩坦克【{$i}】";
            $this->robotList[$userId] = $robot;
        }
    }

    /**
     *
     */
    public function onEvent()
    {
        foreach ($this->actorList as $actor) {
            $actor->onEvent();
        }

        //让用户加入游戏
        $this->consumeUserWaitQueue();

        //每个一秒触发一次(加入陪玩机器人)
        if (Timer::$index % 10 < 1) {
           if (count($this->userList) < 3) {
               foreach ($this->robotList as $robotId => $robot) {
                   if (isset($this->userList[$robotId]) || isset($this->userWaitQueue[$robotId])) {
                        continue;
                   }
                   $robot->queueJoin();
                   break;
               }
           }
        }
    }

    /**
     * 消耗正在排队的队列
     */
    private function consumeUserWaitQueue()
    {
        if (count($this->userList) < self::USER_MAX && count($this->userWaitQueue) > 0) {
            $user = array_shift($this->userWaitQueue);

            $user->beforeEnter();

            //绑定房间
            $user->room = $this;

            //绑定关系
            $this->userList[$user->userId] = $user;
            $this->actorList[$user->userId] = $user;

            $postData = $user->getPositionData();
            Host::pushToAllUser('UserInit', $postData);

            Host::pushToAllUser('QueueExit', ['id' => $user->id]);

            Logger::debug('UserInit', $postData);
        }
    }
}