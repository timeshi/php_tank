<?php
/**
 * @example  https://china-news.net/game/play/1001-php-tank
 * @author  一曲小诗 (QQ:81769876)
 * @contact  81769876@qq.com
 * @copyright your code
 */

/**
 * 处理udp传来的数据
 */
class Udp
{
    /**
     * 直接转发
     */
    const PACKET_FORWARD = 1;

    /**
     * 本地处理
     */
    const PACKET_ACT = 2;

    /**
     * 当接收到udp包的消息
     * @param $dataRaw
     */
    public static function onPacket($dataRaw)
    {
        list($type, $param, $data) = explode('|', $dataRaw, 3);
        if ($type == self::PACKET_FORWARD){ //直接转发
            Host::pushByFd($param, $data);
        } elseif ($type == self::PACKET_ACT) { //执行操作
            $method = $param;
            self::$method($data);
        }
    }

    /**
     * 服务端主动把某个连接的用户踢下线
     * @param $fd
     */
    public static function kickByFd($fd)
    {
        Host::kickByFd($fd);
    }

    /**
     * 响应其他服务器发来的请求
     */
    public static function reloadAllWorker()
    {
        Host::reloadAllWorker();
    }
}