<?php
/**
 * Created by PhpStorm.
 * User: sMac
 * Date: 2018/4/11
 * Time: 12:21
 */
namespace app\logic;

use think\facade\Cache;

class SnowFlake {

    // (2021-08-01)
    const TWEPOCH = 1627776000000; // 时间起始标记点，作为基准，一般取系统的最近时间（一旦确定不能变动）

    const WORKER_ID_BITS     = 5; // 机器标识位数
    const DATACENTER_ID_BITS = 5; // 数据中心标识位数
    const SEQUENCE_BITS      = 12; // 毫秒内自增位

    private $workerId; // 工作机器ID
    private $datacenterId; // 数据中心ID
    private $sequence; // 毫秒内序列

    private $maxWorkerId     = -1 ^ (-1 << self::WORKER_ID_BITS); // 机器ID最大值
    private $maxDatacenterId = -1 ^ (-1 << self::DATACENTER_ID_BITS); // 数据中心ID最大值

    private $workerIdShift      = self::SEQUENCE_BITS; // 机器ID偏左移位数
    private $datacenterIdShift  = self::SEQUENCE_BITS + self::WORKER_ID_BITS; // 数据中心ID左移位数
    private $timestampLeftShift = self::SEQUENCE_BITS + self::WORKER_ID_BITS + self::DATACENTER_ID_BITS; // 时间毫秒左移位数
    private $sequenceMask       = -1 ^ (-1 << self::SEQUENCE_BITS); // 生成序列的掩码

    private $lastTimestamp = -1; // 上次生产id时间戳

    // 创建静态私有的变量保存该类对象
    static private $instance = null;
    // 静态调用生成 id
    public static function generateParticle()
    {
        return (string) self::getInstance()->nextId();
    }
    static public function getInstance()
    {
        //判断$instance是否是Singleton的对象，不是则创建
        if (!self::$instance instanceof self) {
            $cacheKey = "SnowFlake";
            $instance = Cache::get($cacheKey);
            if (!$instance) {
                $instance = new self(1, 1, 0);
                // 直接将此单例放入缓存
                Cache::set($cacheKey, $instance);
            }
            self::$instance = $instance;

        }
        return self::$instance;
    }

    public function __construct($workerId, $datacenterId, $sequence = 0)
    {
        if ($workerId > $this->maxWorkerId || $workerId < 0) {
            throw new \Exception("worker Id can't be greater than {$this->maxWorkerId} or less than 0");
        }

        if ($datacenterId > $this->maxDatacenterId || $datacenterId < 0) {
            throw new \Exception("datacenter Id can't be greater than {$this->maxDatacenterId} or less than 0");
        }

        $this->workerId     = $workerId;
        $this->datacenterId = $datacenterId;
        $this->sequence     = $sequence;
    }

    public function nextId()
    {
        $timestamp = $this->timeGen();

        if ($timestamp < $this->lastTimestamp) {
            $diffTimestamp = bcsub($this->lastTimestamp, $timestamp);
            throw new \Exception("Clock moved backwards.  Refusing to generate id for {$diffTimestamp} milliseconds");
        }

        if ($this->lastTimestamp == $timestamp) {
            $this->sequence = ($this->sequence + 1) & $this->sequenceMask;

            if (0 == $this->sequence) {
                $timestamp = $this->tilNextMillis($this->lastTimestamp);
            }
        } else {
            $this->sequence = 0;
        }

        $this->lastTimestamp = $timestamp;

        /*$gmpTimestamp    = gmp_init($this->leftShift(bcsub($timestamp, self::TWEPOCH), $this->timestampLeftShift));
        $gmpDatacenterId = gmp_init($this->leftShift($this->datacenterId, $this->datacenterIdShift));
        $gmpWorkerId     = gmp_init($this->leftShift($this->workerId, $this->workerIdShift));
        $gmpSequence     = gmp_init($this->sequence);
        return gmp_strval(gmp_or(gmp_or(gmp_or($gmpTimestamp, $gmpDatacenterId), $gmpWorkerId), $gmpSequence));*/

        return (($timestamp - self::TWEPOCH) << $this->timestampLeftShift) |
            ($this->datacenterId << $this->datacenterIdShift) |
            ($this->workerId << $this->workerIdShift) |
            $this->sequence;
    }

    protected function tilNextMillis($lastTimestamp)
    {
        $timestamp = $this->timeGen();
        while ($timestamp <= $lastTimestamp) {
            $timestamp = $this->timeGen();
        }

        return $timestamp;
    }

    protected function timeGen()
    {
        return floor(microtime(true) * 1000);
    }

    // 左移 <<
    protected function leftShift($a, $b)
    {
        return bcmul($a, bcpow(2, $b));
    }

//    const EPOCH = 128585824053;
//    const max12bit = 4095;
//    const max41bit = 1099511627775;
//
//    static $machineId = null;
//
//    public static function machineId($mId = 0) {
//        self::$machineId = $mId;
//    }
//
//    public static function generateParticle() {
//        /*
//        * Time - 42 bits
//        */
//        $time = floor(microtime(true) * 1000);
//
//        /*
//        * Substract custom epoch from current time
//        */
//        $time -= self::EPOCH;
//
//        /*
//        * Create a base and add time to it
//        */
//        $base = decbin(self::max41bit + $time);
//
//
//        /*
//        * Configured machine id - 10 bits - up to 1024 machines
//        */
//        if(!self::$machineId) {
//            $machineid = self::$machineId;
//        } else {
//            $machineid = str_pad(decbin(self::$machineId), 10, "0", STR_PAD_LEFT);
//        }
//
//        /*
//        * sequence number - 12 bits - up to 4096 random numbers per machine
//        */
//        $random = str_pad(decbin(mt_rand(0, self::max12bit)), 12, "0", STR_PAD_LEFT);
//
//        /*
//        * Pack
//        */
//        $base = $base.$machineid.$random;
//
//        /*
//        * Return unique time id no
//        */
//        $res = bindec($base);
//        return "$res";
//    }
//
//    public static function timeFromParticle($particle) {
//        /*
//        * Return time
//        */
//        return bindec(substr(decbin($particle),0,41)) - self::max41bit + self::EPOCH;
//    }
}


?>