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
     * opencc 命令行工具是否可用（检测结果缓存）
     * @var bool|null
     */
    private static $openccAvailable = null;

    /**
     * opencc 配置目录（含尾分隔符），空串表示由 opencc 默认解析配置名
     * @var string
     */
    private static $openccConfigDir = '';

    /**
     * 简繁体转换
     * @param string $input 输入
     * @param string $type 转换类型，s2t、t2s、s2tw、s2hk 等
     * @return string        输出
     */
    public static function convert($input, $type)
    {
        $results = self::convertBatch(array($input), $type);
        return current($results);
    }

    /**
     * 批量转换，多次转换合并为一次 opencc 调用（opencc 进程启动开销约几十毫秒，批量可大幅提速）
     * @param array $inputs 文本数组，保持原有键
     * @param string $type 转换类型，s2t、t2s、s2tw、s2hk 等
     * @return array          转换结果，键与输入一一对应
     */
    public static function convertBatch($inputs, $type)
    {
        if (self::$openccAvailable === null) {
            self::$openccAvailable = self::openccAvailable();
        }
        if (self::$openccAvailable) {
            $outputs = self::convertByOpenccMulti($inputs, $type);
            if ($outputs !== false) {
                return $outputs;
            }
        }
        $results = array();
        foreach ($inputs as $key => $input) {
            $results[$key] = self::convertByDict($input, $type);
        }
        return $results;
    }

    /**
     * 检测 opencc 命令行工具是否可用
     * @return bool
     */
    private static function openccAvailable()
    {
        if (!function_exists('exec')) {
            return false;
        }
        $output = array();
        $code = 1;
        @exec('opencc --version 2>&1', $output, $code);
        if ($code !== 0) {
            return false;
        }
        self::$openccConfigDir = self::detectConfigDir();
        return true;
    }

    /**
     * 探测 opencc 配置目录
     * opencc 对 -c 配置名默认只按当前工作目录解析（部分 Windows 构建不支持内置查找），
     * 此处按可执行文件位置推断 <prefix>/share/opencc 布局，存在则改用绝对路径调用
     * @return string
     */
    private static function detectConfigDir()
    {
        $exeList = array();
        $code = 1;
        if (DIRECTORY_SEPARATOR === '\\') {
            @exec('where opencc 2>NUL', $exeList, $code);
        } else {
            @exec('command -v opencc 2>/dev/null', $exeList, $code);
        }
        if ($code !== 0 || empty($exeList)) {
            return '';
        }
        $configDir = dirname(dirname(trim($exeList[0]))) . DIRECTORY_SEPARATOR
            . 'share' . DIRECTORY_SEPARATOR . 'opencc' . DIRECTORY_SEPARATOR;
        return is_file($configDir . 's2t.json') ? $configDir : '';
    }

    /**
     * 使用 opencc 命令行工具批量转换，失败时返回 false
     * 多段文本按行拼接为一次调用（opencc 逐行转换，行数守恒），转换后按原行数切分
     * @param array $inputs
     * @param string $type
     * @return array|false
     */
    private static function convertByOpenccMulti($inputs, $type)
    {
        $inFile = tempnam(sys_get_temp_dir(), 'cc_in_');
        $outFile = tempnam(sys_get_temp_dir(), 'cc_out_');
        if ($inFile === false || $outFile === false) {
            return false;
        }
        try {
            $lineCounts = array();
            $joined = '';
            foreach ($inputs as $input) {
                $lineCounts[] = substr_count($input, "\n") + 1;
                $joined .= $input . "\n";
            }
            if (file_put_contents($inFile, $joined) === false) {
                return false;
            }
            // 删除预创建的输出文件，由 opencc 转换成功时自行创建，以此判断转换成败
            // （部分 opencc 构建失败时退出码仍为 0，不能依赖退出码判断）
            if (@unlink($outFile) !== true && file_exists($outFile)) {
                return false;
            }
            $config = self::$openccConfigDir === ''
                ? $type . '.json'
                : self::$openccConfigDir . $type . '.json';
            // bypass_shell 直接创建进程，跳过 cmd.exe 包装（Windows 下可节省约 20ms/次）
            $nullDev = DIRECTORY_SEPARATOR === '\\' ? 'NUL' : '/dev/null';
            $cmd = 'opencc -c ' . escapeshellarg($config)
                . ' -i ' . escapeshellarg($inFile)
                . ' -o ' . escapeshellarg($outFile);
            $descriptors = array(
                0 => array('pipe', 'r'),
                1 => array('file', $nullDev, 'w'),
                2 => array('file', $nullDev, 'w'),
            );
            $process = proc_open($cmd, $descriptors, $pipes, null, null, array('bypass_shell' => true));
            if (!is_resource($process)) {
                return false;
            }
            fclose($pipes[0]);
            proc_close($process);

            if (!is_file($outFile)) {
                return false;
            }
            $output = file_get_contents($outFile);
            if ($output === false) {
                return false;
            }
            // Windows 构建的 opencc 输出为 CRLF，归一为 LF 后按行切分
            $lines = explode("\n", rtrim(str_replace("\r\n", "\n", $output), "\n"));
            if (count($lines) !== array_sum($lineCounts)) {
                return false;
            }
            $results = array();
            $pos = 0;
            $keys = array_keys($inputs);
            foreach ($lineCounts as $i => $count) {
                $results[$keys[$i]] = implode("\n", array_slice($lines, $pos, $count));
                $pos += $count;
            }
            return $results;
        } finally {
            if ($inFile !== false) {
                @unlink($inFile);
            }
            if ($outFile !== false) {
                @unlink($outFile);
            }
        }
    }

    /**
     * 使用内置字典转换
     * @param string $input
     * @param string $type
     * @return string
     */
    private static function convertByDict($input, $type)
    {
        if (!isset(self::$hanziDict[$type])) {
            $file = __DIR__ . "/hanzi/{$type}.php";
            if (!is_file($file)) {
                throw new \Exception("不支持的转换类型【{$type}】。");
            }
            self::$hanziDict[$type] = require $file;
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
