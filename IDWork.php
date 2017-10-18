<?php
/**
 * GUID生成器
 * 核心是毫秒级时间41位+机器标识10位+毫秒内序列12位，结构如下：
 * 0-0000000000 0000000000 0000000000 0000000000 0-00000 00000-0000000000 00
 * 整体上按照时间自增排序，整个分布式系统内不会产生id碰撞(机器ID10位来区分)
 *
 * @authors hao
 * @email   hao.jingyang@163.com
 * @date    2017-10-18 10:05
 */
//namespace common\components\libraries;

class IDWork
{
    static $twepoch = 1508256000000; // 开始时间,指定一个小于当前时间的毫秒数即可;
    static $workId;
    static $workIdBits = 10; // 机器标识占的位数
    static $sequence = 0;
    static $sequenceBits = 12; // 毫秒内序列占的位数
    static $lastTimestamp = -1;

    public function __construct($workId)
    {
        $maxworkId = (1 << self::$workIdBits) - 1; // workIdBits=10时是1023
        if ($workId > $maxworkId || $workId < 0) {
            throw new Exception("worker Id can't be greater than $maxworkId or less than 0");
        }
        self::$workId = $workId;
    }

    // 取当前毫秒数
    private function timeGen()
    {
        return (float) sprintf('%.0f', microtime(true) * 1000);
    }

    // 取下一毫秒数
    private function tilNextMillis($lastTimestamp)
    {
        $timestamp = $this->timeGen();
        while ($timestamp <= $lastTimestamp) {
            $timestamp = $this->timeGen();
        }
        return $timestamp;
    }

    public function nextId()
    {
        $timestamp = $this->timeGen();

        // 判断时钟是否正常
        if ($timestamp < self::$lastTimestamp) {
            throw new Excwption("Clock moved backwards.  Refusing to generate id for " . (self::$lastTimestamp - $timestamp) . " milliseconds");
        }

        // 生成唯一序列
        if (self::$lastTimestamp == $timestamp) {
            $sequenceMask = (1 << self::$sequenceBits) - 1;
            self::$sequence = (self::$sequence + 1) & $sequenceMask;
            if (self::$sequence == 0) {
                $timestamp = $this->tilNextMillis(self::$lastTimestamp);
            }
        } else {
            self::$sequence = 0;
        }
        self::$lastTimestamp = $timestamp;

        $timestampLeftShift = self::$workIdBits + self::$sequenceBits;
        $nextId = (($timestamp - self::$twepoch) << $timestampLeftShift) | (self::$workId << self::$workIdBits) | self::$sequence;

        return $nextId;
    }

    /** End Of IDWork.php **/
}
