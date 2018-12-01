<?php

namespace Sevming\Helper\Traits;

trait DataProcessTrait
{
    /**
     * URL拼接域名
     *
     * @param string $url
     * @param string $domain
     *
     * @return string
     */
    public static function urlSplicingDomain($url, $domain)
    {
        if (empty($url)) return '';

        if (stripos($url, 'http://') !== false || stripos($url, 'https://') !== false) {
            return $url;
        }

        return $domain . $url;
    }

    /**
     * 隐藏部分字符串
     *
     * @param string $string
     * @param int $startLen 前面几位
     * @param int $endLen 后面几位
     * @param string $hideStr 隐藏的字符串
     *
     * @return string
     */
    public static function hidePartOfString($string, $startLen = 3, $endLen = 4, $hideStr = '**')
    {
        $hide = '';
        $length = mb_strlen($string);
        if ($length == 1) {
            $hide .= $string . $hideStr;
        } else {
            $hide .= mb_substr($string, 0, $startLen, 'UTF-8') . $hideStr;
            $hide .= mb_substr($string, $length - $endLen, $endLen, 'UTF-8');
        }

        return $hide;
    }

    /**
     * 过滤Emoji表情
     *
     * @param string $str
     *
     * @return string
     */
    public static function filterEmoji($str)
    {
        $str = preg_replace_callback('/./u', function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        }, $str);

        return $str;
    }

    /**
     * 检测字符串是否包含Emoji表情
     *
     * @param string $str
     *
     * @return bool
     */
    public static function isContainsEmoji($str)
    {
        $newStr = static::filterEmoji($str);
        if (mb_strlen($newStr, 'UTF-8') == mb_strlen($str, 'UTF-8')) {
            return false;
        }

        return true;
    }

    /**
     * 字符串格式化
     *
     * @param string $string
     *
     * @return string
     */
    public static function stringFormat($string)
    {
        // 删除不可见的字符
        return preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $string);
    }

    /**
     * Null converts to empty string.
     *
     * @param array $params
     *
     * @return array|string
     */
    public static function nullConvertsToEmptyString($params)
    {
        if (is_array($params)) {
            foreach ($params as $key => $value) {
                if (is_array($value)) {
                    $params[$key] = static::nullConvertsToEmptyString($value);
                } elseif (is_null($value)) {
                    $params[$key] = '';
                }
            }

            return $params;
        }

        return is_null($params) ? '' : $params;
    }

    /**
     * 验证请求参数是否存在
     *
     * @param array $requestData 参数数组
     * @param array $require 需要校验的 key 数组
     * @param array $ignore 校验忽略的 key 数组
     *
     * @return array|bool
     */
    public static function paramsValidate($requestData, $require = [], $ignore = [])
    {
        $requireArray = [];
        $ignoreArray = [];
        foreach ($require as $value) {
            if (!isset($requestData[$value]) || ($requestData[$value] !== 0 && $requestData[$value] !== '0' && empty($requestData[$value]))) {
                return false;
            }

            $requireArray[$value] = $requestData[$value];
        }

        foreach ($ignore as $value) {
            $ignoreArray[$value] = $requestData[$value] ?? null;
        }

        return array_merge($requireArray, $ignoreArray);
    }

    /**
     * 验证参数是否全为正数
     *
     * @param array $requestData 参数数组
     * @param array $require 需要校验的 key 数组
     * @param array $ignore 校验忽略的 key 数组
     * @param bool $positiveFlag 验证参数是否大于0
     *
     * @return bool|array
     */
    public static function paramsPositiveValidate($requestData, $require = [], $ignore = [], $positiveFlag = false)
    {
        $requireArray = [];
        foreach ($require as $item) {
            $value = $requestData[$item] ?? null;
            if (!is_numeric($value) || $value < 0 || ($positiveFlag && $value == 0)) {
                return false;
            }

            $requireArray[$item] = $requestData[$item];
        }

        $ignoreArray = array_intersect_key($requestData, array_flip($ignore));

        return array_merge($requireArray, $ignoreArray);
    }

    /**
     * 检测是否是正整数数字或者正整数字符串
     *
     * @param int|string|array $param
     *
     * @return bool
     */
    public static function isPositiveInteger($param)
    {
        $validator = function ($var) {
            if ((is_int($var) || ctype_digit($var)) && $var > 0) {
                return true;
            }

            return false;
        };

        if (is_array($param)) {
            foreach ($param as $key => $value) {
                $bool = $validator($value);
                if (!$bool) {
                    return false;
                }
            }
        }

        return $validator($param);
    }

    /**
     * JSON字符串校验
     *
     * @param string $jsonStr
     *
     * @return false|array
     */
    public static function jsonValidate($jsonStr)
    {
        $result = json_decode($jsonStr, true);
        return (json_last_error() == JSON_ERROR_NONE) ? $result : false;
    }

    /**
     * 无限级分类(一维数组)
     *
     * @param array $data
     * @param array $fieldNameArr
     * @param int $pid
     * @param int $level
     * @param int $showLevel
     *
     * @return array
     */
    public static function getTree(&$data, $fieldNameArr = ['pid', 'id', 'name'], $pid = 0, $level = 0, $showLevel = 3)
    {
        if ($level >= $showLevel) {
            return $data['new'];
        }

        if (!isset($data['old'])) {
            $data = [
                'old' => $data,
                'new' => []
            ];
        }

        foreach ($data['old'] as $k => $v) {
            if ($v[$fieldNameArr[0]] == $pid) {
                $v['level'] = $level;
                $data['new'][] = $v;
                unset($data['old'][$k]);
                self::getTree($data, $fieldNameArr, $v[$fieldNameArr[1]], $level + 1, $showLevel);
            }
        }

        return $data['new'];
    }

    /**
     * 无限级分类(多维数组)
     *
     * @param array $data
     * @param array $fieldNameArr
     *
     * @return array
     */
    public static function getMultiTree($data, $fieldNameArr = ['pid', 'id'])
    {
        $dataArr = [];
        foreach ($data as $d) {
            $dataArr[$d[$fieldNameArr[1]]] = $d;
            $dataArr[$d[$fieldNameArr[1]]]['child'] = array();
        }
        foreach ($dataArr as $k => $v) {
            if ($v[$fieldNameArr[0]] != 0) {
                $dataArr[$v[$fieldNameArr[0]]]['child'][$v[$fieldNameArr[1]]] = &$dataArr[$v[$fieldNameArr[1]]];
            }
        }
        foreach ($dataArr as $tk => $tv) {
            if ($tv[$fieldNameArr[0]] != 0 || !isset($tv[$fieldNameArr[0]])) {
                unset($dataArr[$tk]);
            }
        }

        return $dataArr;
    }

    /**
     * 验证字符串是否序列化
     *
     * @param string $string
     * @param bool $strict
     *
     * @return bool
     */
    public static function validateSerialized($string, $strict = true)
    {
        if (!is_string($string)) {
            return false;
        }

        $string = trim($string);
        if ('N;' == $string) {
            return true;
        }

        if (strlen($string) < 4) {
            return false;
        }

        if (':' !== $string[1]) {
            return false;
        }

        if ($strict) {
            $lastc = substr($string, -1);
            if (';' !== $lastc && '}' !== $lastc) {
                return false;
            }
        } else {
            $semicolon = strpos($string, ';');
            $brace = strpos($string, '}');
            if (false === $semicolon && false === $brace) return false;
            if (false !== $semicolon && $semicolon < 3) return false;
            if (false !== $brace && $brace < 4) return false;
        }

        $token = $string[0];
        switch ($token) {
            case 's' :
                if ($strict) {
                    if ('"' !== substr($string, -2, 1)) {
                        return false;
                    }
                } elseif (false === strpos($string, '"')) {
                    return false;
                }
            case 'a' :
            case 'O' :
                return (bool)preg_match("/^{$token}:[0-9]+:/s", $string);
            case 'b' :
            case 'i' :
            case 'd' :
                $end = $strict ? '$' : '';
                return (bool)preg_match("/^{$token}:[0-9.E-]+;$end/", $string);
        }

        return false;
    }
}