<?php
/**
 * @example  https://china-news.net/game/play/1001-php-tank
 * @author  一曲小诗 (QQ:81769876)
 * @contact  81769876@qq.com
 * @copyright your code
 */

/**
 * 全局定时器
 */
class Timer
{
    /**
     * 帧频率,单位毫秒
     */
    const FRAME_MS = 100;

    /**
     * 全局唯一时间索引号,每隔100毫秒增加1帧
     * 服务端的最小时间单位是100毫秒,也就是1帧
     * @var int
     */
    public static $index = 0;

    /**
     * 全局唯一的事件集合对象
     * @var Obj[]
     */
    public static $eventList = [];

    /**
     * 初始化全局session的环境
     */
    public static function initEnv()
    {
        //初始化统一的事件发生器
        swoole_timer_tick(self::FRAME_MS, function () {
            self::$index ++;
            try {
                foreach (self::$eventList as $id => $obj) {
                    $obj->onEvent();
                }
            } catch (Throwable $e) { //记录异常
                Logger::logException($e);
            }
        });
    }

    /**
     * 事件队列里添加一个对象
     * @param Obj $obj
     */
    public static function add($obj)
    {
        self::$eventList[$obj->id] = $obj;
    }

    /**
     * 事件队列里移除一个对象
     * @param Obj|int $objOrId
     */
    public static function remove($objOrId)
    {
        if (is_object($objOrId)) {
            $className = get_class($objOrId);
            $objOrId = $objOrId->id;
        } else {
            $className = 'noClass';
        }
        Logger::debug($className . ':' . $objOrId, __METHOD__);
        unset(self::$eventList[$objOrId]);
    }
}
