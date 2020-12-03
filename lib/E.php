<?php
/**
 * @example  https://china-news.net/game/play/1001-php-tank
 * @author  一曲小诗 (QQ:81769876)
 * @contact  81769876@qq.com
 * @copyright your code
 */

/**
 * 快速排出异常
 */
class E extends Exception
{
    public static function out($msg, $code = 100)
    {
        throw new E($msg, $code);
    }
}