<?php
/**
 * 验证类
 * User: Eric Chen
 * Date: 13-4-7
 * Time: 下午2:43
 */

class ValidatorHelp {
    /**
     * @param $email
     * @return boolean
     */
    static function email($email) {
        return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
    }

    /**
     * 验证是否是手机
     * @param $str
     * @return boolean
     */
    static function mobile($str) {
        return preg_match('/^1[3-9]\d{9}$/', $str);
    }

    static function username($str)
    {
        return self::email($str) || self::mobile($str);
    }

    static function isDigital($digi) {
    return preg_match('/^\d+$/', $digi);
}
}