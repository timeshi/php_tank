<?php
/**
 * @example  https://china-news.net/game/play/1001-php-tank
 * @author  一曲小诗 (QQ:81769876)
 * @contact  81769876@qq.com
 * @copyright your code
 */

/**
 * 机器人tank
 */
class Robot extends User
{
    /**
     * 移动步数
     * @var int
     */
    private $moveStep = 5;

    /**
     * @return bool|void
     */
    public function onEvent()
    {
        parent::onEvent();

        //开启移动
        $this->doMove();

        //射击
        $this->doFire();
    }

    /**
     * 移动
     */
    private function doMove()
    {
        if (Timer::$index % 2 > 0) {
            return;
        }

        if ($this->moveStep == 0) {
            $this->dir = mt_rand(1, 4);
            $this->moveStep = mt_rand(3, 8);
        }

        $this->move($this->dir);

        //移动方向减1
        $this->moveStep --;
    }


    /**
     * 开枪
     */
    private function doFire()
    {
        if (Timer::$index % 20 > 0) {
            return;
        }

        //$dir = mt_rand(1, 4);
        $this->fire();
    }
}