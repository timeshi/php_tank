<?php
/**
 * @example  https://china-news.net/game/play/1001-php-tank
 * @author  一曲小诗 (QQ:81769876)
 * @contact  81769876@qq.com
 * @copyright your code
 */

/**
 * 快捷调试类
 */
class Debug
{
    /**
     * 快速执行php代码，然后返回执行结果的字符串
     * @param $phpCode
     * @return false|string
     */
    public static function execPhpCode($phpCode)
    {
        //获取脚本执行内容
        ob_start();
        try {
            $phpCode = $phpCode . ';';
            eval($phpCode);
            $result = ob_get_contents(); //执行成功
        } catch (Throwable $e) { //执行失败
            $result = $e->getMessage();
        }
        ob_end_clean();
        return $result;
    }
}