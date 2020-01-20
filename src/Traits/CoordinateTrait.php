<?php

namespace Sevming\Helper\Traits;

trait CoordinateTrait
{
    /**
     * 计算两点之间的距离
     *
     * @param float $lng1 出发地经度
     * @param float $lat1 出发地纬度
     * @param float $lng2 目的地经度
     * @param float $lat2 目的地纬度
     * @param int $type 1:公里,2:米
     *
     * @return int
     */
    public static function getLonLatDistance($lng1, $lat1, $lng2, $lat2, $type = 1)
    {
        $PI = 3.1415926535898;
        $earthRadius = 6378.137;
        $radLat1 = $lat1 * ($PI / 180);
        $radLat2 = $lat2 * ($PI / 180);
        $a = $radLat1 - $radLat2;
        $b = ($lng1 * ($PI / 180)) - ($lng2 * ($PI / 180));
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
        $s = $s * $earthRadius;
        $s = round($s * 1000);

        return $type == 1 ? ($s / 1000) : $s;
    }

    /**
     * 计算范围
     *
     * @param float $lng 中心点经度
     * @param float $lat 中心点纬度
     * @param float $radius 范围(单位:米)
     *
     * @return array
     */
    public static function getLonLatRange($lng, $lat, $radius)
    {
        $PI = 3.1415926535898;
        // 计算纬度
        $degree = (24901 * 1609) / 360.0;
        $dpmLat = 1 / $degree;
        $radiusLat = $dpmLat * $radius;
        $minLat = $lat - $radiusLat;    // 得到最小纬度
        $maxLat = $lat + $radiusLat;    // 得到最大纬度
        // 计算经度
        $mpdLon = $degree * cos($lat * ($PI / 180));
        $dpmLon = 1 / $mpdLon;
        $radiusLon = $dpmLon * $radius;
        $minLon = $lng - $radiusLon;    // 得到最小经度
        $maxLon = $lng + $radiusLon;    // 得到最大经度
        // 范围
        $range = [
            'minLat' => $minLat,
            'maxLat' => $maxLat,
            'minLon' => $minLon,
            'maxLon' => $maxLon
        ];

        return $range;
    }

    /**
     * 火星坐标系(GCJ-02) 转换 百度坐标系(BD-09)
     *
     * @param float $lng 经度
     * @param float $lat 纬度
     *
     * @return array
     */
    public static function gcj02ToBd09($lng, $lat)
    {
        $XPI = 3.14159265358979324 * 3000.0 / 180.0;
        $z = sqrt($lng * $lng + $lat * $lat) + 0.00002 * sin($lat * $XPI);
        $theta = atan2($lat, $lng) + 0.000003 * cos($lng * $XPI);

        return [
            'lat' => $z * sin($theta) + 0.006,
            'lng' => $z * cos($theta) + 0.0065,
        ];
    }

    /**
     * 百度坐标系(BD-09) 转换 火星坐标系(GCJ-02)
     *
     * @param float $lng 经度
     * @param float $lat 纬度
     *
     * @return array
     */
    public static function bd09ToGcj02($lng, $lat)
    {
        $XPI = 3.14159265358979324 * 3000.0 / 180.0;
        $lng = (double)$lng - 0.006;
        $lat = (double)$lat - 0.0065;
        $z = sqrt($lat * $lat + $lng * $lng) - 0.00002 * sin($lng * $XPI);
        $theta = atan2($lng, $lat) - 0.000003 * cos($lat * $XPI);

        return [
            'lat' => $z * sin($theta),
            'lng' => $z * cos($theta),
        ];
    }
}