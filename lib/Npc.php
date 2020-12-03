<?php
/**
 * @example  https://china-news.net/game/play/1001-php-tank
 * @author  一曲小诗 (QQ:81769876)
 * @contact  81769876@qq.com
 * @copyright your code
 */

/**
 * 非用户角色总类
 */
class Npc extends Actor
{
    public static function getAutoId()
    {
        static $id = 1001;
        $id++;
        if ($id > 999998) {
            $id = 1001;
        }
        return $id;
    }
}