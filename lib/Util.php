<?php
/**
 * @example  https://china-news.net/game/play/1001-php-tank
 * @author  一曲小诗 (QQ:81769876)
 * @contact  81769876@qq.com
 * @copyright your code
 */

/**
 * 通用类
 */
class Util
{
    /**
     * 设置一个数字在两个数字之间
     * @param number $num
     * @param number $min
     * @param number $max
     */
    public static function between(&$num, $min, $max)
    {
        if ($num < $min) {
            $num = $min;
        } elseif ($num > $max) {
            $num = $max;
        }
    }
}