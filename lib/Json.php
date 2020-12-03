<?php
/**
 * @example  https://china-news.net/game/play/1001-php-tank
 * @author  一曲小诗 (QQ:81769876)
 * @contact  81769876@qq.com
 * @copyright your code
 */

/**
 * json类
 * 解析和反解析json字符串
 */
class Json
{
    /**
     * 生成一个json串
     *
     * @param array $array
     * @param bool $format 是否格式化js字符串
     *
     * @return string
     */
    public static function encode($array, $format = false)
    {
        $option = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        $format && $option = $option | JSON_PRETTY_PRINT;
        return json_encode($array, $option);
    }

    /**
     * 解析一个json串
     *
     * @param string $json
     * @param bool $assoc
     *
     * @return mixed
     */
    public static function decode($json, $assoc = true)
    {
        return json_decode($json, $assoc);
    }

    /**
     * 格式化json数据
     * 使用先encode,在decode的方式
     * @param string $json
     * @return mixed
     */
    public static function format($json)
    {
        $json = json_decode($json);
        $json = json_encode($json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        return $json;
    }
}