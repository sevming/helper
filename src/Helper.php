<?php

namespace Sevming\Helper;

use Sevming\Support\Traits\{
    DataProcessTrait, DateTimeTrait, FilesystemTrait, CoordinateTrait
};

class Helper
{
    use DataProcessTrait, DateTimeTrait, FilesystemTrait, CoordinateTrait;

    /**
     * Decimals on price.
     *
     * @param mixed $number
     * @param bool $removeExcessZero 是否去除小数点后多余的0
     * @param int $scale This optional parameter is used to set the number of digits after the decimal place in the result
     *
     * @return string
     */
    public static function decimalFormat($number, $removeExcessZero = false, $scale = 2)
    {
        $number = bcadd($number, 0, $scale);
        if ($removeExcessZero) {
            $number = (string)(float)$number;
        }

        return $number;
    }

    /**
     * Subtract one arbitrary precision number from another.
     *
     * @param mixed $leftOperand The left operand
     * @param mixed $rightOperand The right operand
     * @param int $scale This optional parameter is used to set the number of digits after the decimal place in the result
     *
     * @return string
     */
    public static function decimalSub($leftOperand, $rightOperand, $scale = 2)
    {
        return bcsub($leftOperand, $rightOperand, $scale);
    }

    /**
     * Add two arbitrary precision numbers.
     *
     * @param mixed $leftOperand The left operand
     * @param mixed $rightOperand The right operand
     * @param int $scale This optional parameter is used to set the number of digits after the decimal place in the result
     *
     * @return string
     */
    public static function decimalAdd($leftOperand, $rightOperand, $scale = 2)
    {
        return bcadd($leftOperand, $rightOperand, $scale);
    }

    /**
     * Multiply two arbitrary precision numbers.
     *
     * @param mixed $leftOperand The left operand
     * @param mixed $rightOperand The right operand
     * @param int $scale This optional parameter is used to set the number of digits after the decimal place in the result
     *
     * @return string
     */
    public static function decimalMul($leftOperand, $rightOperand, $scale = 2)
    {
        return bcmul($leftOperand, $rightOperand, $scale);
    }

    /**
     * Divide two arbitrary precision numbers.
     *
     * @param mixed $dividend The dividend
     * @param mixed $divisor The divisor
     * @param int $scale This optional parameter is used to set the number of digits after the decimal place in the result
     *
     * @return string
     */
    public static function decimalDiv($dividend, $divisor, $scale = 2)
    {
        return bcdiv($dividend, $divisor, $scale);
    }

    /**
     * Compare two arbitrary precision numbers
     *
     * @param mixed $leftOperand The left operand
     * @param mixed $rightOperand The right operand
     * @param int $scale The optional scale parameter is used to set the number of digits after the decimal place which will be used in the comparison
     *
     * @return string
     */
    public static function decimalComp($leftOperand, $rightOperand, $scale = 2)
    {
        return bccomp($leftOperand, $rightOperand, $scale);
    }

    /**
     * Generate a more truly "random" alpha-numeric string.
     *
     * @param int $length
     *
     * @return string
     *
     * @throws \Exception
     */
    public static function generateRandom($length = 16)
    {
        $string = '';
        while (($len = strlen($string)) < $length) {
            $size = $length - $len;
            $bytes = random_bytes($size);
            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }

    /**
     * Generate token.
     *
     * @return string
     */
    public static function generateToken()
    {
        return md5(uniqid(md5(microtime(true)),true));
    }

    /**
     * Generate message id.
     *
     * @param string $prefix
     *
     * @return string
     */
    public static function generateMessageId($prefix = '')
    {
        $time = date('YmdHis');
        list($usec, $sec) = explode(' ', microtime());
        $millisecondsStr = str_pad(intval($usec * 1000), 3, '0', STR_PAD_LEFT);
        $rand = mt_rand(1000, 9999);

        return $prefix . $time . $millisecondsStr . $rand;
    }

    /**
     * 客户端类型校验
     *
     * @param int $type 1:微信内置浏览器, 2:支付宝内置浏览器, 3:IOS, 4:Android
     *
     * @return bool
     */
    public static function clientTypeValidator($type)
    {
        $mobileFlag = static::isMobile();
        if ($mobileFlag) {
            $userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
            switch ($type) {
                // 微信内置浏览器
                case 1:
                    return strpos($userAgent, 'micromessenger') !== false;
                // 支付宝内置浏览器
                case 2:
                    return strpos($userAgent, 'aliapp') !== false;
                // IOS
                case 3:
                    return strpos($userAgent, 'iphone') !== false;
                // Android
                case 4:
                    return strpos($userAgent, 'android') !== false;
                default:
                    return false;
            }
        }

        return false;
    }

    /**
     * 判断请求来源是否手机端
     *
     * @return int
     */
    public static function isMobile()
    {
        // HTTP_X_WAP_PROFILE|VIA信息含有wap
        if (isset($_SERVER['HTTP_X_WAP_PROFILE']) || (isset($_SERVER['HTTP_VIA']) && stristr($_SERVER['HTTP_VIA'], 'wap'))) {
            return true;
        }

        $clientKeywords = [
            'nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone',
            'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave',
            'nexusone', 'cldc', 'midp', 'wap', 'mobile'
        ];
        if (preg_match("/(" . implode('|', $clientKeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }

        return false;
    }

    /**
     * Send request.
     *
     * @param string $url
     * @param array $params
     * @param string $method
     * @param array $header
     *
     * @return bool|mixed
     */
    public static function request($url, $params = [], $method = 'GET', $header = [])
    {
        if (!preg_match('/^(http|https)/is', $url)) {
            $url = 'http://' . $url;
        }

        $ch = curl_init();
        switch ($method) {
            case 'GET':
                $url .= !empty($params) ? '?' . http_build_query($params) : '';
                break;
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                break;
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $result = curl_exec($ch);
        $aStatus = curl_getinfo($ch);
        curl_close($ch);
        if (intval($aStatus['http_code']) == 200) {
            return $result;
        }

        return false;
    }
}