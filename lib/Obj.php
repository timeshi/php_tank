<?php
/**
 * @example  https://china-news.net/game/play/1001-php-tank
 * @author  一曲小诗 (QQ:81769876)
 * @contact  81769876@qq.com
 * @copyright your code
 */

/**
 * 所有类的基类
 * 所有属性都用protected类型，保证父类的函数能调用子类的函数，方便varDump与gc回收
 */
class Obj
{
    /**
     * 生成一个超大的自增id,用于全局对象唯一的标示
     * @return int
     */
    public static function createUniqueId()
    {
        static $id = 1000 * 1000 * 1000 * 100; //一百亿
        $id ++;
        return $id;
    }

    /**
     * 对象的唯一标示
     * @var int
     */
    public $id = 0;

    /**
     * @var array
     */
    protected $afterTimerIdList = [];

    /**
     * 序列值
     * @var array
     */
    protected $seqNumList = [];

    /**
     * 对象自循环事件,每隔100毫秒运行一次,由Timer统一控制
     */
    public function onEvent()
    {
        return true;
    }

    /**
     * 获取一个序列值,每次获取,都能得到一个不同的数,循环重复获取
     * 最小是0,最大是比当前值小于1
     * @param int $num
     * @param string $key
     * @return int $num
     */
    public function getSeqNum($num, $key = 'key')
    {
        $index = $key . $num;

        if (isset($this->seqNumList[$index])) {
            $this->seqNumList[$index]++;
            if ($this->seqNumList[$index] >= $num) {
                $this->seqNumList[$index] = 0;
            }
        } else {
            $this->seqNumList[$index] = 0;
        }

        return $this->seqNumList[$index];
    }

    /**
     * 添加将要执行的方法
     * @param int $second 秒数,可以为负数,这里会把他转为1毫秒马上执行
     * @param callable $method 将要执行的方法名
     * @param null $param 方法参数
     * @return int 定时器句柄的索引
     */
    public function addToAfterTimer($second, $method, $param = null)
    {
        //计算毫秒数
        $ms = $second * 1000;

        //不能小于1
        if ($ms < 1) {
            $ms = 1;
        }

        $idIndex = self::createUniqueId();
        $this->afterTimerIdList[$idIndex] = swoole_timer_after($ms, function ($paramList) {
            try {
                list($idIndex, $method, $param) = $paramList;
                unset($this->afterTimerIdList[$idIndex]);
                if ($param === null) { //如果不设置$param, 可以使用函数的默认值,如果设置值,则会覆盖原方法的默认值
                    $this->$method();
                } else {
                    $this->$method($param);
                }
            } catch (Throwable $e) { //一般异常错误
                Logger::logException($e);
            }
        }, [$idIndex, $method, $param]);

        return $idIndex;
    }

    /**
     * 手工移除延后定时器
     * @param $idIndex
     */
    public function removeAfterTimer($idIndex)
    {
        if (isset($this->afterTimerIdList[$idIndex])) {
            swoole_timer_clear($this->afterTimerIdList[$idIndex]);
            unset($this->afterTimerIdList[$idIndex]);
        }
    }

    /**
     * 把一个对象放入销毁队列里
     */
    public function destroy()
    {
        $this->addToAfterTimer(1, 'gc');
    }

    /**
     * 手工gc,删除所有属性
     */
    public function gc()
    {
        //1.判断对象是否已经被销毁,如果已销毁，直接返回
        if ($this->isGc()) {
            return;
        }

        //2.先删除未执行的定时器
        foreach ($this->afterTimerIdList as $timerId) {
            swoole_timer_clear($timerId);
        }

        //3.遍历属性,然后删除 PHP自带垃圾回收，暂时注释TODO
        foreach (get_object_vars($this) as $key => $var) {
            unset($this->$key);
        }
    }

    /**
     * 是否回收过了(如果id不存在,说明被删除了,也就是gc了)
     */
    public function isGc()
    {
        return !isset($this->id);
    }

    /**
     * 是否没有被gc,还存在
     */
    public function isNotGc()
    {
        return isset($this->id);
    }

    /**
     * 只打印唯一属性,避免直接使用var_dump造成递归引用
     */
    public function varDump()
    {
        echo "====== " . get_class($this) . " ======\n";
        foreach (get_object_vars($this) as $key => $var) {

            if (is_object($var)) {
                $var = get_class($var);
            } elseif (is_array($var)) {
                $var = 'arrLen=' . count($var);
            } elseif (is_bool($var)) {
                $var = $var ? 'bool(true)' : 'bool(false)';
            }

            echo "$key => $var \n";
        }
    }
}