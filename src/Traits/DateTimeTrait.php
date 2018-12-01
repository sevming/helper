<?php

namespace Sevming\Helper\Traits;

use \DateTime, \DateTimeZone;
use \InvalidArgumentException;

trait DateTimeTrait
{
    /**
     * 获取DateTime对象
     *
     * @param string|null $time
     *
     * @return DateTime
     *
     * @throws InvalidArgumentException
     */
    public static function getDateTime($time = null)
    {
        $dateTime = $time ?? 'now';
        $dateTime = new DateTime($time);

        if (!($dateTime instanceof DateTime)) {
            throw new \InvalidArgumentException('Invalid "DateTime" object');
        }

        return $dateTime;
    }

    /**
     * 获取时间戳
     *
     * @param string|null $time
     *
     * @return int
     */
    public static function getTimestamp($time = null)
    {
        return static::getDateTime($time)->getTimestamp();
    }

    /**
     * 获取当前时间
     *
     * @param bool $microsecond
     * @param string $format
     *
     * @return string
     */
    public static function getCurrentTime($microsecond = false, $format = 'Y-m-d H:i:s')
    {
        if ($microsecond) {
            $timeZone = new DateTimeZone(date_default_timezone_get() ? : 'Asia/Shanghai');
            $ds = DateTime::createFromFormat('U.u', microtime(true))->setTimezone($timeZone);

            return $ds->format('Y-m-d H:i:s.u');
        }

        return static::getDateTime()->format($format);
    }

    /**
     * 格式化时间
     *
     * @param null|string $time
     * @param string $format
     *
     * @return false|DateTime
     */
    public static function createFromFormat($time = null, $format = 'Y-m-d')
    {
        $time = $time ?? date($format);
        return DateTime::createFromFormat($format, $time);
    }

    /**
     * 时间是否在指定区间,只能判断时间格式如：17:00:00
     *
     * @param string|DateTime $from 开始时间
     * @param string|DateTime $to 结束时间
     * @param string|DateTime $input 给定日期
     *
     * @return bool
     */
    public static function timeIsBetween($from, $to, $input = '')
    {
        if (!($from instanceof DateTime)) {
            $from = static::getDateTime($from);
        }

        if (!($to instanceof DateTime)) {
            $to = static::getDateTime($to);
        }

        if (!($input instanceof DateTime)) {
            $input = static::getDateTime($input);
        }

        $from = DateTime::createFromFormat('!H:i:s', $from->format('H:i:s'));
        $to = DateTime::createFromFormat('!H:i:s', $to->format('H:i:s'));
        $input = DateTime::createFromFormat('!H:i:s', $input->format('H:i:s'));

        if ($from > $to) {
            $to->modify('+1 day');
        }

        return ($from <= $input && $input <= $to) || ($from <= $input->modify('+1 day') && $input <= $to);
    }

    /**
     * 友好的时间显示
     *
     * size = 1, 显示1位, 如: 1年、3个月、5天、20小时...
     * size = 2, 显示2位, 如: 1年1个月、1年3天、5天4小时、2小时25分...
     * size = 3, 显示3位, 如: 1年1个月4天、1年3天20小时、5天4小时3秒、2小时25分10秒...
     * size = 4, 显示4位, 如: 1年1个月4天16小时...
     * size >= 5,显示5位, 如: 1年1个月4天16小时15分钟...
     *
     * @param string|DateTime $datetime 日期字符串或日期 DateTime 对象
     * @param int $size 精确到位数
     * @param bool $absolute 正数的时间间隔
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    public static function friendlyDate($dateTime, $size = 1, $absolute = false)
    {
        if (!($dateTime instanceof DateTime)) {
            $dateTime = static::getDateTime($dateTime);
        }

        $now = static::getDateTime();
        if ($absolute && $dateTime <= $now) {
            return '';
        }

        $interval = $now->diff($dateTime);
        $intervalData = [
            $interval->y, $interval->m, $interval->d,
            $interval->h, $interval->i, $interval->s,
        ];
        $intervalFormat = ['年', '个月', '天', '小时', '分钟', '秒'];

        foreach ($intervalData as $key => $value) {
            if ($value) {
                $intervalData[$key] = $value . $intervalFormat[$key];
            } else {
                unset($intervalData[$key]);
                unset($intervalFormat[$key]);
            }
        }

        return implode('', array_slice($intervalData, 0, $size));
    }

    /**
     * 计算两个时间差
     *
     * @param string|DateTime $time1
     * @param string|DateTime $time2
     *
     * @return array
     */
    public static function timeDiff($time1, $time2)
    {
        if (!($time1 instanceof DateTime)) {
            $time1 = static::getDateTime($time1);
        }

        if (!($time2 instanceof DateTime)) {
            $time2 = static::getDateTime($time2);
        }

        $interval = $time1->diff($time2);

        return [
            'year' => $interval->y,
            'month' => $interval->m,
            'day' => $interval->d,
            'hour' => $interval->h,
            'min' => $interval->i,
            'sec' => $interval->s,
        ];
    }
}