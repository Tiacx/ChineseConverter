中文简繁体转换
============

说明
-----

功能：简繁体中文转换

说明：提取 [sqhlib/hanzi-convert](https://github.com/uutool/hanzi-convert) 包的繁体字，并使用 [opencc](https://github.com/BYVoid/OpenCC) 做转换，在此表示感谢。

支持的转换类型：s2t、s2tw、s2hk、t2s、tw2s、hk2s

安装
-------

```bash
composer require tiacx/chinese-converter
```

使用
-----

简繁转换

```php
use Tiacx\ChineseConverter;

// 简体转标准繁体
echo ChineseConverter::convert('心里', 's2t'), "\n";
// 简体转台湾繁体
echo ChineseConverter::convert('心里', 's2tw'), "\n";
// 简体转香港繁体
echo ChineseConverter::convert('心里', 's2hk'), "\n";
// 标准繁体转简体
echo ChineseConverter::convert('心裏', 't2s'), "\n";
// 台湾繁体转简体
echo ChineseConverter::convert('心裡', 'tw2s'), "\n";
// 香港繁体转简体
echo ChineseConverter::convert('心裏', 'hk2s'), "\n";
```

输出：

```
心裏
心裡
心裏
心里
心里
心里
```

获取全部简繁体

```php
use Tiacx\ChineseConverter;

print_r(ChineseConverter::convertGetAll('心里'));
print_r(ChineseConverter::convertGetAll('心裡'));
print_r(ChineseConverter::convertGetAll('心裏'));
```

输出：

```
Array
(
    [0] => 心里
    [2] => 心裏
    [3] => 心裡
)
Array
(
    [0] => 心裡
    [1] => 心里
    [2] => 心裏
)
Array
(
    [0] => 心裏
    [1] => 心里
    [3] => 心裡
)
```