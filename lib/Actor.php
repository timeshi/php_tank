<?php
/**
 * @example  https://china-news.net/game/play/1001-php-tank
 * @author  一曲小诗 (QQ:81769876)
 * @contact  81769876@qq.com
 * @copyright your code
 */

/**
 * 地图上的所有实体对象:Role, Robot, Npc, Bullet
 */
class Actor extends Obj
{
    /**
     * 方向
     * @var int[][]
     */
    public static $dirVectorList = [
        1 => [1, 0], //右
        2 => [0, 1], //下
        3 => [-1, 0], //左
        4 => [0, -1], //上，客户端正好旋转360度
    ];

    /**
     * 形状类型：圆形
     */
    const SHAPE_CIRCLE = 1;

    /**
     * 形状类型：矩形
     */
    const SHAPE_RECT = 2;

    /**
     * y坐标
     * @var int
     */
    public $x = 0;

    /**
     * x坐标
     * @var int
     */
    public $y = 0;

    /**
     * 形状类型
     * @var int
     */
    public $shapeType = self::SHAPE_CIRCLE;

    /**
     * 对象大小
     * @var int
     */
    public $radius = 0;

    /**
     * 角度朝向,1右，2下，3左，4右
     * @var int
     */
    public $dir = 1;

    /**
     * 是否死亡
     * @var bool
     */
    public $isDeath = false;

    /**
     * 当前血量
     * @var int
     */
    public $hp = 1000;

    /**
     * 最大血量
     * @var int
     */
    public $hpMax = 1000;

    /**
     * 行走速度值,每服务帧行走的距离，单位像素
     * @var int
     */
    public $speed = 20;

    /**
     * @var Room
     */
    public $room = null;

    /**
     * @return bool
     */
    public function onEvent()
    {
        if(!parent::onEvent()) {
            return false;
        }

        if ($this->isDeath) {
            unset($this->room->actorList[$this->id]);
            Host::pushToAllUser('ActorDeath', ['id' => $this->id]);
            return false;
        }

        return true;
    }


    /**
     * 血量减少
     * @param $num
     */
    public function hpDecr($num)
    {
        $this->hp -= $num;
        if ($this->hp < 1) {
            $this->hp = 0; //防止为负数
            $this->isDeath = true;
        }

        Host::pushToAllUser('ActorHpDecr', $this->getHpChangeData($num));
    }

    /**
     * 血量增加
     * @param $num
     */
    public function hpIncr($num)
    {
        if ($this->isDeath) { //如果彻底死了，不能再被加血
            return;
        }

        $this->hp += $num;
        if ($this->hp > $this->hpMax) {
            $this->hp = $this->hpMax;
        }


        Host::pushToAllUser('ActorHpIncr', $this->getHpChangeData($num));
    }

    /**
     * @param $dir
     */
    public function moveDis($dir)
    {
        $this->dir = $dir;
        list($dx, $dy) = self::$dirVectorList[$dir];
        $this->x += $this->speed * $dx;
        $this->y += $this->speed * $dy;

        //防止出界
        Util::between($this->x, 0, Room::MAP_WEIGHT);
        Util::between($this->y, 0, Room::MAP_HEIGHT);

        //Logger::debug(__METHOD__, $this->id, $this->dir, $this->x, $this->y);
    }

    /**
     * @param $num
     * @return array
     */
    protected function getHpChangeData($num)
    {
        return [
            'id' => $this->id,
            'hp' => $this->hp,
            'hpMax' => $this->hpMax,
            'num' => $num,
        ];
    }
}
