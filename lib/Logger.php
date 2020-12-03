<?php
/**
 * @example  https://china-news.net/game/play/1001-php-tank
 * @author  一曲小诗 (QQ:81769876)
 * @contact  81769876@qq.com
 * @copyright your code
 */

/**
 * 记录错误日志
 */
class Logger
{
    /**
     * 记录调试日志,上线的时候,自动关闭
     * @param string $tag
     * @param mixed $var1
     */
    public static function debug($tag, $var1)
    {
        if (!IS_DEBUG) { //线上环境,不输出debug日志
            return;
        }
        self::log(__FUNCTION__, func_get_args());
    }

    /**
     * 记录正常日志
     * @param string $tag
     * @param mixed $var1
     */
    public static function info($tag, $var1)
    {
        self::log(__FUNCTION__, func_get_args());
    }

    /**
     * 记录错误日志
     * @param string $tag
     * @param mixed $var1
     */
    public static function error($tag, $var1)
    {
        self::log(__FUNCTION__, func_get_args());
        self::log('info', func_get_args()); //错误日志同时记录到info文件里，方便查看
    }

    /**
     * 记录异常日志
     * @param Exception $e
     */
    public static function logException($e)
    {
        $content = '-----:' . date("H:i:s") . NL;
        $content .= $e->getTraceAsString() . NL;
        $content .= '=====' . NL;
        self::error('Exception', $content); //异常记录到错误日志里
    }

    /**
     * 记录日志
     * @param string $level
     * @param $varList
     * @internal param $data
     */
    private static function log($level, $varList)
    {
        $content = date("H:i:s");
        foreach ($varList as $var) {
            if (is_scalar($var)) {
                if (is_bool($var)) {
                    $str = $var ? 'true' : 'false';
                } elseif (is_null($var)) {
                    $str = 'null';
                } else {
                    $str = $var;
                }
            } else {
                $str = Json::encode($var);
            }

            //空字符串
            if ($str === '') {
                $str ='empty(string)';
            }

            //删除日志里的换行符与tab符号
            $str = str_replace(["\n", "\t"], ['', ''], $str);

            //附加到字符串
            $content .= "\t" . $str;
        }

        //尾部加上换行符号
        $content .= NL;

        //在命令行输出日志
        if (IS_DEBUG) {
            echo $content;
        }

        $file = ROOT_LOG . date("Y-m-d") . '-' . $level . '.log';
        file_put_contents($file, $content, FILE_APPEND);
    }
}
