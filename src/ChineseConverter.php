<?php

namespace Tiacx;

if (!function_exists('mb_str_split')) {
    function mb_str_split($string, $length = 1) {
        $result = array();
        for ($i = 0; $i < mb_strlen($string); $i += $length) {
            $result[] = mb_substr($string, $i, $length);
        }
        return $result;
    }
}


/**
 * 简繁体转换
 */
class ChineseConverter
{
    public static $hanziDict = [];

    /**
     * 简繁体转换
     * @param string $input 输入
     * @param string $type 转换类型，s2t、t2s、s2tw、s2hk 等
     * @return string        输出
     */
    public static function convert($input, $type)
    {
        if (!isset(self::$hanziDict[$type])) {
            try {
                self::$hanziDict[$type] = require __DIR__ . "/hanzi/{$type}.php";
            } catch (\Exception $e) {
                throw new \Exception("不支持的转换类型【{$type}】。");
            }
        }

        $output = '';
        foreach (mb_str_split($input) as $hanzi) {
            $output .= isset(self::$hanziDict[$type][$hanzi]) ? self::$hanziDict[$type][$hanzi] : $hanzi;
        }
        return $output;
    }

    /**
     * 转换并获取全部简繁体汉字
     * @param string $input
     * @return array
     */
    public static function convertGetAll($input)
    {
        $tc = self::convert($input, 's2t');
        if ($tc == $input) {
            $sc = array_diff([
                self::convert($input, 'tw2s'),
                self::convert($input, 'hk2s'),
                self::convert($input, 't2s'),
            ], [$input]);
            $sc = !empty($sc) ? current($sc) : $input;
        } else {
            $sc = $input;
        }

        $results = [$input];
        $results[] = $sc;
        $results[] = self::convert($sc, 's2t');
        $results[] = self::convert($sc, 's2tw');
        $results[] = self::convert($sc, 's2hk');
        return array_values(array_unique($results));
    }
}
