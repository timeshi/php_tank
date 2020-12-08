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
     * 默认碰撞半径
     * 系统做了简化处理，把子弹单做一个点，把坦克当初一个圆（虽然坦克是方形的）
     */
    const COLLISION_RADIUS_SQUARE = 30 * 30;

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
     * 初始化一个对象
     * @param User $user
     * @return static
     */
    public static function initByUser($user)
    {
        $npcId = self::getAutoId();
        return new self($npcId, $user);
    }

    /**
     * Bullet constructor.
     * @param int $npcId
     * @param User $user
     */
    public function __construct($npcId, $user)
    {
        $this->id = $npcId;
        $this->owner = $user;
        $this->room = $user->room;
        $this->dir = $user->dir;
        $this->x = $user->x;
        $this->y = $user->y;
    }

    /**
     *
     */
    public function onEvent()
    {
        //子弹飞行
        $this->onMove();

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
            if (($dx * $dx + $dy * $dy) < self::COLLISION_RADIUS_SQUARE) {
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
            'toTime' => ($dis / $this->speed) * 100,
            'speed' => $this->speed,
        ];
    }
}