<?php
/**
 * @example  https://china-news.net/game/play/1001-php-tank
 * @author  一曲小诗 (QQ:81769876)
 * @contact  81769876@qq.com
 * @copyright your code
 */
posix_setuid(99); //设置nobody用户执行
ini_set('memory_limit', '1024M');
error_reporting(E_ALL); //打开全部错误,不允许有隐藏警告

/**
 * 定义文本常量:框架程序的根目录
 */
define('ROOT', __DIR__ . '/');

/**
 * 定义文本常量:常用类目录
 */
define('ROOT_LIB', ROOT . 'lib/');

/**
 * 定义文本常量:常用类目录
 */
define('ROOT_WWW', ROOT . 'www/');


/**
 * 当前命令行是否有debug字符串，如果有是debug模式
 */
define('ROOT_LOG', '/tmp/php_tank_log_');

/**
 * 定义文本常量:反斜线
 */
define('BACKSLASH', '\\');

/**
 * 定义文本常量:换行符
 */
define('NL', "\n");

/**
 * 定义文本常量:中国时间格式模板
 */
define('TIME_CHINA_TPL', 'Y-m-d H:i:s');

/**
 * 当前主机名称
 */
define('HOST_NAME', gethostname());


//系统初始化:把所有错误转化为异常
set_error_handler(function($errno, $message) {
    throw new Exception($message, $errno);
});

//系统初始化:自定义类加载函数，在类未引用的时候，调用该函数，延迟加载
spl_autoload_register(function($className) {
    $className = str_replace('_', '/', $className);
    $file = ROOT_LIB . $className . '.php';
    if (is_file($file)) {
        require $file;
    }
});
