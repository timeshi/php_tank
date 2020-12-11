<?php
/**
 * @example  https://china-news.net/game/play/1001-php-tank
 * @author  一曲小诗 (QQ:81769876)
 * @contact  81769876@qq.com
 * @copyright your code
 */

/**
 * npc子弹
 */
class Bullet extends Npc
{
    /**
     * 子弹类型：普通子弹
     */
    const TYPE_BULLET = 1;

    /**
     * 子弹类型：炸弹
     */
    const TYPE_BOMB = 2;

    /**
     * @var User
     */
    public $owner = null;

    /**
     * 子弹血量默认为1
     * (如果大于1，可以做穿甲弹逻辑)
     * @var int
     */
    public $hp = 1;

    /**
     * @var int
     */
    public $speed = 20;

    /**
     * @var int
     */
    public $type = 1;

    /**
     * 开始时间戳
     * @var int
     */
    public $startTimerIndex = 0;

    /**
     * 生命周期，子弹能存在多久
     * @var int
     */
    public $lifeTime = 0;

    /**
     * @var int
     * 默认碰撞半径的平方（直接比较两个半径的平方值，避免开方浪费计算资源）
     * 系统做了简化处理，把子弹单做一个点，把坦克当初一个圆（虽然坦克是方形的）
     */
    public $killRadiusSquare = 900;

    /**
     * 初始化一个对象
     * @param User $user
     * @param int $dir 子弹朝向
     * @return static
     */
    public static function initByUser($user, $dir)
    {
        $npcId = self::getAutoId();
        return new self($npcId, $user, $dir);
    }

    /**
     * Bullet constructor.
     * @param int $npcId
     * @param User $user
     * @param int $dir
     */
    public function __construct($npcId, $user, $dir)
    {
        $this->startTimerIndex = Timer::$index;
        $this->id = $npcId;
        $this->owner = $user;
        $this->room = $user->room;
        $this->dir = $dir;
        $this->x = $user->x;
        $this->y = $user->y;
        $this->setKillRadius(30);
    }

    /**
     * @param int $radius
     * 系统做了简化处理，把子弹单做一个点，把坦克当初一个圆（虽然坦克是方形的）
     */
    public function setKillRadius($radius = 30)
    {
        $this->killRadiusSquare = $radius * $radius;
    }

    /**
     *
     */
    public function onEvent()
    {
        //如果子弹的速度大于0，则飞行
        if ($this->speed > 0) {
            $this->onMove();
        }

        //子弹超过生命生命周期
        if ($this->lifeTime > 0 && (Timer::$index - $this->startTimerIndex > $this->lifeTime)) {
            $this->isDeath = true; //变成死亡状态
        }

        //如果死亡了
        if ($this->isDeath) {
            unset($this->room->actorList[$this->id]);

            Host::pushToAllUser('ActorDeath', ['id' => $this->id]);
            $this->gc();
            return;
        }

        //计算碰撞
        $this->onCollision();
    }

    /**
     *
     */
    public function onMove()
    {
        $this->moveDis($this->dir);
        if ($this->x==0 || $this->x==Room::MAP_WEIGHT || $this->y==0 || $this->y==Room::MAP_HEIGHT) {
            $this->isDeath = true;
        }
    }

    /**
     *
     */
    public function onCollision()
    {
        foreach ($this->room->userList as $user) {
            //过滤掉本身对象
            if ($user === $this->owner) {
                continue;
            }

            //对象死亡，不考虑
            if ($user->isDeath) {
               continue;
            }

            //按照圆计算碰撞
            $dx = $user->x - $this->x;
            $dy = $user->y - $this->y;
            if (($dx * $dx + $dy * $dy) < $this->killRadiusSquare) {
                $user->onCollision($this);
                $this->hpDecr(1); //子弹血量减少1
                break;
            }
        }
    }

    /**
     * @return array
     */
    public function getInitData()
    {
        list($dx, $dy) = self::$dirVectorList[$this->dir];

        //飞行目的地位置
        $toX = $this->x + $dx * 99999;
        $toY = $this->y + $dy * 99999;
        Util::between($toX, 0, Room::MAP_WEIGHT);
        Util::between($toY, 0, Room::MAP_HEIGHT);

        //飞行目的地
        if ($this->speed < 1) {
            $toX = $this->x;
            $toY = $this->y;
        }

        //计算目的距离
        $dis = sqrt(pow($this->x - $toX, 2) + pow($this->y - $toY, 2));

        //返回时间
        return [
            'id' => $this->id,
            'type' => $this->type,
            'dir' => $this->dir,
            'x' => $this->x,
            'y' => $this->y,
            'toX' => $toX,
            'toY' => $toY,
            'toTime' => ($this->speed > 0) ? ($dis / $this->speed) * 100 : 0,
            'speed' => $this->speed,
        ];
    }
}