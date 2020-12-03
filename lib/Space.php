<?php
/**
 * @example  https://china-news.net/game/play/1001-php-tank
 * @author  一曲小诗 (QQ:81769876)
 * @contact  81769876@qq.com
 * @copyright your code
 */

/**
 * 场地空间类，所有的actor
 */
class Space extends Obj
{
    /**
     * 当前空间活跃的实体列表
     * @var Actor[]
     */
    public $actorList = [];

    /**
     * 当前空间活跃的角色列表
     * @var array
     */
    public $roleList = [];
}