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
    const MAP_WEIGHT = 800 - 1;

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
     *
     */
    public function onEvent()
    {
        foreach ($this->actorList as $actor) {
            $actor->onEvent();
        }

        //让用户加入游戏
        $this->consumeUserWaitQueue();
    }

    /**
     * 消耗正在排队的队列
     */
    private function consumeUserWaitQueue()
    {
        if (count($this->userList) < self::USER_MAX && count($this->userWaitQueue) > 0) {
            $user = array_shift($this->userWaitQueue);


            $user->beforeEnter();
            $this->userList[$user->userId] = $user;
            $this->actorList[$user->userId] = $user;

            $postData = $user->getPositionData();
            Host::pushToAllUser('UserInit', $postData);

            Host::pushToAllUser('QueueExit', ['id' => $user->id]);
        }
    }
}